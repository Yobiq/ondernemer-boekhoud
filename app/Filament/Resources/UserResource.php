<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    
    protected static ?string $navigationLabel = 'Gebruikers';
    
    protected static ?string $navigationGroup = 'Klanten';
    
    protected static ?int $navigationSort = 2;

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
                        
                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        Forms\Components\Select::make('client_id')
                            ->label('Klant')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Laat leeg voor admin/boekhouder accounts'),
                        
                        Forms\Components\TextInput::make('password')
                            ->label('Wachtwoord')
                            ->password()
                            ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => \Hash::make($state))
                            ->minLength(8)
                            ->helperText('Minimaal 8 karakters'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Rollen & Permissies')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Rol')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->options(function () {
                                $user = Auth::user();
                                // Bookkeepers can only assign client role
                                if ($user->hasRole('boekhouder') && !$user->hasRole('admin')) {
                                    return Role::whereIn('name', ['client'])->pluck('name', 'id');
                                }
                                // Admins can assign all roles
                                return Role::pluck('name', 'id');
                            })
                            ->required()
                            ->helperText('Selecteer één of meer rollen voor deze gebruiker'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();
                
                // Bookkeepers can only see users from their clients
                if ($user->hasRole('boekhouder') && !$user->hasRole('admin')) {
                    // Get all client IDs that this boekhouder manages
                    // For now, we'll show users from all clients (boekhouders typically manage multiple clients)
                    // If you want to restrict to specific clients, you'd need a boekhouder_clients pivot table
                    return $query->whereNotNull('client_id');
                }
                
                // Admins can see all users
                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),
                
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Klant')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Rol')
                    ->badge()
                    ->colors([
                        'danger' => 'admin',
                        'warning' => 'boekhouder',
                        'success' => 'accountant',
                        'info' => 'client',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'admin' => '👑 Admin',
                        'boekhouder' => '📊 Boekhouder',
                        'accountant' => '💼 Accountant',
                        'client' => '👤 Klant',
                        default => $state,
                    }),
                
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('E-mail Geverifieerd')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Aangemaakt')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Klant')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Rol')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('E-mail Geverifieerd')
                    ->nullable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
    
    public static function canViewAny(): bool
    {
        $user = Auth::user();
        // Only admins and boekhouders can manage users
        return $user && ($user->hasRole('admin') || $user->hasRole('boekhouder'));
    }
}

