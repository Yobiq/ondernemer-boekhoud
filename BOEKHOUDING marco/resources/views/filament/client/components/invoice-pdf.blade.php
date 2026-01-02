<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Factuur {{ $data['invoice_number'] ?? 'N/A' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @page {
            size: A4 portrait;
            margin: 1.2cm 1.5cm;
        }
        
        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Arial, sans-serif;
            font-size: 9.5pt;
            line-height: 1.5;
            color: #1e293b;
            background: #ffffff;
        }
        
        .invoice-container {
            width: 100%;
            max-width: 100%;
        }
        
        /* Header Section - Clean Layout */
        .invoice-header {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-bottom: 0.7cm;
            padding-bottom: 0.5cm;
            border-bottom: 2px solid #10b981;
        }
        
        .header-left,
        .header-right {
            display: table-cell;
            vertical-align: top;
        }
        
        .header-left {
            width: 60%;
            padding-right: 1cm;
            padding-left: 0.5cm;
        }
        
        .header-right {
            width: 40%;
            text-align: right;
            padding-top: 0.4cm;
            padding-right: 0.3cm;
        }
        
        .logo-container {
            margin-bottom: 0.3cm;
        }
        
        .logo-img {
            max-height: 1.5cm;
            max-width: 6cm;
            object-fit: contain;
        }
        
        .company-name {
            font-size: 18pt;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.2cm;
            line-height: 1.2;
            letter-spacing: -0.2px;
        }
        
        .company-details {
            font-size: 8.5pt;
            color: #475569;
            line-height: 1.6;
            margin-top: 0.25cm;
        }
        
        .company-details strong {
            color: #334155;
            font-weight: 600;
            min-width: 1.3cm;
            display: inline-block;
        }
        
        .invoice-title {
            display: none;
        }
        
        /* Invoice Details Box - Light Green Background */
        .invoice-details-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 0.4cm 0.5cm;
            display: inline-block;
            text-align: left;
            min-width: 4.5cm;
        }
        
        .invoice-details-label {
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            color: #059669;
            letter-spacing: 0.5px;
            margin-bottom: 0.25cm;
        }
        
        .invoice-details-row {
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.8;
            margin-bottom: 0.1cm;
        }
        
        .invoice-details-row:last-child {
            margin-bottom: 0;
        }
        
        .invoice-details-label-text {
            font-weight: 600;
            color: #334155;
            display: inline-block;
            min-width: 1.4cm;
        }
        
        .invoice-details-value {
            color: #0f172a;
            font-weight: 500;
        }
        
        /* Customer Section - Light Green Background */
        .customer-section {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 0.4cm 0.5cm;
            margin-bottom: 0.5cm;
        }
        
        .customer-label {
            font-size: 9pt;
            font-weight: 700;
            text-transform: uppercase;
            color: #059669;
            letter-spacing: 0.5px;
            margin-bottom: 0.2cm;
        }
        
        .customer-name {
            font-size: 11pt;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.15cm;
            line-height: 1.3;
        }
        
        .customer-details {
            font-size: 8.5pt;
            color: #475569;
            line-height: 1.6;
        }
        
        .customer-details strong {
            color: #334155;
            font-weight: 600;
            min-width: 1.3cm;
            display: inline-block;
        }
        
        /* Items Section - Green Heading */
        .items-section-label {
            font-size: 9pt;
            font-weight: 700;
            text-transform: uppercase;
            color: #059669;
            letter-spacing: 0.5px;
            margin-bottom: 0.3cm;
        }
        
        .items-table-wrapper {
            margin-bottom: 0.5cm;
        }
        
        table.items-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 9pt;
            background: #ffffff;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #d1fae5;
        }
        
        table.items-table thead {
            background: #059669;
        }
        
        table.items-table th {
            padding: 0.3cm 0.3cm;
            text-align: left;
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            color: #ffffff;
            letter-spacing: 0.5px;
            border: none;
        }
        
        table.items-table th.text-right {
            text-align: right;
        }
        
        table.items-table th:nth-child(1) { width: 42%; }
        table.items-table th:nth-child(2) { width: 12%; }
        table.items-table th:nth-child(3) { width: 16%; }
        table.items-table th:nth-child(4) { width: 12%; }
        table.items-table th:nth-child(5) { width: 18%; }
        
        table.items-table td {
            padding: 0.3cm 0.3cm;
            border-bottom: 1px solid #f0fdf4;
            color: #1e293b;
            vertical-align: middle;
            background: #ffffff;
        }
        
        table.items-table td.text-right {
            text-align: right;
        }
        
        table.items-table tbody tr:nth-child(even) td {
            background: #f9fafb;
        }
        
        table.items-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .item-description {
            font-weight: 500;
            color: #0f172a;
            font-size: 9pt;
        }
        
        .item-quantity,
        .item-price,
        .item-vat,
        .item-total {
            font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
            font-weight: 600;
            font-size: 9pt;
            color: #1e293b;
        }
        
        /* Totals Section - Light Green Background */
        .totals-wrapper {
            display: table;
            width: 100%;
            margin-bottom: 0.5cm;
        }
        
        .totals-left {
            display: table-cell;
            width: 55%;
            vertical-align: top;
            padding-right: 0.8cm;
        }
        
        .totals-right {
            display: table-cell;
            width: 45%;
            vertical-align: top;
        }
        
        .totals-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 0.5cm;
            border-radius: 6px;
        }
        
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totals-table td {
            padding: 0.2cm 0;
            font-size: 9pt;
            border: none;
        }
        
        .totals-table td.label {
            text-align: right;
            color: #64748b;
            font-weight: 500;
            padding-right: 0.5cm;
        }
        
        .totals-table td.value {
            text-align: right;
            color: #1e293b;
            font-weight: 600;
            font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
        }
        
        .totals-table tr.subtotal td {
            padding-top: 0.25cm;
        }
        
        .totals-table tr.vat-total td {
            padding-bottom: 0.25cm;
            border-bottom: 1px solid #bbf7d0;
        }
        
        .totals-table tr.grand-total td {
            padding: 0.3cm 0;
            border-top: 2px solid #059669;
            margin-top: 0.2cm;
            font-size: 10.5pt;
            font-weight: 700;
            color: #059669;
        }
        
        .totals-table tr.grand-total td.label {
            color: #059669;
            font-size: 10.5pt;
        }
        
        .totals-table tr.grand-total td.value {
            color: #059669;
            font-size: 12pt;
        }
        
        /* Notes Section - Light Green Background */
        .notes-section {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 0.4cm 0.5cm;
            margin-bottom: 0;
        }
        
        .notes-label {
            font-size: 9pt;
            font-weight: 700;
            text-transform: uppercase;
            color: #059669;
            letter-spacing: 0.5px;
            margin-bottom: 0.2cm;
        }
        
        .notes-text {
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.5;
            white-space: pre-wrap;
        }
        
        /* Footer - Minimalist */
        .footer {
            margin-top: 0.6cm;
            padding-top: 0.4cm;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 7.5pt;
            color: #94a3b8;
            line-height: 1.5;
        }
        
        .footer-text {
            font-style: italic;
        }
        
        /* Print Optimizations */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .invoice-container {
                page-break-inside: avoid;
            }
            
            table.items-table {
                page-break-inside: auto;
            }
            
            table.items-table thead {
                display: table-header-group;
            }
            
            table.items-table tbody tr {
                page-break-inside: avoid;
            }
            
            .totals-wrapper {
                page-break-inside: avoid;
            }
            
            .notes-section {
                page-break-inside: avoid;
            }
        }
        
        .empty-items {
            text-align: center;
            padding: 0.8cm;
            color: #94a3b8;
            font-style: italic;
            font-size: 9pt;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="header-left">
                @if(isset($data['sender_logo']) && $data['sender_logo'])
                <div class="logo-container">
                    <img src="{{ $data['sender_logo'] }}" alt="Logo" class="logo-img" />
                </div>
                @endif
                <div class="company-name">{{ $data['sender_company_name'] ?? 'Uw Bedrijf' }}</div>
                <div class="company-details">
                    {{ $data['sender_address'] ?? 'Adresgegevens' }}<br>
                    @if(isset($data['sender_phone']) && $data['sender_phone'])
                    <strong>Telefoon:</strong> {{ $data['sender_phone'] }}<br>
                    @endif
                    @if(isset($data['sender_email']) && $data['sender_email'])
                    <strong>E-mail:</strong> {{ $data['sender_email'] }}<br>
                    @endif
                    @if(isset($data['sender_kvk']) && $data['sender_kvk'])
                    <strong>KVK:</strong> {{ $data['sender_kvk'] }}<br>
                    @endif
                    @if(isset($data['sender_vat']) && $data['sender_vat'])
                    <strong>BTW:</strong> {{ $data['sender_vat'] }}
                    @endif
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">FACTUUR</div>
                <div class="invoice-details-box">
                    <div class="invoice-details-label">Factuurgegevens</div>
                    <div class="invoice-details-row">
                        <span class="invoice-details-label-text">Factuurnummer:</span>
                        <span class="invoice-details-value">{{ $data['invoice_number'] ?? 'N/A' }}</span>
                    </div>
                    <div class="invoice-details-row">
                        <span class="invoice-details-label-text">Factuurdatum:</span>
                        <span class="invoice-details-value">{{ $document->document_date ? $document->document_date->format('d-m-Y') : 'N/A' }}</span>
                    </div>
                    <div class="invoice-details-row">
                        <span class="invoice-details-label-text">Vervaldatum:</span>
                        <span class="invoice-details-value">{{ isset($data['due_date']) ? \Carbon\Carbon::parse($data['due_date'])->format('d-m-Y') : 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Section -->
        <div class="customer-section">
            <div class="customer-label">Klantgegevens</div>
            <div class="customer-name">{{ $data['customer_name'] ?? 'Klant Naam' }}</div>
            <div class="customer-details">
                {{ $data['customer_address'] ?? 'Klant Adres' }}<br>
                @if(isset($data['customer_kvk']) && $data['customer_kvk'])
                <strong>KVK:</strong> {{ $data['customer_kvk'] }}<br>
                @endif
                @if(isset($data['customer_vat']) && $data['customer_vat'])
                <strong>BTW:</strong> {{ $data['customer_vat'] }}<br>
                @endif
                @if(isset($data['customer_email']) && $data['customer_email'])
                <strong>E-mail:</strong> {{ $data['customer_email'] }}<br>
                @endif
                @if(isset($data['customer_phone']) && $data['customer_phone'])
                <strong>Telefoon:</strong> {{ $data['customer_phone'] }}
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <div class="items-section-label">Factuuritems</div>
        <div class="items-table-wrapper">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Omschrijving</th>
                        <th class="text-right">Aantal</th>
                        <th class="text-right">Prijs (excl. BTW)</th>
                        <th class="text-right">BTW %</th>
                        <th class="text-right">Bedrag</th>
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
                            <td class="item-description">{{ $item['description'] ?? '' }}</td>
                            <td class="text-right item-quantity">{{ number_format($quantity, 2, ',', '.') }}</td>
                            <td class="text-right item-price">€ {{ number_format($price, 2, ',', '.') }}</td>
                            <td class="text-right item-vat">{{ number_format($vatRate, 0) }}%</td>
                            <td class="text-right item-total">€ {{ number_format($itemTotal, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-items">Geen items toegevoegd</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Totals Section -->
        <div class="totals-wrapper">
            <div class="totals-left">
                @if(isset($data['notes']) && $data['notes'] && trim($data['notes']) !== '' && !str_contains(strtolower($data['notes']), 'vul de volgende velden'))
                <div class="notes-section">
                    <div class="notes-label">Notities</div>
                    <div class="notes-text">{{ $data['notes'] }}</div>
                </div>
                @endif
            </div>
            <div class="totals-right">
                <div class="totals-box">
                    <table class="totals-table">
                        <tr class="subtotal">
                            <td class="label">Subtotaal</td>
                            <td class="value">€ {{ number_format($document->amount_excl ?? 0, 2, ',', '.') }}</td>
                        </tr>
                        <tr class="vat-total">
                            <td class="label">BTW (21%)</td>
                            <td class="value">€ {{ number_format($document->amount_vat ?? 0, 2, ',', '.') }}</td>
                        </tr>
                        <tr class="grand-total">
                            <td class="label">Totaal</td>
                            <td class="value">€ {{ number_format($document->amount_incl ?? 0, 2, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-text">
                Bedankt voor uw vertrouwen! • Deze factuur is gegenereerd via MARCOFIC Klanten Portaal
            </div>
        </div>
    </div>
</body>
</html>
