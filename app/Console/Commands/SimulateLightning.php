<?php

namespace App\Console\Commands;

use App\Events\LightningStrike;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SimulateLightning extends Command
{
    protected $signature = 'lightning:simulate';
    protected $description = 'جلب وبث الصواعق الحقيقية من عدة مصادر';

    public function handle()
    {
        $this->info('🌩️ بدء جلب الصواعق');

        $lastTimestamp = 0;
        // قائمة مصادر JSON محتملة
        $sources = [
            'blitzortung_en' => 'https://en.blitzortung.org/live_lightning_maps.php?format=json&limit=50',
            'blitzortung_data' => 'https://data.blitzortung.org/Data/last_strikes.php?limit=50',
            'lightningmaps_json' => 'https://www.lightningmaps.org/realtime',
        ];

        while (true) {
            $fetched = false;

            foreach ($sources as $name => $url) {
                try {
                    $this->line("محاولة المصدر: $name");

                    $response = Http::withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/119.0',
                        'Accept' => 'application/json',
                    ])->timeout(10)->get($url);

                    if ($response->successful()) {
                        $data = $response->json();

                        if (empty($data)) {
                            $this->warn("المصدر $name أعاد بيانات فارغة");
                            $this->line("الاستجابة: " . substr($response->body(), 0, 200));
                            continue;
                        }

                        $this->info("✅ تم جلب البيانات من $name");
                        $this->processStrikes($data, $lastTimestamp);
                        $fetched = true;
                        break;
                    } else {
                        $this->warn("المصدر $name فشل (كود: " . $response->status() . ")");
                    }
                } catch (\Exception $e) {
                    $this->error("خطأ مع المصدر $name: " . $e->getMessage());
                }
            }

            if (!$fetched) {
                $this->error('❌ جميع المصادر فشلت. انتظار 30 ثانية...');
            }

            sleep(30);
        }
    }

    protected function processStrikes($data, &$lastTimestamp)
    {
        if (!is_array($data)) {
            $this->warn('البيانات ليست مصفوفة');
            return;
        }

        foreach ($data as $strikeData) {
            $lat = $lon = $time = $strength = null;

            // تنسيق Blitzortung (كائن)
            if (is_array($strikeData) && isset($strikeData['lat'], $strikeData['lon'], $strikeData['time'])) {
                $lat = (float)$strikeData['lat'];
                $lon = (float)$strikeData['lon'];
                $time = (int)($strikeData['time'] / 1000000000); // نانو ثانية
                $strength = (int)($strikeData['mds'] ?? 15);
            }
            // تنسيق Lightningmaps (مصفوفة)
            elseif (is_array($strikeData) && count($strikeData) >= 3) {
                $lat = (float)$strikeData[0];
                $lon = (float)$strikeData[1];
                $time = (int)$strikeData[2];
                $strength = (int)($strikeData[3] ?? 15);
            }

            if ($lat && $lon && $time && $time > $lastTimestamp) {
                $strike = [
                    'lat' => $lat,
                    'lon' => $lon,
                    'time' => $time,
                    'strength' => $strength,
                ];

                event(new LightningStrike($strike));

                $this->line("⚡ صاعقة عند ($lat, $lon) بقوة $strength");

                $lastTimestamp = $time;
            }
        }
    }
}
