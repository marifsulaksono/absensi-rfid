<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use App\Models\PresensiModel;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Exports\PresensiExport;
use Maatwebsite\Excel\Facades\Excel;

class LaporanAbsensi extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.laporan-absensi';

    protected static ?int $navigationSort = 2;
    
    protected function getTableQuery()
    {
        return PresensiModel::query()->with('student');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('student.name')->label('Nama Siswa')->searchable(),
            TextColumn::make('student.class.name')->label('Kelas')->searchable(),
            TextColumn::make('date')->label('Tanggal')->date(),
            TextColumn::make('in')->label('Jam Masuk')->time(),
            TextColumn::make('out')->label('Jam Pulang')->time(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Filter::make('date_range')
                ->form([
                    DatePicker::make('start_date')->label('Dari Tanggal'),
                    DatePicker::make('end_date')->label('Sampai Tanggal'),
                ])
                ->query(function (Builder $query, array $data) {
                    return $query
                        ->when($data['start_date'] ?? null, fn ($q, $start) => $q->whereDate('date', '>=', $start))
                        ->when($data['end_date'] ?? null, fn ($q, $end) => $q->whereDate('date', '<=', $end));
                }),
            Filter::make('class_id')
                ->form([
                    \Filament\Forms\Components\Select::make('class_id')
                        ->label('Kelas')
                        ->options(fn () => \App\Models\ClassModel::pluck('name', 'id')->toArray())
                        ->searchable()
                ])
                ->query(function (Builder $query, array $data) {
                    if (!empty($data['class_id'])) {
                        $query->whereHas('student', fn ($q) => $q->where('class_id', $data['class_id']));
                    }
                    return $query;
                }),
            Filter::make('student_id')
                ->form([
                    \Filament\Forms\Components\Select::make('student_id')
                        ->label('Siswa')
                        ->options(fn () => \App\Models\Student::pluck('name', 'id')->toArray())
                        ->searchable()
                ])
                ->query(function (Builder $query, array $data) {
                    if (!empty($data['student_id'])) {
                        $query->where('id_student', $data['student_id']);
                    }
                    return $query;
                }),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export Data')
                ->icon('heroicon-m-arrow-down-tray')
                ->action(fn () => $this->exportData())
                ->color('primary'),
        ];
    }

    public function exportData()
    {
        $filters = $this->tableFilters ?? [];
        $query = $this->getTableQuery();

        // Terapkan filter manual
        if (!empty($filters['date_range']['start_date'])) {
            $query->whereDate('date', '>=', $filters['date_range']['start_date']);
        }
        if (!empty($filters['date_range']['end_date'])) {
            $query->whereDate('date', '<=', $filters['date_range']['end_date']);
        }
        if (!empty($filters['class_id']['class_id'])) {
            $query->whereHas('student', function ($q) use ($filters) {
                $q->where('class_id', $filters['class_id']['class_id']);
            });
        }
        if (!empty($filters['student_id']['student_id'])) {
            $query->where('id_student', $filters['student_id']['student_id']);
        }

        $data = $query->get();

        // Mapping data untuk export
        $exportData = $data->map(function ($row) {
            return [
                'Nama Siswa' => $row->student->name ?? 'Tidak Ada Nama',
                'Kelas' => $row->student->class->name ?? '-',
                'Tanggal' => $row->date,
                'Jam Masuk' => $row->in,
                'Jam Pulang' => $row->out,
            ];
        });

        // Buat export custom
        $export = new class($exportData) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            protected $data;
            public function __construct($data) { $this->data = $data; }
            public function collection() { return $this->data; }
            public function headings(): array {
                return ['Nama Siswa', 'Kelas', 'Tanggal', 'Jam Masuk', 'Jam Pulang'];
            }
        };

        return Excel::download(
            $export,
            'Laporan_Presensi_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

}
