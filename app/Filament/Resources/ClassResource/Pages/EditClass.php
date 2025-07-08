<?php

namespace App\Filament\Resources\ClassResource\Pages;

use Filament\Actions;
use Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ClassResource;

class EditClass extends EditRecord
{
    protected static string $resource = ClassResource::class;

    protected function afterSave(): void
    {
        $this->successNotificationMessage = null;

        Notification::make()
            ->title('Berhasil!')
            ->body('Data kelas berhasil diperbarui')
            ->success()
            ->send();
    }
    
    protected function getRedirectUrl(): string
    {
        return url('/admin/classes');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
