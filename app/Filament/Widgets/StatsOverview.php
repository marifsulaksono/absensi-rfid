<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use App\Models\PresensiModel;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $today = Carbon::now()->format('Y-m-d');
        
        // Get total students
        $totalStudents = Student::count();
        
        // Get students who have attended today
        $attendedToday = PresensiModel::whereDate('created_at', $today)->distinct('id_student')->count();
        
        // Calculate not attended
        $notAttendedToday = $totalStudents - $attendedToday;

        return [
            Stat::make('Total Siswa', $totalStudents)
                ->description('Jumlah seluruh siswa')
                ->color('success')
                ->icon('heroicon-o-users'),
            
            Stat::make('Sudah Absen', $attendedToday)
                ->description('Siswa yang sudah absen hari ini')
                ->color('success')
                ->icon('heroicon-o-check-circle'),
            
            Stat::make('Belum Absen', $notAttendedToday)
                ->description('Siswa yang belum absen hari ini')
                ->color('danger')
                ->icon('heroicon-o-x-circle'),
        ];
    }
}