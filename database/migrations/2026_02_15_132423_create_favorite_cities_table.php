<?php
// database/migrations/xxxx_xx_xx_create_favorite_cities_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorite_cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('country')->nullable();
            $table->decimal('lat', 10, 7);
            $table->decimal('lon', 10, 7);
            $table->json('current_weather')->nullable(); 
            $table->timestamp('weather_updated_at')->nullable();
            $table->timestamps();


            $table->unique(['user_id', 'lat', 'lon']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorite_cities');
    }
};
