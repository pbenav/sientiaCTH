<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkCenter;
use App\Services\SmartClockInService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MobileWebController extends Controller
{
    protected SmartClockInService $smartClockInService;

    public function __construct(SmartClockInService $smartClockInService)
    {
        $this->smartClockInService = $smartClockInService;
    }

    /**
     * Mobile authentication page
     */

    /**
     * Handle mobile authentication
     */
    public function login(Request $request)
    {
        try {
            // Debug logging
            Log::info('Mobile login attempt', [
                'all_data' => $request->all(),
                'user_code' => $request->user_code,
                'work_center_code' => $request->work_center_code,
                'manual_work_center_code' => $request->manual_work_center_code,
            ]);

            // Accept authentication using only user_code. If work_center_code is
            // provided use it; otherwise attempt to infer a suitable work center
            // from the user's current team.
            Log::info('Mobile login attempt (revised) - payload', $request->all());

            $request->validate([
                'user_code' => 'required|string|max:20',
            ]);

            // Try to find the user by secret code
            $user = User::where('user_code', $request->user_code)->first();
            if (!$user) {
                return back()->withErrors(['user_code' => 'Código de usuario inválido']);
            }

            // Do NOT require the client to send a work center code. Try to infer
            // one from the user's current team when available; otherwise allow
            // the mobile session to continue without a preset work center.
            $workCenter = null;
            if ($user->currentTeam) {
                $workCenter = $user->currentTeam->workCenters()->first();
                Log::info('Inferred work center from user currentTeam', ['user_id' => $user->id, 'work_center_id' => $workCenter?->id]);
            }

            // If a work center was inferred, ensure it belongs to the user's team
            // as a precaution. If not found, we continue with a null work center.
            if ($workCenter && $workCenter->team_id !== ($user->current_team_id ?? $user->currentTeam?->id)) {
                Log::warning('Inferred work center does not belong to user team', ['user_id' => $user->id, 'work_center_team' => $workCenter->team_id, 'user_team' => $user->current_team_id]);
                // Do not block the login flow; unset the inferred work center.
                $workCenter = null;
            }

            // Set mobile session. mobile_work_center_id will be null if none was
            // inferred; the mobile client should handle a missing work center.
            session([
                'mobile_user_id' => $user->id,
                'mobile_work_center_id' => $workCenter?->id,
                'mobile_authenticated' => true
            ]);

            return redirect()->route('mobile.home');
        } catch (\Throwable $e) {
            Log::error('Mobile login error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);

            $details = config('app.debug') ? [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ] : null;

            return response()->view('mobile.error', [
                'message' => 'Error interno del servidor al procesar login móvil',
                'details' => $details,
            ], 500);
        }
    }

    /**
     * Mobile home/dashboard
     */
    public function home()
    {
        try {
            $user = User::find(session('mobile_user_id'));
            $workCenter = WorkCenter::find(session('mobile_work_center_id'));

            if (!$user || !$workCenter) {
                return redirect()->route('mobile.auth');
            }

            // Get clock data using SmartClockInService
            $clockData = $this->smartClockInService->getClockAction($user);

            // Get today's stats
            $todayStats = $this->getTodayStats($user);

            return view('mobile.home', [
                'user' => $user,
                'workCenter' => $workCenter,
                'clockData' => $clockData,
                'todayStats' => $todayStats
            ]);
        } catch (\Throwable $e) {
            Log::error('Mobile home error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'session' => [
                    'mobile_user_id' => session('mobile_user_id'),
                    'mobile_work_center_id' => session('mobile_work_center_id')
                ]
            ]);

            $details = config('app.debug') ? [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ] : null;

            return response()->view('mobile.error', [
                'message' => 'Error interno del servidor al cargar la pantalla principal móvil',
                'details' => $details,
            ], 500);
        }
    }

    /**
     * Mobile history page
     */
    public function history(Request $request)
    {
        $user = User::find(session('mobile_user_id'));
        $workCenter = WorkCenter::find(session('mobile_work_center_id'));
        
        // Get filter parameters
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        // Mock data for demonstration
        $clockHistory = [
            now()->format('Y-m-d') => [
                'events' => [
                    [
                        'action' => 'entrada',
                        'datetime' => now()->setHour(8)->setMinute(0)->format('Y-m-d H:i:s'),
                        'team' => 'Equipo Principal'
                    ],
                    [
                        'action' => 'salida',
                        'datetime' => now()->setHour(16)->setMinute(30)->format('Y-m-d H:i:s'),
                        'team' => 'Equipo Principal'
                    ]
                ],
                'summary' => [
                    'worked_hours' => '8:30'
                ]
            ]
        ];
        
        $summaryStats = [
            'total_days' => 5,
            'total_hours' => '40:00'
        ];

        return view('mobile.history', [
            'user' => $user,
            'workCenter' => $workCenter,
            'clockHistory' => $clockHistory,
            'summaryStats' => $summaryStats
        ]);
    }

    /**
     * Mobile schedule configuration
     */
    public function schedule(Request $request)
    {
        $user = User::find(session('mobile_user_id'));
        $workCenter = WorkCenter::find(session('mobile_work_center_id'));
        
        if (!$user || !$workCenter) {
            return redirect()->route('mobile.auth');
        }
        
        $weekOffset = (int) $request->get('week_offset', 0);
        $currentWeek = [
            'start' => now()->addWeeks($weekOffset)->startOfWeek(),
            'end' => now()->addWeeks($weekOffset)->endOfWeek(),
            'week_number' => now()->addWeeks($weekOffset)->week
        ];
        
        // Mock schedule data
        $weekSchedule = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $currentWeek['start']->copy()->addDays($i);
            $isToday = $date->isToday();
            $isWeekend = $date->isWeekend();
            
            $weekSchedule[] = [
                'date' => $date,
                'day_name' => $date->locale('es')->dayName,
                'is_today' => $isToday,
                'is_weekend' => $isWeekend,
                'is_holiday' => false,
                'schedule' => $isWeekend ? [] : [
                    [
                        'team_name' => 'Turno Mañana',
                        'start_time' => '08:00',
                        'end_time' => '16:00',
                        'duration' => '8h',
                        'description' => 'Turno principal'
                    ]
                ],
                'total_hours' => $isWeekend ? null : '8:00'
            ];
        }
        
        $weekSummary = [
            'scheduled_hours' => '40:00',
            'working_days' => 5
        ];

        return view('mobile.schedule', [
            'user' => $user,
            'workCenter' => $workCenter,
            'currentWeek' => $currentWeek,
            'weekSchedule' => $weekSchedule,
            'weekSummary' => $weekSummary
        ]);
    }

    /**
     * Mobile profile page
     */
    public function profile()
    {
        $user = User::find(session('mobile_user_id'));
        $workCenter = WorkCenter::find(session('mobile_work_center_id'));
        
        if (!$user || !$workCenter) {
            return redirect()->route('mobile.auth');
        }
        
        // Mock work statistics
        $workStats = [
            'this_month' => [
                'days_worked' => 20,
                'total_hours' => '160:00'
            ],
            'this_week' => [
                'days_worked' => 5,
                'total_hours' => '40:00'
            ],
            'averages' => [
                'hours_per_day' => '8:00',
                'days_per_week' => '5'
            ]
        ];
        
        // Mock team information
        $teamInfo = [
            [
                'name' => 'Equipo Principal',
                'description' => 'Turno de mañana',
                'is_current' => true
            ]
        ];

        return view('mobile.profile', [
            'user' => $user,
            'workCenter' => $workCenter,
            'workStats' => $workStats,
            'teamInfo' => $teamInfo
        ]);
    }
    
    /**
     * Mobile reports page
     */
    public function reports(Request $request)
    {
        $user = User::find(session('mobile_user_id'));
        $workCenter = WorkCenter::find(session('mobile_work_center_id'));
        
        if (!$user || !$workCenter) {
            return redirect()->route('mobile.auth');
        }
        
        // Get filter parameters
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $reportType = $request->get('report_type', 'summary');
        
        // Mock report data
        $overallStats = [
            'total_days' => 20,
            'total_hours' => '160:00',
            'avg_daily_hours' => '8:00',
            'total_entries' => 40
        ];
        
        $dailyData = [];
        $weeklyData = [];
        $monthlyData = [];
        
        // Generate mock daily data if needed
        if ($reportType === 'daily' || !$reportType) {
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $dailyData[] = [
                    'date' => $date,
                    'total_hours' => '8:00',
                    'entries' => [
                        ['type' => 'entrada', 'time' => '08:00'],
                        ['type' => 'salida', 'time' => '16:00']
                    ]
                ];
            }
        }

        return view('mobile.reports', [
            'user' => $user,
            'workCenter' => $workCenter,
            'overallStats' => $overallStats,
            'dailyData' => $dailyData,
            'weeklyData' => $weeklyData,
            'monthlyData' => $monthlyData
        ]);
    }

    /**
     * Mobile logout
     */
    public function logout()
    {
        session()->forget(['mobile_user_id', 'mobile_work_center_id', 'mobile_authenticated']);
        return redirect()->route('mobile.auth');
    }

    /**
     * Get authenticated mobile user
     */
    private function getMobileUser(): ?User
    {
        $userId = session('mobile_user_id');
        return $userId ? User::find($userId) : null;
    }

    /**
     * Get mobile work center
     */
    private function getMobileWorkCenter(): ?WorkCenter
    {
        $workCenterId = session('mobile_work_center_id');
        return $workCenterId ? WorkCenter::find($workCenterId) : null;
    }

    /**
     * Get today's records for user
     */
    private function getTodayRecords(User $user): \Illuminate\Support\Collection
    {
        return $user->events()
            ->whereDate('start', today())
            ->with('eventType')
            ->orderBy('start')
            ->get();
    }

    /**
     * Calculate total working hours
     */
    private function calculateTotalHours($events): float
    {
        $totalMinutes = 0;
        
        foreach ($events as $event) {
            if ($event->eventType && $event->eventType->is_workday_type && $event->end) {
                $start = Carbon::parse($event->start);
                $end = Carbon::parse($event->end);
                $totalMinutes += $start->diffInMinutes($end);
            }
        }

        return round($totalMinutes / 60, 2);
    }

    /**
     * Calculate break time
     */
    private function calculateBreakTime($events): float
    {
        $breakMinutes = 0;
        
        foreach ($events as $event) {
            if ($event->eventType && $event->eventType->is_break_type && $event->end) {
                $start = Carbon::parse($event->start);
                $end = Carbon::parse($event->end);
                $breakMinutes += $start->diffInMinutes($end);
            }
        }

        return round($breakMinutes / 60, 2);
    }

    /**
     * Get today's statistics for the user
     */
    private function getTodayStats(User $user): array
    {
        try {
            $today = now()->format('Y-m-d');
            
            // Get today's events
            $events = $user->events()
                ->whereDate('start', $today)
                ->orderBy('start', 'desc')
                ->get();
            
            $totalEntries = $events->where('event_type.is_workday_type', true)->where('type', 'start')->count();
            $totalExits = $events->where('event_type.is_workday_type', true)->where('type', 'end')->count();
            
            // Calculate worked hours (simplified - sum of time between start and end events)
            $workedSeconds = 0;
            $startTime = null;
            
            foreach ($events->sortBy('start') as $event) {
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
            
            $workedHours = floor($workedSeconds / 3600);
            $workedMinutes = floor(($workedSeconds % 3600) / 60);
            $workedHoursFormatted = sprintf('%d:%02d', $workedHours, $workedMinutes);
            
            return [
                'worked_hours' => $workedHoursFormatted,
                'total_entries' => $totalEntries,
                'total_exits' => $totalExits
            ];
        } catch (\Exception $e) {
            Log::error('Error getting today stats', ['error' => $e->getMessage()]);
            return [
                'worked_hours' => '0:00',
                'total_entries' => 0,
                'total_exits' => 0
            ];
        }
    }

    /**
     * Show mobile authentication form
     */
    public function showAuth()
    {
        return view('mobile.auth');
    }
}