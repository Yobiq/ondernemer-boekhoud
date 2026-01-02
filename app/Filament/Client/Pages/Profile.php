<?php

namespace App\Filament\Client\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Mijn Profiel';
    protected static ?int $navigationSort = 99;
    protected static string $view = 'filament.client.pages.profile';
    protected static ?string $navigationGroup = 'Mijn Gegevens';
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $user = Auth::user();
        $client = $user->client;
        
        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'company_name' => $client->company_name ?? '',
            'phone' => $client->phone ?? '',
            'kvk_number' => $client->kvk_number ?? '',
            'vat_number' => $client->vat_number ?? '',
            'address_line1' => $client->address_line1 ?? '',
            'address_line2' => $client->address_line2 ?? '',
            'postal_code' => $client->postal_code ?? '',
            'city' => $client->city ?? '',
            'country' => $client->country ?? 'Nederland',
            'website' => $client->website ?? '',
            'logo' => $client->logo ?? null,
            'notes' => $client->notes ?? '',
        ]);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Persoonlijke Gegevens')
                    ->description('Uw account informatie')
                    ->schema([
                        TextInput::make('name')
                            ->label('Naam')
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('email')
                            ->label('E-mailadres')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ])
                    ->columns(2),
                
                Section::make('Bedrijfsgegevens')
                    ->description('Uw bedrijfsinformatie')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Bedrijfslogo')
                            ->disk('public')
                            ->directory('client-logos')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml', 'image/gif', 'image/heic', 'image/heif'])
                            ->maxSize(5120) // 5MB for HEIC files
                            ->helperText('Upload uw bedrijfslogo. Max 5MB. PNG, JPG, SVG of HEIC. Dit logo wordt gebruikt op al uw facturen. Let op: HEIC bestanden kunnen niet worden voorvertoond in de browser.')
                            ->downloadable()
                            ->openable()
                            ->deletable(true)
                            ->storeFileNamesIn('logo_filename')
                            ->image() // Enable image preview for supported formats
                            ->imageEditor(false) // Disable editor to prevent HEIC processing issues
                            ->imagePreviewHeight('150px')
                            ->columnSpanFull(),
                        
                        TextInput::make('company_name')
                            ->label('Bedrijfsnaam')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        
                        Grid::make(2)
                            ->schema([
                                TextInput::make('kvk_number')
                                    ->label('KVK Nummer')
                                    ->maxLength(20)
                                    ->placeholder('12345678'),
                                
                                TextInput::make('vat_number')
                                    ->label('BTW Nummer')
                                    ->maxLength(20)
                                    ->placeholder('NL123456789B01'),
                            ]),
                        
                        TextInput::make('phone')
                            ->label('Telefoonnummer')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('+31 6 12345678'),
                        
                        TextInput::make('website')
                            ->label('Website')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://www.example.nl'),
                    ]),
                
                Section::make('Adresgegevens')
                    ->description('Uw bedrijfsadres')
                    ->schema([
                        TextInput::make('address_line1')
                            ->label('Adresregel 1')
                            ->maxLength(255)
                            ->placeholder('Straatnaam en huisnummer')
                            ->columnSpanFull(),
                        
                        TextInput::make('address_line2')
                            ->label('Adresregel 2')
                            ->maxLength(255)
                            ->placeholder('Appartement, suite, etc. (optioneel)')
                            ->columnSpanFull(),
                        
                        Grid::make(3)
                            ->schema([
                                TextInput::make('postal_code')
                                    ->label('Postcode')
                                    ->maxLength(10)
                                    ->placeholder('1234 AB'),
                                
                                TextInput::make('city')
                                    ->label('Plaats')
                                    ->maxLength(100)
                                    ->placeholder('Amsterdam'),
                                
                                TextInput::make('country')
                                    ->label('Land')
                                    ->default('Nederland')
                                    ->maxLength(100),
                            ]),
                    ]),
                
                Section::make('Overige Informatie')
                    ->description('Aanvullende informatie')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notities')
                            ->rows(4)
                            ->maxLength(1000)
                            ->placeholder('Eventuele aanvullende informatie...')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                
                Section::make('Wachtwoord Wijzigen')
                    ->description('Laat leeg om huidige wachtwoord te behouden')
                    ->schema([
                        TextInput::make('current_password')
                            ->label('Huidig Wachtwoord')
                            ->password()
                            ->dehydrated(false),
                        
                        Grid::make(2)
                            ->schema([
                                TextInput::make('new_password')
                                    ->label('Nieuw Wachtwoord')
                                    ->password()
                                    ->minLength(8)
                                    ->dehydrated(false)
                                    ->requiredWith('current_password'),
                                
                                TextInput::make('new_password_confirmation')
                                    ->label('Bevestig Nieuw Wachtwoord')
                                    ->password()
                                    ->same('new_password')
                                    ->dehydrated(false)
                                    ->requiredWith('current_password'),
                            ]),
                    ])
                    ->collapsible(),
                
                Section::make('Notificatie Voorkeuren')
                    ->description('Kies hoe u op de hoogte wilt blijven')
                    ->schema([
                        Select::make('notification_preferences')
                            ->label('Notificaties')
                            ->options([
                                'all' => 'Alle notificaties',
                                'important' => 'Alleen belangrijke',
                                'none' => 'Geen notificaties',
                            ])
                            ->default('all')
                            ->required(),
                    ])
                    ->collapsible(),
            ])
            ->statePath('data');
    }
    
    public function submit(): void
    {
        $data = $this->form->getState();
        $user = Auth::user();
        $client = $user->client;
        
        // Update user info
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
        
        // Update client/business info
        if ($client) {
            // Handle logo - FileUpload returns array sometimes, get first item or string
            $logoPath = $data['logo'] ?? null;
            if (is_array($logoPath) && !empty($logoPath)) {
                // If it's an array, get the first item
                $logoPath = is_string($logoPath[0]) ? $logoPath[0] : null;
            }
            
            // If logoPath is still an array after processing, try to get the first element
            if (is_array($logoPath)) {
                $logoPath = reset($logoPath);
            }
            
            // Ensure logo path is relative to storage/app/public
            if ($logoPath && !str_starts_with($logoPath, 'client-logos/')) {
                // If it's a full path, extract just the filename and prepend directory
                $logoPath = 'client-logos/' . basename($logoPath);
            }
            
            // Log for debugging if logo upload seems stuck
            if ($logoPath) {
                \Log::info('Logo upload processed', ['logo_path' => $logoPath]);
            }
            
            $client->update([
                'company_name' => $data['company_name'] ?? null,
                'phone' => $data['phone'] ?? null,
                'kvk_number' => $data['kvk_number'] ?? null,
                'vat_number' => $data['vat_number'] ?? null,
                'address_line1' => $data['address_line1'] ?? null,
                'address_line2' => $data['address_line2'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'city' => $data['city'] ?? null,
                'country' => $data['country'] ?? 'Nederland',
                'website' => $data['website'] ?? null,
                'logo' => $logoPath,
                'notes' => $data['notes'] ?? null,
            ]);
        }
        
        // Update password if provided
        if (!empty($data['current_password']) && !empty($data['new_password'])) {
            if (!Hash::check($data['current_password'], $user->password)) {
                Notification::make()
                    ->title('Fout')
                    ->body('Huidig wachtwoord is onjuist')
                    ->danger()
                    ->send();
                return;
            }
            
            $user->update([
                'password' => Hash::make($data['new_password']),
            ]);
        }
        
        Notification::make()
            ->title('Opgeslagen')
            ->body('Uw profiel is bijgewerkt')
            ->success()
            ->send();
    }
}
