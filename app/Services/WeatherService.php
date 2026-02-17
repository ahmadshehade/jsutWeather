<?php
// app/Services/WeatherService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected array $config;

    public function __construct()
    {
        $this->config = config('weather.api.openweather');
        $this->apiKey = $this->config['key'];
        $this->baseUrl = $this->config['base_url'];
    }

    /**
     * الدالة الرئيسية - تكتشف تلقائياً إذا كان الإدخال اسم مدينة أو إحداثيات
     */
    public function getWeather(string $input): array
    {
        // التحقق إذا كان الإدخال إحداثيات (تحتوي على فاصلة)
        if (strpos($input, ',') !== false) {
            $parts = explode(',', $input);
            if (count($parts) == 2 && is_numeric(trim($parts[0])) && is_numeric(trim($parts[1]))) {
                // إدخال إحداثيات
                return $this->getWeatherByCoords(
                    floatval(trim($parts[0])),
                    floatval(trim($parts[1]))
                );
            }
        }

        // إدخال اسم مدينة/قرية - نحتاج أولاً للحصول على إحداثياتها
        $coordinates = $this->getCoordinates($input);

        if ($coordinates) {
            // وجدنا الإحداثيات - نجلب الطقس بها (أدق)
            return $this->getWeatherByCoords(
                $coordinates['lat'],
                $coordinates['lon'],
                $coordinates['name'] // نمرر اسم الموقع الحقيقي
            );
        }

        // إذا لم نجد الإحداثيات، نجرب البحث المباشر بالاسم
        return $this->getCurrentWeather($input);
    }

    /**
     * جلب الطقس بالإحداثيات (يعمل لأي نقطة على الأرض - حتى القرى غير المسجلة)
     */
    public function getWeatherByCoords(float $lat, float $lon, ?string $locationName = null): array
    {
        $cacheKey = "weather_coords_{$lat}_{$lon}";

        return Cache::remember($cacheKey, config('weather.cache.ttl.current'), function () use ($lat, $lon, $locationName) {
            try {
                $response = Http::retry(3, 100)->get("{$this->baseUrl}/weather", [
                    'lat' => $lat,
                    'lon' => $lon,
                    'appid' => $this->apiKey,
                    'units' => $this->config['units'],
                    'lang' => $this->config['lang'],
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    // إذا مررنا اسم موقع، نستخدمه بدلاً من الاسم من API
                    if ($locationName) {
                        $data['name'] = $locationName;
                    } else {
                        // محاولة الحصول على اسم الموقع من خدمة الترميز الجغرافي
                        $locationInfo = $this->getReverseGeocoding($lat, $lon);
                        if ($locationInfo) {
                            $data['name'] = $locationInfo;
                        }
                    }

                    return $this->formatCurrentWeather($data);
                }

                // إذا فشل API، نستخدم بيانات تجريبية
                if (app()->environment('local')) {
                    $name = $locationName ?? "موقع عند {$lat}, {$lon}";
                    return $this->getMockWeatherData($name);
                }

                throw new \Exception('تعذر جلب بيانات الطقس لهذه الإحداثيات');
            } catch (\Exception $e) {
                Log::error('Weather by Coords Error: ' . $e->getMessage());
                throw new \Exception('تعذر جلب بيانات الطقس');
            }
        });
    }

    /**
     * الحصول على اسم المنطقة من الإحداثيات (Reverse Geocoding)
     */
    public function getReverseGeocoding(float $lat, float $lon): ?string
    {
        $cacheKey = "reverse_geo_{$lat}_{$lon}";

        return Cache::remember($cacheKey, 86400, function () use ($lat, $lon) {
            try {
                // استخدام OpenWeather Reverse Geocoding
                $response = Http::get("{$this->baseUrl}/geo/1.0/reverse", [
                    'lat' => $lat,
                    'lon' => $lon,
                    'limit' => 1,
                    'appid' => $this->apiKey,
                ]);

                if ($response->successful() && !empty($response->json())) {
                    $data = $response->json()[0];
                    return $data['name'] . '، ' . $data['country'];
                }

                // بديل: استخدام Open-Meteo Geocoding
                $response2 = Http::get('https://geocoding-api.open-meteo.com/v1/reverse', [
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'language' => 'ar',
                ]);

                if ($response2->successful() && !empty($response2->json('results'))) {
                    $data = $response2->json('results.0');
                    return $data['name'] . '، ' . ($data['country_code'] ?? '');
                }
            } catch (\Exception $e) {
                Log::error('Reverse Geocoding Error: ' . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * جلب التوقعات بالإحداثيات
     */
    public function getForecastByCoords(float $lat, float $lon): array
    {
        $cacheKey = 'forecast_coords_' . md5("{$lat}_{$lon}");

        return Cache::remember($cacheKey, config('weather.cache.ttl.forecast'), function () use ($lat, $lon) {
            try {
                $response = Http::retry(3, 100)->get("{$this->baseUrl}/forecast", [
                    'lat' => $lat,
                    'lon' => $lon,
                    'appid' => $this->apiKey,
                    'units' => $this->config['units'],
                    'lang' => $this->config['lang'],
                    'cnt' => 40,
                ]);

                if ($response->successful()) {
                    return $this->processForecastData($response->json());
                }

                return [];
            } catch (\Exception $e) {
                Log::error('Forecast by Coords Error: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * جلب الطقس الحالي مع تخزين مؤقت (للتوافق)
     */
    public function getCurrentWeather(string $city): array
    {
        $cacheKey = 'weather_current_' . md5($city);

        return Cache::remember($cacheKey, config('weather.cache.ttl.current'), function () use ($city) {
            try {
                $response = Http::retry(3, 100)->get("{$this->baseUrl}/weather", [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => $this->config['units'],
                    'lang' => $this->config['lang'],
                ]);

                if ($response->successful()) {
                    return $this->formatCurrentWeather($response->json());
                }

                // محاولة المصدر البديل
                return $this->getBackupWeather($city);
            } catch (\Exception $e) {
                Log::error('Weather API Error: ' . $e->getMessage());

                if (app()->environment('local')) {
                    return $this->getMockWeatherData($city);
                }

                throw new \Exception('تعذر جلب بيانات الطقس');
            }
        });
    }

    /**
     * جلب التوقعات 5 أيام
     */
    public function getForecast(string $city): array
    {
        // التحقق إذا كان الإدخال إحداثيات
        if (strpos($city, ',') !== false) {
            $parts = explode(',', $city);
            if (count($parts) == 2 && is_numeric(trim($parts[0])) && is_numeric(trim($parts[1]))) {
                return $this->getForecastByCoords(
                    floatval(trim($parts[0])),
                    floatval(trim($parts[1]))
                );
            }
        }

        $cacheKey = 'weather_forecast_' . md5($city);

        return Cache::remember($cacheKey, config('weather.cache.ttl.forecast'), function () use ($city) {
            try {
                $response = Http::retry(3, 100)->get("{$this->baseUrl}/forecast", [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => $this->config['units'],
                    'lang' => $this->config['lang'],
                    'cnt' => 40,
                ]);

                if ($response->successful()) {
                    return $this->processForecastData($response->json());
                }

                return [];
            } catch (\Exception $e) {
                Log::error('Forecast API Error: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * البحث عن مواقع متعددة
     */
    public function searchLocations(string $query): array
    {
        $cacheKey = 'search_' . md5($query);

        return Cache::remember($cacheKey, 3600, function () use ($query) {
            $response = Http::get("{$this->baseUrl}/geo/1.0/direct", [
                'q' => $query,
                'limit' => 10,
                'appid' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $results = $response->json();
                $locations = [];

                foreach ($results as $result) {
                    $locations[] = [
                        'name' => $result['name'],
                        'lat' => $result['lat'],
                        'lon' => $result['lon'],
                        'country' => $result['country'],
                        'state' => $result['state'] ?? '',
                        'display_name' => $this->formatLocationName($result),
                    ];
                }

                return $locations;
            }

            return [];
        });
    }

    /**
     * تنسيق اسم الموقع للعرض
     */
    protected function formatLocationName(array $location): string
    {
        $parts = [$location['name']];

        if (!empty($location['state'])) {
            $parts[] = $location['state'];
        }

        $parts[] = $location['country'];

        return implode('، ', $parts);
    }

    /**
     * الحصول على إحداثيات المدينة
     */
    public function getCoordinates(string $city): ?array
    {
        $cacheKey = 'coords_' . md5($city);

        return Cache::remember($cacheKey, config('weather.cache.ttl.location'), function () use ($city) {
            // تجربة البحث بالاسم مباشرة
            $response = Http::get("{$this->baseUrl}/geo/1.0/direct", [
                'q' => $city,
                'limit' => 5,
                'appid' => $this->apiKey,
            ]);

            if ($response->successful() && !empty($response->json())) {
                $results = $response->json();

                // اختيار أفضل نتيجة
                $result = $results[0];
                return [
                    'lat' => $result['lat'],
                    'lon' => $result['lon'],
                    'name' => $result['name'],
                    'country' => $result['country'],
                    'state' => $result['state'] ?? null,
                ];
            }

            // إذا فشل البحث، نستخدم Open-Meteo كبديل
            return $this->getCoordinatesFromOpenMeteo($city);
        });
    }

    /**
     * البحث عن الإحداثيات باستخدام Open-Meteo
     */
    protected function getCoordinatesFromOpenMeteo(string $city): ?array
    {
        try {
            $response = Http::get('https://geocoding-api.open-meteo.com/v1/search', [
                'name' => $city,
                'count' => 1,
                'language' => 'ar',
                'format' => 'json',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['results'])) {
                    $result = $data['results'][0];
                    return [
                        'lat' => $result['latitude'],
                        'lon' => $result['longitude'],
                        'name' => $result['name'],
                        'country' => $result['country_code'] ?? '',
                        'state' => $result['admin1'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Open-Meteo Geocoding Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * جلب مؤشر UV
     */
    public function getUVIndex(float $lat, float $lon): array
    {
        $cacheKey = "uv_index_{$lat}_{$lon}";

        return Cache::remember($cacheKey, 3600, function () use ($lat, $lon) {
            try {
                $response = Http::get("{$this->baseUrl}/uvi", [
                    'lat' => $lat,
                    'lon' => $lon,
                    'appid' => $this->apiKey,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $uv = round($data['value'] ?? 0);

                    return [
                        'index' => $uv,
                        'level' => $this->getUVLevel($uv),
                        'color' => $this->getUVColor($uv),
                    ];
                }
            } catch (\Exception $e) {
                Log::error('UV Index API Error: ' . $e->getMessage());
            }

            return $this->getMockUVIndex();
        });
    }

    /**
     * مصدر بديل (Open-Meteo)
     */
    protected function getBackupWeather(string $city): array
    {
        $coordinates = $this->getCoordinates($city);

        if (!$coordinates) {
            return [];
        }

        try {
            $response = Http::get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $coordinates['lat'],
                'longitude' => $coordinates['lon'],
                'current_weather' => 'true',
                'hourly' => 'temperature_2m,relativehumidity_2m,windspeed_10m,precipitation',
                'timezone' => 'auto',
            ]);

            if ($response->successful()) {
                return $this->formatOpenMeteoData($response->json(), $coordinates['name']);
            }
        } catch (\Exception $e) {
            Log::error('Open-Meteo API Error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * معالجة بيانات التوقعات (معدلة لتشمل wind_deg)
     */
    protected function processForecastData(array $data): array
    {
        $daily = [];
        $hourly = [];

        foreach ($data['list'] as $item) {
            $date = \Carbon\Carbon::parse($item['dt_txt']);

            $dayKey = $date->format('Y-m-d');
            if (!isset($daily[$dayKey])) {
                $daily[$dayKey] = [
                    'date' => $dayKey,
                    'label' => $date->translatedFormat('l'),
                    'temps' => [],
                    'humidities' => [],
                    'icons' => [],
                    'rain_prob' => [],
                    'wind_degs' => [],
                    'wind_gusts' => [],
                ];
            }

            $daily[$dayKey]['temps'][] = $item['main']['temp'];
            $daily[$dayKey]['humidities'][] = $item['main']['humidity'];
            $daily[$dayKey]['icons'][] = $item['weather'][0]['icon'];
            $daily[$dayKey]['rain_prob'][] = $item['pop'] ?? 0;
            $daily[$dayKey]['wind_degs'][] = $item['wind']['deg'] ?? 0;
            $daily[$dayKey]['wind_gusts'][] = $item['wind']['gust'] ?? null;

            if (count($hourly) < 24) {
                $hourly[] = [
                    'time' => $date->format('H:00'),
                    'temp' => round($item['main']['temp']),
                    'humidity' => $item['main']['humidity'],
                    'rain_prob' => round(($item['pop'] ?? 0) * 100),
                    'icon' => $item['weather'][0]['icon'],
                    'description' => $item['weather'][0]['description'],
                    'wind_speed' => $item['wind']['speed'] ?? null,
                    'wind_deg' => $item['wind']['deg'] ?? null,
                    'wind_gust' => $item['wind']['gust'] ?? null,
                ];
            }
        }

        $dailySummaries = [];
        foreach (array_slice($daily, 0, 7) as $dayKey => $dayData) {
            $firstWindDeg = $dayData['wind_degs'][0] ?? null;
            $maxWindGust = !empty($dayData['wind_gusts']) ? max(array_filter($dayData['wind_gusts'])) : null;

            $dailySummaries[] = [
                'date' => $dayKey,
                'label' => $dayData['label'],
                'min' => round(min($dayData['temps'])),
                'max' => round(max($dayData['temps'])),
                'humidity' => round(array_sum($dayData['humidities']) / count($dayData['humidities'])),
                'icon' => $dayData['icons'][array_key_first($dayData['icons'])],
                'rain_probability' => round(max($dayData['rain_prob']) * 100),
                'wind_gust' => $maxWindGust ?? $this->calculateWindGust($dayData['temps']),
                'wind_deg' => $firstWindDeg,
                'rain_volume' => round($this->calculateRainVolume($dayData['rain_prob']), 1),
                'pressure' => 1015,
            ];
        }

        return [
            'daily' => $dailySummaries,
            'hourly' => $hourly,
            'city' => $data['city']['name'] ?? '',
        ];
    }

    protected function calculateWindGust(array $temps): float
    {
        return round(5 + (max($temps) - min($temps)) * 0.5, 1);
    }

    protected function calculateRainVolume(array $rainProb): float
    {
        $avgProb = array_sum($rainProb) / count($rainProb);
        return round($avgProb * 2, 1);
    }

    /**
     * بيانات تجريبية للتطوير
     */
    protected function getMockWeatherData(string $city): array
    {
        return [
            'main' => [
                'temp' => 26,
                'feels_like' => 24,
                'humidity' => 68,
                'pressure' => 1015,
                'grnd_level' => 1013,
            ],
            'weather' => [
                [
                    'id' => 800,
                    'main' => 'Clear',
                    'description' => 'سماء صافية',
                    'icon' => '01d',
                ]
            ],
            'wind' => [
                'speed' => 4.2,
                'gust' => 6.8,
                'deg' => 120,
            ],
            'name' => $city,
            'visibility' => 10000,
            'dt' => now()->timestamp,
            'coord' => [
                'lat' => 35.5,
                'lon' => 36.0,
            ],
        ];
    }

    /**
     * تنسيق بيانات OpenWeather
     */
    protected function formatCurrentWeather(array $data): array
    {
        return [
            'main' => $data['main'],
            'weather' => $data['weather'],
            'wind' => $data['wind'],
            'name' => $data['name'],
            'visibility' => $data['visibility'] ?? null,
            'sys' => $data['sys'] ?? [],
            'dt' => $data['dt'],
            'coord' => $data['coord'] ?? null,
            'rain_volume' => $data['rain']['1h'] ?? $data['rain']['3h'] ?? null,
        ];
    }

    /**
     * تنسيق بيانات Open-Meteo
     */
    protected function formatOpenMeteoData(array $data, string $city): array
    {
        $current = $data['current_weather'] ?? [];

        return [
            'main' => [
                'temp' => $current['temperature'],
                'feels_like' => $current['temperature'],
                'humidity' => 60,
                'pressure' => 1013,
            ],
            'weather' => [
                [
                    'id' => $this->wmoCodeToWeatherId($current['weathercode'] ?? 0),
                    'main' => $this->wmoCodeToMain($current['weathercode'] ?? 0),
                    'description' => $this->wmoCodeToDescription($current['weathercode'] ?? 0),
                    'icon' => $this->wmoCodeToIcon($current['weathercode'] ?? 0),
                ]
            ],
            'wind' => [
                'speed' => $current['windspeed'],
                'gust' => null,
            ],
            'name' => $city,
            'dt' => strtotime($current['time'] ?? 'now'),
        ];
    }

    protected function wmoCodeToWeatherId(int $code): int
    {
        $map = [
            0 => 800,
            1 => 801,
            2 => 802,
            3 => 804,
            45 => 741,
            48 => 741,
            51 => 500,
            61 => 500,
        ];

        return $map[$code] ?? 800;
    }

    protected function wmoCodeToMain(int $code): string
    {
        return $code == 0 ? 'Clear' : ($code < 4 ? 'Clouds' : 'Rain');
    }

    protected function wmoCodeToDescription(int $code): string
    {
        $descriptions = [
            0 => 'سماء صافية',
            1 => 'صافي بشكل رئيسي',
            2 => 'غائم جزئياً',
            3 => 'غائم',
            45 => 'ضباب',
            48 => 'ضباب مع صقيع',
            51 => 'رذاذ خفيف',
            61 => 'أمطار خفيفة',
        ];

        return $descriptions[$code] ?? 'طقس متغير';
    }

    protected function wmoCodeToIcon(int $code): string
    {
        $icons = [
            0 => '01d',
            1 => '02d',
            2 => '03d',
            3 => '04d',
            45 => '50d',
            48 => '50d',
            51 => '10d',
            61 => '10d',
        ];

        return $icons[$code] ?? '02d';
    }

    protected function getUVLevel(int $uv): string
    {
        return match (true) {
            $uv <= 2 => 'منخفض',
            $uv <= 5 => 'متوسط',
            $uv <= 7 => 'مرتفع',
            $uv <= 10 => 'مرتفع جداً',
            default => 'خطير',
        };
    }

    protected function getUVColor(int $uv): string
    {
        return match (true) {
            $uv <= 2 => '#6ad4b4',
            $uv <= 5 => '#ffd93d',
            $uv <= 7 => '#ff9f4b',
            $uv <= 10 => '#ff6b6b',
            default => '#b886ff',
        };
    }

    protected function getMockUVIndex(): array
    {
        $uv = rand(0, 11);
        return [
            'index' => $uv,
            'level' => $this->getUVLevel($uv),
            'color' => $this->getUVColor($uv),
        ];
    }

    public function getWindDirection($degrees)
    {
        $directions = [
            'شمال' => [337.5, 22.5], // نطاق يمتد عبر 0
            'شمال شرق' => [22.5, 67.5],
            'شرق' => [67.5, 112.5],
            'جنوب شرق' => [112.5, 157.5],
            'جنوب' => [157.5, 202.5],
            'جنوب غرب' => [202.5, 247.5],
            'غرب' => [247.5, 292.5],
            'شمال غرب' => [292.5, 337.5],
        ];

        // معالجة حالة الـ 0° (شمال)
        if ($degrees >= 337.5 || $degrees < 22.5) {
            return 'شمال';
        }

        foreach ($directions as $name => $range) {
            if ($name === 'شمال') continue;
            if ($degrees >= $range[0] && $degrees < $range[1]) {
                return $name;
            }
        }

        return 'غير معروف';
    }
}
