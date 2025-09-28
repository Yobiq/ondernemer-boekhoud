<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factuur {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        
        .company-info {
            flex: 1;
        }
        
        .company-info h1 {
            color: #2563eb;
            font-size: 24px;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        
        .company-info p {
            margin: 2px 0;
            color: #666;
        }
        
        .invoice-info {
            text-align: right;
            flex: 1;
        }
        
        .invoice-info h2 {
            color: #2563eb;
            font-size: 18px;
            margin: 0 0 10px 0;
        }
        
        .invoice-info p {
            margin: 2px 0;
        }
        
        .client-info {
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f8fafc;
            border-radius: 5px;
        }
        
        .client-info h3 {
            color: #2563eb;
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        
        .client-info p {
            margin: 2px 0;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .items-table th,
        .items-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .items-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #374151;
        }
        
        .items-table .description {
            width: 50%;
        }
        
        .items-table .quantity,
        .items-table .price,
        .items-table .total {
            width: 15%;
            text-align: right;
        }
        
        .totals {
            margin-left: auto;
            width: 300px;
        }
        
        .totals table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totals td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .totals .label {
            text-align: left;
        }
        
        .totals .amount {
            text-align: right;
            font-weight: bold;
        }
        
        .totals .total-row {
            background-color: #2563eb;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        
        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8fafc;
            border-radius: 5px;
        }
        
        .notes h3 {
            color: #2563eb;
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-draft { background-color: #f3f4f6; color: #374151; }
        .status-sent { background-color: #dbeafe; color: #1e40af; }
        .status-paid { background-color: #dcfce7; color: #166534; }
        .status-overdue { background-color: #fecaca; color: #991b1b; }
        .status-cancelled { background-color: #f3f4f6; color: #374151; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <h1>{{ $invoice->user->business_name ?: $invoice->user->name }}</h1>
            @if($invoice->user->business_address)
                <p>{{ $invoice->user->business_address }}</p>
            @endif
            @if($invoice->user->business_city)
                <p>{{ $invoice->user->business_postal_code }} {{ $invoice->user->business_city }}</p>
            @endif
            @if($invoice->user->business_email)
                <p>{{ $invoice->user->business_email }}</p>
            @endif
            @if($invoice->user->vat_number)
                <p>BTW: {{ $invoice->user->vat_number }}</p>
            @endif
        </div>
        
        <div class="invoice-info">
            <h2>FACTUUR</h2>
            <p><strong>Factuurnummer:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Factuurdatum:</strong> {{ $invoice->issue_date->format('d-m-Y') }}</p>
            <p><strong>Vervaldatum:</strong> {{ $invoice->due_date->format('d-m-Y') }}</p>
            <p><strong>Status:</strong> 
                <span class="status-badge status-{{ $invoice->status }}">
                    {{ ucfirst($invoice->status) }}
                </span>
            </p>
        </div>
    </div>
    
    <div class="client-info">
        <h3>Factuur voor:</h3>
        <p><strong>{{ $invoice->client->name }}</strong></p>
        @if($invoice->client->company)
            <p>{{ $invoice->client->company }}</p>
        @endif
        @if($invoice->client->address)
            <p>{{ $invoice->client->address }}</p>
        @endif
        @if($invoice->client->city)
            <p>{{ $invoice->client->postal_code }} {{ $invoice->client->city }}</p>
        @endif
        @if($invoice->client->email)
            <p>{{ $invoice->client->email }}</p>
        @endif
        @if($invoice->client->vat_number)
            <p>BTW: {{ $invoice->client->vat_number }}</p>
        @endif
    </div>
    
    @if($invoice->project)
    <div class="client-info">
        <h3>Project:</h3>
        <p><strong>{{ $invoice->project->name }}</strong></p>
        @if($invoice->project->description)
            <p>{{ $invoice->project->description }}</p>
        @endif
    </div>
    @endif
    
    <table class="items-table">
        <thead>
            <tr>
                <th class="description">Omschrijving</th>
                <th class="quantity">Aantal</th>
                <th class="price">Prijs</th>
                <th class="total">Totaal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td class="description">{{ $item->description }}</td>
                <td class="quantity">{{ $item->quantity }}</td>
                <td class="price">€ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                <td class="total">€ {{ number_format($item->total_price, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="totals">
        <table>
            <tr>
                <td class="label">Subtotaal:</td>
                <td class="amount">€ {{ number_format($invoice->subtotal, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">BTW ({{ $invoice->tax_rate }}%):</td>
                <td class="amount">€ {{ number_format($invoice->tax_amount, 2, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td class="label">TOTAAL:</td>
                <td class="amount">€ {{ number_format($invoice->total_amount, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>
    
    @if($invoice->notes || $invoice->terms)
    <div class="notes">
        @if($invoice->notes)
        <h3>Opmerkingen:</h3>
        <p>{{ $invoice->notes }}</p>
        @endif
        
        @if($invoice->terms)
        <h3>Betalingsvoorwaarden:</h3>
        <p>{{ $invoice->terms }}</p>
        @endif
    </div>
    @endif
    
    <div class="footer">
        <p>Deze factuur is gegenereerd door het Habesha Business Finance Platform</p>
        <p>Voor vragen kunt u contact opnemen via {{ $invoice->user->business_email ?: $invoice->user->email }}</p>
    </div>
</body>
</html>
