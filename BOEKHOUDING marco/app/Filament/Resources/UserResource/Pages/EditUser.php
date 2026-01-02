<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation(),
        ];
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Extract roles from form data and handle separately
        $roles = $data['roles'] ?? [];
        unset($data['roles']);
        
        return $data;
    }
    
    protected function afterSave(): void
    {
        // Sync roles after user is saved
        $roles = $this->form->getState()['roles'] ?? [];
        $this->record->syncRoles($roles);
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Gebruiker bijgewerkt')
            ->body('De gebruiker is succesvol bijgewerkt.');
    }
}

