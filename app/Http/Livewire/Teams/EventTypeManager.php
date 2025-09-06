<?php

namespace App\Http\Livewire\Teams;

use App\Models\EventType;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class EventTypeManager extends Component
{
    public $team;
    public $isTeamAdmin;
    public $eventTypes;
    public $confirmingEventTypeDeletion = false;
    public $eventTypeToDelete;
    public $managingEventType = false;
    public $eventType;

    protected $rules = [
        'eventType.name' => 'required|string|max:255',
        'eventType.color' => 'required|string|max:255',
        'eventType.observations' => 'nullable|string',
    ];

    public function mount($team)
    {
        $this->team = $team;
        $this->eventTypes = $team->eventTypes;
        $this->isTeamAdmin = auth()->user()->isTeamAdmin();
    }

    public function render()
    {
        return view('livewire.teams.event-type-manager');
    }

    public function confirmEventTypeDeletion(EventType $eventType)
    {
        $this->confirmingEventTypeDeletion = true;
        $this->eventTypeToDelete = $eventType;
    }

    public function deleteEventType()
    {
        Gate::forUser(auth()->user())->authorize('delete', $this->eventTypeToDelete);

        $this->eventTypeToDelete->delete();

        $this->eventTypes = $this->team->eventTypes()->get();

        $this->confirmingEventTypeDeletion = false;
    }

    public function manageEventType(EventType $eventType = null)
    {
        $this->managingEventType = true;
        $this->eventType = $eventType ?? new EventType();
    }

    public function saveEventType()
    {
        $this->validate();

        if (isset($this->eventType->id)) {
            Gate::forUser(auth()->user())->authorize('update', $this->eventType);
            $this->eventType->save();
        } else {
            Gate::forUser(auth()->user())->authorize('create', [EventType::class, $this->team]);
            $this->team->eventTypes()->save($this->eventType);
        }

        $this->eventTypes = $this->team->eventTypes()->get();
        $this->managingEventType = false;
    }
}
