<?php

namespace App\Models;

use Database\Factories\ClinicSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicSetting extends Model
{
    /** @use HasFactory<ClinicSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'label',
        'description',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, ?string $value, ?string $group = 'general', ?string $label = null): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group ?? 'general',
                'label' => $label,
            ]
        );
    }
}
