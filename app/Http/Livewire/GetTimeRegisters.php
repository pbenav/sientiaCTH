<?php

namespace App\Http\Livewire;

use App\Models\Team;
use App\Models\User;
use App\Models\Event;
use Livewire\Component;
use Livewire\WithPagination;
use Laravel\Jetstream\HasTeams;
use Illuminate\Support\Facades\Auth;

class GetTimeRegisters extends Component
{
    use WithPagination;
    use HasTeams;

    protected $events;
    public $showFiltersModal = false;
    public $search;
    public $filter;
    public $sort = 'start';
    public $direction = 'desc';
    public $qtytoshow = '10';
    public $readyonload = false;
    public User $user;
    public Team $team;
    public $teamUsers;
    public $eventTypes;
    public $isTeamAdmin;
    public $isInspector;
    public $confirmed;
    public $filtered;

    protected $listeners = ['render', 'confirm', 'delete'];

    protected $queryString = [
        'sort' => ['except' => 'start'],
        'direction' => ['except' => 'desc'],
        'qtytoshow' => ['except' => '10']
    ];

    protected $rules = [
        'filter.start' => 'required|date',
        'filter.end' => 'required|date|after:filter.start',
        'filter.name' => 'nullable|string',
        'filter.family_name1' => 'nullable|string',
        'filter.is_open' => 'boolean',
        'filter.description' => 'nullable|string',
    ];

    /**
     * Initialize the component and set default values.
     */
    public function mount()
    {
        $this->filter = new Event([
            "start" => date('Y-m-01'),
            "end" => date('Y-m-t'),
            "name" => "",
            "family_name1" => "",
            "is_open" => false,
            "description" => __('All'),
            "event_type_id" => null,
        ]);
        $this->user = Auth::user();
        $this->events = $this->user->events()->Paginate($this->qtytoshow);
        $this->team = $this->user->currentTeam;
        $this->eventTypes = $this->team ? $this->team->eventTypes : collect();
        $this->isTeamAdmin = $this->user->isTeamAdmin();
        $this->isInspector = $this->user->isInspector();
        $this->confirmed = false;
        $this->filtered = false;

        $this->teamUsers = array();
        if ($this->team && ($this->isTeamAdmin || $this->isInspector)) {
            foreach ($this->team->allUsers() as $us) {
                array_push($this->teamUsers, $us->id);
            }
        } else {
            array_push($this->teamUsers, $this->user->id);
        }
    }

    /**
     * Toggle the sorting direction for the specified column.
     *
     * @param string $sort The column to sort by.
     */
    public function order($sort)
    {
        if ($this->sort == $sort) {
            if ($this->direction == 'asc') {
                $this->direction = 'desc';
            } else {
                $this->direction = 'asc';
            }
        } else {
            $this->sort = $sort;
            $this->direction = 'asc';
        };
    }

    /**
     * Emit the event to edit an existing event.
     *
     * @param Event $ev The event to edit.
     */
    public function edit(Event $ev)
    {
        $this->emitTo('edit-event', 'edit', $ev);
    }

    /**
     * Confirm an event based on user role and event status.
     *
     * @param Event $ev The event to confirm.
     */
    public function confirm(Event $ev)
    {
        if ($this->isTeamAdmin) {
            $ev->toggleConfirm();
        } else if ($ev->is_open) {
            $ev->Confirm();
        }
    }

    /**
     * Emit the confirmation alert for an event.
     *
     * @param Event $ev The event to confirm.
     */
    public function alertConfirm(Event $ev)
    {
        $this->emit('confirmConfirmation', $ev);
    }

    /**
     * Emit the deletion alert for an event.
     *
     * @param Event $ev The event to delete.
     */
    public function alertDelete(Event $ev)
    {
        $this->emit('deleteConfirmation', $ev);
    }

    /**
     * Delete an event if authorized.
     *
     * @param Event $ev The event to delete.
     */
    public function delete(Event $ev)
    {
        if ($this->isTeamAdmin || $ev->is_open) {
            $ev->delete();
        }
        // Redirect to avoid not found errors
        return redirect()->route('events');
    }

    /**
     * Unset the filters and reset related flags.
     */
    public function unsetFilter()
    {
        $this->showFiltersModal = false;
        $this->filtered = false;
        $this->confirmed = false;
    }

    /**
     * Set the filters and show the modal.
     */
    public function setFilter()
    {
        $this->showFiltersModal = true;
        $this->filtered = true;
        $this->confirmed = false;
    }

    /**
     * Retrieve and filter events based on the current settings.
     */
    public function getEvents()
    {
        if (!$this->readyonload) {
            return;
        }

        $query = Event::query()
            ->select(
                'events.id', 'events.user_id', 'users.name', 'users.family_name1',
                'events.start', 'events.end', 'events.description', 'events.is_open'
            )
            ->join('users', 'user_id', '=', 'users.id')
            ->whereIn('events.user_id', $this->teamUsers);

        // General search box
        $query->when($this->search, function ($q, $search) {
            $q->where(function ($subq) use ($search) {
                $subq->where('users.name', 'like', '%' . $search . '%')
                    ->orWhere('events.user_id', $search)
                    ->orWhere('users.family_name1', 'like', '%' . $search . '%')
                    ->orWhere('users.family_name2', 'like', '%' . $search . '%')
                    ->orWhere('events.description', 'like', '%' . $search . '%');
            });
        });

        // Advanced filters modal
        $query->when($this->filtered, function ($q) {
            $q->when($this->filter->start, fn($query) => $query->whereDate('events.start', '>=', $this->filter->start))
              ->when($this->filter->end, fn($query) => $query->whereDate('events.end', '<=', $this->filter->end))
              ->when($this->filter->name, fn($query) => $query->where('users.name', $this->filter->name))
              ->when($this->filter->family_name1, fn($query) => $query->where('users.family_name1', $this->filter->family_name1))
              ->when($this->filter->is_open, fn($query) => $query->where('events.is_open', '1'))
              ->when($this->filter->event_type_id, fn($query) => $query->where('events.event_type_id', $this->filter->event_type_id))
              ->when($this->filter->description && $this->filter->description != __('All'), fn($query) => $query->where('events.description', $this->filter->description));
        });

        // "Show only open" toggle
        $query->when($this->confirmed, function ($q) {
            $q->where('events.is_open', '=', '1');
        });

        $this->events = $query->orderBy($this->sort, $this->direction)->paginate($this->qtytoshow);
    }

    /**
     * Render the component view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $this->getEvents();
        return view('livewire.events.get-time-registers',)
            ->with('events', $this->events)
            ->with('isTeamAdmin', $this->isTeamAdmin)
            ->with('isInspector', $this->isInspector);
    }

    /**
     * Reset the pagination when the event is updated.
     */
    public function updatingEvent()
    {
        $this->resetPage();
    }

    /**
     * Reset the pagination when the confirmation status is updated.
     */
    public function updatingConfirmed()
    {
        $this->resetPage();
    }

    /**
     * Reset the pagination when the quantity to show is updated.
     */
    public function updatingQtytoshow()
    {
        $this->resetPage();
    }

    /**
     * Mark the events as ready to load.
     */
    public function loadEvents()
    {
        $this->readyonload = true;
    }
}
