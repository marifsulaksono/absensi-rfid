<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Tools;
use App\Models\TempRfid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TempRfidController extends Controller
{
    public function getAll()
    {
        $today = Carbon::today();
        
        $absensi = Tools::with('tool')
            ->whereDate('date', $today)
            ->orderBy('in')
            ->get();

        return view('presensi.index', compact('absensi'));
    }

    public function store(Request $request)
    {
        Log::info('Received Register RFID request:', $request->all()); 
        $apikey = env('API_KEY');

        // Validasi request
        $validator = Validator::make($request->all(), [
            'tool_code' => 'required|string',
            'number' => 'required|string'
        ]);

        // validation API Key
        $apikeyHeaderValue = $request->header('x-api-key');
        if ($apikeyHeaderValue != $apikey) {
            return response()->json([
                'error' => 'Invalid API key'
            ]);
        }

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $today = Carbon::now()->format('Y-m-d H:i:s');
        $tool = Tools::where('code', $request->tool_code)->first();
        if (!$tool) {
            return response()->json(['error' => 'Alat tidak ditemukan'], 404);
        } else if ($tool->status == 0) {
            return response()->json(['error' => 'Alat tidak aktif'], 400);
        } else if ($tool->status == 2) {
            return response()->json(['error' => 'Alat digunakan untuk absensi'], 400);
        }

        TempRfid::create([
            'tool_id' => $tool->id,
            'number' => $request->number,
        ]);

        return response()->json([
            'message' => 'Rfid berhasil dicatat',
            'data' => [
                'number' => $request->number
            ]
        ], 201);
    }
}
