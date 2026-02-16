<?php
// app/Services/IQAirService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class IQAirService
{
    protected string $key;

    public function __construct()
    {
        $this->key = env('IQAIR_KEY'); // سجل في https://www.iqair.com
    }

    public function getAirQuality(float $lat, float $lon): array
    {
        $cacheKey = "iqair_{$lat}_{$lon}";

        return Cache::remember($cacheKey, 3600, function () use ($lat, $lon) {
            $response = Http::get('https://api.airvisual.com/v2/nearest_city', [
                'lat' => $lat,
                'lon' => $lon,
                'key' => $this->key,
            ]);

            if ($response->successful()) {
                return $this->formatIQAirData($response->json());
            }

            return [];
        });
    }

    protected function formatIQAirData(array $data): array
    {
        if (($data['status'] ?? '') !== 'success') {
            return [];
        }

        $current = $data['data']['current']['pollution'] ?? [];
        $aqi = $current['aqius'] ?? 0;

        return [
            'aqi' => $this->convertAQILevel($aqi),
            'aqi_us' => $aqi,
            'level' => $this->getAQILevel($aqi),
            'color' => $this->getAQIColor($aqi),
            'main_pollutant' => $current['mainus'] ?? 'pm25',
            'timestamp' => $current['ts'] ?? null,
        ];
    }

    protected function convertAQILevel(int $aqi): int
    {
        return match(true) {
            $aqi <= 50 => 1,
            $aqi <= 100 => 2,
            $aqi <= 150 => 3,
            $aqi <= 200 => 4,
            default => 5,
        };
    }

    protected function getAQILevel(int $aqi): string
    {
        return match(true) {
            $aqi <= 50 => 'جيد',
            $aqi <= 100 => 'متوسط',
            $aqi <= 150 => 'غير صحي للمجموعات الحساسة',
            $aqi <= 200 => 'غير صحي',
            default => 'خطير',
        };
    }

    protected function getAQIColor(int $aqi): string
    {
        return match(true) {
            $aqi <= 50 => '#6ad4b4',
            $aqi <= 100 => '#ffd93d',
            $aqi <= 150 => '#ff9f4b',
            $aqi <= 200 => '#ff6b6b',
            default => '#b886ff',
        };
    }
}
