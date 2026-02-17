<?php

namespace App\Http\Controllers;

use App\Models\FavoriteCity;
use App\Services\WeatherService;
use App\Services\AirQualityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class WeatherController extends Controller
{
    protected WeatherService $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function index(Request $request, AirQualityService $airQualityService)
    {
        $input = $request->input('city', Session::get('last_city', 'London'));
        $weather = null;
        $forecast = [];
        $airQuality = null;
        $coordinates = null;
        $uvIndex = null;
        $searchResults = [];
        $displayName = $input;
        $user = Auth::user();

        try {
            if ($input) {
                // التحقق إذا كان الإدخال إحداثيات
                $isCoordinates = false;
                if (strpos($input, ',') !== false) {
                    $parts = explode(',', $input);
                    if (count($parts) == 2 && is_numeric(trim($parts[0])) && is_numeric(trim($parts[1]))) {
                        $isCoordinates = true;
                    }
                }

                if ($isCoordinates) {
                    // بحث بالإحداثيات
                    $weather = $this->weatherService->getWeather($input);

                    $parts = explode(',', $input);
                    $coordinates = [
                        'lat' => floatval(trim($parts[0])),
                        'lon' => floatval(trim($parts[1])),
                        'name' => $weather['name'] ?? "الموقع",
                    ];
                    $displayName = $coordinates['name'];

                    // محاولة الحصول على اسم أفضل للموقع
                    $locationName = $this->weatherService->getReverseGeocoding(
                        $coordinates['lat'],
                        $coordinates['lon']
                    );
                    if ($locationName) {
                        $displayName = $locationName;
                    }

                    $forecastData = $this->weatherService->getForecast($input);
                } else {
                    // بحث باسم مدينة/قرية
                    $coordinates = $this->weatherService->getCoordinates($input);

                    if ($coordinates) {
                        $weather = $this->weatherService->getWeatherByCoords(
                            $coordinates['lat'],
                            $coordinates['lon'],
                            $coordinates['name']
                        );

                        $forecastData = $this->weatherService->getForecastByCoords(
                            $coordinates['lat'],
                            $coordinates['lon']
                        );

                        $displayName = $coordinates['name'];
                        if (!empty($coordinates['state'])) {
                            $displayName .= '، ' . $coordinates['state'];
                        }
                        $displayName .= '، ' . $coordinates['country'];
                    } else {
                        // لم نجد الإحداثيات - نجرب البحث المباشر
                        $weather = $this->weatherService->getCurrentWeather($input);

                        if (isset($weather['name'])) {
                            $displayName = $weather['name'];
                            $forecastData = $this->weatherService->getForecast($input);

                            $coordinates = isset($weather['coord']) ? [
                                'lat' => $weather['coord']['lat'],
                                'lon' => $weather['coord']['lon'],
                                'name' => $weather['name'],
                            ] : null;
                        } else {
                            // فشل كل شيء - نبحث عن نتائج متعددة
                            $searchResults = $this->weatherService->searchLocations($input);

                            if (empty($searchResults)) {
                                return redirect()->back()->with('error', 'لم يتم العثور على موقع بهذا الاسم');
                            }

                            return view('weather.search-results', compact('searchResults', 'input'));
                        }
                    }
                }

                // ---------------------------
                // إضافة اتجاه الرياح للطقس الحالي
                // ---------------------------
                if ($weather && isset($weather['wind']['deg'])) {
                    $weather['wind']['dir'] = $this->weatherService->getWindDirection($weather['wind']['deg']);
                }

                // ---------------------------
                // إضافة اتجاه الرياح للتوقعات اليومية
                // ---------------------------
                if (isset($forecastData['daily']) && is_array($forecastData['daily'])) {
                    foreach ($forecastData['daily'] as &$day) {
                        if (isset($day['wind_deg'])) {
                            $day['wind_dir'] = $this->weatherService->getWindDirection($day['wind_deg']);
                        } else {
                            $day['wind_dir'] = 'غير معروف';
                        }
                    }
                }

                // ---------------------------
                // إضافة اتجاه الرياح للتوقعات الساعية
                // ---------------------------
                if (isset($forecastData['hourly']) && is_array($forecastData['hourly'])) {
                    foreach ($forecastData['hourly'] as &$hour) {
                        if (isset($hour['wind_deg'])) {
                            $hour['wind_dir'] = $this->weatherService->getWindDirection($hour['wind_deg']);
                        } else {
                            $hour['wind_dir'] = 'غير معروف';
                        }
                    }
                }

                // جلب البيانات الإضافية إذا توفرت الإحداثيات
                if ($coordinates && isset($coordinates['lat']) && isset($coordinates['lon'])) {
                    try {
                        $airQuality = $airQualityService->getAirQuality(
                            $coordinates['lat'],
                            $coordinates['lon']
                        );
                    } catch (\Exception $e) {
                        Log::error('Air Quality Error: ' . $e->getMessage());
                    }

                    try {
                        $uvIndex = $this->weatherService->getUVIndex(
                            $coordinates['lat'],
                            $coordinates['lon']
                        );
                    } catch (\Exception $e) {
                        Log::error('UV Index Error: ' . $e->getMessage());
                    }
                }

                // تجهيز بيانات التوقعات
                $dailySummaries = $forecastData['daily'] ?? [];
                $hourlySeries = $forecastData['hourly'] ?? [];

                $hourlyLabels = [];
                $hourlyTemps = [];
                foreach ($hourlySeries as $hour) {
                    $hourlyLabels[] = $hour['time'];
                    $hourlyTemps[] = $hour['temp'];
                }

                Session::put('last_city', $input);
                $this->addToHistory($input);
            }
        } catch (\Exception $e) {
            Log::error('Weather Controller Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'عذراً، حدث خطأ في جلب بيانات الطقس. يرجى المحاولة مرة أخرى.');
        }

        // جلب المدن المفضلة للمستخدم المسجل
        $favorites = [];
        if ($user) {
            $favorites = FavoriteCity::where('user_id', $user->id)
                ->orderBy('name')
                ->get();

            // ---------------------------
            // إضافة اتجاه الرياح للطقس المخزن في المفضلة
            // ---------------------------
            foreach ($favorites as $fav) {
                if ($fav->current_weather && isset($fav->current_weather['wind']['deg'])) {
                    $weatherData = $fav->current_weather; // الحصول على المصفوفة
                    $weatherData['wind']['dir'] = $this->weatherService->getWindDirection($weatherData['wind']['deg']);
                    $fav->current_weather = $weatherData; // إعادة التعيين
                }
            }
        }

        return view('weather.index', compact(
            'input',
            'displayName',
            'weather',
            'dailySummaries',
            'hourlySeries',
            'hourlyLabels',
            'hourlyTemps',
            'airQuality',
            'uvIndex',
            'coordinates',
            'searchResults',
            'user',
            'favorites'
        ));
    }

    /**
     * البحث عن مواقع (AJAX)
     */
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = $this->weatherService->searchLocations($query);

        return response()->json($results);
    }

    /**
     * إضافة مدينة للمفضلة (للمستخدمين المسجلين فقط)
     */
    public function addFavorite(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول أولاً'
            ], 401);
        }

        $request->validate([
            'name' => 'required|string',
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
            'country' => 'nullable|string'
        ]);

        try {
            // التحقق من عدم التكرار
            $exists = FavoriteCity::where('user_id', Auth::id())
                ->where('lat', $request->lat)
                ->where('lon', $request->lon)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'المدينة موجودة بالفعل في المفضلة'
                ]);
            }

            // جلب بيانات الطقس للمدينة
            $weather = $this->weatherService->getWeatherByCoords(
                $request->lat,
                $request->lon,
                $request->name
            );

            // إضافة اتجاه الرياح قبل التخزين (اختياري)
            if (isset($weather['wind']['deg'])) {
                $weather['wind']['dir'] = $this->weatherService->getWindDirection($weather['wind']['deg']);
            }

            $favorite = FavoriteCity::create([
                'user_id' => Auth::id(),
                'name' => $request->name,
                'lat' => $request->lat,
                'lon' => $request->lon,
                'country' => $request->country ?? '',
                'current_weather' => $weather,
                'weather_updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تمت الإضافة للمفضلة',
                'favorite' => $favorite
            ]);
        } catch (\Exception $e) {
            Log::error('Add Favorite Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إزالة من المفضلة
     */
    public function removeFavorite(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول أولاً'
            ], 401);
        }

        $request->validate([
            'id' => 'required|exists:favorite_cities,id'
        ]);

        $favorite = FavoriteCity::where('user_id', Auth::id())
            ->where('id', $request->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['success' => true, 'message' => 'تمت الإزالة من المفضلة']);
        }

        return response()->json(['success' => false, 'message' => 'المدينة غير موجودة'], 404);
    }

    /**
     * تحديث طقس كل المدن المفضلة للمستخدم
     */
    public function refreshAllFavorites()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $favorites = FavoriteCity::where('user_id', Auth::id())->get();

        foreach ($favorites as $favorite) {
            try {
                $weather = $this->weatherService->getWeatherByCoords(
                    $favorite->lat,
                    $favorite->lon,
                    $favorite->name
                );

                // إضافة اتجاه الرياح
                if (isset($weather['wind']['deg'])) {
                    $weather['wind']['dir'] = $this->weatherService->getWindDirection($weather['wind']['deg']);
                }

                $favorite->updateWeather($weather);
            } catch (\Exception $e) {
                Log::error("فشل تحديث {$favorite->name}: " . $e->getMessage());
            }
        }

        return back()->with('success', 'تم تحديث جميع المدن المفضلة');
    }

    /**
     * سجل البحث
     */
    protected function addToHistory(string $city)
    {
        $history = Session::get('search_history', []);

        $history = array_filter($history, fn($c) => $c !== $city);
        array_unshift($history, $city);
        $history = array_slice($history, 0, 10);

        Session::put('search_history', $history);
    }

    public function live()
    {
        // هذا الـ endpoint لن يُستخدم مع WebSocket، لكن يمكن تركه للتوافق
        return response()->json(['message' => 'Use WebSocket instead']);
    }
}
