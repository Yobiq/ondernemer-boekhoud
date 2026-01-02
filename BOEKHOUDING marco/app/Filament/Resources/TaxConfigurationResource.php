<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaxConfigurationResource\Pages;
use App\Models\TaxConfiguration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaxConfigurationResource extends Resource
{
    protected static ?string $model = TaxConfiguration::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationLabel = 'BTW Configuratie';
    
    protected static ?string $navigationGroup = 'Beheer';
    
    protected static ?int $navigationSort = 25;
    
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basis Instellingen')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->label('Sleutel')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabled(fn ($record) => $record !== null)
                            ->helperText('Unieke identifier voor deze configuratie'),
                        
                        Forms\Components\Select::make('category')
                            ->label('Categorie')
                            ->options([
                                'general' => 'Algemeen',
                                'validation' => 'Validatie',
                                'ocr' => 'OCR',
                                'belastingdienst' => 'Belastingdienst',
                                'auto_approval' => 'Auto-Goedkeuring',
                            ])
                            ->required()
                            ->default('general'),
                        
                        Forms\Components\KeyValue::make('value')
                            ->label('Waarde')
                            ->keyLabel('Sleutel')
                            ->valueLabel('Waarde')
                            ->helperText('JSON structuur voor configuratie waarden'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Beschrijving')
                            ->rows(3),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Actief')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Sleutel')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('category')
                    ->label('Categorie')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'general' => 'gray',
                        'validation' => 'warning',
                        'ocr' => 'info',
                        'belastingdienst' => 'success',
                        'auto_approval' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actief')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Beschrijving')
                    ->limit(50)
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Categorie')
                    ->options([
                        'general' => 'Algemeen',
                        'validation' => 'Validatie',
                        'ocr' => 'OCR',
                        'belastingdienst' => 'Belastingdienst',
                        'auto_approval' => 'Auto-Goedkeuring',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Actief'),
            ])
            ->actions([
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
            'index' => Pages\ListTaxConfigurations::route('/'),
            'create' => Pages\CreateTaxConfiguration::route('/create'),
            'edit' => Pages\EditTaxConfiguration::route('/{record}/edit'),
        ];
    }
}

