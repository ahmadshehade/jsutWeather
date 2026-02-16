<?php
// app/Services/AirQualityService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AirQualityService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openweather.key', env('OPENWEATHER_API_KEY'));
        $this->baseUrl = 'https://api.openweathermap.org/data/2.5/air_pollution';
    }

    /**
     * جلب جودة الهواء لموقع بالإحداثيات
     */
    public function getAirQuality(float $lat, float $lon): array
    {
        $cacheKey = "air_quality_{$lat}_{$lon}";

        return Cache::remember($cacheKey, 3600, function () use ($lat, $lon) {
            try {
                $response = Http::retry(3, 100)->get($this->baseUrl, [
                    'lat' => $lat,
                    'lon' => $lon,
                    'appid' => $this->apiKey,
                ]);

                if ($response->successful()) {
                    return $this->formatAirQuality($response->json());
                }
            } catch (\Exception $e) {
                Log::error('Air Quality API Error: ' . $e->getMessage());
            }

            return $this->getMockAirQuality();
        });
    }

    /**
     * الحصول على إحداثيات المدينة
     */
    public function getCoordinates(string $city): ?array
    {
        $cacheKey = 'coords_' . md5($city);

        return Cache::remember($cacheKey, 86400, function () use ($city) {
            try {
                $response = Http::get('https://api.openweathermap.org/geo/1.0/direct', [
                    'q' => $city,
                    'limit' => 1,
                    'appid' => $this->apiKey,
                ]);

                if ($response->successful() && !empty($response->json())) {
                    $data = $response->json()[0];
                    return [
                        'lat' => $data['lat'],
                        'lon' => $data['lon'],
                        'name' => $data['name'],
                        'country' => $data['country'],
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Geocoding Error in AirQualityService: ' . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * تنسيق بيانات جودة الهواء
     */
    protected function formatAirQuality(array $data): array
    {
        $components = $data['list'][0]['components'] ?? [];
        $main = $data['list'][0]['main'] ?? ['aqi' => 1];

        $aqi = $main['aqi'];

        return [
            'aqi' => $aqi,
            'level' => $this->getAqiLevel($aqi),
            'color' => $this->getAqiColor($aqi),
            'description' => $this->getAqiDescription($aqi),
            'components' => [
                'co' => round($components['co'] ?? 0, 1),
                'no2' => round($components['no2'] ?? 0, 1),
                'o3' => round($components['o3'] ?? 0, 1),
                'pm2_5' => round($components['pm2_5'] ?? 0, 1),
                'pm10' => round($components['pm10'] ?? 0, 1),
            ],
            'recommendations' => $this->getRecommendations($aqi),
        ];
    }

    protected function getAqiLevel(int $aqi): string
    {
        return match($aqi) {
            1 => 'جيد',
            2 => 'متوسط',
            3 => 'غير صحي للمجموعات الحساسة',
            4 => 'غير صحي',
            5 => 'خطير',
            default => 'غير معروف',
        };
    }

    protected function getAqiColor(int $aqi): string
    {
        return match($aqi) {
            1 => '#6ad4b4',
            2 => '#ffd93d',
            3 => '#ff9f4b',
            4 => '#ff6b6b',
            5 => '#b886ff',
            default => '#a0a0a0',
        };
    }

    protected function getAqiDescription(int $aqi): string
    {
        return match($aqi) {
            1 => 'جودة الهواء جيدة، مناسبة للأنشطة الخارجية',
            2 => 'جودة الهواء مقبولة، لكن قد تكون هناك مخاطر لبعض الأشخاص',
            3 => 'المجموعات الحساسة قد تتأثر، يجب تقليل الأنشطة الخارجية',
            4 => 'تأثيرات صحية محتملة للجميع، تجنب الأنشطة الخارجية',
            5 => 'حالة طوارئ صحية، البقاء في المنازل',
            default => 'بيانات غير متوفرة',
        };
    }

    protected function getRecommendations(int $aqi): array
    {
        return match($aqi) {
            1 => [
                'الأنشطة الخارجية' => 'مسموحة',
                'النوافذ' => 'يمكن فتحها',
                'الكمامة' => 'غير ضرورية',
            ],
            2 => [
                'الأنشطة الخارجية' => 'مسموحة بحذر',
                'النوافذ' => 'يمكن فتحها لفترات قصيرة',
                'الكمامة' => 'اختيارية للمجموعات الحساسة',
            ],
            3 => [
                'الأنشطة الخارجية' => 'يجب تقليلها',
                'النوافذ' => 'أبقها مغلقة',
                'الكمامة' => 'موصى بها للمجموعات الحساسة',
            ],
            4 => [
                'الأنشطة الخارجية' => 'تجنبها',
                'النوافذ' => 'أبقها مغلقة بإحكام',
                'الكمامة' => 'موصى بها للجميع',
            ],
            5 => [
                'الأنشطة الخارجية' => 'ممنوعة',
                'النوافذ' => 'أبقها مغلقة بإحكام',
                'الكمامة' => 'إلزامية عند الخروج',
            ],
            default => [],
        };
    }

    /**
     * بيانات تجريبية للتطوير
     */
    protected function getMockAirQuality(): array
    {
        $aqi = rand(1, 5);
        return [
            'aqi' => $aqi,
            'level' => $this->getAqiLevel($aqi),
            'color' => $this->getAqiColor($aqi),
            'description' => $this->getAqiDescription($aqi),
            'components' => [
                'co' => round(rand(100, 1000) / 100, 1),
                'no2' => round(rand(5, 50) / 10, 1),
                'o3' => round(rand(10, 100) / 10, 1),
                'pm2_5' => round(rand(5, 50), 1),
                'pm10' => round(rand(10, 100), 1),
            ],
            'recommendations' => $this->getRecommendations($aqi),
        ];
    }
}
