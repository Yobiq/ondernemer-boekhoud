# Admin/Boekhouder Portaal - Implementatie Plan

**Gebaseerd op:** MARCOFIC KLANTENPORTAAL – DEFINITIEVE BOEKHOUDER SPECIFICATIE
**Status:** Planning
**Focus:** Volledige implementatie van boekhouder portaal met BTW-rapportage per klant

---

## Overzicht

Dit plan beschrijft de implementatie van het admin/boekhouder portaal volgens de definitieve specificatie. Het portaal moet:
- Automatiseringsgraad tonen (90-95% auto-approval)
- BTW-rapportage per klant ondersteunen
- Audit-proof workflow hebben
- Volledig controleerbaar zijn (geen black box)

---

## Fase 1: Database & Models

### 1.1 VatPeriod Model & Migration

**Nieuwe tabel:** `vat_periods`

**Velden:**
- `id` (primary key)
- `client_id` (foreign key naar clients)
- `period_start` (date) - Start van BTW-periode
- `period_end` (date) - Einde van BTW-periode
- `status` (enum: 'open', 'voorbereid', 'ingediend', 'afgesloten')
- `prepared_by` (foreign key naar users, nullable)
- `prepared_at` (timestamp, nullable)
- `submitted_by` (foreign key naar users, nullable)
- `submitted_at` (timestamp, nullable)
- `closed_by` (foreign key naar users, nullable)
- `closed_at` (timestamp, nullable)
- `year` (integer) - Voor filtering
- `quarter` (integer, nullable) - Q1-Q4 of null voor maandelijkse periodes
- `month` (integer, nullable) - 1-12 of null voor kwartaalperiodes
- `notes` (text, nullable)
- `timestamps`

**Indexes:**
- `client_id`, `status`, `year`, `quarter`, `month`
- Composite: `(client_id, year, quarter)`, `(client_id, year, month)`

**Model:** `app/Models/VatPeriod.php`
- Relationships: `client()`, `preparedBy()`, `submittedBy()`, `closedBy()`, `documents()`
- Methods: `isOpen()`, `isLocked()`, `canBeModified()`, `lock()`

### 1.2 VatPeriodDocument Pivot Table

**Nieuwe tabel:** `vat_period_documents`

**Velden:**
- `vat_period_id` (foreign key)
- `document_id` (foreign key)
- `rubriek` (string) - BTW rubriek (1a, 1b, 1c, 2a, 3a, 3b, 4a, 5b)
- `btw_code` (string) - BTW code (NL21, NL9, NL0, VERL, etc.)
- `created_at`, `updated_at`

**Indexes:**
- `vat_period_id`, `document_id`, `rubriek`
- Unique: `(vat_period_id, document_id)`

### 1.3 Audit Log Tabel (Append-Only)

**Nieuwe tabel:** `audit_logs`

**Velden:**
- `id` (primary key)
- `user_id` (foreign key naar users, nullable) - NULL = systeem actie
- `action` (string) - 'upload', 'approve', 'reject', 'modify', 'lock', etc.
- `model_type` (string) - 'Document', 'VatPeriod', etc.
- `model_id` (unsignedBigInteger)
- `old_values` (json, nullable)
- `new_values` (json, nullable)
- `metadata` (json, nullable) - Extra context
- `ip_address` (string, nullable)
- `user_agent` (string, nullable)
- `created_at` (timestamp) - NO updated_at (append-only)

**Indexes:**
- `user_id`, `action`, `model_type`, `model_id`, `created_at`
- Composite: `(model_type, model_id)`

**Model:** `app/Models/AuditLog.php`
- Relationships: `user()`, `model()` (morphTo)
- Scope: `forModel()`, `byUser()`, `byAction()`

### 1.4 Document Model Uitbreidingen

**Toevoegen aan Document model:**
- `vat_rubriek` (string, nullable) - BTW rubriek
- `vat_code` (string, nullable) - BTW code
- `auto_approved` (boolean, default false)
- `auto_approval_reason` (text, nullable) - Waarom auto-approved
- `review_required_reason` (text, nullable) - Waarom handmatige controle nodig

**Relationships:**
- `vatPeriods()` - Many-to-many via pivot

---

## Fase 2: Services & Business Logic

### 2.1 VatCalculatorService

**Bestand:** `app/Services/VatCalculatorService.php`

**Methoden:**
- `calculateRubriek(Document $document): string` - Bepaal rubriek op basis van BTW code
- `validateVatCalculation(Document $document): array` - Valideer BTW berekening
- `calculatePeriodTotals(VatPeriod $period): array` - Bereken totalen per rubriek
- `getRubriekMapping(): array` - Mapping BTW code → rubriek

**Rubriek Mapping:**
- NL21 → 1a (hoog tarief)
- NL9 → 1b (laag tarief)
- NL0 → 1c (vrijgesteld)
- VERL → 2a (verleggingsregeling)
- EU → 3a/3b (intracommunautair)
- IMPORT → 4a/4b (import)
- VOORBELASTING → 5b

### 2.2 VatPeriodLockService

**Bestand:** `app/Services/VatPeriodLockService.php`

**Methoden:**
- `lock(VatPeriod $period, User $user): void` - Sluit periode af
- `unlock(VatPeriod $period, User $user): void` - Ontgrendel (alleen indien status = ingediend)
- `canLock(VatPeriod $period): bool` - Check of periode kan worden afgesloten
- `validateBeforeLock(VatPeriod $period): array` - Valideer voordat afsluiten

**Validatie regels:**
- Alle documenten moeten goedgekeurd zijn
- Geen openstaande taken
- BTW berekeningen moeten kloppen

### 2.3 AuditLogger Service

**Bestand:** `app/Services/AuditLogger.php`

**Methoden:**
- `log(string $action, Model $model, ?User $user = null, ?array $oldValues = null, ?array $newValues = null): AuditLog`
- `logDocumentUpload(Document $document, User $user): void`
- `logDocumentApproval(Document $document, User $user, bool $autoApproved = false): void`
- `logPeriodLock(VatPeriod $period, User $user): void`
- `getHistory(Model $model): Collection` - Haal volledige historie op

**Principes:**
- Append-only (geen updates/deletes)
- Alle acties worden gelogd
- Volledige context (old/new values)

### 2.4 AutoApprovalService

**Bestand:** `app/Services/AutoApprovalService.php`

**Methoden:**
- `shouldAutoApprove(Document $document): bool` - Bepaal of document auto-approved kan worden
- `autoApprove(Document $document): void` - Auto-approve document
- `getAutoApprovalReasons(Document $document): array` - Redenen waarom wel/niet

**Auto-approval criteria:**
- BTW berekening klopt (met tolerantie)
- Grootboek is gematcht
- Geen afwijkingen t.o.v. historie
- Confidence score > threshold

---

## Fase 3: Filament Resources

### 3.1 VatPeriodResource

**Bestand:** `app/Filament/Resources/VatPeriodResource.php`

**Table Columns:**
- Client (relationship)
- Periode (formatted: "2024-Q1" of "Januari 2024")
- Status (badge met kleuren)
- Start datum
- Eind datum
- Voorbereid door (nullable)
- Ingediend door (nullable)
- Afgesloten door (nullable)
- Acties

**Table Filters:**
- Client (select)
- Status (select)
- Jaar (select)
- Kwartaal (select)
- Maand (select)

**Form Fields:**
- Client (required, searchable)
- Period start (date, required)
- Period end (date, required)
- Status (select, readonly als afgesloten)
- Notes (textarea)

**Actions:**
- **Voorbereiden** - Markeer als voorbereid
- **Markeer als Ingediend** - Status → ingediend
- **Afsluiten** - Lock periode (met confirmatie)
- **Bekijk Rubrieken** - Navigeer naar rubriek overzicht
- **Export PDF** - Genereer BTW rapport PDF
- **Export XML** - Genereer voor Belastingdienst

**Pages:**
- `ListVatPeriods` - Overzicht met filters
- `CreateVatPeriod` - Nieuwe periode aanmaken
- `EditVatPeriod` - Bewerken (alleen indien open)
- `ViewVatPeriod` - Detail view met rubrieken

**Navigation:**
- Group: "Financieel"
- Icon: `heroicon-o-document-check`
- Label: "BTW Periodes"
- Sort: 10

### 3.2 VatRubricOverviewResource

**Bestand:** `app/Filament/Resources/VatRubricOverviewResource.php`

**Context:** Dit is een relation manager binnen VatPeriodResource

**Table Columns:**
- Rubriek (1a, 1b, 1c, 2a, 3a, 3b, 4a, 5b)
- Grondslag totaal (amount_excl)
- BTW totaal (amount_vat)
- Aantal documenten (count)
- Acties

**Actions:**
- **Bekijk Documenten** - Drill-down naar documentenlijst

**View:**
- Tabel met alle rubrieken
- Totalen onderaan
- Read-only indien periode afgesloten

### 3.3 Document Drill-Down View

**Nieuwe Page:** `app/Filament/Pages/VatPeriodDocuments.php`

**Functionaliteit:**
- Toont alle documenten voor een specifieke rubriek in een periode
- PDF preview
- Grootboekrekening
- BTW code
- Rubriek
- Audit trail (wie/wat/wanneer)
- Read-only indien periode afgesloten

**Layout:**
- Split view: Links documentenlijst, rechts detail/preview
- Filter op rubriek
- Export opties

### 3.4 BtwReportResource Aanpassen

**Bestand:** `app/Filament/Resources/BtwReportResource.php`

**Aanpassingen:**
- Integreer met VatPeriod model
- Gebruik VatPeriod als bron voor data
- Behoud backward compatibility

---

## Fase 4: Dashboard Widgets

### 4.1 AutomationRateWidget

**Bestand:** `app/Filament/Widgets/AutomationRateWidget.php` (bestaat al, uitbreiden)

**Uitbreidingen:**
- Per klant automatiseringsgraad
- Trend over tijd
- Top 10 klanten (hoog/laag)

### 4.2 VatPeriodsOverviewWidget

**Nieuw:** `app/Filament/Widgets/VatPeriodsOverviewWidget.php`

**Toont:**
- Aantal open periodes
- Aantal voorbereide periodes
- Aantal ingediende periodes
- Aantal afgesloten periodes (deze maand/kwartaal)

### 4.3 DocumentsAwaitingReviewWidget

**Bestand:** `app/Filament/Widgets/DocumentsAwaitingReviewWidget.php` (bestaat al)

**Uitbreidingen:**
- Reden waarom review nodig
- Prioriteit (op basis van bedrag/afwijking)

---

## Fase 5: PDF BTW Rapport Template

### 5.1 PDF Template

**Bestand:** `resources/views/filament/btw-reports/vat-period-pdf.blade.php`

**Inhoud:**
- Klantgegevens (header)
- Periode informatie
- Rubrieken tabel (1a t/m 5b)
- Totalen sectie
- Verklaring boekhouder
- Datum & gebruiker
- Audit trail samenvatting

**Styling:**
- A4 formaat
- Zwart/wit (Belastingdienst stijl)
- Professioneel, clean design
- Print-ready

### 5.2 PDF Generation Service

**Bestand:** `app/Services/VatPeriodPdfService.php`

**Methoden:**
- `generate(VatPeriod $period): string` - Genereer PDF, retourneer file path
- `stream(VatPeriod $period): Response` - Stream PDF naar browser
- `download(VatPeriod $period): Response` - Download PDF

**Gebruik:** Dompdf of TCPDF

---

## Fase 6: Document Review Workflow

### 6.1 DocumentReview Page Uitbreiden

**Bestand:** `app/Filament/Pages/DocumentReview.php` (bestaat al)

**Uitbreidingen:**
- Auto-approval status tonen
- Reden voor review tonen
- BTW rubriek toewijzen
- BTW code validatie
- Audit log tonen

### 6.2 Auto-Approval Badge

**Toevoegen:**
- Badge die toont of document auto-approved is
- Reden voor auto-approval
- Mogelijkheid om auto-approval te overschrijven

---

## Fase 7: Policies & Permissions

### 7.1 VatPeriodPolicy

**Bestand:** `app/Policies/VatPeriodPolicy.php`

**Regels:**
- Boekhouder: Full access
- Admin: Full access
- Klant: Read-only (eigen periodes)

**Methods:**
- `viewAny()`, `view()`, `create()`, `update()`, `delete()`, `lock()`, `unlock()`

### 7.2 AuditLogPolicy

**Bestand:** `app/Policies/AuditLogPolicy.php`

**Regels:**
- Alleen lezen (append-only)
- Boekhouder/Admin: Full access
- Klant: Alleen eigen audit logs

---

## Fase 8: Migrations

### 8.1 Nieuwe Migrations

1. `create_vat_periods_table.php`
2. `create_vat_period_documents_table.php`
3. `create_audit_logs_table.php`
4. `add_vat_fields_to_documents_table.php` - vat_rubriek, vat_code, auto_approved, etc.

### 8.2 Data Migration

**Script:** `database/migrations/migrate_btw_reports_to_vat_periods.php`

**Doel:** Migreer bestaande BtwReport data naar VatPeriod model (indien nodig)

---

## Fase 9: Tests

### 9.1 Unit Tests

- `VatCalculatorServiceTest`
- `VatPeriodLockServiceTest`
- `AutoApprovalServiceTest`
- `AuditLoggerTest`

### 9.2 Feature Tests

- `VatPeriodResourceTest`
- `VatPeriodWorkflowTest`
- `DocumentAutoApprovalTest`
- `AuditLogTest`

---

## Fase 10: Documentatie

### 10.1 Admin Panel Guide

**Bestand:** `ADMIN_PANEL_GUIDE.md`

**Inhoud:**
- Overzicht admin/boekhouder portaal
- BTW periode workflow
- Document review proces
- Auto-approval uitleg
- Audit log gebruik

### 10.2 BTW Module Guide

**Bestand:** `BTW_MODULE_GUIDE.md`

**Inhoud:**
- BTW rubrieken uitleg
- Periode workflow
- PDF/XML export
- Drill-down functionaliteit

---

## Implementatie Volgorde

1. **Fase 1:** Database & Models (VatPeriod, AuditLog, etc.)
2. **Fase 2:** Services (VatCalculator, VatPeriodLock, AuditLogger, AutoApproval)
3. **Fase 3:** Filament Resources (VatPeriodResource, VatRubricOverview)
4. **Fase 4:** Dashboard Widgets
5. **Fase 5:** PDF Template
6. **Fase 6:** Document Review Uitbreidingen
7. **Fase 7:** Policies & Permissions
8. **Fase 8:** Migrations
9. **Fase 9:** Tests
10. **Fase 10:** Documentatie

---

## Belangrijke Aandachtspunten

1. **Append-Only Audit Log:** Geen updates/deletes, alleen inserts
2. **Period Locking:** Na afsluiten geen mutaties mogelijk
3. **Auto-Approval:** Volledig controleerbaar, geen black box
4. **BTW Validatie:** Strikte validatie voordat goedkeuring
5. **Backward Compatibility:** Bestaande BtwReport functionaliteit behouden
6. **Performance:** Indexes op kritieke queries
7. **Security:** Role-based access control overal

---

## Acceptatiecriteria

- [ ] VatPeriod kan worden aangemaakt per klant
- [ ] BTW rubrieken worden correct berekend
- [ ] Periode kan worden afgesloten (lock)
- [ ] Na afsluiten geen mutaties mogelijk
- [ ] Audit log bevat alle acties
- [ ] PDF rapport is audit-proof
- [ ] Auto-approval werkt (90-95%)
- [ ] Drill-down naar documenten werkt
- [ ] Dashboard toont automatiseringsgraad
- [ ] Alle policies werken correct

---

## Bestanden om te Maken/Wijzigen

**Nieuwe Models:**
- `app/Models/VatPeriod.php`
- `app/Models/AuditLog.php`

**Nieuwe Services:**
- `app/Services/VatCalculatorService.php`
- `app/Services/VatPeriodLockService.php`
- `app/Services/AuditLogger.php`
- `app/Services/AutoApprovalService.php`
- `app/Services/VatPeriodPdfService.php`

**Nieuwe Resources:**
- `app/Filament/Resources/VatPeriodResource.php`
- `app/Filament/Resources/VatRubricOverviewResource.php` (RelationManager)

**Nieuwe Pages:**
- `app/Filament/Pages/VatPeriodDocuments.php`

**Nieuwe Policies:**
- `app/Policies/VatPeriodPolicy.php`
- `app/Policies/AuditLogPolicy.php`

**Nieuwe Migrations:**
- `create_vat_periods_table.php`
- `create_vat_period_documents_table.php`
- `create_audit_logs_table.php`
- `add_vat_fields_to_documents_table.php`

**Nieuwe Views:**
- `resources/views/filament/btw-reports/vat-period-pdf.blade.php`

**Wijzigen:**
- `app/Models/Document.php` - Uitbreiden met vat fields
- `app/Filament/Pages/DocumentReview.php` - Auto-approval features
- `app/Filament/Widgets/AutomationRateWidget.php` - Uitbreiden
- `app/Filament/Resources/BtwReportResource.php` - Integratie met VatPeriod

---

**Einde Plan**


