<?php

namespace App\Filament\Resources\StudentResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\StudentResource;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function afterSave(): void
    {
        $this->successNotificationMessage = null;

        Notification::make()
            ->title('Berhasil!')
            ->body('Data siswa berhasil diperbarui')
            ->success()
            ->send();
    }
    
    protected function getRedirectUrl(): string
    {
        return url('/admin/students');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
