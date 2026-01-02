<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class OcrConfiguration extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'document_type',
        'engine',
        'confidence_threshold',
        'enabled',
        'engine_settings',
        'api_keys',
        'usage_count',
        'average_confidence',
        'average_processing_time',
        'notes',
    ];
    
    protected $casts = [
        'confidence_threshold' => 'decimal:2',
        'enabled' => 'boolean',
        'engine_settings' => 'array',
        'api_keys' => 'array',
        'usage_count' => 'integer',
        'average_confidence' => 'decimal:2',
        'average_processing_time' => 'decimal:2',
    ];
    
    /**
     * Get encrypted API key
     */
    public function getApiKey(string $provider): ?string
    {
        $keys = $this->api_keys ?? [];
        
        if (!isset($keys[$provider])) {
            return null;
        }
        
        try {
            return Crypt::decryptString($keys[$provider]);
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Set encrypted API key
     */
    public function setApiKey(string $provider, string $key): void
    {
        $keys = $this->api_keys ?? [];
        $keys[$provider] = Crypt::encryptString($key);
        $this->api_keys = $keys;
    }
    
    /**
     * Update performance metrics
     */
    public function updateMetrics(float $confidence, float $processingTime): void
    {
        $this->usage_count++;
        
        // Update average confidence
        if ($this->average_confidence === null) {
            $this->average_confidence = $confidence;
        } else {
            $this->average_confidence = (($this->average_confidence * ($this->usage_count - 1)) + $confidence) / $this->usage_count;
        }
        
        // Update average processing time
        if ($this->average_processing_time === null) {
            $this->average_processing_time = $processingTime;
        } else {
            $this->average_processing_time = (($this->average_processing_time * ($this->usage_count - 1)) + $processingTime) / $this->usage_count;
        }
        
        $this->save();
    }
}
