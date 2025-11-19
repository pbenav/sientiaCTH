<?php
namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkCenter;
use App\Services\SmartClockInService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class MobileClockController extends Controller
{
    protected SmartClockInService $smartClockInService;

    public function __construct(SmartClockInService $smartClockInService)
    {
        $this->smartClockInService = $smartClockInService;
    }

    /**
     * Handle mobile clock in/out request
     * Accepts user_code and optional work_center_code. If work_center_code is missing
     * the controller will infer the work center from the user's current team.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clock(Request $request): JsonResponse
    {
        try {
            Log::debug('[MobileClockController][clock] Request body:', $request->all());
            // Validate request
            $validator = Validator::make($request->all(), [
                'work_center_code' => 'sometimes|string|max:50',
                'manual_work_center_code' => 'sometimes|string|max:50',
                'user_code' => 'required|string|max:50',
                'action' => 'sometimes|string|in:pause,clock_out,confirm_exceptional_clock_in,exceptional_clock_in',
                'location' => 'sometimes|array',
                'location.latitude' => 'sometimes|numeric|between:-90,90',
                'location.longitude' => 'sometimes|numeric|between:-180,180',
            ]);

            if ($validator->fails()) {
                Log::warning('[MobileClockController][clock] Validation failed', [
                    'input' => $request->all(),
                    'errors' => $validator->errors()
                ]);
                return response()->json([
                    'success' => false,
                    'error' => 'validation_error',
                    'message' => 'Invalid request data',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Normalize work center code
            $workCenterCode = $request->work_center_code ?? $request->manual_work_center_code ?? null;

            // If work center code not provided, try to infer from user->currentTeam
            $user = User::where('user_code', $request->user_code)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'invalid_credentials',
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            if (!$workCenterCode) {
                $team = $user->currentTeam;
                if (!$team) {
                    return response()->json([
                        'success' => false,
                        'error' => 'no_team',
                        'message' => 'User has no current team to infer work center'
                    ], 400);
                }

                $workCenter = $team->workCenters()->first();
                if (!$workCenter) {
                    return response()->json([
                        'success' => false,
                        'error' => 'no_work_center',
                        'message' => 'No work centers configured for user team'
                    ], 404);
                }
            } else {
                $workCenter = WorkCenter::where('code', $workCenterCode)->first();
                if (!$workCenter) {
                    return response()->json([
                        'success' => false,
                        'error' => 'invalid_work_center',
                        'message' => 'Work center not found'
                    ], 404);
                }

                // ensure user belongs to the team owning the work center
                $user = User::where('user_code', $request->user_code)
                    ->whereHas('teams', function ($query) use ($workCenter) {
                        $query->where('teams.id', $workCenter->team_id);
                    })->first();

                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'error' => 'invalid_credentials',
                        'message' => 'Invalid user credentials or unauthorized for this work center'
                    ], 401);
                }
            }

            // Set user's current team to the work center's team
            if ($workCenter->team) {
                $user->switchTeam($workCenter->team);
            }

            // Get current clock status and determine action
            $clockAction = $this->smartClockInService->getClockAction($user);

            if (empty($clockAction['can_clock']) || !$clockAction['can_clock']) {
                // For mobile API, automatically allow exceptional clock-ins
                if (($clockAction['action'] ?? null) === 'confirm_exceptional_clock_in') {
                    $teamTimezone = $user->currentTeam->timezone ?? config('app.timezone');
                    $now = Carbon::now($teamTimezone);
                    $isWithinSchedule = $this->smartClockInService->isUserWithinWorkSchedule($user, $now);

                    // Override to allow clock-in - only mark as exceptional if truly outside schedule
                    $clockAction = [
                        'can_clock' => true,
                        'action' => 'clock_in',
                        'event_type_id' => $clockAction['event_type_id'] ?? null,
                        'overtime' => !$isWithinSchedule,
                        'message' => $isWithinSchedule ? 'Clock-in allowed' : 'Exceptional clock-in allowed'
                    ];
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => 'cannot_clock',
                        'message' => $clockAction['message'] ?? 'Cannot clock at this time'
                    ], 400);
                }
            }

            // Execute the clock action
            $result = $this->executeClockAction($user, $clockAction, $workCenter, $request);

            // Get updated user status
            $currentStatus = $this->getCurrentUserStatus($user);

            // Get today's records
            $todayRecords = $this->getTodayRecords($user);

            // Get work schedule
            $workSchedule = $this->getUserWorkSchedule($user);

            // Calcular horas trabajadas sumando las diferencias entre start y end de los eventos del día
            $workedSeconds = 0;
            if ($todayRecords) {
                foreach ($todayRecords as $record) {
                    if (isset($record['start'], $record['end']) && $record['end']) {
                        $start = Carbon::parse($record['start']);
                        $end = Carbon::parse($record['end']);
                        $workedSeconds += $end->diffInSeconds($start);
                    }
                }
            }
            $workedHours = $workedSeconds > 0 ? sprintf('%d:%02d', intdiv($workedSeconds, 3600), (intdiv($workedSeconds, 60) % 60)) : '0:00';

            Log::debug('MOBILE_CLOCK: todayRecords', ['todayRecords' => $todayRecords]);
            Log::debug('MOBILE_CLOCK: workedSeconds', ['workedSeconds' => $workedSeconds]);
            Log::debug('MOBILE_CLOCK: workedHours', ['workedHours' => $workedHours]);

            // Build a compatible data payload for mobile clients.
            $dataPayload = [
                'action' => $result['action'] ?? null,
                'timestamp' => Carbon::now($user->currentTeam->timezone ?? config('app.timezone'))->toISOString(),
                'work_center_code' => $workCenter->code,
                'user_code' => $user->user_code,
                'message' => $result['message'] ?? null,
                // Provide today_stats.current_status to help mobile UI interpret the state
                'today_stats' => [
                    'total_entries' => $todayRecords ? count(array_filter($todayRecords, fn($r) => isset($r['start']))) : 0,
                    'total_exits' => $todayRecords ? count(array_filter($todayRecords, fn($r) => isset($r['end']))) : 0,
                    'worked_hours' => $workedHours,
                    'current_status' => $this->getCurrentStatusText($clockAction['action'] ?? '')
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? null,
                'data' => $dataPayload,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'family_name1' => $user->family_name1,
                    'family_name2' => $user->family_name2,
                    'current_status' => $currentStatus,
                    'work_center' => [
                        'id' => $workCenter->id,
                        'name' => $workCenter->name,
                        'work_center_code' => $workCenter->code
                    ]
                ],
                'work_schedule' => $workSchedule,
                'today_records' => $todayRecords,
                'server_time' => Carbon::now($user->currentTeam->timezone ?? config('app.timezone'))->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Mobile clock API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'server_error',
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Execute the determined clock action
     */
    private function executeClockAction(User $user, array $clockAction, WorkCenter $workCenter, Request $request): array
    {
        switch ($clockAction['action'] ?? '') {
            case 'clock_in':
                $overtime = $clockAction['overtime'] ?? false;
                $eventTypeId = $clockAction['event_type_id'] ?? null;

                if (!$eventTypeId) {
                    throw new \Exception('No event type configured for clock in');
                }

                $result = $this->smartClockInService->clockIn($user, $eventTypeId, $overtime, 'mobile_api');

                if (empty($result['success'])) {
                    throw new \Exception($result['message'] ?? 'Clock in failed');
                }

                return [
                    'action' => 'clock_in',
                    'message' => $result['message'] ?? 'Clock in successful'
                ];

            case 'clock_out':
                $openEventId = $clockAction['open_event_id'] ?? null;

                if (!$openEventId) {
                    throw new \Exception('No open event found for clock out');
                }

                $result = $this->smartClockInService->clockOut($user, $openEventId);

                if (empty($result['success'])) {
                    throw new \Exception($result['message'] ?? 'Clock out failed');
                }

                return [
                    'action' => 'clock_out',
                    'message' => $result['message'] ?? 'Clock out successful'
                ];

            case 'resume_workday':
                $pauseEventId = $clockAction['pause_event_id'] ?? null;

                if (!$pauseEventId) {
                    throw new \Exception('No pause event found for resume');
                }

                $result = $this->smartClockInService->resumeWorkday($user, $pauseEventId);

                if (empty($result['success'])) {
                    throw new \Exception($result['message'] ?? 'Resume failed');
                }

                return [
                    'action' => 'break_end',
                    'message' => $result['message'] ?? 'Resume successful'
                ];

            case 'working_options':
                $requestedAction = $request->input('action');

                if ($requestedAction === 'pause') {
                    $pauseEventType = $user->currentTeam->eventTypes()
                        ->where('name', 'Pausa')
                        ->where('is_break_type', true)
                        ->first();

                    if (!$pauseEventType) {
                        throw new \Exception('No pause event type configured');
                    }

                    $result = $this->smartClockInService->pauseWorkday($user, $pauseEventType->id);

                    if (empty($result['success'])) {
                        throw new \Exception($result['message'] ?? 'Pause failed');
                    }

                    return [
                        'action' => 'break_start',
                        'message' => $result['message'] ?? 'Pause started'
                    ];
                }

                // Default to clock out
                $openEventId = $clockAction['open_event_id'] ?? null;

                if (!$openEventId) {
                    throw new \Exception('No open event found for clock out');
                }

                $result = $this->smartClockInService->clockOut($user, $openEventId);

                if (empty($result['success'])) {
                    throw new \Exception($result['message'] ?? 'Clock out failed');
                }

                return [
                    'action' => 'clock_out',
                    'message' => $result['message'] ?? 'Clock out successful'
                ];

            default:
                throw new \Exception('Clock action not supported in mobile API: ' . ($clockAction['action'] ?? ''));
        }
    }

    /**
     * Get current user status
     */
    private function getCurrentUserStatus(User $user): string
    {
        $clockAction = $this->smartClockInService->getClockAction($user);

        switch ($clockAction['action'] ?? '') {
            case 'clock_in':
                return 'clocked_out';
            case 'break_start':
                return 'working';
            case 'break_end':
                return 'on_break';
            case 'clock_out':
                return 'working';
            default:
                return 'unknown';
        }
    }

    /**
     * Get today's time records for the user
     */
    private function getTodayRecords(User $user): array
    {
        $teamTimezone = $user->currentTeam->timezone ?? config('app.timezone');
        $today = Carbon::now($teamTimezone)->startOfDay();
        $tomorrow = $today->copy()->addDay();

        $events = $user->events()
            ->whereBetween('start', [$today, $tomorrow])
            ->with('eventType')
            ->orderBy('start')
            ->get();

        return $events->map(function ($event) {
            return [
                'id' => $event->id,
                'type' => $event->eventType->name ?? 'Unknown',
                'start' => Carbon::parse($event->start)->toISOString(),
                'end' => $event->end ? Carbon::parse($event->end)->toISOString() : null,
                'observations' => $event->observations,
                'is_closed' => $event->is_closed
            ];
        })->toArray();
    }

    /**
     * Get user's work schedule
     */
    private function getUserWorkSchedule(User $user): ?array
    {
        $scheduleMeta = $user->meta->where('meta_key', 'work_schedule')->first();

        if (!$scheduleMeta || !$scheduleMeta->meta_value) {
            return null;
        }

        try {
            return json_decode($scheduleMeta->meta_value, true);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get current status without performing actions
     * work_center_code is optional and inferred when missing
     */
    public function status(Request $request): JsonResponse
    {
        // Obtener el estado del trabajador
        $request->validate([
            'user_code' => 'required|string'
        ]);

        $user = User::where('user_code', $request->user_code)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        try {
            $clockAction = $this->smartClockInService->getClockAction($user);
            $clockAction = $clockAction ?? [];

            $todayStats = $this->getTodayStats($user) ?? [];

            $isWithinSchedule = $this->smartClockInService->isUserWithinWorkSchedule($user, now());
            $customMessage = null;
            if (!$isWithinSchedule) {
                $customMessage = 'Fuera de horario laboral. Fichaje excepcional.';
            } else if (($clockAction['action'] ?? '') === 'clock_in') {
                $customMessage = 'LISTO';
            }
            $currentStatus = $this->getCurrentStatusText($clockAction['action'] ?? 'unknown');
                // --- Calcular next_slot siempre ---
                $teamTimezone = $user->currentTeam->timezone ?? config('app.timezone');
                $now = \Carbon\Carbon::now($teamTimezone);
                $scheduleMeta = $user->meta->where('meta_key', 'work_schedule')->first();
                $schedule = $scheduleMeta ? json_decode($scheduleMeta->meta_value, true) : [];
                $nextSlot = null;
                if (isset($clockAction['next_slot']) && is_array($clockAction['next_slot']) && !empty($clockAction['next_slot'])) {
                    $nextSlot = $clockAction['next_slot'];
                } else {
                    $nextSlot = !empty($schedule) ? $this->smartClockInService->getNextScheduledSlot($now, $schedule) : null;
                }
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'team' => $user->currentTeam->name ?? 'Sin equipo',
                    ],
                    'action' => $clockAction['action'] ?? 'unknown',
                    'can_clock' => $clockAction['can_clock'] ?? false,
                    'message' => $customMessage ?? $clockAction['message'] ?? null,
                    'overtime' => $clockAction['overtime'] ?? false,
                    'event_type_id' => $clockAction['event_type_id'] ?? null,
                        'next_slot' => $nextSlot,
                    'today_stats' => [
                        'total_entries' => $todayStats['total_entries'] ?? 0,
                        'total_exits' => $todayStats['total_exits'] ?? 0,
                        'worked_hours' => $todayStats['worked_hours'] ?? '0:00',
                        'current_status' => $currentStatus,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud'
            ], 500);
        }
    }

    /**
     * Get current status text based on action
     */
    private function getCurrentStatusText(string $action): string
    {
        // Si la acción es clock_in o confirm_exceptional_clock_in, diferenciamos por horario
        if ($action === 'clock_in') {
            return 'INICIAR JORNADA';
        }
        if ($action === 'confirm_exceptional_clock_in') {
            return 'INICIAR REGISTRO EXCEPCIONAL';
        }
        return match ($action) {
            'working_options' => 'TRABAJANDO',
            'resume_workday' => 'EN PAUSA',
            default => 'INICIAR JORNADA'
        };
    }

    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'work_center_code' => 'sometimes|string',
            'user_code' => 'required|string',
            'offline_events' => 'array'
        ]);

        $workCenterCode = $request->work_center_code ?? null;
        $user = User::where('user_code', $request->user_code)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        if (!$workCenterCode) {
            $team = $user->currentTeam;
            if (!$team) {
                return response()->json([
                    'success' => false,
                    'message' => 'User has no current team'
                ], 400);
            }

            $workCenter = $team->workCenters()->first();
            if (!$workCenter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Centro de trabajo no encontrado'
                ], 404);
            }
        } else {
            $workCenter = WorkCenter::where('code', $workCenterCode)->first();
            if (!$workCenter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Centro de trabajo no encontrado'
                ], 404);
            }
        }

        $syncResults = [];
        $offlineEvents = $request->offline_events ?? [];

        try {
            foreach ($offlineEvents as $event) {
                // Basic validation for offline event structure
                if (!isset($event['action']) || !isset($event['datetime'])) {
                    $syncResults[] = [
                        'event' => $event,
                        'success' => false,
                        'message' => 'Evento incompleto'
                    ];
                    continue;
                }

                try {
                    $eventDateTime = Carbon::parse($event['datetime']);
                    // TODO: implement actual synchronization logic
                    $syncResults[] = [
                        'event' => $event,
                        'success' => true,
                        'message' => 'Evento sincronizado (mock)',
                        'processed_at' => now()->toISOString()
                    ];
                } catch (\Exception $eventError) {
                    $syncResults[] = [
                        'event' => $event,
                        'success' => false,
                        'message' => 'Error al procesar evento: ' . $eventError->getMessage()
                    ];
                }
            }

            $successCount = count(array_filter($syncResults, fn ($r) => $r['success']));
            $totalCount = count($syncResults);

            return response()->json([
                'success' => true,
                'message' => "Sincronización completada: {$successCount}/{$totalCount} eventos procesados",
                'data' => [
                    'total_events' => $totalCount,
                    'successful_syncs' => $successCount,
                    'failed_syncs' => $totalCount - $successCount,
                    'sync_results' => $syncResults,
                    'sync_timestamp' => now()->toISOString()
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Mobile sync error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error durante la sincronización'
            ], 500);
        }
    }

    /**
     * Get worker data by code for mobile setup
     */
    public function getWorkerData(string $code): JsonResponse
    {
        try {
            $user = User::where('user_code', $code)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trabajador no encontrado'
                ], 404);
            }

            $workCenters = $user->teams->flatMap(function ($team) {
                return $team->workCenters->map(function ($workCenter) use ($team) {
                    return [
                        'id' => $workCenter->id,
                        'name' => $workCenter->name,
                        'code' => $workCenter->code,
                        'team_name' => $team->name,
                        'timezone' => $team->timezone ?? config('app.timezone')
                    ];
                });
            })->values();

            $scheduleMeta = $user->meta->where('meta_key', 'work_schedule')->first();
            $workSchedule = null;
            if ($scheduleMeta && $scheduleMeta->meta_value) {
                $workSchedule = json_decode($scheduleMeta->meta_value, true);
            }

            $holidaysMeta = $user->meta->where('meta_key', 'holidays')->first();
            $holidays = [];
            if ($holidaysMeta && $holidaysMeta->meta_value) {
                $holidays = json_decode($holidaysMeta->meta_value, true) ?? [];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name ?? '',
                        'family_name1' => $user->family_name1 ?? '',
                        'user_code' => $user->user_code
                    ],
                    'work_centers' => $workCenters,
                    'work_schedule' => $workSchedule,
                    'holidays' => $holidays
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Mobile getWorkerData error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos del trabajador'
            ], 500);
        }
    }

    /**
     * Get today's statistics for a user
     */
    private function getTodayStats(User $user): array
    {
        try {
            $today = now()->format('Y-m-d');
            // Obtener todos los eventos de hoy
            $events = $user->events()
                ->whereDate('start', $today)
                ->orderBy('start', 'asc')
                ->with('eventType')
                ->get();

            $totalEntries = $events->where('eventType.is_workday_type', true)->where('type', 'start')->count();
            $totalExits = $events->where('eventType.is_workday_type', true)->where('type', 'end')->count();

            // Calcular segundos trabajados (sumar intervalos entre start/end laborales)
            $workedSeconds = 0;
            $startTime = null;
            foreach ($events as $event) {
                if ($event->eventType && $event->eventType->is_workday_type) {
                    if ($event->type === 'start') {
                        $startTime = Carbon::parse($event->start);
                    } elseif ($event->type === 'end' && $startTime) {
                        $endTime = Carbon::parse($event->start);
                        $workedSeconds += $startTime->diffInSeconds($endTime);
                        $startTime = null;
                    }
                }
            }

            // Calcular segundos de pausa (sumar intervalos entre start/end de pausas)
            $pauseSeconds = 0;
            $pauseStart = null;
            foreach ($events as $event) {
                if ($event->eventType && $event->eventType->is_break_type) {
                    if ($event->type === 'start') {
                        $pauseStart = Carbon::parse($event->start);
                    } elseif ($event->type === 'end' && $pauseStart) {
                        $pauseEnd = Carbon::parse($event->start);
                        $pauseSeconds += $pauseStart->diffInSeconds($pauseEnd);
                        $pauseStart = null;
                    }
                }
            }

            // Restar pausas al tiempo trabajado
            $netWorkedSeconds = max(0, $workedSeconds - $pauseSeconds);
            $workedHours = floor($netWorkedSeconds / 3600);
            $workedMinutes = floor(($netWorkedSeconds % 3600) / 60);
            $workedHoursFormatted = sprintf('%d:%02d', $workedHours, $workedMinutes);

            // Estado actual
            $currentStatus = $this->getCurrentStatusText($this->smartClockInService->getClockAction($user)['action'] ?? '');

            return [
                'worked_hours' => $workedHoursFormatted,
                'total_entries' => $totalEntries,
                'total_exits' => $totalExits,
                'current_status' => $currentStatus
            ];
        } catch (\Exception $e) {
            Log::error('Error getting today stats', ['error' => $e->getMessage()]);
            return [
                'worked_hours' => '0:00',
                'total_entries' => 0,
                'total_exits' => 0,
                'current_status' => 'unknown'
            ];
        }
    }
}

