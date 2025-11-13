<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    public static function value(string $key, $default = null)
    {
        $cacheKey = "site_setting_{$key}";

        $setting = Cache::rememberForever($cacheKey, function () use ($key) {
            return static::where('key', $key)->first();
        });

        return $setting?->value ?? $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $fallback = $default ? '1' : '0';
        $value = static::value($key, $fallback);

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("site_setting_{$key}");
    }
}
