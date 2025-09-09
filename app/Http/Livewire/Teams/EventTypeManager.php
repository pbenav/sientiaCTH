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

    // Form properties
    public $eventTypeId;
    public $name;
    public $color;
    public $observations;
    public $is_all_day;

    protected $rules = [
        'name' => 'required|string|max:255',
        'color' => 'required|string|max:255',
        'observations' => 'nullable|string',
        'is_all_day' => 'required|boolean',
    ];

    protected $validationAttributes = [
        'name' => 'nombre',
        'color' => 'color',
        'observations' => 'observaciones',
        'is_all_day' => 'todo el día',
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

        return redirect()->route('teams.show', $this->team);
    }

    public function manageEventType($eventTypeId = null)
    {
        $this->resetErrorBag();
        $this->managingEventType = true;
        $this->eventTypeId = $eventTypeId;

        if ($eventTypeId) {
            $eventType = EventType::find($eventTypeId);
            $this->name = $eventType->name;
            $this->color = $eventType->color;
            $this->observations = $eventType->observations;
            $this->is_all_day = $eventType->is_all_day;
        } else {
            $this->name = '';
            $this->color = '#000000';
            $this->observations = '';
            $this->is_all_day = false;
        }
    }

    public function saveEventType()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'color' => $this->color,
            'observations' => $this->observations,
            'is_all_day' => $this->is_all_day,
        ];

        if ($this->eventTypeId) {
            $eventType = EventType::find($this->eventTypeId);
            Gate::forUser(auth()->user())->authorize('update', $eventType);
            $eventType->update($data);
        } else {
            Gate::forUser(auth()->user())->authorize('create', [EventType::class, $this->team]);
            $this->team->eventTypes()->create($data);
        }

        $this->eventTypes = $this->team->eventTypes()->get();
        $this->managingEventType = false;
    }
}
