<?php

namespace App\Filament\Pages;

use App\Models\VatPeriod;
use App\Models\Client;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use App\Services\VatCalculatorService;

class TaxReconciliation extends Page implements HasForms
{
    use InteractsWithForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-scale';
    
    protected static string $view = 'filament.pages.tax-reconciliation';
    
    protected static ?string $navigationLabel = 'BTW Afstemming';
    
    protected static ?string $navigationGroup = 'Financieel';
    
    protected static ?int $navigationSort = 20;
    
    protected static bool $shouldRegisterNavigation = false;
    
    protected static ?string $title = 'BTW Periode Afstemming';
    
    public ?int $period1_id = null;
    public ?int $period2_id = null;
    public ?array $comparison = null;
    
    public function mount(): void
    {
        $this->form->fill();
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Selecteer Periodes')
                    ->schema([
                        Forms\Components\Select::make('period1_id')
                            ->label('Periode 1')
                            ->options(function () {
                                return VatPeriod::with('client')
                                    ->orderBy('period_start', 'desc')
                                    ->get()
                                    ->mapWithKeys(function ($period) {
                                        return [$period->id => "{$period->client->name} - {$period->period_string}"];
                                    });
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn () => $this->compare()),
                        
                        Forms\Components\Select::make('period2_id')
                            ->label('Periode 2')
                            ->options(function () {
                                return VatPeriod::with('client')
                                    ->orderBy('period_start', 'desc')
                                    ->get()
                                    ->mapWithKeys(function ($period) {
                                        return [$period->id => "{$period->client->name} - {$period->period_string}"];
                                    });
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn () => $this->compare()),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }
    
    public function compare(): void
    {
        $data = $this->form->getState();
        
        if (empty($data['period1_id']) || empty($data['period2_id'])) {
            $this->comparison = null;
            return;
        }
        
        $period1 = VatPeriod::with('documents')->find($data['period1_id']);
        $period2 = VatPeriod::with('documents')->find($data['period2_id']);
        
        if (!$period1 || !$period2) {
            return;
        }
        
        $vatCalculator = app(VatCalculatorService::class);
        $totals1 = $vatCalculator->calculatePeriodTotals($period1);
        $totals2 = $vatCalculator->calculatePeriodTotals($period2);
        
        $comparison = [];
        $rubrieken = ['1a', '1b', '1c', '2a', '3a', '3b', '4a', '5b'];
        
        foreach ($rubrieken as $rubriek) {
            $amount1 = $totals1[$rubriek]['amount'] ?? 0;
            $vat1 = $totals1[$rubriek]['vat'] ?? 0;
            $amount2 = $totals2[$rubriek]['amount'] ?? 0;
            $vat2 = $totals2[$rubriek]['vat'] ?? 0;
            
            $amountDiff = $amount2 - $amount1;
            $vatDiff = $vat2 - $vat1;
            $amountPercentChange = $amount1 > 0 ? (($amountDiff / $amount1) * 100) : 0;
            
            $comparison[$rubriek] = [
                'rubriek' => $rubriek,
                'period1_amount' => $amount1,
                'period1_vat' => $vat1,
                'period2_amount' => $amount2,
                'period2_vat' => $vat2,
                'amount_diff' => $amountDiff,
                'vat_diff' => $vatDiff,
                'amount_percent_change' => $amountPercentChange,
                'variance' => abs($amountPercentChange) > 10 ? 'high' : (abs($amountPercentChange) > 5 ? 'medium' : 'low'),
            ];
        }
        
        $this->comparison = $comparison;
        $this->period1_id = $data['period1_id'];
        $this->period2_id = $data['period2_id'];
    }
    
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('compare')
                ->label('Vergelijk')
                ->icon('heroicon-o-arrows-right-left')
                ->action('compare'),
        ];
    }
}

