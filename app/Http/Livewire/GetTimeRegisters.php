<?php

namespace App\Http\Livewire;

use App\Models\Team;
use App\Models\User;
use App\Models\Event;
use App\Notifications\EventAuthorized;
use App\Notifications\EventDeAuthorized;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;
use Livewire\WithPagination;
use Laravel\Jetstream\HasTeams;
use Illuminate\Support\Facades\Auth;

/**
 * A Livewire component for displaying and managing time registers.
 *
 * This component provides a paginated and filterable table of events, with
 * functionality for searching, sorting, and performing actions on events.
 */
class GetTimeRegisters extends Component
{
    use WithPagination;
    use HasTeams;

    protected $events;
    public bool $showFiltersModal = false;
    public string $search = '';
    public Event $filter;
    public string $sort = 'start';
    public string $direction = 'desc';
    public string $qtytoshow = '10';
    public bool $readyonload = false;
    public User $user;
    public Team $team;
    public array $teamUsers;
    public $teamUserList;
    public $eventTypes;
    public bool $isTeamAdmin = false;
    public bool $isInspector = false;
    public bool $confirmed = false;
    public bool $filtered = false;
    public bool $showOnlyMine = false;
    public bool $showEventModal = false;
    public ?Event $selectedEvent;
    public $announcements;
    
    // Propiedades individuales para el filtro (para queryString)
    public string $filterStart = '';
    public string $filterEnd = '';
    public ?int $filterUserId = null;
    public ?int $filterEventTypeId = null;

    protected $listeners = ['render', 'confirm', 'delete', 'eventAuthorizationChanged' => '$refresh'];

    protected $queryString = [
        'sort' => ['except' => 'start'],
        'direction' => ['except' => 'desc'],
        'qtytoshow' => ['except' => '10'],
        'search' => ['except' => ''],
        'confirmed' => ['except' => false],
        'filtered' => ['except' => false],
        'showOnlyMine' => [],
        'filterStart' => ['except' => '', 'as' => 'start'],
        'filterEnd' => ['except' => '', 'as' => 'end'],
        'filterUserId' => ['except' => null, 'as' => 'user'],
        'filterEventTypeId' => ['except' => null, 'as' => 'type']
    ];

    protected $rules = [
        'filter.start' => 'required|date',
        'filter.end' => 'required|date|after:filter.start',
        'filter.user_id' => 'nullable|integer',
        'filter.is_open' => 'boolean',
        'filter.event_type_id' => 'nullable|integer',
        'filterStart' => 'required|date',
        'filterEnd' => 'required|date|after:filterStart',
        'filterUserId' => 'nullable|integer',
        'filterEventTypeId' => 'nullable|integer',
    ];

    /**
     * Initialize the component and set default values.
     */
    public function mount()
    {
        // Inicializar propiedades del filtro si no vienen de URL
        if (empty($this->filterStart)) {
            $this->filterStart = date('Y-m-01');
        }
        if (empty($this->filterEnd)) {
            $this->filterEnd = date('Y-m-t');
        }
        
        // Crear objeto filter sincronizado con las propiedades individuales
        $this->filter = new Event([
            "start" => $this->filterStart,
            "end" => $this->filterEnd,
            "user_id" => $this->filterUserId,
            "is_open" => false,
            "event_type_id" => $this->filterEventTypeId,
        ]);
        
        // Sincronizar propiedades individuales con el objeto
        $this->syncFilterProperties();
        
        $this->user = Auth::user();
        $this->events = $this->user->events()->Paginate($this->qtytoshow);
        $this->team = $this->user->currentTeam;
        $this->teamUserList = $this->team ? $this->team->allUsers() : collect();
        $this->eventTypes = $this->team ? $this->team->eventTypes : collect();
        $this->isTeamAdmin = $this->user->isTeamAdmin();
        $this->isInspector = $this->user->isInspector();
        
        // Establecer valores por defecto solo si no vienen de la URL
        if (!request()->has('confirmed')) {
            $this->confirmed = false;
        }
        if (!request()->has('filtered')) {
            $this->filtered = false;
        }
        if (!request()->has('showOnlyMine')) {
            // Para administradores, inicializar con showOnlyMine = true por defecto
            $this->showOnlyMine = $this->isTeamAdmin;
        }
        
        // Cargar anuncios activos del equipo
        if ($this->team) {
            $this->announcements = $this->team->announcements()
                ->active()
                ->with('creator')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $this->announcements = collect();
        }

        if ($this->team && ($this->isTeamAdmin || $this->isInspector)) {
            $this->teamUsers = $this->team->allUsers()->pluck('id')->toArray();
        } else {
            $this->teamUsers = [$this->user->id];
        }
    }

    /**
     * Toggle the sorting direction for the specified column.
     *
     * @param string $sort The column to sort by.
     * @return void
     */
    public function order(string $sort): void
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
     * Toggle the filter to show only the current user's events.
     *
     * @return void
     */
    public function filterOnlyMine(): void
    {
        $this->showOnlyMine = !$this->showOnlyMine;
    }

    /**
     * Emit the event to edit an existing event.
     *
     * @param \App\Models\Event $ev The event to edit.
     * @return void
     */
    public function edit(Event $ev): void
    {
        $this->emitTo('edit-event', 'edit', $ev);
    }

    /**
     * Confirm an event based on user role and event status.
     *
     * @param \App\Models\Event $ev The event to confirm.
     * @return void
     */
    public function confirm(Event $ev): void
    {
        if (!$ev->hasCompleteDates()) {
            $this->emit('incompleteEventConfirmation');
            return;
        }

        if ($this->isTeamAdmin) {
            $wasOpen = $ev->is_open; // Guardar el estado anterior
            if ($ev->toggleConfirm()) {
                // Si estaba abierto (is_open = true) y se cerró, se confirmó
                // Si estaba cerrado (is_open = false) y se abrió, se desconfirmó
                if ($wasOpen) {
                    $this->emit('alert', __('event_confirmation.confirmed'));
                } else {
                    $this->emit('alert', __('event_confirmation.unconfirmed'));
                }
            } else {
                $this->emit('incompleteEventConfirmation');
            }
        } else if ($ev->is_open) {
            if ($ev->confirm()) {
                $this->emit('alert', __('event_confirmation.confirmed'));
            } else {
                $this->emit('incompleteEventConfirmation');
            }
        }
    }

    /**
     * Emit the confirmation alert for an event.
     *
     * @param \App\Models\Event $ev The event to confirm.
     * @return void
     */
    public function alertConfirm(Event $ev): void
    {
        if (!$ev->is_open && !$this->isTeamAdmin) {
            $this->emit('alertFail', __("This event is already closed and cannot be modified."));
            return;
        }
        
        if (!$ev->hasCompleteDates()) {
            $this->emit('incompleteEventConfirmation');
            return;
        }
        
        $this->emit('confirmConfirmation', $ev);
    }

    /**
     * Emit the deletion alert for an event.
     *
     * @param \App\Models\Event $ev The event to delete.
     * @return void
     */
    public function alertDelete(Event $ev): void
    {
        if (!$ev->is_open && !$this->isTeamAdmin) {
            $this->emit('alertFail', __("This event is already closed and cannot be modified."));
            return;
        }
        $this->emit('deleteConfirmation', $ev);
    }

    /**
     * Delete an event if authorized.
     *
     * @param \App\Models\Event $ev The event to delete.
     * @return \Illuminate\Http\RedirectResponse
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
     *
     * @return void
     */
    public function unsetFilter(): void
    {
        $this->showFiltersModal = false;
        $this->filtered = false;
        $this->confirmed = false;
    }

    /**
     * Set the filters and show the modal.
     *
     * @return void
     */
    public function setFilter(): void
    {
        $this->showFiltersModal = true;
        $this->filtered = true;
        $this->confirmed = false;
    }

    /**
     * Sincronizar propiedades individuales con el objeto filter
     */
    public function syncFilterProperties(): void
    {
        $this->filterStart = $this->filter->start ?? date('Y-m-01');
        $this->filterEnd = $this->filter->end ?? date('Y-m-t');
        $this->filterUserId = $this->filter->user_id;
        $this->filterEventTypeId = $this->filter->event_type_id;
    }

    /**
     * Retrieve and filter events based on the current settings.
     *
     * @return void
     */
    public function getEvents(): void
    {
        if (!$this->readyonload) {
            return;
        }

        $query = Event::query()->with('eventType')
            ->select(
                'events.id', 'events.user_id', 'users.name', 'users.family_name1',
                'events.start', 'events.end', 'events.description', 'events.is_open', 'events.event_type_id',
                'events.is_authorized', 'events.observations'
            )
            ->join('users', 'events.user_id', '=', 'users.id')
            ->leftJoin('event_types', 'events.event_type_id', '=', 'event_types.id')
            ->where(function ($query) {
                $query->where('event_types.team_id', $this->team->id)
                      ->orWhereNull('events.event_type_id');
            })
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
              ->when($this->filter->user_id, fn($query) => $query->where('events.user_id', $this->filter->user_id))
              ->when($this->filter->is_open, fn($query) => $query->where('events.is_open', '1'))
              ->when($this->filter->event_type_id, fn($query) => $query->where('events.event_type_id', $this->filter->event_type_id));
        });

        // "Show only open" toggle
        $query->when($this->confirmed, function ($q) {
            $q->where('events.is_open', '=', '1');
        });

        // Lógica del filtro "Mis registros" - comportamiento inverso para admins
        if ($this->isTeamAdmin) {
            // Para administradores: showOnlyMine=true muestra solo mis registros
            $query->when($this->showOnlyMine, function ($q) {
                $q->where('events.user_id', Auth::id());
            });
        } else {
            // Para usuarios normales: showOnlyMine=true muestra solo mis registros (comportamiento original)
            $query->when($this->showOnlyMine, function ($q) {
                $q->where('events.user_id', Auth::id());
            });
        }

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
     *
     * @return void
     */
    public function updatingEvent(): void
    {
        $this->resetPage();
    }

    /**
     * Reset the pagination when the confirmation status is updated.
     *
     * @return void
     */
    public function updatingConfirmed(): void
    {
        $this->resetPage();
    }

    /**
     * Reset the pagination when the quantity to show is updated.
     *
     * @return void
     */
    public function updatingQtytoshow(): void
    {
        $this->resetPage();
    }

    /**
     * Mark the events as ready to load.
     *
     * @return void
     */
    public function loadEvents(): void
    {
        $this->readyonload = true;
    }

    /**
     * Check if a color is dark.
     *
     * @param string $hexColor
     * @return boolean
     */
    public function isDark(string $hexColor): bool
    {
        if(empty($hexColor)) return false;
        $hexColor = str_replace('#', '', $hexColor);
        if(strlen($hexColor) != 6) return false;
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
        return $luminance < 0.5;
    }

    /**
     * Toggle the authorization status of an event.
     *
     * @param int $eventId
     * @return void
     */
    public function toggleAuthorization(int $eventId): void
    {
        if (!$this->isTeamAdmin) {
            $this->emit('alertFail', __('You are not authorized to perform this action.'));
            return;
        }

        $event = Event::find($eventId);

        if (!$event || !$event->eventType || !$event->eventType->is_authorizable) {
            $this->emit('alertFail', __('This event cannot be authorized.'));
            return;
        }

        $event->is_authorized = !$event->is_authorized;
        $event->is_open = !$event->is_authorized;

        if ($event->is_authorized) {
            $event->authorized_by_id = Auth::id();
        } else {
            $event->authorized_by_id = null;
        }

        $event->save();

        if ($event->is_authorized) {
            $event->user->notify(new EventAuthorized($event));
            $this->emit('alert', __('Event :id has been authorized (Status: Closed)', ['id' => $event->id]));
        } else {
            $event->user->notify(new EventDeAuthorized($event));
            $this->emit('alert', __('Event :id has been un-authorized (Status: Open)', ['id' => $event->id]));
        }

        $this->emit('eventAuthorizationChanged');
    }

    /**
     * Show the event details modal.
     *
     * @param int $eventId
     * @return void
     */
    public function showEventModal(int $eventId): void
    {
        $this->selectedEvent = Event::with(['eventType', 'authorizedBy'])->findOrFail($eventId);
        $this->showEventModal = true;
    }
}
