<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Pages\Page;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Setting extends Page
{
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $navigationIcon = 'heroicon-m-key';
    protected static ?string $navigationLabel = 'Ganti Password';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.setting';

    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('current_password')
                ->password()
                ->required()
                ->label('Password Lama'),
            TextInput::make('new_password')
                ->password()
                ->required()
                ->minLength(8)
                ->label('Password Baru'),
            TextInput::make('new_password_confirmation')
                ->password()
                ->required()
                ->same('new_password')
                ->label('Konfirmasi Password'),
        ];
    }

    public function save()
    {
        $user = Auth::user();

        // Validasi password lama
        if (!Hash::check($this->current_password, $user->password)) {
            throw ValidationException::withMessages(['current_password' => 'Password lama salah!']);
        }

        // Simpan password baru
        $user->update(['password' => Hash::make($this->new_password)]);

        session()->flash('success', 'Password berhasil diubah!');
    }
}
