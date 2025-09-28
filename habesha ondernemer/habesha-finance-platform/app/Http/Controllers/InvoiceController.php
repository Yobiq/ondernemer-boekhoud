<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\InvoiceMail;
use Illuminate\Support\Facades\Mail;

final class InvoiceController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request): Response
    {
        $invoices = Invoice::where('user_id', $request->user()->id)
            ->with(['client', 'project'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
        ]);
    }

    public function create(Request $request): Response
    {
        $clients = Client::where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get();

        $projects = Project::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->with('client')
            ->orderBy('name')
            ->get();

        return Inertia::render('Invoices/Create', [
            'clients' => $clients,
            'projects' => $projects,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after:issue_date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
        ]);

        // Generate invoice number
        $lastInvoice = Invoice::where('user_id', $request->user()->id)
            ->orderBy('id', 'desc')
            ->first();
        
        $invoiceNumber = 'INV-' . str_pad((string)(($lastInvoice ? $lastInvoice->id : 0) + 1), 4, '0', STR_PAD_LEFT);

        // Calculate totals
        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $itemTotal;
        }

        $taxAmount = $subtotal * ($validated['tax_rate'] / 100);
        $totalAmount = $subtotal + $taxAmount;

        // Create invoice
        $invoice = $request->user()->invoices()->create([
            'client_id' => $validated['client_id'],
            'project_id' => $validated['project_id'],
            'invoice_number' => $invoiceNumber,
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
            'subtotal' => $subtotal,
            'tax_rate' => $validated['tax_rate'],
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'notes' => $validated['notes'],
            'terms' => $validated['terms'],
        ]);

        // Create invoice items
        foreach ($validated['items'] as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Factuur succesvol aangemaakt.');
    }

    public function show(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        $invoice->load(['client', 'project', 'items', 'payments']);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    public function edit(Invoice $invoice): Response
    {
        $this->authorize('update', $invoice);

        $clients = Client::where('user_id', $invoice->user_id)
            ->orderBy('name')
            ->get();

        $projects = Project::where('user_id', $invoice->user_id)
            ->where('status', 'active')
            ->with('client')
            ->orderBy('name')
            ->get();

        $invoice->load('items');

        return Inertia::render('Invoices/Edit', [
            'invoice' => $invoice,
            'clients' => $clients,
            'projects' => $projects,
        ]);
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after:issue_date',
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
        ]);

        // Calculate totals
        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $itemTotal;
        }

        $taxAmount = $subtotal * ($validated['tax_rate'] / 100);
        $totalAmount = $subtotal + $taxAmount;

        // Update invoice
        $invoice->update([
            'client_id' => $validated['client_id'],
            'project_id' => $validated['project_id'],
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'],
            'status' => $validated['status'],
            'subtotal' => $subtotal,
            'tax_rate' => $validated['tax_rate'],
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'notes' => $validated['notes'],
            'terms' => $validated['terms'],
        ]);

        // Update invoice items
        $invoice->items()->delete();
        foreach ($validated['items'] as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Factuur succesvol bijgewerkt.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorize('delete', $invoice);

        $invoice->delete();

                return redirect()->route('invoices.index')
                    ->with('success', 'Factuur succesvol verwijderd.');
            }

            public function pdf(Invoice $invoice)
            {
                $this->authorize('view', $invoice);

                $invoice->load(['client', 'project', 'items', 'user']);

                $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
                
                return $pdf->download("factuur-{$invoice->invoice_number}.pdf");
            }

            // New method for sending invoice via email
            public function email(Request $request, Invoice $invoice)
            {
                $this->authorize('view', $invoice);

                $validated = $request->validate([
                    'message' => 'nullable|string|max:1000',
                ]);

                $invoice->load(['client', 'project', 'items', 'user']);

                // Send email
                Mail::to($invoice->client->email)
                    ->send(new InvoiceMail($invoice, $validated['message'] ?? null));

                return redirect()->route('invoices.show', $invoice)
                    ->with('success', 'Factuur succesvol per email verzonden naar ' . $invoice->client->email);
            }
        }
