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
use App\Http\Requests\Api\ClockInRequest;
use App\Http\Requests\Api\SyncRequest;
use App\Http\Resources\ClockStatusResource;
use App\Http\Resources\WorkCenterResource;
use App\Models\Holiday;


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
    /**
     * Handle mobile clock in/out request
     *
     * @param ClockInRequest $request
     * @return JsonResponse
     */
    public function clock(ClockInRequest $request): JsonResponse
    {
        try {
            Log::debug('[MobileClockController][clock] Request body:', $request->all());

            // Normalize work center code
            $workCenterCode = $request->work_center_code ?? $request->manual_work_center_code ?? null;

            // If work center code not provided, try to infer from user->currentTeam
            $user = User::where('user_code', $request->user_code)->first();
            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            if (!$workCenterCode) {
                $team = $user->currentTeam;
                if (!$team) {
                    return response()->json([
                        'message' => 'User has no current team to infer work center'
                    ], 400);
                }

                $workCenter = $team->workCenters()->first();
                if (!$workCenter) {
                    return response()->json([
                        'message' => 'No work centers configured for user team'
                    ], 404);
                }
            } else {
                $workCenter = WorkCenter::where('code', $workCenterCode)->first();
                if (!$workCenter) {
                    return response()->json([
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
                        'message' => $clockAction['message'] ?? 'Cannot clock at this time'
                    ], 400);
                }
            }

            // Execute the clock action
            $result = $this->executeClockAction($user, $clockAction, $workCenter, $request);

            // Prepare data for resource
            $currentStatus = $this->getCurrentUserStatus($user);
            $todayRecords = $this->getTodayRecords($user);
            $workSchedule = $this->getUserWorkSchedule($user);
            $todayStats = $this->getTodayStats($user);

            // Construct the resource data
            $resourceData = [
                'user' => $user,
                'action' => $result['action'] ?? null,
                'can_clock' => false, // Just clocked, so usually false immediately or depends on next state
                'message' => $result['message'] ?? null,
                'overtime' => $clockAction['overtime'] ?? false,
                'event_type_id' => $clockAction['event_type_id'] ?? null,
                'pause_event_id' => $clockAction['pause_event_id'] ?? null,
                'today_stats' => $todayStats,
                'today_records' => $todayRecords,
                'current_slot' => null, // Calculate if needed
                'next_slot' => null, // Calculate if needed
            ];
            
            Log::debug('[MobileClockController][clock] Resource data after clock:', [
                'pause_event_id' => $resourceData['pause_event_id'],
                'action' => $resourceData['action']
            ]);

            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? null,
                'data' => new ClockStatusResource($resourceData),
            ]);

        } catch (\Exception $e) {
            Log::error('Mobile clock API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
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
                $observations = $request->input('observations');

                if (!$eventTypeId) {
                    throw new \Exception('No event type configured for clock in');
                }

                $result = $this->smartClockInService->clockIn($user, $eventTypeId, $overtime, 'mobile_api', $observations);

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
                // Usar el pause_event_id recibido en el request si está presente
                $pauseEventId = $request->input('pause_event_id') ?? $clockAction['pause_event_id'] ?? null;

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
        
        // Use UTC as base for queries to avoid server timezone interference
        $todayUTC = Carbon::now('UTC')->startOfDay();
        $tomorrowUTC = $todayUTC->copy()->addDay();

        $events = $user->events()
            ->whereBetween('start', [$todayUTC, $tomorrowUTC])
            ->with('eventType')
            ->orderBy('start')
            ->get();

        return $events->map(function ($event) {
            // Parse timestamps as UTC since they're stored in UTC in the database
            $start = $event->start ? Carbon::parse($event->start, 'UTC') : null;
            $end = $event->end ? Carbon::parse($event->end, 'UTC') : null;
            $duration = ($start && $end) ? $end->diffInSeconds($start) : null;
            return [
                'id' => $event->id,
                'type' => $event->eventType->name ?? 'Unknown',
                'event_type_id' => $event->event_type_id ?? null,
                'pause_event_id' => $event->pause_event_id ?? null,
                'start' => $start ? $start->toISOString() : null,
                'end' => $end ? $end->toISOString() : null,
                'duration_seconds' => $duration,
                'location_start' => $event->location_start ?? null,
                'location_end' => $event->location_end ?? null,
                'observations' => $event->observations,
                'is_open' => $event->is_open,
                'created_at' => $event->created_at ? Carbon::parse($event->created_at, 'UTC')->toISOString() : null,
                'updated_at' => $event->updated_at ? Carbon::parse($event->updated_at, 'UTC')->toISOString() : null
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
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        try {
            $clockAction = $this->smartClockInService->getClockAction($user);
            $clockAction = $clockAction ?? [];
            
            Log::debug('[MobileClockController][status] Clock action:', [
                'user_code' => $user->user_code,
                'clock_action' => $clockAction
            ]);

            $todayStats = $this->getTodayStats($user) ?? [];

            $isWithinSchedule = $this->smartClockInService->isUserWithinWorkSchedule($user, now());
            $customMessage = null;
            if (!$isWithinSchedule) {
                $customMessage = 'Fuera de horario laboral. Fichaje excepcional.';
            } else if (($clockAction['action'] ?? '') === 'clock_in') {
                $customMessage = 'LISTO';
            }
            
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
            
            $todayRecords = $this->getTodayRecords($user);
            
            // Calcular el tramo horario actual usando el servicio compartido
            $currentSlot = null;
            if (!empty($schedule)) {
                $currentSlot = $this->smartClockInService->getCurrentScheduledSlot($now, $schedule);
            }

            $resourceData = [
                'user' => $user,
                'action' => $clockAction['action'] ?? 'unknown',
                'can_clock' => $clockAction['can_clock'] ?? false,
                'message' => $customMessage ?? $clockAction['message'] ?? null,
                'overtime' => $clockAction['overtime'] ?? false,
                'event_type_id' => $clockAction['event_type_id'] ?? null,
                'pause_event_id' => $clockAction['pause_event_id'] ?? null,
                'next_slot' => $nextSlot,
                'current_slot' => $currentSlot,
                'today_stats' => $todayStats,
                'today_records' => $todayRecords,
            ];
            
            Log::debug('[MobileClockController][status] Resource data:', [
                'pause_event_id' => $resourceData['pause_event_id'],
                'action' => $resourceData['action']
            ]);

            return response()->json([
                'success' => true,
                'data' => new ClockStatusResource($resourceData)
            ]);
        } catch (\Exception $e) {
            return response()->json([
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

    public function sync(SyncRequest $request): JsonResponse
    {
        $workCenterCode = $request->work_center_code ?? null;
        $user = User::where('user_code', $request->user_code)->first();
        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        if (!$workCenterCode) {
            $team = $user->currentTeam;
            if (!$team) {
                return response()->json([
                    'message' => 'User has no current team'
                ], 400);
            }

            $workCenter = $team->workCenters()->first();
            if (!$workCenter) {
                return response()->json([
                    'message' => 'Centro de trabajo no encontrado'
                ], 404);
            }
        } else {
            $workCenter = WorkCenter::where('code', $workCenterCode)->first();
            if (!$workCenter) {
                return response()->json([
                    'message' => 'Centro de trabajo no encontrado'
                ], 404);
            }
        }

        $syncResults = [];
        $offlineEvents = $request->offline_events ?? [];

        try {
            foreach ($offlineEvents as $event) {
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
            $workSchedule = [];
            if ($scheduleMeta && $scheduleMeta->meta_value) {
                $workSchedule = json_decode($scheduleMeta->meta_value, true);
                // Convert numeric day representations to letter abbreviations (L, M, X, J, V, S, D)
                $dayMap = [
                    1 => 'L', // Lunes
                    2 => 'M', // Martes
                    3 => 'X', // Miércoles
                    4 => 'J', // Jueves
                    5 => 'V', // Viernes
                    6 => 'S', // Sábado
                    7 => 'D', // Domingo
                ];
                if (is_array($workSchedule)) {
                    foreach ($workSchedule as &$slot) {
                        // Single day fields
                        if (isset($slot['day_of_week'])) {
                            $d = $slot['day_of_week'];
                            $slot['day_of_week'] = is_numeric($d) && isset($dayMap[(int)$d]) ? $dayMap[(int)$d] : $d;
                        }
                        if (isset($slot['day'])) {
                            $d = $slot['day'];
                            $slot['day'] = is_numeric($d) && isset($dayMap[(int)$d]) ? $dayMap[(int)$d] : $d;
                        }
                        // Multiple days array
                        if (isset($slot['days']) && is_array($slot['days'])) {
                            $slot['days'] = array_map(function ($d) use ($dayMap) {
                                return is_numeric($d) && isset($dayMap[(int)$d]) ? $dayMap[(int)$d] : $d;
                            }, $slot['days']);
                        }
                    }
                    unset($slot);
                }
            }

            // Fetch holidays for the user's current team and current year
            $currentYear = now()->year;
            $teamId = $user->current_team_id;
            
            $holidays = Holiday::where('team_id', $teamId)
                ->whereYear('date', $currentYear)
                ->orderBy('date')
                ->get()
                ->map(function ($holiday) {
                    return [
                        'id' => $holiday->id,
                        'name' => $holiday->name,
                        'date' => $holiday->date->format('Y-m-d'),
                        'type' => $holiday->type ?? 'national'
                    ];
                })
                ->values()
                ->all();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name ?? '',
                        'family_name1' => $user->family_name1 ?? '',
                        'family_name2' => $user->family_name2 ?? '',
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

