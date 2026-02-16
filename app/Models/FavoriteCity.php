<?php
// app/Models/FavoriteCity.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FavoriteCity extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'country',
        'lat',
        'lon',
        'current_weather',
        'weather_updated_at'
    ];

    protected $casts = [
        'lat' => 'float',
        'lon' => 'float',
        'current_weather' => 'array',
        'weather_updated_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function updateWeather(array $weatherData): void
    {
        $this->update([
            'current_weather' => $weatherData,
            'weather_updated_at' => now()
        ]);
    }
}
