<?php

namespace App\Filament\Resources\BtwReportResource\Pages;

use App\Filament\Resources\BtwReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBtwReports extends ListRecords
{
    protected static string $resource = BtwReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
