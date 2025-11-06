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

            // Find user by secret code and work center
            $user = User::where('secret_code', $request->user_secret_code)
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
                return response()->json([
                    'success' => false,
                    'error' => 'cannot_clock',
                    'message' => $clockAction['message']
                ], 400);
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
                // For working state, we need to determine if user wants to pause or clock out
                // For API, we'll default to starting a pause
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
                'start' => $event->start->toISOString(),
                'end' => $event->end ? $event->end->toISOString() : null,
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
}