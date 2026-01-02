# OCR Workflow Fix - Document Processing & VAT Period Attachment

## Problem Analysis

When clients upload documents (facturen/invoices), the following issues were identified:

1. **OCR jobs not processing**: Jobs were queued but queue worker wasn't running
2. **Missing VAT rubriek calculation**: Documents weren't getting VAT rubriek assigned before approval
3. **Missing VAT code**: VAT code wasn't being set before period attachment
4. **Documents not attached to periods**: Even when approved, documents might not have rubriek/code for period attachment

## Solutions Implemented

### 1. VAT Rubriek & Code Calculation in OCR Job

**File**: `app/Jobs/ProcessDocumentOcrJob.php`

**Changes**:
- Added VAT rubriek calculation **before** approval (Step 5)
- Added VAT code determination based on VAT rate
- Applied to both:
  - Regular OCR-processed documents
  - Form-created sales invoices (FactuurMaken)

**Code Location**:
```php
// Step 5: Calculate VAT rubriek and code BEFORE approval
$vatCalculator = app(\App\Services\VatCalculatorService::class);
$this->document->vat_rubriek = $vatCalculator->calculateRubriek($this->document);

// Determine VAT code if not set
if (!$this->document->vat_code) {
    $vatRate = (float) ($this->document->vat_rate ?? 0);
    $this->document->vat_code = match(true) {
        $vatRate == 21 => 'NL21',
        $vatRate == 9 => 'NL9',
        $vatRate == 0 => 'NL0',
        default => null,
    };
}
```

### 2. Form-Created Sales Invoices Fix

Form-created invoices (via FactuurMaken page) now also get:
- VAT rubriek calculated
- VAT code set
- Auto-approved flag set

### 3. Queue Worker Requirement

**IMPORTANT**: OCR processing runs asynchronously via queue. You must run a queue worker:

**Option A: Queue Worker (Recommended for Production)**
```bash
# Run continuously
php artisan queue:work --queue=ocr,default

# Or use Horizon (if installed)
php artisan horizon
```

**Option B: Sync Queue (Development Only)**
For development/testing, you can run jobs synchronously by setting in `.env`:
```
QUEUE_CONNECTION=sync
```

⚠️ **Warning**: Sync queue runs jobs immediately but blocks the request. Use only for development!

## Complete Workflow

### Client Uploads Document

1. **Upload** (`SmartUpload.php` or `DocumentUpload.php`)
   - Document created with `status='pending'`
   - `ProcessDocumentOcrJob` dispatched to queue

2. **OCR Processing** (`ProcessDocumentOcrJob`)
   - Status → `ocr_processing`
   - OCR extracts data (amounts, dates, supplier)
   - BTW validation
   - Ledger account suggestion
   - **VAT rubriek calculation** ✨ NEW
   - **VAT code determination** ✨ NEW
   - Status → `approved` (if auto-approvable) or `review_required`

3. **Document Observer** (`DocumentObserver::updated()`)
   - When status changes to `approved`
   - Calls `attachToVatPeriod()`
   - Document attached to correct VAT period with rubriek and code

4. **VAT Period Attachment** (`DocumentObserver::attachToVatPeriod()`)
   - Finds or creates current VAT period for client
   - Checks if document date is in period range
   - Attaches with:
     - `rubriek` (from document or calculated)
     - `btw_code` (from document or calculated)

## Testing the Fix

1. **Ensure queue worker is running**:
   ```bash
   php artisan queue:work --queue=ocr,default
   ```

2. **Upload a test document** via client portal

3. **Check logs**:
   ```bash
   tail -f storage/logs/laravel.log | grep "OCR Job"
   ```

4. **Verify document**:
   - Status should change: `pending` → `ocr_processing` → `approved`/`review_required`
   - `vat_rubriek` should be set (1a, 1b, 2a, etc.)
   - `vat_code` should be set (NL21, NL9, NL0)
   - Document should appear in VAT period workflow

5. **Check VAT Period**:
   - Go to Client Tax Workflow
   - Verify document appears in correct period
   - Verify BTW calculation includes the document

## Debugging

If documents stay in `pending` status:

1. **Check if queue worker is running**:
   ```bash
   ps aux | grep "queue:work"
   ```

2. **Check queue for pending jobs**:
   ```bash
   php artisan queue:work --once --queue=ocr
   ```

3. **Check logs for errors**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **Manually process a job**:
   ```bash
   php artisan queue:work --once --queue=ocr --verbose
   ```

## Key Files Modified

- `app/Jobs/ProcessDocumentOcrJob.php` - Added VAT rubriek/code calculation
- `app/Observers/DocumentObserver.php` - Already handles period attachment (no changes needed)

## Summary

✅ VAT rubriek is now calculated **before** document approval  
✅ VAT code is now set **before** document approval  
✅ Documents are properly attached to VAT periods with correct rubriek/code  
✅ Form-created invoices also get rubriek/code  
✅ Queue worker requirement documented  

The workflow now ensures that when documents are approved, they have all necessary data (rubriek, code) for correct VAT period attachment and calculation.


