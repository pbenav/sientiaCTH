<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkCenter;
use App\Services\SmartClockInService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MobileClockController extends Controller
{
    protected SmartClockInService $smartClockInService;

    public function __construct(SmartClockInService $smartClockInService)
    {
        $this->smartClockInService = $smartClockInService;
    }

    /**
     * Handle mobile clock in/out request
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function clock(Request $request): JsonResponse
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'work_center_code' => 'required|string|max:50',
                'user_secret_code' => 'required|string|max:10',
                'action' => 'sometimes|string|in:pause,clock_out',
                'location' => 'sometimes|array',
                'location.latitude' => 'sometimes|numeric|between:-90,90',
                'location.longitude' => 'sometimes|numeric|between:-180,180',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'validation_error',
                    'message' => 'Invalid request data',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find work center
            $workCenter = WorkCenter::where('code', $request->work_center_code)->first();
            if (!$workCenter) {
                return response()->json([
                    'success' => false,
                    'error' => 'invalid_work_center',
                    'message' => 'Work center not found'
                ], 404);
            }

            // Find user by user code and work center
            $user = User::where('user_code', $request->user_secret_code)
                       ->whereHas('teams', function($query) use ($workCenter) {
                           $query->where('teams.id', $workCenter->team_id);
                       })
                       ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'invalid_credentials',
                    'message' => 'Invalid user credentials or unauthorized for this work center'
                ], 401);
            }

            // Set user's current team to the work center's team
            $user->switchTeam($workCenter->team);

            // Get current clock status and determine action
            $clockAction = $this->smartClockInService->getClockAction($user);

            if (!$clockAction['can_clock']) {
                // For mobile API, automatically allow exceptional clock-ins
                if ($clockAction['action'] === 'confirm_exceptional_clock_in') {
                    // Check if user is actually within work schedule
                    $teamTimezone = $user->currentTeam->timezone ?? config('app.timezone');
                    $now = Carbon::now($teamTimezone);
                    $isWithinSchedule = $this->smartClockInService->isWithinWorkSchedule($now);

                    // Override to allow clock-in - only mark as exceptional if truly outside schedule
                    $clockAction = [
                        'can_clock' => true,
                        'action' => 'clock_in',
                        'event_type_id' => $clockAction['event_type_id'],
                        'overtime' => !$isWithinSchedule, // Only exceptional if outside schedule
                        'message' => $isWithinSchedule ? 'Clock-in allowed' : 'Exceptional clock-in allowed'
                    ];
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => 'cannot_clock',
                        'message' => $clockAction['message']
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

            return response()->json([
                'success' => true,
                'action_taken' => $result['action'],
                'message' => $result['message'],
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'family_name1' => $user->family_name1,
                    'current_status' => $currentStatus,
                    'work_center' => [
                        'id' => $workCenter->id,
                        'name' => $workCenter->name,
                        'code' => $workCenter->code
                    ]
                ],
                'work_schedule' => $workSchedule,
                'today_records' => $todayRecords,
                'server_time' => Carbon::now($user->currentTeam->timezone ?? config('app.timezone'))->toISOString()
            ]);

        } catch (\Exception $e) {
            \Log::error('Mobile clock API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
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
        switch ($clockAction['action']) {
            case 'clock_in':
                $overtime = $clockAction['overtime'] ?? false;
                $eventTypeId = $clockAction['event_type_id'] ?? null;
                
                if (!$eventTypeId) {
                    throw new \Exception('No event type configured for clock in');
                }
                
                $result = $this->smartClockInService->clockIn($user, $eventTypeId, $overtime);
                
                if (!$result['success']) {
                    throw new \Exception($result['message']);
                }
                
                return [
                    'action' => 'clock_in',
                    'message' => $result['message']
                ];

            case 'clock_out':
                $openEventId = $clockAction['open_event_id'] ?? null;
                
                if (!$openEventId) {
                    throw new \Exception('No open event found for clock out');
                }
                
                $result = $this->smartClockInService->clockOut($user, $openEventId);
                
                if (!$result['success']) {
                    throw new \Exception($result['message']);
                }
                
                return [
                    'action' => 'clock_out',
                    'message' => $result['message']
                ];

            case 'resume_workday':
                $pauseEventId = $clockAction['pause_event_id'] ?? null;
                
                if (!$pauseEventId) {
                    throw new \Exception('No pause event found for resume');
                }
                
                $result = $this->smartClockInService->resumeWorkday($user, $pauseEventId);
                
                if (!$result['success']) {
                    throw new \Exception($result['message']);
                }
                
                return [
                    'action' => 'break_end',
                    'message' => $result['message']
                ];

            case 'working_options':
                // For working state, check if user wants to pause or clock out
                $requestedAction = $request->input('action');
                
                if ($requestedAction === 'pause') {
                    // User wants to start a pause
                    $pauseEventType = $user->currentTeam->eventTypes()
                        ->where('name', 'Pausa')
                        ->where('is_break_type', true)
                        ->first();
                        
                    if (!$pauseEventType) {
                        throw new \Exception('No pause event type configured');
                    }
                    
                    $result = $this->smartClockInService->pauseWorkday($user, $pauseEventType->id);
                    
                    if (!$result['success']) {
                        throw new \Exception($result['message']);
                    }
                    
                    return [
                        'action' => 'break_start',
                        'message' => $result['message']
                    ];
                } else {
                    // Default to clock out
                    $openEventId = $clockAction['open_event_id'] ?? null;
                    
                    if (!$openEventId) {
                        throw new \Exception('No open event found for clock out');
                    }
                    
                    $result = $this->smartClockInService->clockOut($user, $openEventId);
                    
                    if (!$result['success']) {
                        throw new \Exception($result['message']);
                    }
                    
                    return [
                        'action' => 'clock_out',
                        'message' => $result['message']
                    ];
                }

            default:
                throw new \Exception('Clock action not supported in mobile API: ' . $clockAction['action']);
        }
    }

    /**
     * Get current user status
     */
    private function getCurrentUserStatus(User $user): string
    {
        $clockAction = $this->smartClockInService->getClockAction($user);
        
        switch ($clockAction['action']) {
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
     * Get current user status without performing any action
     */
    public function status(Request $request)
    {
        $request->validate([
            'work_center_code' => 'required|string',
            'user_code' => 'required|string'
        ]);

        // Find work center
        $workCenter = WorkCenter::where('code', $request->work_center_code)->first();
        if (!$workCenter) {
            return response()->json([
                'success' => false,
                'message' => 'Centro de trabajo no encontrado'
            ], 404);
        }

        // Find user
        $user = User::where('user_code', $request->user_code)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        try {
            // Get current status using the service
            $clockAction = $this->smartClockInService->getClockAction($user);
            $nextAction = $clockAction['action'];
            
            // Get today's events for summary
            $today = now()->format('Y-m-d');
            $todayEvents = []; // In real implementation, query today's events
            
            // Calculate today's statistics
            $todayStats = [
                'entries_count' => 0,
                'exits_count' => 0,
                'worked_hours' => '0:00',
                'current_status' => $nextAction === 'entrada' ? 'fuera' : 'trabajando',
                'last_action' => null
            ];
            
            // Mock calculation - replace with real logic
            if (count($todayEvents) > 0) {
                $lastEvent = $todayEvents[count($todayEvents) - 1];
                $todayStats['last_action'] = [
                    'type' => $lastEvent['type'] ?? 'entrada',
                    'time' => $lastEvent['time'] ?? now()->format('H:i'),
                    'datetime' => $lastEvent['datetime'] ?? now()->toISOString()
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'code' => $user->user_code
                    ],
                    'work_center' => [
                        'id' => $workCenter->id,
                        'name' => $workCenter->name,
                        'code' => $workCenter->code
                    ],
                    'next_action' => $nextAction,
                    'can_clock' => true, // Could add business logic here
                    'today_stats' => $todayStats,
                    'current_time' => now()->toISOString(),
                    'timezone' => config('app.timezone')
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Mobile status error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el estado del usuario'
            ], 500);
        }
    }
    
    /**
     * Sync offline data (for future offline support)
     */
    public function sync(Request $request)
    {
        $request->validate([
            'work_center_code' => 'required|string',
            'user_code' => 'required|string',
            'offline_events' => 'array'
        ]);

        // Find work center and user
        $workCenter = WorkCenter::where('code', $request->work_center_code)->first();
        if (!$workCenter) {
            return response()->json([
                'success' => false,
                'message' => 'Centro de trabajo no encontrado'
            ], 404);
        }

        $user = User::where('user_code', $request->user_code)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        $syncResults = [];
        $offlineEvents = $request->offline_events ?? [];

        try {
            foreach ($offlineEvents as $event) {
                // Validate offline event structure
                if (!isset($event['action']) || !isset($event['datetime'])) {
                    $syncResults[] = [
                        'event' => $event,
                        'success' => false,
                        'message' => 'Evento incompleto'
                    ];
                    continue;
                }

                // Try to process the offline event
                try {
                    $eventDateTime = Carbon::parse($event['datetime']);
                    
                    // Here you would implement the actual sync logic
                    // For now, we'll just validate and log
                    
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

            $successCount = count(array_filter($syncResults, fn($r) => $r['success']));
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
            \Log::error('Mobile sync error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error durante la sincronización'
            ], 500);
        }
    }
}