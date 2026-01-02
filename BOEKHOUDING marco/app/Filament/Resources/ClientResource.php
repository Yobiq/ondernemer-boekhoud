<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'Klanten';
    
    protected static ?string $navigationGroup = 'Klanten';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basis Informatie')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Naam')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('company_name')
                            ->label('Bedrijfsnaam')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefoon')
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Adres')
                    ->schema([
                        Forms\Components\TextInput::make('address_line1')
                            ->label('Adresregel 1')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('address_line2')
                            ->label('Adresregel 2')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('postal_code')
                            ->label('Postcode')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('city')
                            ->label('Stad')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('country')
                            ->label('Land')
                            ->default('Nederland')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Bedrijfsgegevens')
                    ->schema([
                        Forms\Components\TextInput::make('kvk_number')
                            ->label('KVK Nummer')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('vat_number')
                            ->label('BTW Nummer')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('website')
                            ->label('Website')
                            ->url()
                            ->maxLength(255),
                        
                        Forms\Components\FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->directory('client-logos')
                            ->visibility('public'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('BTW Instellingen')
                    ->schema([
                        Forms\Components\Select::make('default_vat_period_type')
                            ->label('Standaard BTW Periode Type')
                            ->options([
                                'monthly' => 'Maandelijks',
                                'quarterly' => 'Kwartaals',
                            ])
                            ->default('quarterly')
                            ->required(),
                        
                        Forms\Components\Select::make('vat_submission_method')
                            ->label('BTW Indieningsmethode')
                            ->options([
                                'digital' => 'Digitaal',
                                'paper' => 'Papier',
                                'api' => 'API',
                            ]),
                        
                        Forms\Components\Toggle::make('auto_approval_enabled')
                            ->label('Auto-Goedkeuring Ingeschakeld')
                            ->default(true)
                            ->helperText('Documenten met hoge confidence worden automatisch goedgekeurd'),
                        
                        Forms\Components\TextInput::make('auto_approval_threshold')
                            ->label('Auto-Goedkeuring Drempelwaarde')
                            ->numeric()
                            ->suffix('%')
                            ->default(85)
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText('Minimum confidence score voor auto-goedkeuring'),
                        
                        Forms\Components\Toggle::make('email_notifications_enabled')
                            ->label('E-mail Notificaties Ingeschakeld')
                            ->default(true),
                        
                        Forms\Components\KeyValue::make('notification_preferences')
                            ->label('Notificatie Voorkeuren')
                            ->keyLabel('Type')
                            ->valueLabel('Ingeschakeld')
                            ->helperText('Aanpasbare notificatie instellingen'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Overige')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notities')
                            ->rows(3),
                        
                        Forms\Components\Toggle::make('active')
                            ->label('Actief')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Bedrijfsnaam')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('vat_number')
                    ->label('BTW Nummer')
                    ->searchable(),
                
                Tables\Columns\IconColumn::make('active')
                    ->label('Actief')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Aangemaakt')
                    ->dateTime('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Actief'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            RelationManagers\TaxPeriodsRelationManager::class,
            RelationManagers\TaxDocumentsRelationManager::class,
            RelationManagers\TaxAnalyticsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
