<?php

namespace App\Filament\Client\Pages;

use App\Jobs\ProcessDocumentOcrJob;
use App\Models\Document;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class SmartUpload extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-camera';
    protected static ?string $navigationLabel = 'Document Uploaden';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.client.pages.smart-upload';
    protected static ?string $navigationGroup = 'Documenten';
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill();
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // STEP 1: What are you uploading?
                    Wizard\Step::make('Type Document')
                        ->description('Wat voor soort document uploadt u?')
                        ->icon('heroicon-o-question-mark-circle')
                        ->schema([
                            Section::make()
                                ->schema([
                                    Radio::make('document_type')
                                        ->label('')
                                        ->options([
                                            'receipt' => '🧾 Bonnetje / Klein Bonnetje',
                                            'purchase_invoice' => '📄 Inkoopfactuur van Leverancier',
                                            'bank_statement' => '🏦 Bankafschrift (CSV)',
                                            'sales_invoice' => '🧑‍💼 Verkoopfactuur (aan klant)',
                                            'other' => '📁 Anders / Weet niet zeker',
                                        ])
                                        ->descriptions([
                                            'receipt' => 'Tankbon, supermarkt, parkeren, kleine aankopen',
                                            'purchase_invoice' => 'Factuur van leverancier of dienstverlener',
                                            'bank_statement' => 'Export van uw bank (ING, Rabobank, etc.)',
                                            'sales_invoice' => 'Factuur die u naar uw klant hebt gestuurd',
                                            'other' => 'Contract, brief, of onzeker',
                                        ])
                                        ->required()
                                        ->live()
                                        ->columnSpanFull(),
                                ])
                                ->heading('💡 Wat uploadt u?')
                                ->description('Dit helpt ons systeem om beter te verwerken')
                        ]),
                    
                    // STEP 2: Upload (Context-Aware)
                    Wizard\Step::make('Upload')
                        ->description('Upload uw document')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->schema([
                            Section::make()
                                ->schema([
                                    FileUpload::make('files')
                                        ->label(fn (Get $get) => match($get('document_type')) {
                                            'receipt' => '📸 Maak foto van bonnetje',
                                            'bank_statement' => '📊 Upload CSV bestand',
                                            default => '📄 Upload document',
                                        })
                                        ->acceptedFileTypes(fn (Get $get) => match($get('document_type')) {
                                            'bank_statement' => ['text/csv', 'application/vnd.ms-excel'],
                                            default => ['image/*', 'application/pdf'],
                                        })
                                        ->image(fn (Get $get) => $get('document_type') === 'receipt')
                                        ->imageEditor(fn (Get $get) => $get('document_type') === 'receipt')
                                        ->maxSize(20480)
                                        ->multiple()
                                        ->reorderable()
                                        ->panelLayout('grid')
                                        ->disk('local')
                                        ->directory('client-uploads')
                                        ->visibility('private')
                                        ->downloadable()
                                        ->previewable()
                                        ->openable()
                                        ->deletable()
                                        ->helperText(fn (Get $get) => match($get('document_type')) {
                                            'receipt' => '📱 Op mobiel: Camera opent automatisch! Houd telefoon recht boven bonnetje. Sleep bestanden hierheen of klik om te selecteren.',
                                            'bank_statement' => '💡 Download CSV export van uw internetbankieren. Sleep bestanden hierheen of klik om te selecteren.',
                                            'purchase_invoice' => '📄 PDF of foto van de factuur. Sleep bestanden hierheen of klik om te selecteren.',
                                            default => '📸 Foto of PDF werkt beiden prima. Sleep bestanden hierheen of klik om te selecteren.',
                                        })
                                        ->columnSpanFull(),
                                ])
                                ->heading(fn (Get $get) => match($get('document_type')) {
                                    'receipt' => '📸 Maak een foto',
                                    'bank_statement' => '📊 Upload bankafschrift',
                                    'purchase_invoice' => '📄 Upload factuur',
                                    default => '📤 Upload uw document',
                                })
                                ->description(fn (Get $get) => match($get('document_type')) {
                                    'receipt' => 'Foto\'s geven 15-20% betere resultaten dan scans!',
                                    'bank_statement' => 'CSV wordt automatisch geïmporteerd als transacties',
                                    default => 'Wij zorgen voor de rest!',
                                })
                        ]),
                    
                    // STEP 3: Confirmation
                    Wizard\Step::make('Bevestigen')
                        ->description('Klaar om te versturen')
                        ->icon('heroicon-o-check-circle')
                        ->schema([
                            Section::make()
                                ->schema([
                                    \Filament\Forms\Components\Placeholder::make('summary')
                                        ->label('')
                                        ->content(function ($get) {
                                            $files = $get('files') ?? [];
                                            $type = $get('document_type');
                                            $total = count($files);
                                            
                                            if ($total === 0) {
                                                return view('filament.client.upload-summary', [
                                                    'total' => 0,
                                                    'type' => $type,
                                                ]);
                                            }
                                            
                                            return view('filament.client.upload-summary', [
                                                'total' => $total,
                                                'type' => $type,
                                                'typeLabel' => $this->getTypeLabel($type),
                                            ]);
                                        })
                                        ->columnSpanFull(),
                                ])
                        ]),
                ])
                ->submitAction(view('filament.client.actions.submit-smart-upload'))
                ->columnSpanFull()
            ])
            ->statePath('data');
    }
    
    protected function getTypeLabel(string $type): string
    {
        return match($type) {
            'receipt' => 'bonnetje(s)',
            'purchase_invoice' => 'inkoopfactuur/facturen',
            'bank_statement' => 'bankafschrift(en)',
            'sales_invoice' => 'verkoopfactuur/facturen',
            'other' => 'document(en)',
            default => 'document(en)',
        };
    }
    
    public function submit(): void
    {
        $data = $this->form->getState();
        
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
        
        $files = $data['files'] ?? [];
        $documentType = $data['document_type'] ?? 'other';
        $uploadSource = request()->userAgent() && preg_match('/Mobile|Android|iPhone/i', request()->userAgent()) 
            ? 'mobile_camera' 
            : 'web';
        
        $uploadedCount = 0;
        
        foreach ($files as $file) {
            $document = Document::create([
                'client_id' => $clientId,
                'file_path' => $file,
                'original_filename' => basename($file),
                'status' => 'pending',
                'document_type' => $documentType,
                'upload_source' => $uploadSource,
            ]);
            
            ProcessDocumentOcrJob::dispatch($document);
            $uploadedCount++;
        }
        
        if ($uploadedCount > 0) {
            $typeLabel = $this->getTypeLabel($documentType);
            
            Notification::make()
                ->title('✅ Documenten Ontvangen!')
                ->body("Perfect! We verwerken uw {$typeLabel} automatisch. U hoeft verder niets te doen.")
                ->success()
                ->duration(6000)
                ->send();
            
            $this->redirect('/klanten');
        }
    }
}

