<?php

namespace App\Filament\Resources\VatPeriodResource\Pages;

use App\Filament\Resources\VatPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVatPeriod extends EditRecord
{
    protected static string $resource = VatPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => !$this->record->isLocked()),
        ];
    }
}


