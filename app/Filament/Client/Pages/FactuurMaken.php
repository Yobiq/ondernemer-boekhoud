<?php

namespace App\Filament\Client\Pages;

use App\Models\Document;
use Filament\Pages\Page;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FactuurMaken extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-plus';
    protected static ?string $navigationLabel = 'Factuur Maken';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.client.pages.factuur-maken';
    protected static ?string $navigationGroup = 'Documenten';
    
    public ?array $data = [];
    public ?array $createdInvoice = null;
    public string $activeTab = 'create';
    public string $search = '';
    public string $statusFilter = 'all';
    public string $viewMode = 'table'; // 'table', 'grid', or 'list'
    
    public function mount(): void
    {
        $user = Auth::user();
        $client = $user->client;
        
        // Build sender address from client data
        $senderAddress = '';
        if ($client) {
            $addressParts = array_filter([
                $client->address_line1,
                $client->address_line2,
                trim(($client->postal_code ?? '') . ' ' . ($client->city ?? '')),
            ]);
            $senderAddress = implode("\n", $addressParts);
        }
        
        // Get logo from client profile
        $clientLogo = $client->logo ?? null;
        
        // Ensure dates are Carbon instances for proper handling
        $invoiceDate = now();
        $dueDate = now()->addDays(30);
        
        // Ensure all required fields have values to prevent validation errors on navigation
        $formData = [
            'invoice_date' => $invoiceDate->format('Y-m-d'),
            'due_date' => $dueDate->format('Y-m-d'),
            // Sender (Van) information from client profile - ensure required fields have values
            'sender_company_name' => $client->company_name ?? $client->name ?? '',
            'sender_address' => $senderAddress ?: '',
            'sender_email' => $client->email ?? $user->email ?? '',
            'sender_phone' => $client->phone ?? '',
            'sender_kvk' => $client->kvk_number ?? '',
            'sender_vat' => $client->vat_number ?? '',
            'sender_logo' => $clientLogo, // Use logo from client profile
            'customer_name' => '', // Initialize customer name
            'items' => [
                [
                    'description' => '',
                    'quantity' => 1,
                    'price' => 0,
                    'vat_rate' => '21',
                ],
            ],
        ];
        
        $this->form->fill($formData);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // STEP 1: Customer Information (Naar)
                    Wizard\Step::make('Naar')
                        ->label('Klant Gegevens')
                        ->description('Voor wie is deze factuur?')
                        ->icon('heroicon-o-user')
                        ->completedIcon('heroicon-o-check-circle')
                    ->schema([
                Section::make('Naar (Klant Gegevens)')
                    ->description('Voor wie is deze factuur?')
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextInput::make('customer_name')
                            ->label('Klant Naam / Bedrijfsnaam')
                            ->maxLength(255)
                            ->placeholder('Naam van uw klant')
                            ->default('')
                            ->columnSpanFull(),
                        
                        TextInput::make('customer_email')
                            ->label('Klant E-mail')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('email@voorbeeld.nl')
                            ->columnSpan(1),
                        
                        TextInput::make('customer_phone')
                            ->label('Telefoonnummer')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('+31 6 12345678')
                            ->columnSpan(1),
                        
                        Textarea::make('customer_address')
                            ->label('Klant Adres')
                            ->rows(3)
                            ->placeholder('Straatnaam 123&#10;1234 AB Amsterdam')
                            ->helperText('Straat, Postcode en Plaats')
                            ->columnSpanFull(),
                        
                        TextInput::make('customer_vat')
                            ->label('BTW-nummer (optioneel)')
                            ->maxLength(20)
                            ->placeholder('NL123456789B01')
                            ->columnSpan(1),
                        
                        TextInput::make('customer_kvk')
                            ->label('KVK-nummer (optioneel)')
                            ->maxLength(20)
                            ->placeholder('12345678')
                            ->helperText('Kamer van Koophandel nummer')
                            ->columnSpan(1),
                    ])
                    ->columns(2),
                        ]),
                    
                    // STEP 2: Invoice Details
                    Wizard\Step::make('Details')
                        ->label('Factuur Details')
                        ->description('Factuurnummer en datums')
                        ->icon('heroicon-o-document-text')
                        ->completedIcon('heroicon-o-check-circle')
                        ->columns(2)
                        ->schema([
                Section::make('Factuur Details')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label('Factuurnummer')
                            ->maxLength(50)
                            ->placeholder('Automatisch gegenereerd indien leeg')
                            ->helperText('Laat leeg voor automatische generatie')
                            ->columnSpan(1),
                        
                        DatePicker::make('invoice_date')
                            ->label('Factuur Datum')
                            ->default(now())
                            ->displayFormat('d-m-Y')
                            ->native(false)
                            ->dehydrated()
                            ->columnSpan(1),
                        
                        DatePicker::make('due_date')
                            ->label('Vervaldatum')
                            ->default(now()->addDays(30))
                            ->displayFormat('d-m-Y')
                            ->native(false)
                            ->dehydrated()
                            ->helperText('Betaaltermijn: 30 dagen')
                            ->columnSpan(1),
                        
                        Textarea::make('notes')
                            ->label('Opmerkingen (optioneel)')
                            ->rows(2)
                            ->placeholder('Extra informatie voor de klant')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
                        ]),
                    
                    // STEP 3: Invoice Items
                    Wizard\Step::make('Items')
                        ->label('Factuurregels')
                        ->description('Voeg producten of diensten toe')
                        ->icon('heroicon-o-list-bullet')
                        ->completedIcon('heroicon-o-check-circle')
                        ->schema([
                Section::make('Factuurregels')
                    ->description('Voeg producten of diensten toe aan de factuur')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        Repeater::make('items')
                            ->label('Items')
                            ->schema([
                                TextInput::make('description')
                                    ->label('Omschrijving')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Product of dienst omschrijving')
                                                    ->columnSpanFull(),
                                
                                                Grid::make(12)
                                                    ->schema([
                                TextInput::make('quantity')
                                    ->label('Aantal')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(0.01)
                                    ->step(0.01)
                                    ->suffix('st')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                                $this->calculateItemTotal($set, $get);
                                    })
                                                            ->columnSpan(3),
                                
                                TextInput::make('price')
                                    ->label('Prijs (excl. BTW)')
                                    ->numeric()
                                    ->required()
                                    ->prefix('€')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->placeholder('0.00')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                                $this->calculateItemTotal($set, $get);
                                    })
                                                            ->columnSpan(4),
                                
                                Select::make('vat_rate')
                                    ->label('BTW %')
                                    ->options([
                                                                '21' => '21% (Hoog)',
                                                                '9' => '9% (Laag)',
                                                                '0' => '0% (Vrij)',
                                    ])
                                    ->required()
                                    ->default('21')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                                $this->calculateItemTotal($set, $get);
                                    })
                                                            ->columnSpan(3),
                                
                                TextInput::make('item_total')
                                    ->label('Totaal (incl. BTW)')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->prefix('€')
                                    ->default('0.00')
                                                            ->columnSpan(2)
                                    ->extraAttributes(['class' => 'item-total-display']),
                                                    ]),
                            ])
                                            ->columns(1)
                            ->defaultItems(1)
                            ->minItems(1)
                            ->addActionLabel('➕ Item Toevoegen')
                            ->reorderable()
                            ->itemLabel(fn (array $state): ?string => $state['description'] ?? null)
                            ->collapsible(),
                        
                        Section::make('Totaal Overzicht')
                            ->description('Automatisch berekende totalen')
                            ->icon('heroicon-o-calculator')
                            ->schema([
                                \Filament\Forms\Components\Placeholder::make('totals_display')
                                    ->label('')
                                    ->content(fn () => view('filament.client.components.invoice-totals', [
                                        'totals' => $this->getTotals(),
                                    ])),
                            ])
                            ->collapsible()
                            ->collapsed(false),
                    ]),
                        ]),
                    
                    // STEP 4: Review & Submit
                    Wizard\Step::make('Review')
                        ->label('Overzicht')
                        ->description('Controleer en bevestig')
                        ->icon('heroicon-o-check-circle')
                        ->schema([
                            Section::make('Factuur Overzicht')
                                ->description('Controleer alle gegevens voordat u de factuur aanmaakt')
                                ->icon('heroicon-o-eye')
                                ->schema([
                                    \Filament\Forms\Components\Placeholder::make('invoice_preview')
                                        ->label('')
                                        ->content(function () {
                                            $data = $this->form->getRawState();
                                            
                                            // Generate invoice number if not set
                                            if (empty($data['invoice_number'])) {
                                                $user = Auth::user();
                                                $clientId = $user->client_id;
                                                $data['invoice_number'] = 'INV-' . now()->format('Y') . '-' . str_pad(
                                                    Document::where('client_id', $clientId)
                                                        ->where('document_type', 'sales_invoice')
                                                        ->whereYear('created_at', now()->year)
                                                        ->count() + 1,
                                                    4,
                                                    '0',
                                                    STR_PAD_LEFT
                                                );
                                            }
                                            
                                            // Calculate totals from items if not already calculated
                                            if (!isset($data['subtotal']) || !isset($data['vat_total']) || !isset($data['total'])) {
                                                $subtotal = 0;
                                                $vatTotal = 0;
                                                $total = 0;
                                                
                                                foreach ($data['items'] ?? [] as $item) {
                                                    $quantity = (float) ($item['quantity'] ?? 1);
                                                    $price = (float) ($item['price'] ?? 0);
                                                    $vatRate = (float) ($item['vat_rate'] ?? 21);
                                                    
                                                    $itemSubtotal = $quantity * $price;
                                                    $itemVat = $itemSubtotal * ($vatRate / 100);
                                                    $itemTotal = $itemSubtotal + $itemVat;
                                                    
                                                    $subtotal += $itemSubtotal;
                                                    $vatTotal += $itemVat;
                                                    $total += $itemTotal;
                                                }
                                                
                                                $data['subtotal'] = $subtotal;
                                                $data['vat_total'] = $vatTotal;
                                                $data['total'] = $total;
                                            }
                                            
                                            return view('filament.client.components.invoice-preview-full', [
                                                'data' => $data,
                                            ]);
                                        }),
                                ]),
                        ]),
                ])
                ->submitAction(view('filament.client.actions.submit-invoice-action'))
                ->skippable()
                ->persistStepInQueryString()
                ->columnSpanFull()
            ])
            ->statePath('data');
    }
    
    protected function calculateItemTotal(callable $set, callable $get, ?string $statePath = null): void
    {
        // Prevent infinite loops by checking if we're already updating
        static $calculating = false;
        if ($calculating) {
            return;
        }
        
        $calculating = true;
        
        try {
        $quantity = (float) ($get('quantity') ?? 1);
        $price = (float) ($get('price') ?? 0);
        $vatRate = (float) ($get('vat_rate') ?? 21);
        
        if ($quantity > 0 && $price > 0) {
            $subtotal = $quantity * $price;
            $vatAmount = $subtotal * ($vatRate / 100);
            $total = $subtotal + $vatAmount;
            
            $set('item_total', number_format($total, 2, '.', ''));
        } else {
            $set('item_total', '0.00');
            }
        } finally {
            $calculating = false;
        }
    }
    
    public function getTotals(): array
    {
        $data = $this->form->getRawState();
        $subtotal = 0;
        $vatTotal = 0;
        $total = 0;
        
        foreach ($data['items'] ?? [] as $item) {
            $quantity = (float) ($item['quantity'] ?? 1);
            $price = (float) ($item['price'] ?? 0);
            $vatRate = (float) ($item['vat_rate'] ?? 21);
            
            $itemSubtotal = $quantity * $price;
            $itemVat = $itemSubtotal * ($vatRate / 100);
            $itemTotal = $itemSubtotal + $itemVat;
            
            $subtotal += $itemSubtotal;
            $vatTotal += $itemVat;
            $total += $itemTotal;
        }
        
        return [
            'subtotal' => number_format($subtotal, 2, ',', '.'),
            'vat_total' => number_format($vatTotal, 2, ',', '.'),
            'total' => number_format($total, 2, ',', '.'),
        ];
    }
    
    public function submit(): void
    {
        try {
            // Get raw state - this should contain all data from all wizard steps
            $rawData = $this->form->getRawState();
            
            // Debug: Check what data we actually have
            \Log::info('Invoice Submit - Raw Data:', [
                'has_customer_name' => !empty($rawData['customer_name']),
                'has_sender_company' => !empty($rawData['sender_company_name']),
                'has_sender_email' => !empty($rawData['sender_email']),
                'has_items' => !empty($rawData['items']) && count($rawData['items']) > 0,
                'customer_name_value' => $rawData['customer_name'] ?? 'MISSING',
                'items_count' => count($rawData['items'] ?? []),
            ]);
            
            // Manually validate required fields (since we removed ->required() to allow navigation)
            $validator = \Illuminate\Support\Facades\Validator::make($rawData, [
                'customer_name' => 'required|string|max:255',
                'invoice_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:invoice_date',
                'items' => 'required|array|min:1',
                'items.*.description' => 'required|string|max:255',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.vat_rate' => 'required|string',
            ]);
            
            if ($validator->fails()) {
                Notification::make()
                    ->title('Validatiefout')
                    ->body('Vul alle verplichte velden in: ' . implode(', ', array_keys($validator->errors()->toArray())))
                    ->danger()
                    ->send();
                throw new \Illuminate\Validation\ValidationException($validator);
            }
            
            $data = $rawData;
            
        $user = Auth::user();
        $clientId = $user->client_id;
        
        if (!$clientId) {
            Notification::make()
                ->title('Fout')
                ->body('Geen klant gekoppeld aan uw account')
                ->danger()
                ->send();
            return;
        }
            
            // Validate required fields
            if (empty($data['sender_company_name']) || empty($data['sender_email'])) {
                Notification::make()
                    ->title('Validatiefout')
                    ->body('Vul alle verplichte velden in bij "Uw Gegevens"')
                    ->danger()
                    ->send();
                return;
            }
            
            if (empty($data['customer_name'])) {
                Notification::make()
                    ->title('Validatiefout')
                    ->body('Vul de klantnaam in')
                    ->danger()
                    ->send();
                return;
            }
            
            if (empty($data['items']) || count($data['items']) === 0) {
                Notification::make()
                    ->title('Validatiefout')
                    ->body('Voeg minimaal één item toe aan de factuur')
                    ->danger()
                    ->send();
                return;
            }
            
            // Validate items
            foreach ($data['items'] as $index => $item) {
                if (empty($item['description']) || empty($item['price']) || (float)($item['price'] ?? 0) <= 0) {
                    Notification::make()
                        ->title('Validatiefout')
                        ->body("Item " . ($index + 1) . " heeft geen geldige omschrijving of prijs")
                        ->danger()
                        ->send();
                    return;
                }
        }
        
        // Calculate totals
        $subtotal = 0;
        $vatTotal = 0;
        $total = 0;
        
        foreach ($data['items'] ?? [] as $item) {
            $itemSubtotal = ($item['quantity'] ?? 1) * ($item['price'] ?? 0);
            $vatRate = ($item['vat_rate'] ?? 21) / 100;
            $itemVat = $itemSubtotal * $vatRate;
            $itemTotal = $itemSubtotal + $itemVat;
            
            $subtotal += $itemSubtotal;
            $vatTotal += $itemVat;
            $total += $itemTotal;
        }
        
        // Generate invoice number if not provided
        $invoiceNumber = $data['invoice_number'] ?? 'INV-' . now()->format('Y') . '-' . str_pad(
            Document::where('client_id', $clientId)
                ->where('document_type', 'sales_invoice')
                ->whereYear('created_at', now()->year)
                ->count() + 1,
            4,
            '0',
            STR_PAD_LEFT
        );
        
        // Create document record
        $documentData = [
            'client_id' => $clientId,
            'original_filename' => "Factuur_{$invoiceNumber}.pdf",
            'status' => 'pending',
            'document_type' => 'sales_invoice',
            'upload_source' => 'web',
            'amount_excl' => $subtotal,
            'amount_vat' => $vatTotal,
            'amount_incl' => $total,
            'vat_rate' => '21', // Default, can be calculated from items
            'supplier_name' => $data['customer_name'] ?? null,
            'document_date' => $data['invoice_date'] ?? now(),
            'ocr_data' => [
                'invoice_number' => $invoiceNumber,
                // Sender (Van) information
                'sender_company_name' => $data['sender_company_name'] ?? null,
                'sender_address' => $data['sender_address'] ?? null,
                'sender_email' => $data['sender_email'] ?? null,
                'sender_phone' => $data['sender_phone'] ?? null,
                'sender_kvk' => $data['sender_kvk'] ?? null,
                'sender_vat' => $data['sender_vat'] ?? null,
                'sender_logo' => $user->client->logo ?? null, // Use logo from client profile
                // Customer (Naar) information
                'customer_name' => $data['customer_name'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_address' => $data['customer_address'] ?? null,
                'customer_vat' => $data['customer_vat'] ?? null,
                'customer_kvk' => $data['customer_kvk'] ?? null,
                // Invoice details
                'invoice_date' => $data['invoice_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'items' => $data['items'] ?? [],
                'subtotal' => $subtotal,
                'vat_total' => $vatTotal,
                'total' => $total,
            ],
        ];
        
        // Create a placeholder file path (you might want to generate a PDF here)
        $documentData['file_path'] = 'sales-invoices/placeholder_' . $invoiceNumber . '.txt';
        Storage::put($documentData['file_path'], "Factuur {$invoiceNumber}\n\nDit is een door de klant aangemaakte factuur.");
        
        $document = Document::create($documentData);
        
        // Store created invoice data for preview
        $this->createdInvoice = [
            'invoice_number' => $invoiceNumber,
            // Sender (Van) information
            'sender_company_name' => $data['sender_company_name'] ?? null,
            'sender_address' => $data['sender_address'] ?? null,
            'sender_email' => $data['sender_email'] ?? null,
            'sender_phone' => $data['sender_phone'] ?? null,
            'sender_kvk' => $data['sender_kvk'] ?? null,
            'sender_vat' => $data['sender_vat'] ?? null,
                'sender_logo' => $user->client->logo ?? null, // Use logo from client profile
            // Customer (Naar) information
            'customer_name' => $data['customer_name'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? null,
            'customer_address' => $data['customer_address'] ?? null,
            'customer_vat' => $data['customer_vat'] ?? null,
            'customer_kvk' => $data['customer_kvk'] ?? null,
            // Invoice details
            'invoice_date' => $data['invoice_date'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'items' => $data['items'] ?? [],
            'subtotal' => $subtotal,
            'vat_total' => $vatTotal,
            'total' => $total,
            'document_id' => $document->id,
        ];
        
        Notification::make()
            ->title('✅ Factuur Aangemaakt!')
            ->body("Factuur {$invoiceNumber} is aangemaakt en wordt verwerkt.")
            ->success()
            ->duration(5000)
            ->send();
        
            // Reset form for new invoice
        $this->form->fill([
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'items' => [
                [
                    'description' => '',
                    'quantity' => 1,
                    'price' => 0,
                    'vat_rate' => '21',
                ],
            ],
        ]);
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('Fout bij aanmaken factuur')
                ->body('Er is een fout opgetreden: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function clearPreview(): void
    {
        $this->createdInvoice = null;
    }
    
    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }
    
    public function switchViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }
    
    public function getInvoices()
    {
        $user = Auth::user();
        $clientId = $user->client_id;
        
        if (!$clientId) {
            return collect();
        }
        
        // Only show invoices created through this form (not uploaded documents)
        // Form-created invoices have original_filename starting with "Factuur_"
        $query = Document::where('client_id', $clientId)
            ->where('document_type', 'sales_invoice')
            ->where('upload_source', 'web')
            ->where('original_filename', 'like', 'Factuur_%'); // Only form-created invoices
        
        // Status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }
        
        // Search filter
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('original_filename', 'like', '%' . $this->search . '%')
                  ->orWhereJsonContains('ocr_data->invoice_number', $this->search)
                  ->orWhereJsonContains('ocr_data->customer_name', $this->search)
                  ->orWhere('supplier_name', 'like', '%' . $this->search . '%');
            });
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }
    
    public function updatedSearch(): void
    {
        // Live search - no action needed, getInvoices() will handle it
    }
    
    public function updatedStatusFilter(): void
    {
        // Live filter - no action needed
    }
    
    public function downloadInvoicePdf(Document $document)
    {
        $user = Auth::user();
        
        // Verify ownership
        if ($document->client_id !== $user->client_id) {
            Notification::make()
                ->title('Fout')
                ->body('U heeft geen toegang tot deze factuur')
                ->danger()
                ->send();
            return;
        }
        
        $invoiceData = $document->ocr_data ?? [];
        
        // Generate PDF HTML
        $html = view('filament.client.components.invoice-pdf', [
            'document' => $document,
            'data' => $invoiceData,
        ])->render();
        
        // For now, return HTML (can be converted to PDF with dompdf/snappy later)
        // Create a downloadable HTML file
        $invoiceNumber = $invoiceData['invoice_number'] ?? 'INV-' . $document->id;
        $filename = "Factuur_{$invoiceNumber}.html";
        $path = 'sales-invoices/' . $filename;
        
        Storage::disk('local')->put($path, $html);
        
        return Storage::disk('local')->download($path, $filename);
    }
    
    public function viewInvoice(Document $invoice): void
    {
        $user = Auth::user();
        
        // Verify ownership
        if ($invoice->client_id !== $user->client_id) {
            Notification::make()
                ->title('Fout')
                ->body('U heeft geen toegang tot deze factuur')
                ->danger()
                ->send();
            return;
        }
        
        $invoiceData = $invoice->ocr_data ?? [];
        $subtotal = $invoice->amount_excl ?? 0;
        $vat = $invoice->amount_vat ?? 0;
        $total = $invoice->amount_incl ?? 0;
        
        $this->createdInvoice = array_merge($invoiceData, [
            'document_id' => $invoice->id,
            'subtotal' => $subtotal,
            'vat_total' => $vat,
            'total' => $total,
        ]);
        
        $this->activeTab = 'create';
    }
    
    public function markAsPaid(Document $invoice): void
    {
        $user = Auth::user();
        
        // Verify ownership
        if ($invoice->client_id !== $user->client_id) {
            Notification::make()
                ->title('Fout')
                ->body('U heeft geen toegang tot deze factuur')
                ->danger()
                ->send();
            return;
        }
        
        $invoice->update([
            'is_paid' => true,
            'paid_at' => now(),
        ]);
        
        Notification::make()
            ->title('Factuur gemarkeerd als betaald')
            ->body('De factuur is nu gemarkeerd als betaald en is belastbaar.')
            ->success()
            ->send();
    }
    
    public function markAsUnpaid(Document $invoice): void
    {
        $user = Auth::user();
        
        // Verify ownership
        if ($invoice->client_id !== $user->client_id) {
            Notification::make()
                ->title('Fout')
                ->body('U heeft geen toegang tot deze factuur')
                ->danger()
                ->send();
            return;
        }
        
        $invoice->update([
            'is_paid' => false,
            'paid_at' => null,
        ]);
        
        Notification::make()
            ->title('Factuur gemarkeerd als onbetaald')
            ->body('De factuur is nu gemarkeerd als onbetaald en is niet belastbaar.')
            ->success()
            ->send();
    }
    
    public function deleteInvoice(Document $invoice): void
    {
        $user = Auth::user();
        
        // Verify ownership
        if ($invoice->client_id !== $user->client_id) {
            Notification::make()
                ->title('Fout')
                ->body('U heeft geen toegang tot deze factuur')
                ->danger()
                ->send();
            return;
        }
        
        // Delete the invoice
        $invoice->delete();
        
        Notification::make()
            ->title('Factuur verwijderd')
            ->body('De factuur is succesvol verwijderd.')
            ->success()
            ->send();
    }
}

