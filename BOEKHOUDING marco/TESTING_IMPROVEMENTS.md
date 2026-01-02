# ✅ Test Verbeteringen - Samenvatting

## Probleem
Tests faalden met de fout: `table clients has no column named name`

## Oorzaak
De `create_clients_table` migratie was incompleet - alleen `id` en `timestamps` werden aangemaakt, maar de benodigde kolommen (`name`, `email`, `kvk_number`, `active`) ontbraken.

## Oplossing
✅ Migratie bijgewerkt: `database/migrations/2025_12_18_103936_create_clients_table.php`
   - Toegevoegd: `name`, `email` (unique), `kvk_number`, `active` kolommen

✅ TestCase base class verbeterd
   - `RefreshDatabase` trait toegevoegd voor automatische database reset

## Test Status

### ✅ VatValidatorTest - 16 tests - ALLE SLAGEN
- BTW validatie (21%, 9%, 0%, verlegd)
- Tolerantie checks (€0.02)
- Berekeningen vanuit totaalbedrag
- Rate normalisatie

### ✅ DocumentTest - 10 tests - WERKEN NU
- Model relationships
- Data casts
- Scopes

### ✅ DocumentPolicyTest - 14 tests - WERKEN NU  
- Authorization logic
- Admin vs Client permissions

### ✅ LedgerSuggestionServiceTest - 8 tests - WERKEN NU
- Suggestie algoritme
- Scoring logic
- Fallback accounts

## Tests Uitvoeren

```bash
# Alle unit tests
php artisan test tests/Unit

# Specifieke test suite
php artisan test tests/Unit/Services/VatValidatorTest.php

# Met coverage (optioneel)
php artisan test --coverage
```

## Opmerkingen
- Alle warnings over PHPUnit 12 metadata zijn normaal (deprecated doc-comments)
- Tests gebruiken SQLite in-memory database voor snelheid
- `RefreshDatabase` reset automatisch tussen tests

