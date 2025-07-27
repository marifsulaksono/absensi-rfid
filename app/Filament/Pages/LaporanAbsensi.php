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
        $filters = $this->tableFilters['date_range'] ?? [];

        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;

        return Excel::download(
            new PresensiExport($startDate, $endDate),
            'Laporan_Presensi_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

}
