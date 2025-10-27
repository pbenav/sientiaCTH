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

    // Form state
    public array $state = [];

    protected $rules = [
        'state.name' => 'required|string|max:255',
        'state.color' => 'required|string|max:255',
        'state.observations' => 'nullable|string',
        'state.is_all_day' => 'required|boolean',
        'state.is_workday_type' => 'required|boolean',
    ];

    protected $validationAttributes = [
        'state.name' => 'name',
        'state.color' => 'color',
        'state.observations' => 'observations',
        'state.is_all_day' => 'all day',
        'state.is_workday_type' => 'workday type',
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

        if ($eventTypeId) {
            $eventType = EventType::find($eventTypeId);
            $this->state = $eventType->toArray();
        } else {
            $this->state = [
                'name' => '',
                'color' => '#000000',
                'observations' => '',
                'is_all_day' => false,
                'is_workday_type' => false,
            ];
        }

        $this->managingEventType = true;
    }

    /**
     * Save the event type.
     *
     * @return void
     */
    public function saveEventType(): void
    {
        $this->validate();
        if (!empty($this->state['is_workday_type']) && $this->state['is_workday_type']) {
            $this->team->eventTypes()->where('id', '!=', $this->state['id'] ?? null)->update(['is_workday_type' => false]);
        }

        if (!empty($this->state['id'])) {
            $eventType = EventType::where('id', $this->state['id'])->where('team_id', $this->team->id)->firstOrFail();
            Gate::forUser(auth()->user())->authorize('update', $eventType);
            $eventType->update($this->state);
        } else {
            Gate::forUser(auth()->user())->authorize('create', $this->team);
            $this->team->eventTypes()->create($this->state);
        }

        $this->eventTypes = $this->team->eventTypes()->get();
        $this->managingEventType = false;
    }
}
