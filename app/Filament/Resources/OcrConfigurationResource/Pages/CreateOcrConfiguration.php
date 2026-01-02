<?php

namespace App\Filament\Resources\OcrConfigurationResource\Pages;

use App\Filament\Resources\OcrConfigurationResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Crypt;

class CreateOcrConfiguration extends CreateRecord
{
    protected static string $resource = OcrConfigurationResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Encrypt API keys before saving
        $apiKeys = [];
        
        if (!empty($data['aws_key']) && !empty($data['aws_secret'])) {
            $apiKeys['aws'] = [
                'key' => Crypt::encryptString($data['aws_key']),
                'secret' => Crypt::encryptString($data['aws_secret']),
            ];
        }
        
        if (!empty($data['google_credentials_path'])) {
            $apiKeys['google'] = [
                'credentials_path' => $data['google_credentials_path'],
            ];
        }
        
        if (!empty($data['azure_endpoint']) && !empty($data['azure_api_key'])) {
            $apiKeys['azure'] = [
                'endpoint' => $data['azure_endpoint'],
                'api_key' => Crypt::encryptString($data['azure_api_key']),
            ];
        }
        
        $data['api_keys'] = $apiKeys;
        
        // Remove unencrypted keys from data
        unset($data['aws_key'], $data['aws_secret'], $data['google_credentials_path'], 
              $data['azure_endpoint'], $data['azure_api_key']);
        
        return $data;
    }
}

