<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * Get a setting value by key with optional default.
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        if ($setting !== null && $setting->value !== null && $setting->value !== '') {
            return $setting->value;
        }
        return $default;
    }

    /**
     * Set / update a setting value by key.
     */
    public static function set(string $key, $value, ?string $description = null)
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : ($value ?? ''),
                'description' => $description,
            ]
        );
    }

    /**
     * Get all settings as key => value associative array.
     */
    public static function getAllAsArray(): array
    {
        return static::pluck('value', 'key')->toArray();
    }
}
