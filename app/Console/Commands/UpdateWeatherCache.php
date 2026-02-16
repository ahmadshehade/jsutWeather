<?php
// app/Console/Commands/UpdateWeatherCache.php

namespace App\Console\Commands;

use App\Jobs\UpdateWeatherForFavorites;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class UpdateWeatherCache extends Command
{
    protected $signature = 'weather:update-cache';
    protected $description = 'تحديث بيانات الطقس المخزنة مؤقتاً';

    public function handle()
    {
        // تحذير: Session قد لا تكون متاحة في كل البيئات
        $this->warn('تنبيه: Session قد لا تعمل بشكل صحيح في Commands');

        $favorites = Session::get('favorite_cities', []);

        if (empty($favorites)) {
            $this->info('لا توجد مدن مفضلة لتحديثها');
            return 0;
        }

        $this->info('جاري تحديث بيانات الطقس للمدن المفضلة...');

        // تمرير البيانات مباشرة للـ Job
        UpdateWeatherForFavorites::dispatch($favorites);

        $this->info('تم بدء عملية التحديث بنجاح');

        return 0;
    }
}
