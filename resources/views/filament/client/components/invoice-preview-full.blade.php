@php
    $formatDate = function($dateStr) {
        if (!$dateStr) return '';
        try {
            $date = is_string($dateStr) ? \Carbon\Carbon::parse($dateStr) : $dateStr;
            return $date->format('d-m-Y');
        } catch (\Exception $e) {
            return $dateStr;
        }
    };
    
    $formatCurrency = function($amount) {
        return '€ ' . number_format((float)$amount, 2, ',', '.');
    };
@endphp

<div class="invoice-preview-full">
    <div class="invoice-preview-header-section">
        <div class="invoice-preview-company">
            <div class="invoice-preview-company-name">{{ $data['sender_company_name'] ?? 'Uw Bedrijf' }}</div>
            <div class="invoice-preview-company-details">
                {{ $data['sender_address'] ?? 'Adresgegevens' }}<br>
                {{ $data['sender_email'] ?? 'email@voorbeeld.nl' }}<br>
                @if(isset($data['sender_phone']) && $data['sender_phone'])
                Tel: {{ $data['sender_phone'] }}<br>
                @endif
                @if(isset($data['sender_vat']) && $data['sender_vat'])
                BTW: {{ $data['sender_vat'] }}<br>
                @endif
                @if(isset($data['sender_kvk']) && $data['sender_kvk'])
                KVK: {{ $data['sender_kvk'] }}
                @endif
            </div>
        </div>
        <div class="invoice-preview-invoice-info">
            <div class="invoice-preview-invoice-title">FACTUUR</div>
            <div class="invoice-preview-invoice-details">
                <strong>Factuurnummer:</strong> {{ $data['invoice_number'] ?? 'AUTO' }}<br>
                <strong>Factuurdatum:</strong> {{ $formatDate($data['invoice_date']) }}<br>
                <strong>Vervaldatum:</strong> {{ $formatDate($data['due_date']) }}
            </div>
        </div>
    </div>

    <div class="invoice-preview-customer-section">
        <div class="invoice-preview-customer-label">Factureren aan</div>
        <div class="invoice-preview-customer-name">{{ $data['customer_name'] ?? 'Klant Naam' }}</div>
        <div class="invoice-preview-customer-details">
            {{ $data['customer_address'] ?? 'Klant Adres' }}<br>
            {{ $data['customer_email'] ?? 'klant@voorbeeld.nl' }}<br>
            @if(isset($data['customer_phone']) && $data['customer_phone'])
            Tel: {{ $data['customer_phone'] }}<br>
            @endif
            @if(isset($data['customer_vat']) && $data['customer_vat'])
            BTW-nummer: {{ $data['customer_vat'] }}<br>
            @endif
            @if(isset($data['customer_kvk']) && $data['customer_kvk'])
            KVK-nummer: {{ $data['customer_kvk'] }}
            @endif
        </div>
    </div>

    <table class="invoice-preview-items-table">
        <thead>
            <tr>
                <th>Omschrijving</th>
                <th class="text-right">Aantal</th>
                <th class="text-right">Prijs</th>
                <th class="text-right">BTW</th>
                <th class="text-right">Totaal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['items'] ?? [] as $item)
                @php
                    $quantity = (float)($item['quantity'] ?? 1);
                    $price = (float)($item['price'] ?? 0);
                    $vatRate = (float)($item['vat_rate'] ?? 21);
                    $itemSubtotal = $quantity * $price;
                    $itemVat = $itemSubtotal * ($vatRate / 100);
                    $itemTotal = $itemSubtotal + $itemVat;
                @endphp
                <tr>
                    <td>{{ $item['description'] ?? '' }}</td>
                    <td class="text-right">{{ number_format($quantity, 2, ',', '.') }}</td>
                    <td class="text-right">{{ $formatCurrency($price) }}</td>
                    <td class="text-right">{{ $vatRate }}%</td>
                    <td class="text-right">{{ $formatCurrency($itemTotal) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-tertiary); padding: 2rem;">
                        Geen items toegevoegd
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="invoice-preview-totals">
        <div class="invoice-preview-total-row">
            <span>Subtotaal (excl. BTW)</span>
            <span>{{ $formatCurrency($data['subtotal'] ?? 0) }}</span>
        </div>
        <div class="invoice-preview-total-row">
            <span>BTW</span>
            <span>{{ $formatCurrency($data['vat_total'] ?? 0) }}</span>
        </div>
        <div class="invoice-preview-total-row total">
            <span>Totaal (incl. BTW)</span>
            <span>{{ $formatCurrency($data['total'] ?? 0) }}</span>
        </div>
    </div>

    @if($data['notes'] ?? null)
    <div class="invoice-preview-notes">
        <div class="invoice-preview-notes-label">Opmerkingen:</div>
        <div class="invoice-preview-notes-text">{{ $data['notes'] }}</div>
    </div>
    @endif

    <div class="invoice-preview-footer">
        Bedankt voor uw vertrouwen!<br>
        Deze factuur is gegenereerd via MARCOFIC Klanten Portaal
    </div>
</div>

