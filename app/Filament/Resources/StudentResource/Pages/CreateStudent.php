<?php

namespace App\Filament\Resources\StudentResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\StudentResource;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function getRedirectUrl(): string
    {
        Notification::make()
        ->title('Berhasil!')
        ->body('Data siswa berhasil ditambahkan')
        ->success()
        ->send();

        return url('/admin/students');
    }
}
