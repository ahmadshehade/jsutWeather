<?php
// app/Services/WeatherDataProcessor.php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class WeatherDataProcessor
{
    /**
     * معالجة بيانات التوقعات وتجميعها حسب اليوم
     */
    public function processForecast(array $forecast): array
    {
        if (!isset($forecast['list'])) {
            return ['daily' => [], 'hourly' => []];
        }

        $groups = $this->groupByDay($forecast['list']);
        $dailySummaries = $this->buildDailySummaries($groups);
        $hourlySeries = $this->buildHourlySeries($forecast['list']);

        return [
            'daily' => $dailySummaries,
            'hourly' => $hourlySeries
        ];
    }

    /**
     * إثراء بيانات الطقس الحالي بمعلومات إضافية
     */
    public function enrichCurrentWeather(array $weather): array
    {
        if (empty($weather)) {
            return [];
        }

        // إضافة هبات الرياح
        $weather['wind']['gust'] = $weather['wind']['gust'] ?? null;

        // إضافة كمية الأمطار
        $weather['rain'] = $weather['rain'] ?? null;
        if (isset($weather['rain']['1h'])) {
            $weather['rain_volume'] = $weather['rain']['1h'];
        } elseif (isset($weather['rain']['3h'])) {
            $weather['rain_volume'] = $weather['rain']['3h'];
        }

        // إضافة الرؤية
        $weather['visibility_km'] = isset($weather['visibility'])
            ? round($weather['visibility'] / 1000, 1)
            : null;

        // إضافة الضغط الأرضي
        $weather['main']['grnd_level'] = $weather['main']['grnd_level']
            ?? $weather['main']['pressure']
            ?? null;

        return $weather;
    }

    /**
     * تجميع التوقعات حسب اليوم
     */
    private function groupByDay(array $forecastList): Collection
    {
        $groups = [];

        foreach ($forecastList as $item) {
            $date = Carbon::parse($item['dt_txt'])->format('Y-m-d');

            if (!isset($groups[$date])) {
                $groups[$date] = $this->initializeDayGroup();
            }

            $this->addForecastItemToGroup($groups[$date], $item);
        }

        return collect($groups);
    }

    /**
     * تهيئة مجموعة يوم جديد
     */
    private function initializeDayGroup(): array
    {
        return [
            'temps' => [],
            'humidities' => [],
            'icons' => [],
            'rain_probabilities' => [],
            'rain_volumes' => [],
            'wind_gusts' => [],
            'pressures' => [],
            'clouds' => []
        ];
    }

    /**
     * إضافة عنصر توقعات للمجموعة
     */
    private function addForecastItemToGroup(array &$group, array $item): void
    {
        // البيانات الأساسية
        $group['temps'][] = $item['main']['temp'] ?? 0;
        $group['humidities'][] = $item['main']['humidity'] ?? 0;
        $group['icons'][] = $item['weather'][0]['icon'] ?? null;

        // احتمالية الأمطار (الأهم!)
        $group['rain_probabilities'][] = $item['pop'] ?? 0;

        // كمية الأمطار
        $group['rain_volumes'][] = $this->extractRainVolume($item);

        // هبات الرياح
        $group['wind_gusts'][] = $item['wind']['gust'] ?? 0;

        // الضغط الجوي
        $group['pressures'][] = $item['main']['grnd_level'] ?? $item['main']['pressure'] ?? 0;

        // تغطية السحب
        $group['clouds'][] = $item['clouds']['all'] ?? 0;
    }

    /**
     * استخراج كمية الأمطار من العنصر
     */
    private function extractRainVolume(array $item): float
    {
        if (isset($item['rain']['3h'])) {
            return $item['rain']['3h'];
        }
        if (isset($item['rain']['1h'])) {
            return $item['rain']['1h'] * 3; // تقريب لـ3 ساعات
        }
        return 0;
    }

    /**
     * بناء الملخصات اليومية
     */
    private function buildDailySummaries(Collection $groups): array
    {
        $summaries = [];

        foreach (array_slice($groups->toArray(), 0, 8, true) as $date => $g) {
            $summaries[] = [
                'date' => $date,
                'label' => Carbon::parse($date)->translatedFormat('l d M'),
                'min' => round(min($g['temps'])),
                'max' => round(max($g['temps'])),
                'humidity' => round($this->average($g['humidities'])),
                'icon' => $g['icons'][0] ?? null,

                // 🌧 بيانات الطقس المتقدمة
                'rain_probability' => round(max($g['rain_probabilities']) * 100),
                'rain_volume' => round(array_sum($g['rain_volumes']), 1),
                'wind_gust' => round(max($g['wind_gusts']), 1),
                'pressure' => round($this->average($g['pressures'])),
                'clouds' => round($this->average($g['clouds'])),

                // مؤشرات مساعدة
                'will_rain' => max($g['rain_probabilities']) > 0.3,
                'heavy_rain' => max($g['rain_probabilities']) > 0.7,
                'stormy' => max($g['wind_gusts']) > 15
            ];
        }

        return $summaries;
    }

    /**
     * بناء بيانات الساعة
     */
    private function buildHourlySeries(array $forecastList, int $limit = 24): array
    {
        $series = [];
        $count = 0;

        foreach ($forecastList as $item) {
            if ($count >= $limit) break;

            $rainProb = ($item['pop'] ?? 0) * 100;
            $rainVolume = $item['rain']['3h'] ?? $item['rain']['1h'] ?? null;

            $series[] = [
                'time' => Carbon::parse($item['dt_txt'])->format('d M H:00'),
                'temp' => round($item['main']['temp'] ?? 0),
                'humidity' => $item['main']['humidity'] ?? null,
                'rain_probability' => round($rainProb),
                'rain_volume' => $rainVolume ? round($rainVolume, 1) : 0,
                'wind_speed' => $item['wind']['speed'] ?? 0,
                'wind_gust' => $item['wind']['gust'] ?? 0,
                'pressure' => $item['main']['grnd_level'] ?? $item['main']['pressure'] ?? 0,
                'clouds' => $item['clouds']['all'] ?? 0,
                'weather_id' => $item['weather'][0]['id'] ?? null,
                'weather_description' => $item['weather'][0]['description'] ?? '',
                'weather_icon' => $item['weather'][0]['icon'] ?? null,
                'weather_main' => $item['weather'][0]['main'] ?? null
            ];

            $count++;
        }

        return $series;
    }

    /**
     * حساب المتوسط
     */
    private function average(array $values): float
    {
        return !empty($values) ? array_sum($values) / count($values) : 0;
    }
}
