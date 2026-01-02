# 📊 OCR Extractie Proces - Complete Documentatie

## 🔄 **COMPLETE OCR WORKFLOW**

### **1. Document Upload**
```
Client/Admin uploadt document
    ↓
Document record wordt aangemaakt
    - status: 'pending'
    - file_path: pad naar bestand
    - original_filename: bestandsnaam
    ↓
ProcessDocumentOcrJob wordt gedispatched naar 'ocr' queue
```

### **2. OCR Job Processing**
```
ProcessDocumentOcrJob::handle()
    ↓
Status update: 'pending' → 'ocr_processing'
    ↓
OcrService::processDocument(file_path)
    ↓
OCR Engine (OCR.space of Tesseract) extraheert data
    ↓
Data wordt genormaliseerd naar standaard structuur
    ↓
Document wordt geüpdatet met:
    - ocr_data (JSONB): volledige OCR output
    - amount_excl, amount_vat, amount_incl
    - vat_rate
    - document_date
    - supplier_name
    - supplier_vat
    - confidence_score
    ↓
BTW validatie
    ↓
Ledger account suggestie
    ↓
Status update: 'approved' of 'review_required'
```

### **3. Waar wordt OCR Data Opgeslagen?**

**Database:**
- **Tabel:** `documents`
- **Kolom:** `ocr_data` (JSONB type)
- **Structuur:**
```json
{
  "supplier": {
    "name": "Leverancier Naam",
    "vat_number": "NL123456789B01",
    "iban": "NL91ABNA0417164300"
  },
  "invoice": {
    "number": "FAC-2024-001",
    "date": "2024-12-31"
  },
  "amounts": {
    "excl": 100.00,
    "vat": 21.00,
    "incl": 121.00,
    "vat_rate": "21"
  },
  "currency": "EUR",
  "raw_text": "Volledige OCR tekst...",
  "confidence_scores": {
    "average": 85.5,
    "min": 80,
    "max": 90,
    "count": 1
  }
}
```

**Ook opgeslagen in aparte kolommen:**
- `amount_excl`, `amount_vat`, `amount_incl`
- `vat_rate`
- `document_date`
- `supplier_name`
- `supplier_vat`
- `confidence_score`

### **4. Waar kun je OCR Data Zien?**

#### **A. Document Review Pagina** (`/admin/document-review`)
- **Sectie:** "📊 OCR Geëxtraheerde Data"
- **Toont:**
  - ✅ OCR Status badge
  - Confidence score
  - Geëxtraheerde velden (Leverancier, Datum, Bedrag, BTW)
  - Raw OCR tekst (uitklapbaar)
  - Volledige JSON data (uitklapbaar)

#### **B. Documents By Client Pagina** (`/admin/documents-by-client`)
- **Kolom:** "OCR Confidence"
- **Toont:**
  - Confidence percentage
  - ✅ "Data" als OCR data beschikbaar is
  - ⏳ "Wacht" als nog geen data
- **Tooltip:** Toont welke data is geëxtraheerd (Bedragen, Leverancier, Datum)
- **Actie:** "📊 OCR Data" knop om volledige OCR data te bekijken

#### **C. Quick View Modal**
- **Actie:** "👁️ Quick View" op documenten tabel
- **Toont:** Basis document info inclusief OCR status

### **5. OCR Status Indicators**

| Status | Betekenis | Waar te zien |
|--------|----------|--------------|
| `pending` | Wacht op OCR verwerking | Documents tabel, status kolom |
| `ocr_processing` | OCR is bezig | Documents tabel, status kolom |
| `review_required` | OCR klaar, handmatige review nodig | Document Review pagina |
| `approved` | OCR succesvol, goedgekeurd | Documents tabel, status kolom |

### **6. Debugging OCR Problemen**

**Check Queue Worker:**
```bash
php artisan queue:work --queue=ocr,default
# of
php artisan horizon
```

**Check Logs:**
```bash
tail -f storage/logs/laravel.log | grep OCR
```

**Check Document OCR Data:**
```php
$document = Document::find($id);
dd($document->ocr_data); // Volledige OCR data
dd($document->status); // Huidige status
```

**Check of Job is Gedispatched:**
```php
// In DocumentObserver of upload handler
ProcessDocumentOcrJob::dispatch($document);
// Check queue: php artisan queue:work
```

### **7. OCR Data Structuur**

**Volledige Structuur:**
```php
[
    'supplier' => [
        'name' => string|null,
        'vat_number' => string|null,
        'iban' => string|null,
    ],
    'invoice' => [
        'number' => string|null,
        'date' => string|null, // YYYY-MM-DD format
    ],
    'amounts' => [
        'excl' => float|null,
        'vat' => float|null,
        'incl' => float|null,
        'vat_rate' => string|null, // '21', '9', '0', 'verlegd'
    ],
    'currency' => string, // Default: 'EUR'
    'raw_text' => string, // Volledige OCR tekst
    'confidence_scores' => [
        'average' => float,
        'min' => float,
        'max' => float,
        'count' => int,
    ],
]
```

### **8. Wanneer wordt OCR Data Geëxtraheerd?**

1. **Direct na upload** (via queue job)
2. **Bij handmatige reprocessing** (via "🔄 Herverwerk OCR" actie)
3. **Bij bulk reprocessing** (via bulk actie)

### **9. Waar wordt OCR Data Gebruikt?**

- **BTW Validatie:** `amount_excl`, `amount_vat`, `vat_rate`
- **Ledger Suggestie:** `supplier_name`, `document_type`, `amounts`
- **Auto-Approval:** Confidence score + BTW validatie
- **VAT Period Attachment:** `vat_rubriek`, `vat_code`
- **Document Review:** Alle velden voor handmatige controle

### **10. Troubleshooting**

**Probleem: OCR data wordt niet geëxtraheerd**
- ✅ Check queue worker: `php artisan queue:work --queue=ocr`
- ✅ Check logs: `storage/logs/laravel.log`
- ✅ Check file path: Bestand moet bestaan op `storage/app/private/`
- ✅ Check OCR.space API key: `config('ocr.ocrspace_api_key')`

**Probleem: OCR data is leeg**
- ✅ Check OCR engine: OCR.space of Tesseract beschikbaar?
- ✅ Check bestand: Is het bestand leesbaar?
- ✅ Check logs: Zijn er errors in `laravel.log`?

**Probleem: Data wordt niet getoond in UI**
- ✅ Check `ocr_data` kolom: Is het niet null?
- ✅ Check Blade view: `filament.components.ocr-data-viewer`
- ✅ Check document status: Moet 'review_required' of 'approved' zijn

---

## 📍 **SNEL REFERENTIE**

**Waar zie je OCR data?**
1. `/admin/document-review` - Volledige OCR data viewer
2. `/admin/documents-by-client` - OCR confidence kolom + tooltip
3. Quick View modal - Basis OCR status

**Hoe check je of OCR werkt?**
1. Upload een document
2. Check queue worker: `php artisan queue:work --queue=ocr`
3. Check document status: Moet 'ocr_processing' → 'review_required' of 'approved'
4. Check `ocr_data` kolom in database of via Document Review pagina

**Hoe reprocess je OCR?**
1. Ga naar Documents By Client
2. Klik op "🔄 Herverwerk OCR" actie
3. Of gebruik bulk actie "🔄 Bulk OCR Herverwerken"


