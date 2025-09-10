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

    // public function exportData()
    // {
    //     return Response::streamDownload(function () {
    //         $handle = fopen('php://output', 'w');
    //         fputcsv($handle, ['Nama Siswa', 'Tanggal', 'Jam Masuk', 'Jam Pulang']);

    //         $data = PresensiModel::with('student')->get();

    //         foreach ($data as $row) {
    //             fputcsv($handle, [
    //                 $row->student->name ?? 'Tidak Ada Nama',
    //                 $row->date,
    //                 $row->in,
    //                 $row->out
    //             ]);
    //         }

    //         fclose($handle);
    //     }, 'Laporan_Presensi_' . now()->format('Y-m-d') . '.csv');
    // }

    public function exportData()
    {
        $dateFilters = $this->tableFilters['date_range'] ?? [];
        $classId = $this->tableFilters['class_id'] ?? null;
        $studentId = $this->tableFilters['student_id'] ?? null;

        $startDate = $dateFilters['start_date'] ?? null;
        $endDate = $dateFilters['end_date'] ?? null;

        return Excel::download(
            new PresensiExport($startDate, $endDate, $classId, $studentId),
            'Laporan_Presensi_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

}
