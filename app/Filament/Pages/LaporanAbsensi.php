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

    protected function getTableHeaderFilters(): array
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

    public function exportData()
    {
        return Response::streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Nama Siswa', 'Tanggal', 'Jam Masuk', 'Jam Pulang']);

            $data = PresensiModel::with('student')->get();

            foreach ($data as $row) {
                fputcsv($handle, [
                    $row->student->name ?? 'Tidak Ada Nama',
                    $row->date,
                    $row->in,
                    $row->out
                ]);
            }

            fclose($handle);
        }, 'Laporan_Presensi_' . now()->format('Y-m-d') . '.csv');
    }
}


// namespace App\Filament\Pages;

// use Filament\Pages\Page;
// use Filament\Tables\Contracts\HasTable;
// use Filament\Tables\Concerns\InteractsWithTable;
// use App\Models\PresensiModel;
// use Filament\Tables\Columns\TextColumn;
// use Filament\Tables\Filters\Filter;
// use Filament\Forms\Components\DatePicker;
// use Filament\Tables\Actions\ExportAction;
// use Filament\Tables\Filters\SelectFilter;

// class LaporanAbsensi extends Page implements HasTable
// {
//     use InteractsWithTable;

//     protected static ?string $navigationGroup = 'Laporan';
//     protected static ?string $navigationIcon = 'heroicon-o-document-text';

//     protected static string $view = 'filament.pages.laporan-absensi';

//     protected function getTableQuery()
//     {
//         return PresensiModel::query()->with('student');
//     }

//     protected function getTableColumns(): array
//     {
//         return [
//             TextColumn::make('student.name')->label('Nama Siswa')->searchable(),
//             TextColumn::make('date')->label('Tanggal')->date(),
//             TextColumn::make('in')->label('Jam Masuk')->time(),
//             TextColumn::make('out')->label('Jam Pulang')->time(),
//         ];
//     }

//     protected function getTableFilters(): array
//     {
//         return [
//             SelectFilter::make('id_student')
//                 ->label('Nama Siswa')
//                 ->relationship('student', 'name')
//                 ->searchable(),
                
//             Filter::make('date')
//                 ->label('Tanggal')
//                 ->form([
//                     DatePicker::make('date'),
//                 ])
//                 ->query(fn ($query, $data) => $query->when($data['date'] ?? null, fn ($query, $date) => $query->whereDate('date', $date))),
//         ];
//     }

//     protected function getTableActions(): array
//     {
//         return [
//             ExportAction::make(), // Tambahkan tombol ekspor
//         ];
//     }
// }
