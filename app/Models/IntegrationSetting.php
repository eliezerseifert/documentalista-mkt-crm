<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'payload',
    ];

    protected $casts = [
        'payload' => 'encrypted:array',
    ];

    public static function get(string $key, array $default = []): array
    {
        $setting = static::where('key', $key)->first();
        return $setting ? ($setting->payload ?? $default) : $default;
    }

    public static function set(string $key, array $payload): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['payload' => $payload]
        );
    }
}
