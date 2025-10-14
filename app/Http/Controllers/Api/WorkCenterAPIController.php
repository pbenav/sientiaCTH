<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WorkCenterAPIController extends Controller
{
    public function validateCode(Request $request, \App\Services\EncryptionService $encryptionService)
    {
        $request->validate([
            'encrypted_code' => 'required',
        ]);

        $decryptedCode = $encryptionService->decrypt($request->encrypted_code);

        $workCenter = \App\Models\WorkCenter::where('code', $decryptedCode)->first();

        if (!$workCenter) {
            return response()->json(['message' => 'Work center not found'], 404);
        }

        return response()->json($workCenter);
    }

    public function index()
    {
        $workCenters = \App\Models\WorkCenter::all();

        return response()->json($workCenters);
    }
}
