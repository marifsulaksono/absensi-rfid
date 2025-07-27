<?php

namespace App\Exports;

use App\Models\PresensiModel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class PresensiExport implements FromCollection, WithHeadings
{
    protected ?string $startDate;
    protected ?string $endDate;

    public function __construct(?string $startDate, ?string $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection(): Collection
    {
        return PresensiModel::with('student')
            ->when($this->startDate, fn($q) => $q->whereDate('date', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->whereDate('date', '<=', $this->endDate))
            ->get()
            ->map(function ($row) {
                return [
                    'Nama Siswa' => $row->student->name ?? 'Tidak Ada Nama',
                    'Tanggal' => $row->date,
                    'Jam Masuk' => $row->in,
                    'Jam Pulang' => $row->out,
                ];
            });
    }

    public function headings(): array
    {
        return ['Nama Siswa', 'Tanggal', 'Jam Masuk', 'Jam Pulang'];
    }
}
