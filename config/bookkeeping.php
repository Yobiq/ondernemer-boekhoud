<?php

/**
 * Bookkeeping System Configuration
 * 
 * MEDIUM PRIORITY FIX: Centralizes all magic numbers and thresholds
 * for easier adjustment and consistency across the system
 */

return [
    'auto_approval' => [
        // Confidence threshold for auto-approval (0-1 scale, where 1 = 100%)
        'confidence_threshold' => env('AUTO_APPROVAL_CONFIDENCE', 0.85),
        
        // OCR confidence threshold (0-100 scale)
        'ocr_confidence_threshold' => env('OCR_CONFIDENCE_THRESHOLD', 90),
        
        // Minimum confidence for ledger account suggestion
        'ledger_confidence_threshold' => env('LEDGER_CONFIDENCE_THRESHOLD', 50),
    ],

    'vat' => [
        // Maximum tolerance for VAT calculations (in euros)
        'tolerance' => env('VAT_TOLERANCE', 0.02),
        
        // Amount calculation tolerance (in euros)
        'amount_tolerance' => env('VAT_AMOUNT_TOLERANCE', 0.01),
    ],

    'insights' => [
        // Deviation multiplier for unusual amount detection
        // Amount is flagged if > (average * multiplier) or < (average / multiplier)
        'deviation_multiplier' => env('DEVIATION_MULTIPLIER', 3),
        
        // Minimum historical documents needed for deviation detection
        'min_history_for_deviation' => env('MIN_HISTORY_FOR_DEVIATION', 3),
    ],

    'ocr' => [
        // Maximum file size for OCR processing (in KB)
        'max_file_size_kb' => env('OCR_MAX_FILE_SIZE_KB', 10240), // 10MB
        
        // OCR processing timeout (in seconds)
        'timeout' => env('OCR_TIMEOUT', 300), // 5 minutes
    ],

    'periods' => [
        // Default VAT period type (quarterly or monthly)
        'default_type' => env('VAT_PERIOD_TYPE', 'quarterly'),
        
        // Allow adding documents to locked periods
        'allow_locked_period_changes' => env('ALLOW_LOCKED_PERIOD_CHANGES', true),
    ],

    'notifications' => [
        // Send notifications for failed period attachments
        'notify_period_attachment_failures' => env('NOTIFY_PERIOD_ATTACHMENT_FAILURES', true),
        
        // Send notifications for documents stuck in pending
        'notify_stuck_documents' => env('NOTIFY_STUCK_DOCUMENTS', true),
        
        // Hours before considering a document "stuck"
        'stuck_document_hours' => env('STUCK_DOCUMENT_HOURS', 24),
    ],
];


