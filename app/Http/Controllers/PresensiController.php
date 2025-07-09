<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Tools;
use App\Models\Student;
use App\Models\RfidModel;
use Illuminate\Http\Request;
use App\Models\PresensiModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PresensiController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        $absensi = PresensiModel::with('student')
            ->whereDate('date', $today)
            ->orderBy('in')
            ->get();

        return view('presensi.index', compact('absensi'));
    }

    // Method untuk menampilkan data absensi terbaru
    public function getDailyAttandance(Request $request)
    {
        $latestPresence = PresensiModel::with('student')
            ->whereDate('date', Carbon::today())
            ->where('is_displayed', false)
            ->orderBy('updated_at', 'asc')
            ->first();

        // Trigger jika tidak ada data baru
        if (!$latestPresence) {
            return response()->json(['new_presence' => false]);
        }

        if (!is_null($latestPresence->in) && is_null($latestPresence->out)) {
            $status = 'in'; // Jika baru scan masuk (out masih kosong), berarti masuk
        } else {
            $status = 'out'; // Jika sudah ada out atau keduanya ada, berarti keluar
        }

        // Menentukan pesan berdasarkan status
        $message = ($status === 'in') ? 'Selamat datang!' : 'Terima kasih, sampai jumpa!';

        // Update status `is_displayed` menjadi true agar tidak muncul lagi
        $latestPresence->update(['is_displayed' => true]);

        return response()->json([
            'new_presence' => true,
            'student_name' => $latestPresence->student->name,
            'student_class' => $latestPresence->student->class->name,
            'student_address' => $latestPresence->student->address,
            'student_nis' => $latestPresence->student->nis,
            'student_photo' => $latestPresence->student->photo,
            'status' => $status,
            'message' => $message,
        ]);
    }

    public function store(Request $request)
    {
        $apikey = env('API_KEY');

        // Validasi request
        $validator = Validator::make($request->all(), [
            'number' => 'required|string',
            'tool_code' => 'required|string'
        ]);

        // Validasi API Key
        $apikeyHeaderValue = $request->header('x-api-key');
        if ($apikeyHeaderValue != $apikey) {
            return response()->json(['error' => 'Invalid API key']);
        }

        $tool = Tools::where('code', $request->tool_code)->first();
        if (!$tool) {
            return response()->json(['error' => 'Alat tidak ditemukan'], 404);
        } else if ($tool->status == 0) {
            return response()->json(['error' => 'Alat tidak aktif'], 400);
        } else if ($tool->status == 1) {
            return response()->json(['error' => 'Alat digunakan untuk scan kartu baru'], 400);
        }

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Cari siswa berdasarkan nomor RFID
        $student = Student::where('rfid_number', $request->number)
            ->where('is_active', 1)
            ->first();
        if (!$student) {
            return response()->json(['error' => 'Siswa tidak ditemukan atau tidak aktif'], 404);
        }

        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');

        // Cek apakah siswa sudah absen hari ini
        $present = PresensiModel::where('id_student', $student->id)
            ->where('date', $today)
            ->first();

        // Jam batas masuk: 05:00 - 08:00
        $startIn = Carbon::createFromTime(5, 0, 0);
        $endIn = Carbon::createFromTime(8, 0, 0);

        // Jam batas keluar: di atas 09:00
        $minOut = Carbon::createFromTime(9, 0, 0);

        if (!$present) {
            // Absensi masuk
            // if ($now->between($startIn, $endIn)) {
                // Jika dalam range jam masuk, insert
                PresensiModel::create([
                    'id_student' => $student->id,
                    'date' => $today,
                    'in' => $currentTime,
                    'out' => null,
                    'is_displayed' => false,
                    'tool_id' => $tool->id
                ]);
            // } else {
            //     return response()->json(['error' => 'Waktu absensi masuk hanya antara jam 05:00 - 08:00'], 400);
            // }
        } else if ($present->out === null) {
            // Absensi keluar
            if ($now->gt($minOut)) {
                $present->update([
                    'out' => $currentTime,
                    'is_displayed' => false
                ]);
            } else {
                return response()->json(['error' => 'Waktu absensi pulang hanya setelah jam 09:00'], 400);
            }
        } else {
            return response()->json(['200' => 'Kamu sudah melakukan absensi hari ini'], 200);
        }

        return response()->json([
            'message' => 'Absensi berhasil dicatat',
            'data' => [
                'id_student' => $student->id,
                'date' => $today,
                'in' => $present ? $present->in : $currentTime,
                'out' => $present ? $currentTime : null,
                'is_displayed' => false
            ]
        ], 201);
    }

}
