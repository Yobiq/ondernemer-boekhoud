<div class="invoice-totals-display">
    <div class="totals-row">
        <span class="totals-label">Subtotaal (excl. BTW):</span>
        <span class="totals-value">€ {{ $totals['subtotal'] }}</span>
    </div>
    <div class="totals-row">
        <span class="totals-label">BTW:</span>
        <span class="totals-value">€ {{ $totals['vat_total'] }}</span>
    </div>
    <div class="totals-row totals-total">
        <span class="totals-label">Totaal (incl. BTW):</span>
        <span class="totals-value">€ {{ $totals['total'] }}</span>
    </div>
</div>

<style>
    .invoice-totals-display {
        padding: 1.5rem;
        background: var(--secondary-bg, #f8fafc);
        border-radius: var(--radius-md, 0.5rem);
        border: 2px solid var(--border-color, #e2e8f0);
    }
    
    .dark .invoice-totals-display {
        background: var(--secondary-bg, #1e293b);
        border-color: var(--border-color, #334155);
    }
    
    .totals-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        font-size: 0.9375rem;
    }
    
    .totals-row:not(:last-child) {
        border-bottom: 1px solid var(--border-color, #e2e8f0);
    }
    
    .totals-label {
        font-weight: 600;
        color: var(--text-secondary, #64748b);
    }
    
    .totals-value {
        font-weight: 700;
        color: var(--text-primary, #0f172a);
        font-size: 1rem;
    }
    
    .totals-total {
        margin-top: 0.5rem;
        padding-top: 1rem;
        border-top: 2px solid var(--border-color, #e2e8f0) !important;
        border-bottom: none !important;
    }
    
    .totals-total .totals-label {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--text-primary, #0f172a);
    }
    
    .totals-total .totals-value {
        font-size: 1.5rem;
        color: var(--accent-blue, #3b82f6);
    }
</style>




