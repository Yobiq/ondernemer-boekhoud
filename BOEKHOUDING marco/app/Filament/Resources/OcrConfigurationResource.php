<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OcrConfigurationResource\Pages;
use App\Models\OcrConfiguration;
use App\Services\OCR\OcrEngineFactory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;

class OcrConfigurationResource extends Resource
{
    protected static ?string $model = OcrConfiguration::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationLabel = 'OCR Configuratie';
    
    protected static ?string $navigationGroup = 'Beheer';
    
    protected static ?int $navigationSort = 20;
    
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        $availableEngines = OcrEngineFactory::getAvailableEngines();
        
        return $form
            ->schema([
                Forms\Components\Section::make('Basis Instellingen')
                    ->schema([
                        Forms\Components\Select::make('document_type')
                            ->label('Document Type')
                            ->options([
                                'invoice' => 'Factuur',
                                'receipt' => 'Bonnetje',
                                'form' => 'Formulier',
                                'bank_statement' => 'Bankafschrift',
                                'other' => 'Overig',
                            ])
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabled(fn ($record) => $record !== null),
                        
                        Forms\Components\Select::make('engine')
                            ->label('OCR Engine')
                            ->options([
                                'tesseract' => 'Tesseract (Lokaal)',
                                'aws_textract' => 'AWS Textract',
                                'google_vision' => 'Google Cloud Vision',
                                'azure_form_recognizer' => 'Azure Form Recognizer',
                            ])
                            ->required()
                            ->default('tesseract')
                            ->disabled(fn ($get) => !in_array($get('engine'), array_keys($availableEngines))),
                        
                        Forms\Components\TextInput::make('confidence_threshold')
                            ->label('Minimale Confidence Score')
                            ->numeric()
                            ->default(70)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->helperText('Minimum confidence score voor acceptatie'),
                        
                        Forms\Components\Toggle::make('enabled')
                            ->label('Ingeschakeld')
                            ->default(true),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('API Sleutels')
                    ->schema([
                        Forms\Components\TextInput::make('aws_key')
                            ->label('AWS Access Key')
                            ->password()
                            ->dehydrated(false)
                            ->visible(fn ($get) => $get('engine') === 'aws_textract'),
                        
                        Forms\Components\TextInput::make('aws_secret')
                            ->label('AWS Secret Key')
                            ->password()
                            ->dehydrated(false)
                            ->visible(fn ($get) => $get('engine') === 'aws_textract'),
                        
                        Forms\Components\TextInput::make('google_credentials_path')
                            ->label('Google Credentials Pad')
                            ->helperText('Pad naar JSON credentials bestand')
                            ->dehydrated(false)
                            ->visible(fn ($get) => $get('engine') === 'google_vision'),
                        
                        Forms\Components\TextInput::make('azure_endpoint')
                            ->label('Azure Endpoint')
                            ->url()
                            ->dehydrated(false)
                            ->visible(fn ($get) => $get('engine') === 'azure_form_recognizer'),
                        
                        Forms\Components\TextInput::make('azure_api_key')
                            ->label('Azure API Key')
                            ->password()
                            ->dehydrated(false)
                            ->visible(fn ($get) => $get('engine') === 'azure_form_recognizer'),
                    ])
                    ->collapsible(),
                
                Forms\Components\Section::make('Engine Specifieke Instellingen')
                    ->schema([
                        Forms\Components\KeyValue::make('engine_settings')
                            ->label('Instellingen')
                            ->keyLabel('Sleutel')
                            ->valueLabel('Waarde')
                            ->helperText('Extra instellingen voor de geselecteerde engine'),
                    ])
                    ->collapsible(),
                
                Forms\Components\Section::make('Prestatie Statistieken')
                    ->schema([
                        Forms\Components\TextInput::make('usage_count')
                            ->label('Aantal Gebruik')
                            ->disabled()
                            ->default(0),
                        
                        Forms\Components\TextInput::make('average_confidence')
                            ->label('Gemiddelde Confidence')
                            ->disabled()
                            ->suffix('%'),
                        
                        Forms\Components\TextInput::make('average_processing_time')
                            ->label('Gemiddelde Verwerkingstijd')
                            ->disabled()
                            ->suffix(' seconden'),
                    ])
                    ->columns(3)
                    ->visible(fn ($record) => $record !== null),
                
                Forms\Components\Textarea::make('notes')
                    ->label('Notities')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Document Type')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'invoice' => 'Factuur',
                        'receipt' => 'Bonnetje',
                        'form' => 'Formulier',
                        'bank_statement' => 'Bankafschrift',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('engine')
                    ->label('Engine')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'tesseract' => 'Tesseract',
                        'aws_textract' => 'AWS Textract',
                        'google_vision' => 'Google Vision',
                        'azure_form_recognizer' => 'Azure Form Recognizer',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'tesseract' => 'gray',
                        'aws_textract' => 'warning',
                        'google_vision' => 'info',
                        'azure_form_recognizer' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('confidence_threshold')
                    ->label('Confidence Threshold')
                    ->suffix('%')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('enabled')
                    ->label('Ingeschakeld')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('usage_count')
                    ->label('Gebruik')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('average_confidence')
                    ->label('Avg. Confidence')
                    ->suffix('%')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('average_processing_time')
                    ->label('Avg. Tijd')
                    ->suffix('s')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('engine')
                    ->label('Engine')
                    ->options([
                        'tesseract' => 'Tesseract',
                        'aws_textract' => 'AWS Textract',
                        'google_vision' => 'Google Vision',
                        'azure_form_recognizer' => 'Azure Form Recognizer',
                    ]),
                
                Tables\Filters\TernaryFilter::make('enabled')
                    ->label('Ingeschakeld'),
            ])
            ->actions([
                Action::make('test')
                    ->label('Test OCR')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->form([
                        Forms\Components\FileUpload::make('test_file')
                            ->label('Test Bestand')
                            ->required()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf']),
                    ])
                    ->action(function (OcrConfiguration $record, array $data) {
                        try {
                            $filePath = Storage::path($data['test_file']);
                            $engine = OcrEngineFactory::create($record->engine, $record->document_type);
                            
                            $startTime = microtime(true);
                            $result = $engine->process($filePath);
                            $processingTime = microtime(true) - $startTime;
                            
                            $confidence = 0;
                            if (method_exists($engine, 'getConfidenceScores')) {
                                $scores = $engine->getConfidenceScores();
                                $confidence = $scores['average'] ?? 0;
                            }
                            
                            // Update metrics
                            $record->updateMetrics($confidence, $processingTime);
                            
                            Notification::make()
                                ->title('OCR Test Succesvol')
                                ->body("Verwerkingstijd: " . round($processingTime, 2) . "s\nConfidence: " . round($confidence, 2) . "%")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('OCR Test Mislukt')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOcrConfigurations::route('/'),
            'create' => Pages\CreateOcrConfiguration::route('/create'),
            'edit' => Pages\EditOcrConfiguration::route('/{record}/edit'),
        ];
    }
}

