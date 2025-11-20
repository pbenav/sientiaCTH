<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    /**
     * Get user's work schedule
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'user_code' => 'required|string'
        ]);

        // Find user by user_code
        $user = User::where('user_code', $request->user_code)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        try {
            $scheduleMeta = $user->meta->where('meta_key', 'work_schedule')->first();
            $schedule = $scheduleMeta && $scheduleMeta->meta_value 
                ? json_decode($scheduleMeta->meta_value, true) 
                : null;

            // Return raw schedule data (List of slots)
            // The mobile app will now handle this format directly.
            if (!$schedule) {
                $schedule = [];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'schedule' => $schedule,
                    'timezone' => $user->currentTeam->timezone ?? config('app.timezone')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching schedule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el horario'
            ], 500);
        }
    }

    /**
     * Update user's work schedule
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'user_code' => 'required|string',
            'schedule' => 'required|array'
        ]);

        // Find user by user_code
        $user = User::where('user_code', $request->user_code)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        try {
            // Validate schedule structure (basic validation)
            $validSchedule = $request->input('schedule', []);
            if (!is_array($validSchedule)) {
                $validSchedule = [];
            }

            // Update or create user meta
            $user->meta()->updateOrCreate(
                ['meta_key' => 'work_schedule'],
                ['meta_value' => json_encode($validSchedule)]
            );

            return response()->json([
                'success' => true,
                'message' => 'Horario actualizado correctamente',
                'data' => [
                    'schedule' => $validSchedule
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating schedule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el horario'
            ], 500);
        }
    }
}
