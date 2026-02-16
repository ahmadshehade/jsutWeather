<?php
// app/Jobs/UpdateWeatherForFavorites.php

namespace App\Jobs;

use App\Services\WeatherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpdateWeatherForFavorites implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $favorites; // يجب أن تكون public لتسلسلها

    public function __construct(array $favorites)
    {
        $this->favorites = $favorites;
    }

    public function handle(WeatherService $weatherService): void
    {
        Log::info('بدء تحديث الطقس للمدن المفضلة', ['count' => count($this->favorites)]);

        foreach ($this->favorites as $favorite) {
            try {
                // التحقق من وجود البيانات المطلوبة
                if (!isset($favorite['lat'], $favorite['lon'], $favorite['name'])) {
                    Log::warning('بيانات مدينة غير مكتملة', $favorite);
                    continue;
                }

                // تحديث بيانات الطقس لكل مدينة مفضلة
                $weather = $weatherService->getWeatherByCoords(
                    (float) $favorite['lat'],
                    (float) $favorite['lon'],
                    $favorite['name']
                );

                // تخزين مؤقت للوصول السريع
                Cache::put(
                    "favorite_weather_{$favorite['lat']}_{$favorite['lon']}",
                    $weather,
                    now()->addHours(1)
                );

                Log::info("✅ تم تحديث طقس {$favorite['name']}");

                // تأخير بسيط بين الطلبات
                if (count($this->favorites) > 1) {
                    usleep(500000); // 0.5 ثانية
                }
            } catch (\Exception $e) {
                Log::error("❌ فشل تحديث طقس {$favorite['name']}: " . $e->getMessage(), [
                    'exception' => get_class($e),
                    'favorite' => $favorite
                ]);
            }
        }

        Log::info('انتهى تحديث الطقس للمدن المفضلة');
    }

    /**
     * معالجة فشل الـ Job
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('فشل Job تحديث الطقس', [
            'error' => $exception->getMessage(),
            'favorites_count' => count($this->favorites)
        ]);
    }
}
