<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class EventResource extends JsonResource
{
    public function toArray($request)
    {
        $start = $this->start ? Carbon::parse($this->start) : null;
        $end = $this->end ? Carbon::parse($this->end) : null;
        $duration = ($start && $end) ? $end->diffInSeconds($start) : null;

        return [
            'id' => $this->id,
            'type' => $this->eventType->name ?? 'Unknown',
            'event_type_id' => $this->event_type_id ?? null,
            'pause_event_id' => $this->pause_event_id ?? null,
            'start' => $start ? $start->toISOString() : null,
            'end' => $end ? $end->toISOString() : null,
            'duration_seconds' => $duration,
            'location_start' => $this->location_start ?? null,
            'location_end' => $this->location_end ?? null,
            'observations' => $this->observations,
            'is_open' => $this->is_open,
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->toISOString() : null,
            'updated_at' => $this->updated_at ? Carbon::parse($this->updated_at)->toISOString() : null
        ];
    }
}
