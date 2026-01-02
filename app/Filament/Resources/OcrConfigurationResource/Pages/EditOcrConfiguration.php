<?php

namespace App\Filament\Resources\OcrConfigurationResource\Pages;

use App\Filament\Resources\OcrConfigurationResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Crypt;

class EditOcrConfiguration extends EditRecord
{
    protected static string $resource = OcrConfigurationResource::class;
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Decrypt API keys for editing
        $apiKeys = $this->record->api_keys ?? [];
        
        if (isset($apiKeys['aws'])) {
            try {
                $data['aws_key'] = Crypt::decryptString($apiKeys['aws']['key'] ?? '');
                $data['aws_secret'] = Crypt::decryptString($apiKeys['aws']['secret'] ?? '');
            } catch (\Exception $e) {
                // Keys might not be set yet
            }
        }
        
        if (isset($apiKeys['google'])) {
            $data['google_credentials_path'] = $apiKeys['google']['credentials_path'] ?? '';
        }
        
        if (isset($apiKeys['azure'])) {
            $data['azure_endpoint'] = $apiKeys['azure']['endpoint'] ?? '';
            try {
                $data['azure_api_key'] = Crypt::decryptString($apiKeys['azure']['api_key'] ?? '');
            } catch (\Exception $e) {
                // Key might not be set yet
            }
        }
        
        return $data;
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
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

