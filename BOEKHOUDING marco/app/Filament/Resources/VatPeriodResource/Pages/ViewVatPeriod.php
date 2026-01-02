<?php

namespace App\Filament\Resources\VatPeriodResource\Pages;

use App\Filament\Resources\VatPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewVatPeriod extends ViewRecord
{
    protected static string $resource = VatPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => !$this->record->isLocked()),
        ];
    }
}


