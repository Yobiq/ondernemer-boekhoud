<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\TaxOverviewWidget;
use App\Filament\Widgets\PeriodStatusWidget;
use App\Filament\Widgets\ClientTaxSummaryWidget;
use App\Filament\Widgets\ValidationAlertsWidget;
use App\Filament\Widgets\AutomationRateChartWidget;
use App\Filament\Widgets\ProcessingTimeMetricsWidget;

class TaxManagementDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static string $view = 'filament.pages.tax-management-dashboard';
    
    protected static ?string $navigationLabel = 'BTW Dashboard';
    
    protected static ?string $navigationGroup = 'Financieel';
    
    protected static ?int $navigationSort = 1;
    
    protected static bool $shouldRegisterNavigation = false;
    
    protected static ?string $title = 'BTW Beheer Dashboard';
    
    protected function getHeaderWidgets(): array
    {
        return [
            TaxOverviewWidget::class,
            PeriodStatusWidget::class,
            ClientTaxSummaryWidget::class,
        ];
    }
    
    protected function getFooterWidgets(): array
    {
        return [
            ValidationAlertsWidget::class,
            AutomationRateChartWidget::class,
            ProcessingTimeMetricsWidget::class,
            \App\Filament\Widgets\TaxTrendsWidget::class,
            \App\Filament\Widgets\RubriekBreakdownWidget::class,
            \App\Filament\Widgets\ClientComparisonWidget::class,
            \App\Filament\Widgets\TaxCalendarWidget::class,
        ];
    }
}

