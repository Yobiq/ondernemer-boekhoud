<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Factuur {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 300;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .invoice-details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
            text-align: center;
            margin: 20px 0;
        }
        .due-date {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            margin: 20px 0;
        }
        .company-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Factuur {{ $invoice->invoice_number }}</h1>
        <p>Bedankt voor uw zaken!</p>
    </div>

    <div class="content">
        <div class="company-info">
            <h3>Van: {{ $invoice->user->business_name ?? $invoice->user->name }}</h3>
            <p>{{ $invoice->user->business_address }}</p>
            <p>{{ $invoice->user->business_postal_code }} {{ $invoice->user->business_city }}</p>
            <p>{{ $invoice->user->business_country }}</p>
            @if($invoice->user->vat_number)
                <p>BTW: {{ $invoice->user->vat_number }}</p>
            @endif
        </div>

        <div class="invoice-details">
            <h3>Factuur Details</h3>
            <p><strong>Factuurnummer:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Factuurdatum:</strong> {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d-m-Y') }}</p>
            <p><strong>Vervaldatum:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') }}</p>
            @if($invoice->project)
                <p><strong>Project:</strong> {{ $invoice->project->name }}</p>
            @endif
        </div>

        <div class="amount">
            Totaal: {{ number_format($invoice->total_amount, 2, ',', '.') }} {{ $invoice->currency }}
        </div>

        <div class="due-date">
            <strong>Vervaldatum: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') }}</strong>
        </div>

        @if($message)
            <div style="background: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <h4>Bericht:</h4>
                <p>{{ $message }}</p>
            </div>
        @endif

        <p>De factuur is als PDF bijgevoegd aan deze email. U kunt deze downloaden en bewaren voor uw administratie.</p>

        <div style="text-align: center;">
            <a href="#" class="button">Factuur Bekijken</a>
        </div>
    </div>

    <div class="footer">
        <p>Deze email is automatisch gegenereerd door het Habesha Finance Platform.</p>
        <p>Voor vragen kunt u contact opnemen met {{ $invoice->user->business_email ?? $invoice->user->email }}</p>
    </div>
</body>
</html>
