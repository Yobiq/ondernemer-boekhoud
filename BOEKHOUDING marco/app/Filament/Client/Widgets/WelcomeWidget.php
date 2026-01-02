<?php

namespace App\Filament\Client\Widgets;

use Filament\Widgets\Widget;

class WelcomeWidget extends Widget
{
    protected static string $view = 'filament.client.widgets.welcome-widget';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = -1;
    
    /**
     * Disable this widget - it should never be displayed
     */
    public static function canView(): bool
    {
        return false;
    }
}

