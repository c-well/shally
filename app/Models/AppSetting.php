<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Tiny key/value store for site-wide settings. Backed by app_settings table.
 * Cached for 60s so view rendering doesn't hammer the DB.
 */
class AppSetting extends Model
{
    protected $table = 'app_settings';
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::remember("app_setting:$key", 60, function () use ($key, $default) {
            $row = static::find($key);
            return $row?->value ?? $default;
        });
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("app_setting:$key");
    }
}
