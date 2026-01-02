<?php

namespace App\Filament\Client\Pages;

use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Onboarding extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Handleiding';
    protected static ?int $navigationSort = 99;
    protected static string $view = 'filament.client.pages.onboarding';
    protected static ?string $navigationGroup = 'Hulp';
    
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
                    // Step 1: Welkom
                    Wizard\Step::make('Welkom')
                        ->description('Welkom bij MARCOFIC!')
                        ->icon('heroicon-o-hand-raised')
                        ->schema([
                            Section::make()
                                ->schema([
                                    Placeholder::make('welcome')
                                        ->content(view('filament.client.onboarding.welcome', [
                                            'user' => Auth::user(),
                                        ]))
                                ])
                        ]),
                    
                    // Step 2: Camera Upload
                    Wizard\Step::make('Camera Upload')
                        ->description('Hoe werkt het?')
                        ->icon('heroicon-o-camera')
                        ->schema([
                            Section::make()
                                ->schema([
                                    Placeholder::make('camera_guide')
                                        ->content(view('filament.client.onboarding.camera-guide'))
                                ])
                        ]),
                    
                    // Step 3: Dashboard
                    Wizard\Step::make('Dashboard')
                        ->description('Uw persoonlijke overzicht')
                        ->icon('heroicon-o-chart-bar')
                        ->schema([
                            Section::make()
                                ->schema([
                                    Placeholder::make('dashboard_guide')
                                        ->content(view('filament.client.onboarding.dashboard-guide'))
                                ])
                        ]),
                    
                    // Step 4: Tips
                    Wizard\Step::make('Tips & Tricks')
                        ->description('Optimale resultaten')
                        ->icon('heroicon-o-light-bulb')
                        ->schema([
                            Section::make()
                                ->schema([
                                    Placeholder::make('tips')
                                        ->content(view('filament.client.onboarding.tips'))
                                ])
                        ]),
                    
                    // Step 5: Start
                    Wizard\Step::make('Klaar!')
                        ->description('Begin met uploaden')
                        ->icon('heroicon-o-rocket-launch')
                        ->schema([
                            Section::make()
                                ->schema([
                                    Placeholder::make('start')
                                        ->content(view('filament.client.onboarding.start'))
                                ])
                        ]),
                ])
                ->submitAction(view('filament.client.onboarding.submit-action'))
                ->skippable()
                ->persistStepInQueryString()
                ->columnSpanFull()
            ])
            ->statePath('data');
    }
    
    public function complete(): void
    {
        // Mark onboarding as complete
        $user = Auth::user();
        $user->update(['onboarding_completed' => true]);
        
        // Redirect to upload page
        $this->redirect(\App\Filament\Client\Pages\DocumentUpload::getUrl());
    }
    
    public function getTitle(): string
    {
        return 'Welkom bij MARCOFIC';
    }
}

