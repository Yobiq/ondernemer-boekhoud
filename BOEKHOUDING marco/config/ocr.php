<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default OCR Engine
    |--------------------------------------------------------------------------
    |
    | The default OCR engine to use when no specific engine is requested.
    | Options: tesseract, aws_textract, google_vision, azure_form_recognizer
    |
    */
    'default_engine' => env('OCR_DEFAULT_ENGINE', 'tesseract'),
    
    /*
    |--------------------------------------------------------------------------
    | OCR.space API Key
    |--------------------------------------------------------------------------
    |
    | Your OCR.space API key for free OCR processing.
    |
    */
    'ocrspace_api_key' => env('OCRSPACE_API_KEY', 'K81873206488957'),
    
    /*
    |--------------------------------------------------------------------------
    | OCR Engine Configuration per Document Type
    |--------------------------------------------------------------------------
    |
    | Configure which OCR engine to use for each document type.
    | This allows optimization: use cloud OCR for complex documents,
    | local Tesseract for simple ones.
    |
    */
    'engines' => [
        'invoice' => [
            'engine' => env('OCR_ENGINE_INVOICE', 'tesseract'),
            'confidence_threshold' => 75,
        ],
        'receipt' => [
            'engine' => env('OCR_ENGINE_RECEIPT', 'tesseract'),
            'confidence_threshold' => 70,
        ],
        'form' => [
            'engine' => env('OCR_ENGINE_FORM', 'tesseract'),
            'confidence_threshold' => 75,
        ],
        'bank_statement' => [
            'engine' => env('OCR_ENGINE_BANK', 'tesseract'),
            'confidence_threshold' => 70,
        ],
        'simple' => [
            'engine' => 'tesseract',
            'confidence_threshold' => 65,
        ],
        'complex' => [
            'engine' => 'tesseract',
            'confidence_threshold' => 75,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Fallback Chain
    |--------------------------------------------------------------------------
    |
    | If the primary OCR engine fails, try these engines in order.
    |
    */
    'fallback_chain' => [
        'tesseract' => ['ocrspace'], // Tesseract first, fallback to OCR.space
        'ocrspace' => [], // OCR.space as fallback only
        'aws_textract' => ['tesseract', 'ocrspace', 'google_vision', 'azure_form_recognizer'],
        'google_vision' => ['tesseract', 'ocrspace', 'azure_form_recognizer'],
        'azure_form_recognizer' => ['tesseract', 'ocrspace', 'aws_textract'],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cost Optimization
    |--------------------------------------------------------------------------
    |
    | Use local Tesseract for files smaller than this size (in bytes).
    | Larger files will use cloud OCR.
    |
    */
    'cost_optimization' => [
        'max_file_size_for_tesseract' => 500000, // 500KB
    ],
];

