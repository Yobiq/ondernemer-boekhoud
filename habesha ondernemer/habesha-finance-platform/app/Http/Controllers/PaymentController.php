<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;

final class PaymentController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $payments = Payment::whereHas('invoice', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })
            ->with(['invoice.client'])
            ->orderBy('payment_date', 'desc')
            ->paginate(15);

        return Inertia::render('Payments/Index', [
            'payments' => $payments,
        ]);
    }

    public function create(Request $request): Response
    {
        $invoiceId = $request->get('invoice_id');
        
        $invoices = Invoice::where('user_id', $request->user()->id)
            ->where('status', '!=', 'paid')
            ->with(['client'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Payments/Create', [
            'invoices' => $invoices,
            'selectedInvoiceId' => $invoiceId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:bank_transfer,cash,credit_card,paypal,other',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Check if user owns the invoice
        $invoice = Invoice::where('user_id', $request->user()->id)
            ->findOrFail($validated['invoice_id']);

        $payment = Payment::create($validated);

        // Update invoice status if fully paid
        $totalPaid = $invoice->payments()->sum('amount');
        if ($totalPaid >= $invoice->total_amount) {
            $invoice->update([
                'status' => 'paid',
                'paid_date' => $validated['payment_date'],
            ]);
        } else {
            $invoice->update(['status' => 'sent']);
        }

        return redirect()->route('payments.index')
            ->with('success', 'Betaling succesvol geregistreerd.');
    }

    public function show(Payment $payment): Response
    {
        $this->authorize('view', $payment);

        $payment->load(['invoice.client']);

        return Inertia::render('Payments/Show', [
            'payment' => $payment,
        ]);
    }

    public function edit(Payment $payment): Response
    {
        $this->authorize('update', $payment);

        $invoices = Invoice::where('user_id', $payment->invoice->user_id)
            ->where('status', '!=', 'paid')
            ->with(['client'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Payments/Edit', [
            'payment' => $payment,
            'invoices' => $invoices,
        ]);
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('update', $payment);

        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:bank_transfer,cash,credit_card,paypal,other',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $oldInvoice = $payment->invoice;
        $payment->update($validated);
        $newInvoice = $payment->invoice;

        // Update old invoice status
        $this->updateInvoiceStatus($oldInvoice);

        // Update new invoice status if different
        if ($oldInvoice->id !== $newInvoice->id) {
            $this->updateInvoiceStatus($newInvoice);
        }

        return redirect()->route('payments.index')
            ->with('success', 'Betaling succesvol bijgewerkt.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $this->authorize('delete', $payment);

        $invoice = $payment->invoice;
        $payment->delete();

        // Update invoice status after payment deletion
        $this->updateInvoiceStatus($invoice);

        return redirect()->route('payments.index')
            ->with('success', 'Betaling succesvol verwijderd.');
    }

    private function updateInvoiceStatus(Invoice $invoice): void
    {
        $totalPaid = $invoice->payments()->sum('amount');
        
        if ($totalPaid >= $invoice->total_amount) {
            $invoice->update([
                'status' => 'paid',
                'paid_date' => $invoice->payments()->latest('payment_date')->first()?->payment_date,
            ]);
        } else {
            $invoice->update([
                'status' => 'sent',
                'paid_date' => null,
            ]);
        }
    }
}
