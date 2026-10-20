<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class IntegrationSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $row = static::query()->where('key', $key)->first();

        return $row?->value ?? $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function getEncrypted(string $key, mixed $default = null): mixed
    {
        $value = static::getValue($key);

        if ($value === null || $value === '') {
            return $default;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value;
        }
    }

    public static function setEncrypted(string $key, ?string $value): void
    {
        if ($value === null || $value === '') {
            static::setValue($key, null);

            return;
        }

        static::setValue($key, Crypt::encryptString($value));
    }
}
