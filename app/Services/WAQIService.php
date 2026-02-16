<?php
// app/Services/WAQIService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WAQIService
{
    protected string $token;

    public function __construct()
    {
        $this->token = env('WAQI_TOKEN'); // سجل في الموقع
    }

    public function getAirQuality(string $city): array
    {
        $cacheKey = "waqi_{$city}";

        return Cache::remember($cacheKey, 3600, function () use ($city) {
            $response = Http::get("https://api.waqi.info/feed/{$city}/", [
                'token' => $this->token,
            ]);

            if ($response->successful()) {
                return $this->formatWAQIData($response->json());
            }

            return [];
        });
    }

    protected function formatWAQIData(array $data): array
    {
        if (($data['status'] ?? '') !== 'ok') {
            return [];
        }

        $aqi = $data['data']['aqi'] ?? 0;
        $iaqi = $data['data']['iaqi'] ?? [];

        return [
            'aqi' => $this->convertAQILevel($aqi),
            'level' => $this->getAQILevel($aqi),
            'color' => $this->getAQIColor($aqi),
            'pm25' => $iaqi['pm25']['v'] ?? null,
            'pm10' => $iaqi['pm10']['v'] ?? null,
            'o3' => $iaqi['o3']['v'] ?? null,
            'no2' => $iaqi['no2']['v'] ?? null,
            'so2' => $iaqi['so2']['v'] ?? null,
            'co' => $iaqi['co']['v'] ?? null,
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
