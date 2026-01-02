<?php

namespace App\Filament\Resources\VatPeriodResource\Pages;

use App\Filament\Resources\VatPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVatPeriods extends ListRecords
{
    protected static string $resource = VatPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}


