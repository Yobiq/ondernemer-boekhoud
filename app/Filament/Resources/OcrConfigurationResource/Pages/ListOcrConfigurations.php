<?php

namespace App\Filament\Resources\OcrConfigurationResource\Pages;

use App\Filament\Resources\OcrConfigurationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOcrConfigurations extends ListRecords
{
    protected static string $resource = OcrConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

