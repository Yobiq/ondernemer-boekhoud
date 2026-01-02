<?php

namespace App\Filament\Resources\VatPeriodResource\Pages;

use App\Filament\Resources\VatPeriodResource;
use Filament\Resources\Pages\CreateRecord;
use Carbon\Carbon;

class CreateVatPeriod extends CreateRecord
{
    protected static string $resource = VatPeriodResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate year, quarter, month from dates
        $startDate = Carbon::parse($data['period_start']);
        $endDate = Carbon::parse($data['period_end']);
        
        $data['year'] = $startDate->year;
        
        // Determine if it's a quarter or month
        $daysDiff = $startDate->diffInDays($endDate);
        
        if ($daysDiff >= 85 && $daysDiff <= 95) {
            // Likely a quarter
            $data['quarter'] = ceil($startDate->month / 3);
            $data['month'] = null;
        } else {
            // Likely a month
            $data['month'] = $startDate->month;
            $data['quarter'] = null;
        }
        
        return $data;
    }
}


