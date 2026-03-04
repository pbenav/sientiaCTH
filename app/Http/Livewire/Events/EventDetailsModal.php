<?php

namespace App\Http\Livewire\Events;

use App\Models\Event;
use Livewire\Component;

/**
 * Unified event details modal component.
 * Replaces event-info-modal, event-info-modal-simple, and inline modals.
 */
class EventDetailsModal extends Component
{
    use \App\Traits\HandlesTimezoneConversion;

    public $showModal = false;
    public $eventData = null;
    public $eventId = null;
    public $isTeamAdmin = false;

    protected $listeners = [
        'showEventDetails' => 'showEvent',
        'showEventInfoModal' => 'showEvent', // Backward compatibility
        'reopeningRequestSent' => 'closeModal',
    ];

    public function mount()
    {
        $this->isTeamAdmin = auth()->user() && auth()->user()->hasTeamRole(auth()->user()->currentTeam, 'admin');
    }

    /**
     * Show event details modal.
     * Accepts both array (for backward compatibility) and event ID.
     */
    public function showEvent($data): void
    {
        if (is_array($data)) {
            // Backward compatibility: array format
            $this->eventData = $data;
            $this->eventId = $data['id'] ?? null;
        } else {
            // New format: event ID
            $this->eventId = $data;
            $event = Event::with(['user', 'eventType', 'workCenter', 'team'])->find($data);
            
            if ($event) {
                $timezone = $this->getTeamTimezone($event->team);

                $this->eventData = [
                    'id' => $event->id,
                    'user_id' => $event->user_id,
                    'user' => [
                        'name' => $event->user->name ?? '',
                        'family_name1' => $event->user->family_name1 ?? '',
                    ],
                    'event_type' => $event->eventType ? [
                        'name' => $event->eventType->name,
                        'color' => $event->eventType->color ?? '#3788d8',
                        'is_authorizable' => $event->eventType->is_authorizable ?? false,
                    ] : null,
                    'team' => $event->team ? [
                        'name' => $event->team->name,
                    ] : null,
                    'work_center' => $event->workCenter ? [
                        'name' => $event->workCenter->name,
                    ] : null,
                    'start' => $event->start ? $this->utcToTeamTimezone($event->start, $timezone)->format('d/m/Y H:i') : null,
                    'end' => $event->end ? $this->utcToTeamTimezone($event->end, $timezone)->format('d/m/Y H:i') : null,
                    'duration' => $event->getPeriod() ?? __('N/A'),
                    'description' => $event->description,
                    'observations' => $event->observations,
                    'latitude' => $event->latitude ?? (($event->location_start['latitude'] ?? ($event->location_end['latitude'] ?? null))),
                    'longitude' => $event->longitude ?? (($event->location_start['longitude'] ?? ($event->location_end['longitude'] ?? null))),
                    'nfc_tag_id' => $event->nfc_tag_id,
                    'ip_address' => $event->ip_address,
                    'is_open' => $event->is_open,
                    'is_exceptional' => $event->is_exceptional ?? false,
                    'authorized' => $event->is_authorized ?? false,
                    'created_at' => $event->created_at ? $this->utcToTeamTimezone($event->created_at, $timezone)->format('d/m/Y H:i') : null,
                    'updated_at' => $event->updated_at ? $this->utcToTeamTimezone($event->updated_at, $timezone)->format('d/m/Y H:i') : null,
                ];
            }
        }
        
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->eventData = null;
        $this->eventId = null;
    }

    public function render()
    {
        return view('livewire.events.event-details-modal');
    }
}
