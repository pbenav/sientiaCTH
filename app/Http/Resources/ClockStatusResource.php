<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClockStatusResource extends JsonResource
{
    public function toArray($request)
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
            'next_slot' => $this->resource['next_slot'] ?? null,
            'current_slot' => $this->resource['current_slot'] ?? null,
            'today_stats' => $this->resource['today_stats'] ?? [],
            'today_records' => $this->resource['today_records'] ?? [],
        ];
    }
}
