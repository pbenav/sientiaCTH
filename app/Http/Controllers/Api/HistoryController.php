<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HistoryController extends Controller
{
    /**
     * Get user's event history with optional date filters
     */
    public function index(Request $request)
    {
        $request->validate([
            'user_code' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        // Find user by code
        $user = User::where('user_code', $request->user_code)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => __('User not found'),
            ], 404);
        }

        $teamTimezone = $user->currentTeam->timezone ?? config('app.timezone');
        
        // Parse dates in team timezone, default to last 30 days
        $startDate = $request->start_date 
            ? Carbon::parse($request->start_date, $teamTimezone)->startOfDay()
            : Carbon::now($teamTimezone)->subDays(30)->startOfDay();
            
        $endDate = $request->end_date
            ? Carbon::parse($request->end_date, $teamTimezone)->endOfDay()
            : Carbon::now($teamTimezone)->endOfDay();

        // Convert to UTC for database query
        $startDateUTC = $startDate->copy()->setTimezone('UTC');
        $endDateUTC = $endDate->copy()->setTimezone('UTC');

        Log::debug('[HistoryController][index] Query params:', [
            'user_code' => $request->user_code,
            'start_date_team_tz' => $startDate->toDateTimeString(),
            'end_date_team_tz' => $endDate->toDateTimeString(),
            'start_date_utc' => $startDateUTC->toDateTimeString(),
            'end_date_utc' => $endDateUTC->toDateTimeString(),
        ]);

        // Get events with pagination
        $perPage = $request->per_page ?? 50;
        
        $events = Event::where('user_id', $user->id)
            ->whereBetween('start', [$startDateUTC, $endDateUTC])
            ->with('eventType')
            ->orderBy('start', 'desc')
            ->paginate($perPage);

        // Transform events
        $transformedEvents = $events->map(function ($event) use ($teamTimezone) {
            $start = $event->start ? Carbon::parse($event->start, 'UTC') : null;
            $end = $event->end ? Carbon::parse($event->end, 'UTC') : null;
            
            // Calculate duration
            $duration = null;
            $durationFormatted = null;
            if ($start && $end) {
                $duration = $end->diffInSeconds($start);
                $hours = floor($duration / 3600);
                $minutes = floor(($duration % 3600) / 60);
                $durationFormatted = sprintf('%d:%02d', $hours, $minutes);
            }

            return [
                'id' => $event->id,
                'type' => $event->eventType->name ?? 'Unknown',
                'event_type_id' => $event->event_type_id,
                'start' => $start ? $start->toISOString() : null,
                'end' => $end ? $end->toISOString() : null,
                'duration_seconds' => $duration,
                'duration_formatted' => $durationFormatted,
                'is_open' => $event->is_open,
                'is_authorized' => $event->is_authorized,
                'is_exceptional' => $event->is_exceptional,
                'observations' => $event->observations,
                'description' => $event->description,
                'created_at' => $event->created_at ? Carbon::parse($event->created_at, 'UTC')->toISOString() : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'events' => $transformedEvents,
                'pagination' => [
                    'current_page' => $events->currentPage(),
                    'per_page' => $events->perPage(),
                    'total' => $events->total(),
                    'last_page' => $events->lastPage(),
                    'has_more' => $events->hasMorePages(),
                ],
                'filters' => [
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ],
            ],
        ]);
    }
}
