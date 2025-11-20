<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Handles API requests for user profiles.
 *
 * This controller is responsible for retrieving and updating user profile
 * data.
 */
class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $user->load('events.workCenter');
        $schedule = $user->meta->where('meta_key', 'schedule')->first();
        if ($schedule) {
            $user->schedule = json_decode($schedule->meta_value);
        }
        $user->last_5_events = $user->events->sortByDesc('start')->take(5);

        return response()->json($user);
    }

    /**
     * Update user profile from mobile app.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_code' => 'required|string',
                'name' => 'required|string|max:255',
                'family_name1' => 'nullable|string|max:255',
                'family_name2' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::where('user_code', $request->user_code)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Update user fields
            $user->name = $request->name;
            $user->family_name1 = $request->family_name1;
            $user->family_name2 = $request->family_name2;
            $user->save();

            Log::info("Profile updated for user {$user->user_code} from mobile app");

            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado correctamente',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'family_name1' => $user->family_name1,
                    'family_name2' => $user->family_name2,
                    'user_code' => $user->user_code
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Mobile profile update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el perfil'
            ], 500);
        }
    }
}
