<x-filament-panels::page>
    <div class="max-w-md mx-auto">
        <h2 class="text-xl font-bold mb-4">Ganti Password</h2>
        <form wire:submit.prevent="save" class="space-y-4">
            {{ $this->form }}

            <x-filament::button type="submit">
                Simpan Password Baru
            </x-filament::button>

            @if (session()->has('success'))
                <div class="text-green-600">{{ session('success') }}</div>
            @endif
        </form>
    </div>
</x-filament-panels::page>
