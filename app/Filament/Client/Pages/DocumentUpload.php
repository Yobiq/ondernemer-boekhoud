<?php

namespace App\Filament\Client\Pages;

use App\Jobs\ProcessDocumentOcrJob;
use App\Models\Document;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class DocumentUpload extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-camera';
    protected static ?string $navigationLabel = 'Document Uploaden';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.client.pages.document-upload';
    protected static ?string $navigationGroup = 'Documenten';
    
    protected static bool $shouldRegisterNavigation = false;
    
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
                    Wizard\Step::make('Foto Maken')
                        ->description('Maak een foto van uw document met de camera')
                        ->icon('heroicon-o-camera')
                        ->schema([
                            Section::make()
                                ->schema([
                                    FileUpload::make('photos')
                                        ->label('Document Foto\'s')
                                        ->image()
                                        ->imageEditor()
                                        ->imageEditorAspectRatios([
                                            null,
                                            '16:9',
                                            '4:3',
                                        ])
                                        ->imageResizeTargetWidth('1920')
                                        ->imageResizeTargetHeight('1080')
                                        ->maxSize(10240)
                                        ->multiple()
                                        ->reorderable()
                                        ->panelLayout('grid')
                                        ->acceptedFileTypes(['image/*'])
                                        ->disk('local')
                                        ->directory('client-uploads')
                                        ->visibility('private')
                                        ->helperText('💡 TIP: Op mobiel opent de camera automatisch. Zorg voor goede belichting!')
                                        ->hint('15-20% betere OCR!')
                                        ->hintColor('success')
                                        ->columnSpanFull(),
                                ])
                                ->heading('📸 Maak een foto met uw camera')
                                ->description('De beste manier voor nauwkeurige verwerking. Foto\'s geven 15-20% betere OCR resultaten!')
                        ]),
                    
                    Wizard\Step::make('Of Upload Bestand')
                        ->description('Upload een PDF, afbeelding of Excel bestand')
                        ->icon('heroicon-o-document-arrow-up')
                        ->schema([
                            Section::make()
                                ->schema([
                                    FileUpload::make('files')
                                        ->label('Documenten')
                                        ->acceptedFileTypes([
                                            'application/pdf',
                                            'image/*',
                                            'text/csv',
                                            'application/vnd.ms-excel',
                                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        ])
                                        ->maxSize(20480) // 20MB
                                        ->multiple()
                                        ->reorderable()
                                        ->disk('local')
                                        ->directory('client-uploads')
                                        ->visibility('private')
                                        ->helperText('Accepteert: PDF, JPG, PNG, CSV, Excel')
                                        ->columnSpanFull(),
                                ])
                                ->heading('📄 Upload uw bestanden')
                                ->description('Voor documenten die u al digitaal heeft')
                        ]),
                    
                    Wizard\Step::make('Bevestigen')
                        ->description('Controleer en verstuur')
                        ->icon('heroicon-o-check-circle')
                        ->schema([
                            Section::make()
                                ->schema([
                                    \Filament\Forms\Components\Placeholder::make('summary')
                                        ->label('')
                                        ->content(function ($get) {
                                            $photos = $get('photos') ?? [];
                                            $files = $get('files') ?? [];
                                            $total = count($photos) + count($files);
                                            
                                            if ($total === 0) {
                                                return '⚠️ U heeft nog geen documenten gekozen';
                                            }
                                            
                                            return "✅ U heeft {$total} document(en) klaar om te versturen\n\n" .
                                                   "📸 Foto's: " . count($photos) . "\n" .
                                                   "📄 Bestanden: " . count($files) . "\n\n" .
                                                   "Zodra u op 'Verstuur' klikt, worden uw documenten automatisch verwerkt door ons systeem.";
                                        })
                                        ->columnSpanFull(),
                                ])
                                ->heading('🎉 Klaar om te versturen!')
                        ]),
                ])
                ->submitAction(view('filament.client.actions.submit-upload'))
                ->columnSpanFull()
            ])
            ->statePath('data');
    }
    
    public function submit(): void
    {
        $data = $this->form->getState();
        
        $user = Auth::user();
        $clientId = $user->client_id ?? null;
        
        if (!$clientId) {
            Notification::make()
                ->title('Geen klant gekoppeld')
                ->body('Uw account is nog niet gekoppeld aan een klant. Neem contact op met MARCOFIC.')
                ->danger()
                ->send();
            return;
        }
        
        $uploadedCount = 0;
        
        // Process photos
        $photos = $data['photos'] ?? [];
        foreach ($photos as $photo) {
            $document = Document::create([
                'client_id' => $clientId,
                'file_path' => $photo,
                'original_filename' => basename($photo),
                'status' => 'pending',
            ]);
            
            // Dispatch OCR job
            ProcessDocumentOcrJob::dispatch($document);
            $uploadedCount++;
        }
        
        // Process files
        $files = $data['files'] ?? [];
        foreach ($files as $file) {
            $document = Document::create([
                'client_id' => $clientId,
                'file_path' => $file,
                'original_filename' => basename($file),
                'status' => 'pending',
            ]);
            
            // Dispatch OCR job
            ProcessDocumentOcrJob::dispatch($document);
            $uploadedCount++;
        }
        
        if ($uploadedCount > 0) {
            Notification::make()
                ->title('Documenten ontvangen!')
                ->body("✅ {$uploadedCount} document(en) succesvol geüpload. Ze worden nu automatisch verwerkt.")
                ->success()
                ->duration(5000)
                ->send();
            
            // Reset form
            $this->form->fill();
        }
    }
    
    public function getTitle(): string
    {
        return 'Document Uploaden';
    }
    
    public function getHeading(): string
    {
        return '📸 Documenten Uploaden';
    }
    
    public function getSubheading(): ?string
    {
        return 'Maak een foto met uw telefoon of upload een bestand vanaf uw computer';
    }
}

