<?php

namespace App\Filament\Resources\ToolResource\Pages;

use Filament\Actions;
use App\Filament\Resources\ToolResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTool extends CreateRecord
{
    protected static string $resource = ToolResource::class;

    protected function getRedirectUrl(): string
    {
        Notification::make()
        ->title('Berhasil!')
        ->body('Data alat berhasil ditambahkan')
        ->success()
        ->send();

        return url('/admin/tools');
    }
}
