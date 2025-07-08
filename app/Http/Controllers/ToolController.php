<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Tools;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ToolController extends Controller
{
    public function getByCode(Request $request, String $code)
    {
        Log::info('Received get code request', $request->all()); // Pass the request data as context
        Log::info('Tool code:', ['code' => $code]); // Pass tool code as an array
        // validation API Key
        $apikey = env('API_KEY');
        $apikeyHeaderValue = $request->header('x-api-key');
        if ($apikeyHeaderValue != $apikey) {
            return response()->json([
                'error' => 'Invalid API key'
            ]);
        }

        $tool = Tools::where('code', $code)
            ->first();

        if (!$tool) {
            return response()->json([
                'message' => 'Tool tidak ditemukan',
                'data' => null
            ], 404);
        }

        return response()->json([
            'message' => 'Tool berhasil didapatkan',
            'data' => $tool
        ], 200);
    }

}
