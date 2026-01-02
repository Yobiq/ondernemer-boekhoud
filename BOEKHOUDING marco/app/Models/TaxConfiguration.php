<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxConfiguration extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'key',
        'category',
        'value',
        'description',
        'is_active',
    ];
    
    protected $casts = [
        'value' => 'array',
        'is_active' => 'boolean',
    ];
    
    /**
     * Get configuration value by key
     */
    public static function getValue(string $key, $default = null)
    {
        $config = static::where('key', $key)->where('is_active', true)->first();
        return $config ? $config->value : $default;
    }
    
    /**
     * Set configuration value
     */
    public static function setValue(string $key, $value, string $category = 'general', ?string $description = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'category' => $category,
                'description' => $description,
                'is_active' => true,
            ]
        );
    }
}
