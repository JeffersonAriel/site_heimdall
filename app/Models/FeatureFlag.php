<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $fillable = ['key', 'enabled', 'config'];

    protected $casts = [
        'enabled' => 'boolean',
        'config' => 'array',
    ];

    /**
     * Check if a feature flag is enabled.
     */
    public static function isEnabled(string $key, bool $default = false): bool
    {
        $flag = self::where('key', $key)->first();
        return $flag ? $flag->enabled : $default;
    }
}
