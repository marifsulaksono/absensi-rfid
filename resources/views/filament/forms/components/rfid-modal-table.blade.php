@php
    use App\Models\TempRfid;
    $rfids = \App\Models\TempRfid::with('tool')->orderByDesc('scanned_at')->get();
@endphp

<table class="w-full text-sm">
    <thead class="font-semibold text-gray-700">
        <tr>
            <th class="px-3 py-2 border">Nomor RFID</th>
            <th class="px-3 py-2 border">Nama Alat</th>
            <th class="px-3 py-2 border">Tanggal Scan</th>
            <th class="px-3 py-2 border">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rfids as $rfid)
            <tr class="border-t hover:bg-gray-100">
                <td class="px-3 py-2 border">{{ $rfid->number }}</td>
                <td class="px-3 py-2 border">{{ $rfid->tool->name ?? '-' }}</td>
                <td class="px-3 py-2 border">{{ $rfid->scanned_at ? \Carbon\Carbon::parse($rfid->scanned_at)->format('d M Y H:i') : '-' }}</td>
                <td class="px-3 py-2 border">
                  <button
                     type="button"
                     x-data
                     @click="$wire.set('data.rfid_number', '{{ $rfid->number }}'); $dispatch('close-modal')"
                     class="text-blue-600 hover:underline"
                  >
                     Pilih
                  </button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
