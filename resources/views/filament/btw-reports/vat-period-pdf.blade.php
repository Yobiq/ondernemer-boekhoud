<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>BTW Rapport {{ $period->period_string }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @page {
            size: A4 portrait;
            margin: 2cm;
        }
        
        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #000;
            background: #fff;
        }
        
        .header {
            margin-bottom: 1.5cm;
            border-bottom: 2px solid #000;
            padding-bottom: 0.5cm;
        }
        
        .header h1 {
            font-size: 18pt;
            font-weight: 700;
            margin-bottom: 0.3cm;
        }
        
        .client-info {
            font-size: 9pt;
            line-height: 1.6;
        }
        
        .period-info {
            margin-bottom: 1cm;
            font-size: 9pt;
        }
        
        .period-info strong {
            font-weight: 700;
        }
        
        .rubrieken-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1cm;
        }
        
        .rubrieken-table th,
        .rubrieken-table td {
            padding: 0.4cm;
            text-align: left;
            border: 1px solid #000;
        }
        
        .rubrieken-table th {
            background: #f0f0f0;
            font-weight: 700;
            font-size: 9pt;
        }
        
        .rubrieken-table td {
            font-size: 9pt;
        }
        
        .rubrieken-table td.text-right {
            text-align: right;
        }
        
        .rubrieken-table tr.total-row {
            font-weight: 700;
            background: #f0f0f0;
        }
        
        .footer {
            margin-top: 1.5cm;
            padding-top: 0.5cm;
            border-top: 1px solid #000;
            font-size: 8pt;
            text-align: center;
        }
        
        .signature-section {
            margin-top: 1.5cm;
            padding-top: 0.5cm;
            border-top: 1px solid #000;
        }
        
        .signature-line {
            margin-top: 1.5cm;
            width: 50%;
            border-top: 1px solid #000;
            padding-top: 0.2cm;
            font-size: 8pt;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>BTW Rapport</h1>
        <div class="client-info">
            <strong>{{ $period->client->name }}</strong><br>
            @if($period->client->address_line1)
            {{ $period->client->address_line1 }}<br>
            @endif
            @if($period->client->postal_code && $period->client->city)
            {{ $period->client->postal_code }} {{ $period->client->city }}<br>
            @endif
            @if($period->client->vat_number)
            BTW: {{ $period->client->vat_number }}<br>
            @endif
        </div>
    </div>

    <div class="period-info">
        <strong>Periode:</strong> {{ $period->period_string }}<br>
        <strong>Start:</strong> {{ $period->period_start->format('d-m-Y') }}<br>
        <strong>Eind:</strong> {{ $period->period_end->format('d-m-Y') }}<br>
        <strong>Status:</strong> 
        @if($period->status === 'open')
            Open
        @elseif($period->status === 'voorbereid')
            Voorbereid
        @elseif($period->status === 'ingediend')
            Ingediend
        @else
            Afgesloten
        @endif
    </div>

    <table class="rubrieken-table">
        <thead>
            <tr>
                <th>Rubriek</th>
                <th>Omschrijving</th>
                <th class="text-right">Grondslag</th>
                <th class="text-right">BTW</th>
                <th class="text-right">Aantal</th>
            </tr>
        </thead>
        <tbody>
            @foreach(['1a', '1b', '1c', '2a', '2b', '3a', '3b', '4a', '4b', '5b'] as $rubriek)
                @php
                    $rubriekData = $totals[$rubriek] ?? ['amount' => 0, 'vat' => 0, 'count' => 0];
                @endphp
                @if($rubriekData['count'] > 0 || $rubriek === '5b')
                <tr>
                    <td><strong>{{ $rubriek }}</strong></td>
                    <td>{{ $vatCalculator->getRubriekName($rubriek) }}</td>
                    <td class="text-right">€ {{ number_format($rubriekData['amount'], 2, ',', '.') }}</td>
                    <td class="text-right">€ {{ number_format($rubriekData['vat'], 2, ',', '.') }}</td>
                    <td class="text-right">{{ $rubriekData['count'] }}</td>
                </tr>
                @endif
            @endforeach
            
            @php
                $totaalGrondslag = array_sum(array_column($totals, 'amount'));
                $totaalBTW = array_sum(array_column($totals, 'vat'));
                $totaalAantal = array_sum(array_column($totals, 'count'));
            @endphp
            <tr class="total-row">
                <td colspan="2"><strong>TOTAAL</strong></td>
                <td class="text-right"><strong>€ {{ number_format($totaalGrondslag, 2, ',', '.') }}</strong></td>
                <td class="text-right"><strong>€ {{ number_format($totaalBTW, 2, ',', '.') }}</strong></td>
                <td class="text-right"><strong>{{ $totaalAantal }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-line">
            <strong>Verklaring:</strong><br>
            Ik verklaar dat bovenstaande gegevens juist en volledig zijn.
        </div>
        <div style="margin-top: 1cm; font-size: 8pt;">
            <strong>Datum:</strong> {{ now()->format('d-m-Y') }}<br>
            @if($period->preparedBy)
            <strong>Voorbereid door:</strong> {{ $period->preparedBy->name }}<br>
            @endif
            @if($period->closedBy)
            <strong>Afgesloten door:</strong> {{ $period->closedBy->name }}<br>
            @endif
        </div>
    </div>

    <div class="footer">
        Dit rapport is gegenereerd door MARCOFIC Boekhouding Systeem<br>
        Alle bedragen zijn in EUR. Dit rapport is audit-proof en volledig herleidbaar.
    </div>
</body>
</html>


