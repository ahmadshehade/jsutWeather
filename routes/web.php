<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;


Route::get('/ping', function () {
    return 'pong';
});
// =============================================
// الصفحة الرئيسية - تفتح الطقس مباشرة
// =============================================
Route::get('/', function () {
    // إذا كان المستخدم مسجل دخول → يذهب للطقس
    // إذا لم يكن مسجل → يذهب لتسجيل الدخول
    return redirect()->route('weather.index');
});

// =============================================
// جميع صفحات الطقس محمية بمصادقة
// =============================================
Route::middleware(['auth'])->group(function () {
    // الصفحة الرئيسية للطقس
    Route::get('/weather', [WeatherController::class, 'index'])->name('weather.index');

    // البحث عن مدن
    Route::get('/weather/search', [WeatherController::class, 'search'])->name('weather.search');
     Route::get('/api/lightning-live', [WeatherController::class, 'live']);

    // إدارة المفضلة
    Route::post('/weather/favorite/add', [WeatherController::class, 'addFavorite'])->name('weather.favorite.add');
    Route::post('/weather/favorite/remove', [WeatherController::class, 'removeFavorite'])->name('weather.favorite.remove');
    Route::get('/weather/favorites/refresh', [WeatherController::class, 'refreshAllFavorites'])->name('weather.favorites.refresh');
});

// =============================================
// صفحة Dashboard القديمة - ممكن تشيلها أو تحتفظ فيها
// =============================================
Route::get('/dashboard', function () {
    return redirect()->route('weather.index'); // تحويل لصفحة الطقس
})->middleware(['auth', 'verified'])->name('dashboard');

// =============================================
// إدارة الملف الشخصي
// =============================================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
