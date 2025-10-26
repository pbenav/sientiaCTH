<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;

/**
 * Handles API requests for work centers.
 *
 * This controller is responsible for validating work center codes and listing
 * work centers.
 */
class WorkCenterAPIController extends Controller
{
    /**
     * Validate a work center code.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\EncryptionService $encryptionService
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateCode(Request $request, \App\Services\EncryptionService $encryptionService)
    {
        $request->validate([
            'encrypted_code' => 'required',
        ]);

        try {
            $decryptedCode = $encryptionService->decrypt($request->encrypted_code);
        } catch (DecryptException $e) {
            return response()->json(['message' => 'Invalid code format'], 422);
        }

        $workCenter = auth()->user()->currentTeam->workCenters()->where('code', $decryptedCode)->first();

        if (!$workCenter) {
            return response()->json(['message' => 'Work center not found'], 404);
        }

        return response()->json($workCenter);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $workCenters = auth()->user()->currentTeam->workCenters;

        return response()->json($workCenters);
    }
}
