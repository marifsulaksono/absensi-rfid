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
            'student' => $latestPresence->student->name,
            'status' => $status,
            'message' => $message,
        ]);
    }

    public function store(Request $request)
    {
        Log::info('Received RFID Present request:', $request->all());
        $apikey = env('API_KEY');

        // Validasi request
        $validator = Validator::make($request->all(), [
            'number' => 'required|string',
            'tool_code' => 'required|string'
        ]);

        // validation API Key
        $apikeyHeaderValue = $request->header('x-api-key');
        if ($apikeyHeaderValue != $apikey) {
            return response()->json([
                'error' => 'Invalid API key'
            ]);
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

        // Cari RFID di database
        $student = Student::where('rfid_number', $request->number)
                ->where('is_active', 1)
                ->first();
        if (!$student) {
            return response()->json(['error' => 'Siswa tidak ditemukan atau tidak aktif'], 404);
        }

        $today = Carbon::now()->format('Y-m-d');
        $currentTime = Carbon::now()->format('H:i:s');

        // Cek apakah sudah ada data presents untuk id_student di hari ini
        $present = PresensiModel::where('id_student', $student->id)->where('date', $today)->first();

        if (!$present) {
            // Jika belum ada, insert data baru
            PresensiModel::create([
                'id_student' => $student->id,
                'date' => $today,
                'in' => $currentTime,
                'out' => null,
                'is_displayed' => false,
                'tool_id' => $tool->id
            ]);
        } else if ($present->out === null) {
            // Jika sudah ada dan kolom 'out' masih kosong, update kolom 'out' dengan waktu sekarang
            $present->update([
                'out' => $currentTime,
                'is_displayed' => false
            ]);
        } else {
            // Jika sudah ada dan kolom 'out' sudah terisi, kirim pesan error
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
