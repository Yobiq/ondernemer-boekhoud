<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extract roles from form data and handle separately
        $roles = $data['roles'] ?? [];
        unset($data['roles']);
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        // Assign roles after user is created
        $roles = $this->form->getState()['roles'] ?? [];
        if (!empty($roles)) {
            $this->record->syncRoles($roles);
        }
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Gebruiker aangemaakt')
            ->body('De gebruiker is succesvol aangemaakt.');
    }
}

