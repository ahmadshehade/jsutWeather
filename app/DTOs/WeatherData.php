<?php
// app/DTOs/WeatherData.php

namespace App\DTOs;

class WeatherData
{
    public function __construct(
        public readonly ?array $weather,
        public readonly ?array $forecast,
        public readonly array $dailySummaries,
        public readonly array $hourlySeries,
        public readonly ?string $city
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            weather: $data['weather'] ?? null,
            forecast: $data['forecast'] ?? null,
            dailySummaries: $data['dailySummaries'] ?? [],
            hourlySeries: $data['hourlySeries'] ?? [],
            city: $data['city'] ?? null
        );
    }
}
