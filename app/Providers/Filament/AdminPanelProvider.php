<?php

namespace App\Providers\Filament;

use App\Http\Middleware\RedirectToCorrectPanel;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->login() // Enable login page
            ->brandName('MARCOFIC Admin')
            ->brandLogo(asset('images/logo.svg'))
            ->brandLogoHeight('2rem')
            ->colors([
                'primary' => Color::hex('#059669'), // MARCOFIC green
                'success' => Color::Green,
                'warning' => Color::Amber,
                'danger' => Color::Red,
                'info' => Color::Blue,
            ])
            ->font('Inter')
            ->darkMode(true)
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                'Workflow' => '🔄 Workflow',
                'Overzichten' => '📊 Overzichten',
                'Beheer' => '⚙️ Beheer',
                'Klanten' => '👥 Klanten',
            ])
            ->sidebarFullyCollapsibleOnDesktop()
            ->sidebarWidth('16rem')
            ->spa()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                \App\Filament\Pages\ClientTaxWorkflow::class,
                \App\Filament\Pages\BtwPeriodesPerKlant::class,
                \App\Filament\Pages\BtwAangifteEnAftrek::class, // New unified page
                \App\Filament\Pages\DocumentsByClient::class,
                \App\Filament\Pages\DocumentReview::class,
                \App\Filament\Pages\GrootboekOverzicht::class,
                \App\Filament\Pages\FinancialInsightsDashboard::class,
                \App\Filament\Pages\ClientDashboard::class, // New unified client dashboard
                \App\Filament\Pages\GlobalSearch::class, // Global search
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Dashboard widgets auto-discovered
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                RedirectToCorrectPanel::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
