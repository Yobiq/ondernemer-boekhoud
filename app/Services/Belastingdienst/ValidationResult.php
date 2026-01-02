<?php

namespace App\Services\Belastingdienst;

class ValidationResult
{
    public bool $isValid;
    public array $errors = [];
    public array $warnings = [];
    
    public function __construct(bool $isValid = true)
    {
        $this->isValid = $isValid;
    }
    
    public function addError(string $field, string $message): void
    {
        $this->isValid = false;
        $this->errors[$field] = $message;
    }
    
    public function addWarning(string $field, string $message): void
    {
        $this->warnings[$field] = $message;
    }
}

