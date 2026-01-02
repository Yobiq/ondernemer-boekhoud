<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    // IMMUTABLE MODEL - No updates allowed!
    // Only created_at, NO updated_at (see migration)
    
    public const UPDATED_AT = null; // Disable updated_at
    
    protected $fillable = [
        'user_id',
        'action',
        'entity_type', // Keep for backward compatibility
        'entity_id', // Keep for backward compatibility
        'model_type', // New field
        'model_id', // New field
        'old_values',
        'new_values',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model (polymorphic)
     */
    public function model()
    {
        $type = $this->attributes['model_type'] ?? $this->attributes['entity_type'] ?? null;
        $id = $this->attributes['model_id'] ?? $this->attributes['entity_id'] ?? null;
        
        if (!$type || !$id) {
            return null;
        }
        
        // Handle both full class names and model names
        if (!class_exists($type)) {
            $type = "App\\Models\\{$type}";
        }
        
        if (class_exists($type)) {
            return $type::find($id);
        }
        
        return null;
    }
    
    /**
     * Get model type attribute (with fallback)
     */
    public function getModelTypeAttribute($value)
    {
        return $value ?? $this->attributes['entity_type'] ?? null;
    }
    
    /**
     * Get model id attribute (with fallback)
     */
    public function getModelIdAttribute($value)
    {
        return $value ?? $this->attributes['entity_id'] ?? null;
    }

    /**
     * Scope: For specific model
     */
    public function scopeForModel($query, $modelType, $modelId)
    {
        return $query->where(function ($q) use ($modelType, $modelId) {
            $q->where(function ($q2) use ($modelType, $modelId) {
                $q2->where('model_type', $modelType)
                   ->where('model_id', $modelId);
            })->orWhere(function ($q2) use ($modelType, $modelId) {
                $q2->where('entity_type', $modelType)
                   ->where('entity_id', $modelId);
            });
        });
    }

    /**
     * Scope: By user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: By action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Prevent updates - this model is append-only
     */
    public function update(array $attributes = [], array $options = [])
    {
        throw new \Exception('AuditLog records cannot be updated - they are immutable!');
    }

    /**
     * Prevent deletes - this model is append-only
     */
    public function delete()
    {
        throw new \Exception('AuditLog records cannot be deleted - they are immutable!');
    }
}
