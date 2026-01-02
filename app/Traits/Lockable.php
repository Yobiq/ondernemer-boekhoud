<?php

namespace App\Traits;

trait Lockable
{
    /**
     * Lock the model (make it read-only)
     */
    public function lock(): void
    {
        $this->locked_at = now();
        $this->save();
    }

    /**
     * Check if the model is locked
     */
    public function isLocked(): bool
    {
        return !is_null($this->locked_at);
    }

    /**
     * Boot the trait - prevent updates on locked records
     */
    public static function bootLockable(): void
    {
        static::updating(function ($model) {
            if ($model->isLocked() && !$model->isDirty('locked_at')) {
                throw new \Exception(
                    get_class($model) . " #{$model->id} is locked and cannot be updated."
                );
            }
        });

        static::deleting(function ($model) {
            if ($model->isLocked()) {
                throw new \Exception(
                    get_class($model) . " #{$model->id} is locked and cannot be deleted."
                );
            }
        });
    }
}

