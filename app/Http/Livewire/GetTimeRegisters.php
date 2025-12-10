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
    
    // Propiedades individuales para el filtro (para queryString)
    public string $filterStart = '';
    public string $filterEnd = '';
    public ?int $filterUserId = null;
    public ?int $filterEventTypeId = null;

    protected $listeners = [
        'render',
        'confirm',
        'delete',
        'eventAuthorizationChanged' => '$refresh',
        '$refresh' => 'refreshComponent'
    ];
    /**
     * Forzar refresco y reinicio de paginación tras eliminación
     */
    public function refreshComponent(): void
    {
        \Log::debug('refreshComponent ejecutado'); // Registro de depuración
        $this->readyonload = true; // Ensure events can be loaded
        $this->resetPage();
        // No need to call getEvents() - render() will do it
    }

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
        
        $this->user = Auth::user();
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
     * @param int $eventId The event ID to edit.
     * @return void
     */
    public function edit($eventId): void
    {
        \Log::info('GetTimeRegisters::edit called', ['eventId' => $eventId]);
        $ev = Event::find($eventId);
        if (!$ev) {
            \Log::info('GetTimeRegisters::edit - Event not found', ['eventId' => $eventId]);
            return;
        }
        \Log::info('GetTimeRegisters::edit - Emitting to edit-event', ['eventId' => $ev->id]);
        $this->emitTo('edit-event', 'edit', $ev->id);
    }


    /**
     * Confirm an event based on user role and event status.
     *
     * @param \App\Models\Event $ev The event to confirm.
     * @return void
     */
    public function confirm($eventId): void
    {
        $ev = Event::find($eventId);
        if (!$ev || !$ev->hasCompleteDates()) {
            $this->emit('incompleteEventConfirmation');
            return;
        }
        if ($this->isTeamAdmin) {
            $wasOpen = $ev->is_open;
            if ($ev->toggleConfirm()) {
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
    public function alertConfirm($eventId): void
    {
        $ev = Event::find($eventId);
        if (!$ev) return;
        if (!$ev->is_open && !$this->isTeamAdmin) {
            $this->emit('alertFail', __("This event is already closed and cannot be modified."));
            return;
        }
        if (!$ev->hasCompleteDates()) {
            $this->emit('incompleteEventConfirmation');
            return;
        }
        $this->emit('confirmConfirmation', $ev->id);
    }

    /**
     * Emit the deletion alert for an event.
     *
     * @param \App\Models\Event $ev The event to delete.
     * @return void
     */
    public function alertDelete($eventId): void
    {
        $ev = Event::find($eventId);
        if (!$ev) return;
        if (!$ev->is_open && !$this->isTeamAdmin) {
            $this->emit('alertFail', __("This event is already closed and cannot be modified."));
            return;
        }
        $this->emit('deleteConfirmation', $ev->id);
    }

    /**
     * Delete an event if authorized and refresh the component.
     *
     * @param int $eventId The ID of the event to delete.
     * @return void
     */
    public function delete($eventId): void
    {
        $ev = Event::find($eventId);
        if (!$ev) return;
        \Log::debug('Intentando eliminar evento', ['event_id' => $eventId]);
        if ($this->isTeamAdmin || $ev->is_open) {
            $ev->delete();
            \Log::debug('Evento eliminado', ['event_id' => $eventId]);
            $this->emit('alert', __('Event has been removed!'));
            $this->refreshComponent(); // Ensure the component refreshes after deletion
        } else {
            \Log::debug('No se pudo eliminar el evento', ['event_id' => $eventId]);
        }
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
     * Reset all filters to default values
     */
    protected function resetFilters(): void
    {
        $this->filtered = false;
        $this->confirmed = false;
        $this->search = '';
        $this->filterStart = '';
        $this->filterEnd = '';
        $this->filterUserId = null;
        $this->filterEventTypeId = null;
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
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getEvents()
    {
        \Log::info('getEvents ejecutado', [
            'readyonload' => $this->readyonload,
            'team_id' => $this->team ? $this->team->id : null,
            'team_name' => $this->team ? $this->team->name : null,
            'teamUsers' => $this->teamUsers,
            'filters' => [
                'search' => $this->search,
                'filtered' => $this->filtered,
                'confirmed' => $this->confirmed,
                'showOnlyMine' => $this->showOnlyMine,
            ],
        ]);

        if (!$this->readyonload) {
            \Log::info('getEvents no se ejecuta porque readyonload es false');
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->qtytoshow);
        }

        $query = Event::query()
            ->with(['user', 'eventType'])
            ->whereIn('user_id', $this->teamUsers)
            ->where('team_id', $this->team->id);
        
        \Log::info('getEvents - Query construida', [
            'filtering_by_team_id' => $this->team->id,
            'filtering_by_users' => $this->teamUsers,
        ]);

        // General search box
        $query->when($this->search, function ($q, $search) {
            $q->where(function ($subq) use ($search) {
                $subq->whereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', '%' . $search . '%')
                            ->orWhere('family_name1', 'like', '%' . $search . '%')
                            ->orWhere('family_name2', 'like', '%' . $search . '%');
                    })
                    ->orWhere('user_id', $search)
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        });

        // Advanced filters modal
        $query->when($this->filtered, function ($q) {
            $q->when($this->filter->start, fn($query) => $query->whereDate('start', '>=', $this->filter->start))
              ->when($this->filter->end, fn($query) => $query->whereDate('end', '<=', $this->filter->end))
              ->when($this->filter->user_id, fn($query) => $query->where('user_id', $this->filter->user_id))
              ->when($this->filter->is_open, fn($query) => $query->where('is_open', '1'))
              ->when($this->filter->event_type_id, fn($query) => $query->where('event_type_id', $this->filter->event_type_id));
        });

        // "Show only open" toggle
        $query->when($this->confirmed, function ($q) {
            $q->where('is_open', '=', '1');
        });

        // "My Records" filter logic - igual para admins y usuarios normales
        $query->when($this->showOnlyMine, function ($q) {
            $q->where('user_id', Auth::id());
        });

        if ($this->sort === 'name') {
            $query->join('users', 'events.user_id', '=', 'users.id')
                  ->select('events.*')
                  ->orderBy('users.name', $this->direction)
                  ->orderBy('users.family_name1', $this->direction);
        } else {
            $query->orderBy($this->sort, $this->direction);
        }

        $events = $query->paginate($this->qtytoshow);

        \Log::info('getEvents resultados', [
            'event_count' => $events->count(),
            'total_events' => $events->total(),
            'first_event_id' => $events->count() > 0 ? $events->first()->id : null,
            'first_event_team_id' => $events->count() > 0 ? $events->first()->team_id : null,
            'first_event_user_id' => $events->count() > 0 ? $events->first()->user_id : null,
        ]);
        
        return $events;
    }

    /**
     * Render the component view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Recargar equipo actual del usuario por si cambió con el selector
        $this->user = Auth::user();
        $currentTeam = $this->user->currentTeam;

        // Ensure currentTeam is not null before proceeding
        if ($currentTeam && (!$this->team || $this->team->id !== $currentTeam->id)) {
            $this->team = $currentTeam;
            $this->teamUserList = $this->team ? $this->team->allUsers() : collect();
            $this->eventTypes = $this->team ? $this->team->eventTypes : collect();
            $this->isTeamAdmin = $this->user->isTeamAdmin();
            $this->isInspector = $this->user->isInspector();

            if ($this->team && ($this->isTeamAdmin || $this->isInspector)) {
                $this->teamUsers = $this->team->allUsers()->pluck('id')->toArray();
            } else {
                $this->teamUsers = [$this->user->id];
            }
        }
        
        \Log::info('GetTimeRegisters - render()', [
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'current_team_id' => $currentTeam ? $currentTeam->id : null,
            'current_team_name' => $currentTeam ? $currentTeam->name : null,
            'component_team_id' => $this->team ? $this->team->id : null,
            'component_team_name' => $this->team ? $this->team->name : null,
            'team_changed' => (!$this->team || $this->team->id !== $currentTeam->id),
        ]);
        
        // Si el equipo cambió, actualizar todo el contexto
        if (!$this->team || $this->team->id !== $currentTeam->id) {
            \Log::info('GetTimeRegisters - Actualizando contexto de equipo', [
                'old_team_id' => $this->team ? $this->team->id : null,
                'new_team_id' => $currentTeam->id,
            ]);
            
            $this->team = $currentTeam;
            $this->teamUserList = $this->team ? $this->team->allUsers() : collect();
            $this->eventTypes = $this->team ? $this->team->eventTypes : collect();
            $this->isTeamAdmin = $this->user->isTeamAdmin();
            $this->isInspector = $this->user->isInspector();
            
            if ($this->team && ($this->isTeamAdmin || $this->isInspector)) {
                $this->teamUsers = $this->team->allUsers()->pluck('id')->toArray();
            } else {
                $this->teamUsers = [$this->user->id];
            }
            
            \Log::info('GetTimeRegisters - Contexto actualizado', [
                'teamUsers_count' => count($this->teamUsers),
                'teamUsers' => $this->teamUsers,
                'isTeamAdmin' => $this->isTeamAdmin,
                'isInspector' => $this->isInspector,
            ]);
        }
        
        $events = $this->getEvents();
        return view('livewire.events.get-time-registers')
            ->with('events', $events)
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
     * Cap the value at 100 to prevent DoS attacks.
     *
     * @param mixed $value The new quantity value
     * @return void
     */
    public function updatingQtytoshow($value): void
    {
        // Cap the value at 100 to prevent DoS attacks
        // Ensure minimum of 1 and cast to integer for type safety
        $this->qtytoshow = (string) min(100, max(1, (int)$value));
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
     * Show the event modal with the event details.
     *
     * @param int $eventId
     * @return void
     */
    public function showEventModal(int $eventId): void
    {
        $this->selectedEvent = Event::with(['eventType', 'authorizedBy'])->findOrFail($eventId);
        $this->showEventModal = true;
    }

    /**
     * Get the color for an event based on its type and properties.
     *
     * @param Event $event
     * @return string
     */
    public function getEventColor(Event $event): string
    {
        $defaultColor = '#3788d8';
        
        if ($event->is_exceptional) {
            // Use special event color if event is exceptional
            return $this->team->special_event_color ?? '#DC2626';
        } elseif ($event->eventType) {
            if ($event->eventType->color) {
                // Use event type color if available
                return $event->eventType->color;
            } elseif (!$event->eventType->is_workday_type) {
                // Use special event color for non-workday types without specific color
                return $this->team->special_event_color ?? '#EA8000';
            }
        } else {
            // Use special event color for events without type
            return $this->team->special_event_color ?? '#EA8000';
        }
        
        return $defaultColor;
    }
}
