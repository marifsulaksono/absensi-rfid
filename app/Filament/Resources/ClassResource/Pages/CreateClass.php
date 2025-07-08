<?php

namespace App\Filament\Resources\ClassResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use App\Filament\Resources\ClassResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClass extends CreateRecord
{
    protected static string $resource = ClassResource::class;

    protected function getRedirectUrl(): string
    {
        Notification::make()
        ->title('Berhasil!')
        ->body('Data kelas berhasil ditambahkan')
        ->success()
        ->send();

        return url('/admin/classes');
    }
}
