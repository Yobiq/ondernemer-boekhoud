<?php

namespace App\Filament\Pages;

use App\Models\Document;
use App\Models\LedgerAccount;
use App\Services\VatValidator;
use App\Services\VatCalculatorService;
use App\Services\AutoApprovalService;
use App\Services\AuditLogger;
use App\Services\Belastingdienst\BelastingdienstValidator;
use App\Services\Belastingdienst\VatRatesService;
use App\Services\DocumentInsightsService;
use App\Jobs\ProcessDocumentOcrJob;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class DocumentReview extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = '👀 Document Beoordeling';
    protected static ?string $navigationGroup = 'Workflow';
    protected static ?int $navigationSort = 1;
    
    protected static bool $shouldRegisterNavigation = true;
    protected static string $view = 'filament.pages.document-review';
    
    public ?Document $document = null;
    public ?array $data = [];
    
    public ?int $currentIndex = 0;
    public ?int $totalDocuments = 0;
    
    public function mount(?int $document = null): void
    {
        // Get total count
        $this->totalDocuments = Document::where('status', 'review_required')->count();
        
        // Get document to review
        if ($document) {
            $this->document = Document::find($document);
        } else {
            $this->document = Document::where('status', 'review_required')
                ->oldest()
                ->first();
        }
        
        if ($this->document) {
            // Calculate current index
            $this->currentIndex = Document::where('status', 'review_required')
                ->where('id', '<=', $this->document->id)
                ->count();
            
            // Fill form with document data (prioritize extracted OCR data)
            $formData = [
                'ledger_account_id' => $this->document->ledger_account_id,
                'amount_excl' => $this->document->amount_excl,
                'amount_vat' => $this->document->amount_vat,
                'amount_incl' => $this->document->amount_incl,
                'vat_rate' => $this->document->vat_rate,
                'vat_code' => $this->document->vat_code,
                'document_date' => $this->document->document_date?->format('Y-m-d'),
                'supplier_name' => $this->document->supplier_name,
            ];
            
            // Auto-determine vat_code if not set but vat_rate is available
            if (empty($formData['vat_code']) && !empty($formData['vat_rate'])) {
                if ($formData['vat_rate'] !== 'verlegd') {
                    $formData['vat_code'] = match((int)$formData['vat_rate']) {
                        21 => 'NL21',
                        9 => 'NL9',
                        0 => 'NL0',
                        default => null,
                    };
                } else {
                    $formData['vat_code'] = 'VERL';
                }
            }
            
            // If OCR data exists but fields are empty, try to extract from OCR data
            if ($this->document->ocr_data && is_array($this->document->ocr_data)) {
                $ocrData = $this->document->ocr_data;
                
                // Fill empty fields from OCR data
                if (empty($formData['supplier_name']) && !empty($ocrData['supplier']['name'])) {
                    $formData['supplier_name'] = $ocrData['supplier']['name'];
                }
                
                if (empty($formData['document_date']) && !empty($ocrData['invoice']['date'])) {
                    try {
                        $date = \Carbon\Carbon::parse($ocrData['invoice']['date']);
                        $formData['document_date'] = $date->format('Y-m-d');
                    } catch (\Exception $e) {
                        // Date parsing failed, keep null
                    }
                }
                
                if (empty($formData['amount_excl']) && !empty($ocrData['amounts']['excl'])) {
                    $formData['amount_excl'] = $ocrData['amounts']['excl'];
                }
                
                if (empty($formData['amount_vat']) && !empty($ocrData['amounts']['vat'])) {
                    $formData['amount_vat'] = $ocrData['amounts']['vat'];
                }
                
                if (empty($formData['amount_incl']) && !empty($ocrData['amounts']['incl'])) {
                    $formData['amount_incl'] = $ocrData['amounts']['incl'];
                }
                
                if (empty($formData['vat_rate']) && !empty($ocrData['amounts']['vat_rate'])) {
                    $formData['vat_rate'] = $ocrData['amounts']['vat_rate'];
                    // Auto-set vat_code when vat_rate is set from OCR
                    if (empty($formData['vat_code']) && $formData['vat_rate'] !== 'verlegd') {
                        $formData['vat_code'] = match((int)$formData['vat_rate']) {
                            21 => 'NL21',
                            9 => 'NL9',
                            0 => 'NL0',
                            default => null,
                        };
                    } elseif (empty($formData['vat_code']) && $formData['vat_rate'] === 'verlegd') {
                        $formData['vat_code'] = 'VERL';
                    }
                }
            }
            
            $this->form->fill($formData);
        }
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(12)
                    ->schema([
                        // Left column (8/12) - PDF Viewer & Document Info
                        Grid::make(1)
                            ->columnSpan(8)
                            ->schema([
                                Section::make('📄 Document Preview')
                                    ->description('Bekijk het geüploade document')
                                    ->schema([
                                        Placeholder::make('pdf_viewer')
                                            ->content(function () {
                                                if (!$this->document) {
                                                    return '<div class="p-8 text-center text-gray-500">Geen documenten beschikbaar voor review</div>';
                                                }
                                                
                                                $fileExtension = strtolower(pathinfo($this->document->original_filename, PATHINFO_EXTENSION));
                                                $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                $isPdf = $fileExtension === 'pdf';
                                                
                                                return view('filament.components.document-viewer', [
                                                    'document' => $this->document,
                                                    'isImage' => $isImage,
                                                    'isPdf' => $isPdf,
                                                ]);
                                            }),
                                    ])
                                    ->collapsible()
                                    ->collapsed(false),
                                
                                // Smart Insights Section
                                Section::make('🔍 Slimme Inzichten')
                                    ->description('Automatische controles en waarschuwingen')
                                    ->schema([
                                        Placeholder::make('smart_insights_content')
                                            ->content(function () {
                                                if (!$this->document) {
                                                    return null;
                                                }
                                                
                                                $insightsService = app(DocumentInsightsService::class);
                                                $insights = $insightsService->getInsights($this->document);
                                                
                                                return view('filament.components.smart-insights', [
                                                    'insights' => $insights ?? [],
                                                    'hasIssues' => !empty($insights),
                                                ]);
                                            }),
                                    ])
                                    ->collapsible()
                                    ->collapsed(false),
                                
                                // OCR Data Section
                                Section::make('📊 OCR Geëxtraheerde Data')
                                    ->description('Gegevens geëxtraheerd door OCR')
                                    ->schema([
                                        Placeholder::make('ocr_data_viewer')
                                            ->content(function () {
                                                if (!$this->document) {
                                                    return '<span class="text-gray-500">Geen document geselecteerd</span>';
                                                }
                                                
                                                return view('filament.components.ocr-data-viewer', [
                                                    'document' => $this->document,
                                                ]);
                                            }),
                                    ])
                                    ->collapsible()
                                    ->collapsed(true)
                                    ->visible(fn () => $this->document !== null && !empty($this->document->ocr_data)),
                            ]),
                        
                        // Right column (4/12) - Form Fields
                        Grid::make(1)
                            ->columnSpan(4)
                            ->schema([
                                // Auto-Approval Status
                                Section::make('📊 Auto-Approval Status')
                                    ->description('Status van automatische goedkeuring')
                                    ->schema([
                                        Placeholder::make('auto_approval_info')
                                            ->content(function () {
                                                if (!$this->document) return '-';
                                                
                                                $autoApprovalService = app(AutoApprovalService::class);
                                                $canAutoApprove = $autoApprovalService->shouldAutoApprove($this->document);
                                                $reasons = $autoApprovalService->getAutoApprovalReasons($this->document);
                                                
                                                return view('filament.components.auto-approval-info', [
                                                    'icon' => '📊',
                                                    'canAutoApprove' => $canAutoApprove,
                                                    'autoApprovalReasons' => $reasons,
                                                ]);
                                            }),
                                    ])
                                    ->collapsible()
                                    ->collapsed(false)
                                    ->visible(fn () => $this->document !== null),
                                
                                // Document Details Section
                                Section::make('📋 Document Gegevens')
                                    ->description('Basisinformatie over het document')
                                    ->schema([
                                        TextInput::make('supplier_name')
                                            ->label('Leverancier')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Naam van de leverancier')
                                            ->helperText('Automatisch ingevuld vanuit OCR indien beschikbaar')
                                            ->columnSpanFull(),
                                        
                                        DatePicker::make('document_date')
                                            ->label('Factuurdatum')
                                            ->native(false)
                                            ->displayFormat('d-m-Y')
                                            ->required()
                                            ->helperText('Datum van het document')
                                            ->columnSpanFull(),
                                        
                                        Select::make('ledger_account_id')
                                            ->label('Grootboekrekening')
                                            ->options(
                                                LedgerAccount::active()
                                                    ->orderBy('code')
                                                    ->get()
                                                    ->mapWithKeys(fn($a) => [$a->id => "{$a->code} - {$a->description}"])
                                            )
                                            ->searchable()
                                            ->required()
                                            ->live()
                                            ->helperText('Selecteer de juiste grootboekrekening voor dit document')
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible()
                                    ->collapsed(false),
                                
                                Section::make('💰 Bedragen')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('amount_excl')
                                                    ->label('Excl. BTW')
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                        $vatRate = $get('vat_rate');
                                                        $amountExcl = (float) $state;
                                                        if ($vatRate && $vatRate !== 'verlegd' && $amountExcl > 0) {
                                                            $amountVat = $amountExcl * ((float) $vatRate / 100);
                                                            $amountIncl = $amountExcl + $amountVat;
                                                            $set('amount_vat', number_format($amountVat, 2, '.', ''));
                                                            $set('amount_incl', number_format($amountIncl, 2, '.', ''));
                                                        }
                                                    }),
                                                
                                                TextInput::make('amount_vat')
                                                    ->label('BTW Bedrag')
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                        $amountExcl = (float) $get('amount_excl');
                                                        $amountVat = (float) $state;
                                                        if ($amountExcl > 0) {
                                                            $amountIncl = $amountExcl + $amountVat;
                                                            $set('amount_incl', number_format($amountIncl, 2, '.', ''));
                                                        }
                                                    }),
                                                
                                                TextInput::make('amount_incl')
                                                    ->label('Incl. BTW')
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                        $amountExcl = (float) $get('amount_excl');
                                                        $amountIncl = (float) $state;
                                                        if ($amountExcl > 0) {
                                                            $amountVat = $amountIncl - $amountExcl;
                                                            $set('amount_vat', number_format($amountVat, 2, '.', ''));
                                                        }
                                                    }),
                                            ]),
                                    ])
                                    ->collapsible()
                                    ->collapsed(false),
                                
                                Section::make('📋 BTW Gegevens')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('vat_rate')
                                                    ->label('BTW Tarief')
                                                    ->options([
                                                        '21' => '21%',
                                                        '9' => '9%',
                                                        '0' => '0%',
                                                        'verlegd' => 'Verlegd',
                                                    ])
                                                    ->required()
                                                    ->live(onBlur: false)
                                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                        $amountExcl = (float) $get('amount_excl');
                                                        if ($amountExcl > 0 && $state && $state !== 'verlegd') {
                                                            $amountVat = $amountExcl * ((float) $state / 100);
                                                            $amountIncl = $amountExcl + $amountVat;
                                                            $set('amount_vat', number_format($amountVat, 2, '.', ''));
                                                            $set('amount_incl', number_format($amountIncl, 2, '.', ''));
                                                        }
                                                        
                                                        // Auto-fill BTW code based on rate - MUST happen immediately
                                                        if ($state && $state !== 'verlegd') {
                                                            $vatCode = match((int)$state) {
                                                                21 => 'NL21',
                                                                9 => 'NL9',
                                                                0 => 'NL0',
                                                                default => null,
                                                            };
                                                            if ($vatCode) {
                                                                $set('vat_code', $vatCode);
                                                            }
                                                        } elseif ($state === 'verlegd') {
                                                            $set('vat_code', 'VERL');
                                                        }
                                                        
                                                        $this->validateBtw();
                                                    })
                                                    ->reactive(),
                                                
                                                Select::make('vat_code')
                                                    ->label('BTW Code')
                                                    ->options([
                                                        'NL21' => 'NL21 - Hoog tarief (21%)',
                                                        'NL9' => 'NL9 - Laag tarief (9%)',
                                                        'NL0' => 'NL0 - Vrijgesteld (0%)',
                                                        'VERL' => 'VERL - Verleggingsregeling',
                                                        'EU' => 'EU - Intracommunautair',
                                                        'IMPORT' => 'IMPORT - Import',
                                                        'VOORBELASTING' => 'VOORBELASTING - Voorbelasting',
                                                    ])
                                                    ->default(function (Get $get) {
                                                        // First check if document already has vat_code
                                                        if ($this->document?->vat_code) {
                                                            return $this->document->vat_code;
                                                        }
                                                        
                                                        // Then check form state (if vat_rate was just set)
                                                        $vatRate = $get('vat_rate');
                                                        if ($vatRate && $vatRate !== 'verlegd') {
                                                            return match((int)$vatRate) {
                                                                21 => 'NL21',
                                                                9 => 'NL9',
                                                                0 => 'NL0',
                                                                default => null,
                                                            };
                                                        } elseif ($vatRate === 'verlegd') {
                                                            return 'VERL';
                                                        }
                                                        
                                                        // Fallback to document's vat_rate
                                                        $docVatRate = $this->document?->vat_rate;
                                                        if ($docVatRate && $docVatRate !== 'verlegd') {
                                                            return match((int)$docVatRate) {
                                                                21 => 'NL21',
                                                                9 => 'NL9',
                                                                0 => 'NL0',
                                                                default => null,
                                                            };
                                                        } elseif ($docVatRate === 'verlegd') {
                                                            return 'VERL';
                                                        }
                                                        
                                                        return null;
                                                    })
                                                    ->required()
                                                    ->helperText('Automatisch ingevuld op basis van BTW tarief')
                                                    ->live(onBlur: false)
                                                    ->afterStateHydrated(function (Get $get, Set $set) {
                                                        // When form is hydrated, ensure vat_code is set if vat_rate exists
                                                        $vatCode = $get('vat_code');
                                                        $vatRate = $get('vat_rate');
                                                        
                                                        if (empty($vatCode) && !empty($vatRate)) {
                                                            if ($vatRate !== 'verlegd') {
                                                                $newVatCode = match((int)$vatRate) {
                                                                    21 => 'NL21',
                                                                    9 => 'NL9',
                                                                    0 => 'NL0',
                                                                    default => null,
                                                                };
                                                                if ($newVatCode) {
                                                                    $set('vat_code', $newVatCode);
                                                                }
                                                            } else {
                                                                $set('vat_code', 'VERL');
                                                            }
                                                        }
                                                    })
                                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                        if ($state) {
                                                            $vatCalculator = app(VatCalculatorService::class);
                                                            // Calculate rubriek from code
                                                            $tempDoc = $this->document ?? new Document();
                                                            $tempDoc->vat_code = $state;
                                                            $tempDoc->document_type = $this->document?->document_type ?? 'purchase_invoice';
                                                            $tempDoc->vat_rate = $get('vat_rate') ?? $this->document?->vat_rate;
                                                            $rubriek = $vatCalculator->calculateRubriek($tempDoc);
                                                            $set('vat_rubriek', $rubriek);
                                                        }
                                                    })
                                                    ->reactive(),
                                            ]),
                                        
                                        Placeholder::make('vat_rubriek_display')
                                            ->label('BTW Rubriek')
                                            ->content(function (Get $get) {
                                                if (!$this->document) {
                                                    return view('filament.components.placeholder-text', [
                                                        'text' => 'Geen document geselecteerd',
                                                        'type' => 'info'
                                                    ]);
                                                }
                                                
                                                // Calculate rubriek based on current form values
                                                $tempDoc = $this->document->replicate();
                                                $tempDoc->document_type = $this->document->document_type;
                                                $tempDoc->vat_rate = $get('vat_rate') ?? $this->document->vat_rate;
                                                $tempDoc->vat_code = $get('vat_code') ?? $this->document->vat_code;
                                                
                                                $vatCalculator = app(VatCalculatorService::class);
                                                $rubriek = $vatCalculator->calculateRubriek($tempDoc);
                                                
                                                $rubriekLabels = [
                                                    '1a' => '1a - Verkoop 21% (BTW Verschuldigd)',
                                                    '1b' => '1b - Verkoop 9% (BTW Verschuldigd)',
                                                    '1c' => '1c - Verkoop 0% (BTW Verschuldigd)',
                                                    '2a' => '2a - Inkoop BTW Aftrekbaar',
                                                    '5b' => '5b - Aftrek Overig',
                                                ];
                                                
                                                $label = $rubriekLabels[$rubriek] ?? $rubriek;
                                                
                                                return view('filament.components.vat-rubriek-display', [
                                                    'rubriek' => $rubriek,
                                                    'label' => $label,
                                                ]);
                                            })
                                            ->helperText('Automatisch berekend op basis van document type en BTW tarief'),
                                        
                                        Placeholder::make('btw_validation')
                                            ->label('✅ BTW Validatie (Belastingdienst)')
                                            ->content(function (Get $get) {
                                                if (!$this->document) {
                                                    return view('filament.components.placeholder-text', [
                                                        'text' => 'Geen document geselecteerd',
                                                        'type' => 'info'
                                                    ]);
                                                }
                                                
                                                $amountExcl = $get('amount_excl');
                                                $amountVat = $get('amount_vat');
                                                $vatRate = $get('vat_rate');
                                                $vatCode = $get('vat_code');
                                                
                                                if (empty($amountExcl) || empty($amountVat) || empty($vatRate) || empty($vatCode)) {
                                                    return view('filament.components.placeholder-text', [
                                                        'text' => 'Vul alle bedragen en BTW gegevens in om validatie uit te voeren',
                                                        'type' => 'info'
                                                    ]);
                                                }
                                                
                                                // Update document temporarily for validation
                                                $tempDoc = $this->document->replicate();
                                                $tempDoc->amount_excl = $amountExcl;
                                                $tempDoc->amount_vat = $amountVat;
                                                // Normalize vat_rate - ensure it's a string for consistency
                                                $tempDoc->vat_rate = is_numeric($vatRate) ? (string)$vatRate : $vatRate;
                                                
                                                // Auto-correct vat_code to match vat_rate if they don't match
                                                $expectedCode = null;
                                                if ($vatRate && $vatRate !== 'verlegd') {
                                                    $expectedCode = match((int)$vatRate) {
                                                        21 => 'NL21',
                                                        9 => 'NL9',
                                                        0 => 'NL0',
                                                        default => null,
                                                    };
                                                } elseif ($vatRate === 'verlegd') {
                                                    $expectedCode = 'VERL';
                                                }
                                                
                                                // Use expected code if it exists and doesn't match current code
                                                if ($expectedCode) {
                                                    $tempDoc->vat_code = $expectedCode;
                                                } else {
                                                    $tempDoc->vat_code = $vatCode;
                                                }
                                                
                                                // Calculate rubriek for validation
                                                $vatCalculator = app(VatCalculatorService::class);
                                                $tempDoc->vat_rubriek = $vatCalculator->calculateRubriek($tempDoc);
                                                
                                                // Use Belastingdienst validator
                                                $belastingdienstValidator = app(BelastingdienstValidator::class);
                                                $result = $belastingdienstValidator->validateVatCalculation($tempDoc);
                                                
                                                return view('filament.components.btw-validation-result', [
                                                    'isValid' => $result->isValid,
                                                    'errors' => $result->errors ?? [],
                                                    'warnings' => $result->warnings ?? [],
                                                    'calculatedRubriek' => $tempDoc->vat_rubriek,
                                                ]);
                                            })
                                            ->live(),
                                    ])
                                    ->collapsible()
                                    ->collapsed(false),
                                
                                // Notes Section
                                Section::make('📝 Notities')
                                    ->description('Optionele opmerkingen en notities')
                                    ->schema([
                                        Textarea::make('notes')
                                            ->label('Notities')
                                            ->rows(4)
                                            ->placeholder('Voeg hier eventuele notities of opmerkingen toe...')
                                            ->helperText('Optionele notities voor dit document')
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible()
                                    ->collapsed(true),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }
    
    protected function validateBtw(): void
    {
        // Trigger reactive update
    }
    
    public function approve(): void
    {
        $data = $this->form->getState();
        
        if (!$this->document) {
            Notification::make()
                ->title('Fout')
                ->body('Geen document geselecteerd')
                ->danger()
                ->send();
            return;
        }
        
        // Validate BTW before approving
        $validator = app(VatValidator::class);
        $vatResult = $validator->validate(
            (float)$data['amount_excl'],
            (float)$data['amount_vat'],
            $data['vat_rate']
        );
        
        if (!$vatResult['valid']) {
            Notification::make()
                ->title('BTW Validatie Fout')
                ->body($vatResult['message'])
                ->danger()
                ->send();
            return;
        }
        
        // Calculate BTW rubriek based on updated document data
        $vatCalculator = app(VatCalculatorService::class);
        $vatCode = $data['vat_code'] ?? $this->document->vat_code;
        
        // Update document temporarily to calculate correct rubriek
        $tempDoc = $this->document->replicate();
        $tempDoc->document_type = $this->document->document_type; // Keep original type
        $tempDoc->vat_rate = $data['vat_rate'];
        $tempDoc->vat_code = $vatCode;
        $vatRubriek = $vatCalculator->calculateRubriek($tempDoc);
        
        $this->document->update([
            'ledger_account_id' => $data['ledger_account_id'],
            'amount_excl' => $data['amount_excl'],
            'amount_vat' => $data['amount_vat'],
            'amount_incl' => $data['amount_incl'],
            'vat_rate' => $data['vat_rate'],
            'vat_code' => $vatCode,
            'vat_rubriek' => $vatRubriek,
            'document_date' => $data['document_date'],
            'supplier_name' => $data['supplier_name'],
            'status' => 'approved',
        ]);
        
        // Log approval
        $auditLogger = app(AuditLogger::class);
        $auditLogger->logDocumentApproval($this->document, auth()->user(), $this->document->auto_approved);
        
        Notification::make()
            ->title('Document goedgekeurd')
            ->body($this->document->auto_approved ? 'Document was automatisch goedgekeurd' : 'Document handmatig goedgekeurd')
            ->success()
            ->send();
        
        $this->redirect(static::getUrl());
    }
    
    public function reject(): void
    {
        if (!$this->document) {
            return;
        }
        
        $this->document->update(['status' => 'archived']);
        
        Notification::make()
            ->title('Document afgewezen')
            ->success()
            ->send();
        
        $this->redirect(static::getUrl());
    }
    
    public function next(): void
    {
        if (!$this->document) {
            $this->redirect(static::getUrl());
            return;
        }
        
        $nextDoc = Document::where('status', 'review_required')
            ->where('id', '>', $this->document->id)
            ->orderBy('id', 'asc')
            ->first();
        
        if ($nextDoc) {
            $this->redirect(static::getUrl(['document' => $nextDoc->id]));
        } else {
            $this->redirect(static::getUrl());
        }
    }
    
    public function previous(): void
    {
        if (!$this->document) {
            return;
        }
        
        $previousDoc = Document::where('status', 'review_required')
            ->where('id', '<', $this->document->id)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($previousDoc) {
            $this->redirect(static::getUrl(['document' => $previousDoc->id]));
        }
    }
    
    public function bulkApprove(): void
    {
        $count = Document::where('status', 'review_required')
            ->where('confidence_score', '>=', 85)
            ->update(['status' => 'approved']);
        
        Notification::make()
            ->title("Bulk goedkeuring")
            ->body("{$count} documenten automatisch goedgekeurd (confidence ≥85%)")
            ->success()
            ->send();
        
        $this->redirect(static::getUrl());
    }
    
    public function getOcrRawText(): ?string
    {
        if (!$this->document || !$this->document->ocr_data) {
            return null;
        }
        
        return $this->document->ocr_data['raw_text'] ?? 'Geen OCR tekst beschikbaar';
    }
    
    protected function renderConfidenceBadge(float $score, string $label = 'Confidence'): string
    {
        $color = $score >= 90 ? 'green' : ($score >= 70 ? 'yellow' : 'red');
        $icon = $score >= 90 ? '✅' : ($score >= 70 ? '⚠️' : '❌');
        
        return "<div class='p-2 bg-{$color}-50 dark:bg-{$color}-900/20 rounded border border-{$color}-200 dark:border-{$color}-800'>
            <div class='flex items-center justify-between'>
                <span class='text-{$color}-800 dark:text-{$color}-200 font-semibold'>{$icon} {$label}</span>
                <span class='text-{$color}-600 dark:text-{$color}-400 font-bold'>{$score}%</span>
            </div>
        </div>";
    }
    
    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('reprocess_ocr')
                ->label('🔄 Herverwerk OCR')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('OCR Herverwerken')
                ->modalDescription('Weet u zeker dat u de OCR voor dit document opnieuw wilt uitvoeren? Dit overschrijft de huidige geëxtraheerde gegevens.')
                ->action('reprocessOcr')
                ->visible(fn () => $this->document !== null && in_array($this->document->status, ['pending', 'ocr_processing', 'review_required'])),
            
            Action::make('bulk_approve')
                ->label('Bulk Goedkeuren (85%+)')
                ->icon('heroicon-o-check-circle')
                ->color('warning')
                ->requiresConfirmation()
                ->action('bulkApprove')
                ->visible(fn () => $this->totalDocuments > 0),
        ];
    }
    
    public function reprocessOcr(): void
    {
        if (!$this->document) {
            Notification::make()
                ->title('Fout')
                ->body('Geen document geselecteerd')
                ->danger()
                ->send();
            return;
        }
        
        // Reset document to pending and clear OCR data
        $this->document->update([
            'status' => 'pending',
            'ocr_data' => null,
            'amount_excl' => null,
            'amount_vat' => null,
            'amount_incl' => null,
            'vat_rate' => null,
            'document_date' => null,
            'supplier_name' => null,
            'supplier_vat' => null,
            'confidence_score' => null,
            'review_required_reason' => null,
        ]);
        
        // Dispatch OCR job
        ProcessDocumentOcrJob::dispatch($this->document);
        
        Notification::make()
            ->title('OCR Herverwerking Gestart')
            ->body('Het document wordt opnieuw verwerkt. Dit kan enkele minuten duren.')
            ->success()
            ->send();
        
        // Refresh the page to show updated status
        $this->redirect(static::getUrl(['document' => $this->document->id]));
    }
}
