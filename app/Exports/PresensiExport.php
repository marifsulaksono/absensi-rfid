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
    protected $classId;
    protected $studentId;

    public function __construct(?string $startDate, ?string $endDate, $classId = null, $studentId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->classId = $classId;
        $this->studentId = $studentId;
    }

    public function collection(): Collection
    {
        $query = PresensiModel::with(['student.class'])
            ->when($this->startDate, fn($q) => $q->whereDate('date', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->whereDate('date', '<=', $this->endDate))
            ->when($this->classId, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('class_id', $this->classId)))
            ->when($this->studentId, fn($q) => $q->where('id_student', $this->studentId));

        $data = $query->get();

        \Log::info('[PresensiExport] Jumlah data hasil query: ' . $data->count());
        \Log::info('[PresensiExport] Data mentah:', $data->toArray());

        return $data->map(function ($row) {
            return [
                'Nama Siswa' => $row->student->name ?? 'Tidak Ada Nama',
                'Kelas' => $row->student->class->name ?? '-',
                'Tanggal' => $row->date,
                'Jam Masuk' => $row->in,
                'Jam Pulang' => $row->out,
            ];
        });
    }

    public function headings(): array
    {
        return ['Nama Siswa', 'Kelas', 'Tanggal', 'Jam Masuk', 'Jam Pulang'];
    }
}
