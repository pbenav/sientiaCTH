<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Clock Status Resource (Flutter API)
 * 
 * Provides comprehensive clock-in/out status for mobile app.
 * Includes user info, available actions, schedule slots, and daily statistics.
 * 
 * Response Structure:
 * @property-read array $user User information (name, email, team)
 * @property-read string $action Available action: 'clock_in', 'clock_out', 'none'
 * @property-read bool $can_clock Whether user can perform clock action now
 * @property-read string|null $message Status message for UI display
 * @property-read bool $overtime Whether current time is overtime
 * @property-read int|null $event_type_id Event type ID for this action
 * @property-read int|null $pause_event_id Active pause event ID
 * @property-read int|null $pause_event_type_id Pause event type ID
 * @property-read string|null $special_event_color Hex color for special events
 * @property-read array|null $next_slot Next scheduled time slot
 * @property-read array|null $current_slot Current active time slot
 * @property-read array $today_stats Today's work statistics (hours, breaks, etc.)
 * @property-read array $today_records Today's event records
 * @property-read int|null $current_team_id Active team ID
 * @property-read string|null $current_team_name Active team name
 * @property-read string|null $current_work_center_code Active work center code
 * 
 * Used by: MobileClockController::status()
 * 
 * @version 1.0.0
 * @since 2025-01-10
 */
class ClockStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'user' => [
                'name' => $this->resource['user']->name,
                'email' => $this->resource['user']->email,
                'team' => $this->resource['user']->currentTeam->name ?? 'Sin equipo',
            ],
            'action' => $this->resource['action'] ?? 'unknown',
            'can_clock' => $this->resource['can_clock'] ?? false,
            'message' => $this->resource['message'] ?? null,
            'overtime' => $this->resource['overtime'] ?? false,
            'event_type_id' => $this->resource['event_type_id'] ?? null,
            'pause_event_id' => $this->resource['pause_event_id'] ?? null,
            'pause_event_type_id' => $this->resource['pause_event_type_id'] ?? null,
            'special_event_color' => $this->resource['special_event_color'] ?? null,
            'next_slot' => $this->resource['next_slot'] ?? null,
            'current_slot' => $this->resource['current_slot'] ?? null,
            'today_stats' => $this->resource['today_stats'] ?? [],
            'today_records' => $this->resource['today_records'] ?? [],
            // Team information for validation
            'current_team_id' => $this->resource['current_team_id'] ?? null,
            'current_team_name' => $this->resource['current_team_name'] ?? null,
            'current_work_center_code' => $this->resource['current_work_center_code'] ?? null,
        ];
    }
}
