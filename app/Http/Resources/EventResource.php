<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

/**
 * Event Resource (Flutter API)
 * 
 * Transforms Event model into JSON format for mobile app consumption.
 * Provides time tracking event data with ISO 8601 timestamps and duration calculations.
 * 
 * Response Structure:
 * @property-read int $id Event unique identifier
 * @property-read string $type Event type name (e.g., "Jornada Laboral")
 * @property-read int|null $event_type_id Foreign key to event_types table
 * @property-read int|null $pause_event_id Associated pause event ID
 * @property-read string|null $start Start datetime (ISO 8601 format)
 * @property-read string|null $end End datetime (ISO 8601 format, null if open)
 * @property-read int|null $duration_seconds Duration in seconds (null if open)
 * @property-read string|null $location_start GPS location name at clock-in
 * @property-read string|null $location_end GPS location name at clock-out
 * @property-read string|null $observations User notes/observations
 * @property-read bool $is_open Event is currently open (not clocked out)
 * @property-read string|null $created_at Creation datetime (ISO 8601)
 * @property-read string|null $updated_at Last update datetime (ISO 8601)
 * 
 * @version 1.0.0
 * @since 2025-01-10
 */
class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
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
