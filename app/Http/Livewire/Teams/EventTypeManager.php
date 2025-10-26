<?php

namespace App\Http\Livewire\Teams;

use App\Models\EventType;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

/**
 * A Livewire component for managing event types for a team.
 *
 * This component provides functionality for creating, updating, and deleting
 * event types.
 */
class EventTypeManager extends Component
{
    public $team;
    public bool $isTeamAdmin;
    public $eventTypes;
    public bool $confirmingEventTypeDeletion = false;
    public ?EventType $eventTypeToDelete;
    public bool $managingEventType = false;

    // Form properties
    public ?int $eventTypeId;
    public string $name;
    public string $color;
    public ?string $observations;
    public bool $is_all_day;
    public bool $is_workday_type;

    protected $rules = [
        'name' => 'required|string|max:255',
        'color' => 'required|string|max:255',
        'observations' => 'nullable|string',
        'is_all_day' => 'required|boolean',
        'is_workday_type' => 'required|boolean',
    ];

    protected $validationAttributes = [
        'name' => 'name',
        'color' => 'color',
        'observations' => 'observations',
        'is_all_day' => 'all day',
        'is_workday_type' => 'workday type',
    ];

    /**
     * Mount the component.
     *
     * @param mixed $team
     * @return void
     */
    public function mount($team): void
    {
        $this->team = $team;
        $this->eventTypes = $team->eventTypes;
        $this->isTeamAdmin = auth()->user()->isTeamAdmin();
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.teams.event-type-manager');
    }

    /**
     * Confirm the deletion of an event type.
     *
     * @param \App\Models\EventType $eventType
     * @return void
     */
    public function confirmEventTypeDeletion(EventType $eventType): void
    {
        $this->confirmingEventTypeDeletion = true;
        $this->eventTypeToDelete = $eventType;
    }

    /**
     * Delete an event type.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteEventType()
    {
        Gate::forUser(auth()->user())->authorize('delete', $this->eventTypeToDelete);

        $this->eventTypeToDelete->delete();

        $this->eventTypes = $this->team->eventTypes()->get();

        $this->confirmingEventTypeDeletion = false;

        return redirect()->route('teams.show', $this->team);
    }

    /**
     * Show the form for managing an event type.
     *
     * @param int|null $eventTypeId
     * @return void
     */
    public function manageEventType(int $eventTypeId = null): void
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
            $this->is_workday_type = $eventType->is_workday_type;
        } else {
            $this->name = '';
            $this->color = '#000000';
            $this->observations = '';
            $this->is_all_day = false;
            $this->is_workday_type = false;
        }
    }

    /**
     * Save the event type.
     *
     * @return void
     */
    public function saveEventType(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'color' => $this->color,
            'observations' => $this->observations,
            'is_all_day' => $this->is_all_day,
            'is_workday_type' => $this->is_workday_type,
        ];

        if ($this->is_workday_type) {
            $this->team->eventTypes()->where('id', '!=', $this->eventTypeId)->update(['is_workday_type' => false]);
        }

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
