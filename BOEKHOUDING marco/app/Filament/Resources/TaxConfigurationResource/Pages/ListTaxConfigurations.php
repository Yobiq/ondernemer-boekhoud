<?php

namespace App\Filament\Resources\TaxConfigurationResource\Pages;

use App\Filament\Resources\TaxConfigurationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaxConfigurations extends ListRecords
{
    protected static string $resource = TaxConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

