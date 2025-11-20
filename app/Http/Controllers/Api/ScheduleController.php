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

            // Check if schedule is in the old format (List of slots) or new format (Map of days)
            // The system currently uses List of slots for SmartClockInService, so we likely need to convert.
            if ($schedule && isset($schedule[0]) && is_array($schedule[0])) {
                // It's a list of slots, convert to Map of days for mobile app
                $schedule = $this->convertScheduleListToMap($schedule);
            } elseif (!$schedule) {
                $schedule = [
                    'monday' => [],
                    'tuesday' => [],
                    'wednesday' => [],
                    'thursday' => [],
                    'friday' => [],
                    'saturday' => [],
                    'sunday' => []
                ];
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
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            $validSchedule = [];

            foreach ($days as $day) {
                $validSchedule[$day] = $request->input("schedule.$day", []);
                // Ensure it's an array of strings like "HH:MM-HH:MM"
                if (!is_array($validSchedule[$day])) {
                    $validSchedule[$day] = [];
                }
            }

            // Convert the Map of days back to List of slots for storage/compatibility
            $scheduleList = $this->convertScheduleMapToList($validSchedule);

            // Update or create user meta
            $user->meta()->updateOrCreate(
                ['meta_key' => 'work_schedule'],
                ['meta_value' => json_encode($scheduleList)]
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
    /**
     * Convert DB format (List of slots) to Mobile format (Map of days)
     */
    private function convertScheduleListToMap(array $scheduleList): array
    {
        $map = [
            'monday' => [], 'tuesday' => [], 'wednesday' => [], 'thursday' => [],
            'friday' => [], 'saturday' => [], 'sunday' => []
        ];

        $dayMapping = [
            '1' => 'monday', 'L' => 'monday',
            '2' => 'tuesday', 'M' => 'tuesday',
            '3' => 'wednesday', 'X' => 'wednesday',
            '4' => 'thursday', 'J' => 'thursday',
            '5' => 'friday', 'V' => 'friday',
            '6' => 'saturday', 'S' => 'saturday',
            '7' => 'sunday', 'D' => 'sunday',
        ];

        foreach ($scheduleList as $slot) {
            $start = $slot['start'] ?? '';
            $end = $slot['end'] ?? '';
            $days = $slot['days'] ?? [];

            if (!$start || !$end) continue;

            $timeRange = "$start-$end";

            foreach ($days as $day) {
                $dayKey = $dayMapping[(string)$day] ?? null;
                if ($dayKey) {
                    $map[$dayKey][] = $timeRange;
                }
            }
        }

        // Remove duplicates
        foreach ($map as $day => $ranges) {
            $map[$day] = array_unique($ranges);
        }

        return $map;
    }

    /**
     * Convert Mobile format (Map of days) to DB format (List of slots)
     */
    private function convertScheduleMapToList(array $scheduleMap): array
    {
        $list = [];
        $dayMapping = [
            'monday' => '1',
            'tuesday' => '2',
            'wednesday' => '3',
            'thursday' => '4',
            'friday' => '5',
            'saturday' => '6',
            'sunday' => '7',
        ];

        foreach ($scheduleMap as $dayName => $timeRanges) {
            $isoDay = $dayMapping[$dayName] ?? null;
            if (!$isoDay) continue;

            foreach ($timeRanges as $range) {
                $parts = explode('-', $range);
                if (count($parts) !== 2) continue;

                $list[] = [
                    'days' => [$isoDay],
                    'start' => $parts[0],
                    'end' => $parts[1]
                ];
            }
        }

        // Optimization: Merge identical slots could be done here, but simple list is valid.
        return $list;
    }
}
