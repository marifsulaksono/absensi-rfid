<?php

namespace App\Filament\Resources\ToolResource\Pages;

use Filament\Actions;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\ToolResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTool extends EditRecord
{
    protected static string $resource = ToolResource::class;

    protected function afterSave(): void
    {
        $this->successNotificationMessage = null;

        Notification::make()
            ->title('Berhasil!')
            ->body('Data alat berhasil diperbarui')
            ->success()
            ->send();
    }
    
    protected function getRedirectUrl(): string
    {
        return url('/admin/tools');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
