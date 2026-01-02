<?php

namespace App\Http\Livewire;

use App\Models\Team;
use App\Models\User;
use App\Models\Event;
use App\Notifications\EventAuthorized;
use App\Notifications\EventDeAuthorized;
use App\Traits\InsertHistory;
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
    use InsertHistory;

    public bool $showFiltersModal = false;
    public string $search = '';
    public Event $filter;
    public string $sort = 'start';
    public string $direction = 'desc';
    public string $qtytoshow = '10';
    public bool $readyonload = false;
    public ?User $user;
    public ?Team $team;
    public array $teamUsers;
    public $teamUserList;
    public $eventTypes;
    public bool $isTeamAdmin = false;
    public bool $isInspector = false;
    public bool $confirmed = false;
    public bool $filtered = false;
    public bool $showOnlyMine = false;

    
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

    public ?int $targetEventId = null;

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
        'filterEventTypeId' => ['except' => null, 'as' => 'type'],
        'targetEventId' => ['except' => null, 'as' => 'event_id']
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
        
        // Crear objeto filter sincronizado con las propiedades individuales
        $this->filter = new Event([
            "start" => $this->filterStart,
            "end" => $this->filterEnd,
            "user_id" => $this->filterUserId,
            "is_open" => false,
            "event_type_id" => $this->filterEventTypeId,
        ]);
        
        $this->user = Auth::user();
        $this->team = $this->user ? $this->user->currentTeam : null;
        $this->teamUserList = $this->team ? $this->team->allUsers()->sortBy(function ($user) {
            return strtolower(($user->name ?? '') . ' ' . ($user->family_name ?? '') . ' ' . ($user->family_name2 ?? ''));
        })->values() : collect();
        $this->eventTypes = $this->team ? $this->team->eventTypes : collect();
        $this->isTeamAdmin = $this->user->isTeamAdmin() || $this->user->is_admin;
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

        // Limit maximum events to show per page to 100
        if ($this->qtytoshow > 100) {
            $this->qtytoshow = '100';
        }

        // Open edit modal if event_id is present in URL
        if ($this->targetEventId) {
            // We need to defer this call slightly to ensure the view is ready to receive the event
            // But since we are in mount, we can't easily defer. 
            // However, emitTo works if the component is rendered in the same request.
            // Let's try emitting directly to the new modal.
            $this->showEvent($this->targetEventId);
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
        $ev = Event::with('eventType')->find($eventId);
        if (!$ev) {
            \Log::info('GetTimeRegisters::edit - Event not found', ['eventId' => $eventId]);
            return;
        }
        \Log::info('GetTimeRegisters::edit - Emitting to edit-event', ['eventId' => $ev->id]);
        $this->emitTo('edit-event', 'edit', $ev->id);
    }

    /**
     * Show event details modal (Deep Link or View).
     *
     * @param int $eventId The event ID to show.
     * @return void
     */
    public function showEvent($eventId): void
    {
        \Log::info('GetTimeRegisters::showEvent called', ['eventId' => $eventId]);
        $ev = Event::with(['user', 'eventType', 'workCenter'])->find($eventId);
        if (!$ev) return;
        
        $this->emit('showEventDetails', $ev->id);
    }


    /**
     * Confirm an event based on user role and event status.
     *
     * @param \App\Models\Event $ev The event to confirm.
     * @return void
     */
    public function confirm($eventId): void
    {
        $ev = Event::with('user')->find($eventId);
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
     * Close the filters modal without resetting values.
     * This allows the user to reopen the modal with the same filters.
     *
     * @return void
     */
    public function closeFiltersModal(): void
    {
        $this->showFiltersModal = false;
    }

    /**
     * Deactivate filters and reset all filter values to defaults.
     * This is called when the user explicitly wants to remove the filters.
     *
     * @return void
     */
    public function unsetFilter(): void
    {
        $this->showFiltersModal = false;
        $this->filtered = false;
        $this->confirmed = false;
        $this->resetFilters();
    }

    /**
     * Reset all filters to default values
     */
    protected function resetFilters(): void
    {
        $this->search = '';
        $this->filterStart = '';
        $this->filterEnd = '';
        $this->filterUserId = null;
        $this->filterEventTypeId = null;
        
        // Sincronizar con el objeto filter
        $this->filter->start = '';
        $this->filter->end = '';
        $this->filter->user_id = null;
        $this->filter->event_type_id = null;
    }

    /**
     * Open the filters modal.
     * If filters are already set, keep them. Otherwise, set default dates.
     *
     * @return void
     */
    public function openFiltersModal(): void
    {
        // Solo establecer fechas por defecto si están vacías
        if (empty($this->filterStart)) $this->filterStart = date('Y-m-01');
        if (empty($this->filterEnd)) $this->filterEnd = date('Y-m-t');
        
        // Sincronizar con el objeto filter
        $this->filter->start = $this->filterStart;
        $this->filter->end = $this->filterEnd;
        $this->filter->user_id = $this->filterUserId;
        $this->filter->event_type_id = $this->filterEventTypeId;

        $this->showFiltersModal = true;
    }
    
    /**
     * Apply the filters and close the modal.
     *
     * @return void
     */
    public function applyFiltersFromModal(): void
    {
        // Sincronizar propiedades individuales con el objeto filter
        $this->filterStart = $this->filter->start;
        $this->filterEnd = $this->filter->end;
        $this->filterUserId = $this->filter->user_id;
        $this->filterEventTypeId = $this->filter->event_type_id;
        
        $this->filtered = true;
        $this->confirmed = false;
        $this->showFiltersModal = false;
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
    /**
     * Retrieve and filter events based on the current settings.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getEvents()
    {
        if (!$this->readyonload) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->qtytoshow);
        }

        $query = Event::query()->with(['user', 'eventType']);
        $this->applyFilters($query);

        // Sorting
        if ($this->sort === 'name') {
            $query->join('users', 'events.user_id', '=', 'users.id')
                ->select('events.*')
                ->orderBy('users.name', $this->direction)
                ->orderBy('users.family_name1', $this->direction);
        } else {
            // Default sort or specific column sort
            $query->orderBy($this->sort, $this->direction);
        }
        
        // Secondary sort to ensure deterministic order
        if ($this->sort !== 'start') {
            $query->orderBy('start', 'desc');
        }

        return $query->paginate($this->qtytoshow);
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
            $this->teamUserList = $this->team ? $this->team->allUsers()->sortBy(function ($user) {
                return strtolower(($user->name ?? '') . ' ' . ($user->family_name ?? '') . ' ' . ($user->family_name2 ?? ''));
            })->values() : collect();
            $this->eventTypes = $this->team ? $this->team->eventTypes : collect();
            $this->isTeamAdmin = $this->user->isTeamAdmin() || $this->user->is_admin;
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
            $this->teamUserList = $this->team ? $this->team->allUsers()->sortBy(function ($user) {
                return strtolower(($user->name ?? '') . ' ' . ($user->family_name ?? '') . ' ' . ($user->family_name2 ?? ''));
            })->values() : collect();
            $this->eventTypes = $this->team ? $this->team->eventTypes : collect();
            $this->isTeamAdmin = $this->user->isTeamAdmin() || $this->user->is_admin;
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
        
        // Calculate summary statistics
        // Note: calculateSummary uses applyFilters internally
        $summary = $this->calculateSummary();
        
        return view('livewire.events.get-time-registers')
            ->with('events', $events)
            ->with('isTeamAdmin', $this->isTeamAdmin)
            ->with('isInspector', $this->isInspector)
            ->with('summary', $summary);
    }

    /**
     * Calculate summary statistics for the filtered events
     *
     * @return array
     */
    private function calculateSummary(): array
    {
        if (!$this->readyonload) {
            return ['workedSeconds' => 0, 'pauseSeconds' => 0, 'netSeconds' => 0];
        }

        // Get all events matching current filters (without pagination)
        $query = Event::query()->with(['eventType']);
        $this->applyFilters($query);

        $allEvents = $query->get();

        $workedSeconds = 0;
        $pauseSeconds = 0;

        foreach ($allEvents as $event) {
            if (!$event->end) continue;

            $start = \Carbon\Carbon::parse($event->start);
            $end = \Carbon\Carbon::parse($event->end);
            $duration = $start->diffInSeconds($end);

            if ($event->eventType && $event->eventType->is_pause_type) {
                $pauseSeconds += $duration;
            } else {
                $workedSeconds += $duration;
            }
        }

        return [
            'workedSeconds' => $workedSeconds,
            'pauseSeconds' => $pauseSeconds,
            'netSeconds' => $workedSeconds - $pauseSeconds,
        ];
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

        // Clonar el evento original para auditoría
        $originalEvent = clone $event;
        
        $event->is_authorized = !$event->is_authorized;
        $event->is_open = !$event->is_authorized;

        if ($event->is_authorized) {
            $event->authorized_by_id = Auth::id();
        } else {
            $event->authorized_by_id = null;
        }

        $event->save();
        
        // Registrar auditoría SIEMPRE para cambios de autorización
        $this->insertHistory('events', $originalEvent, $event, true);
        unset($originalEvent);

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

    /**
     * Apply common filters to the query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyFilters($query)
    {
        $query->whereIn('user_id', $this->teamUsers)
              ->where('team_id', $this->team->id);

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

        $query->when($this->filtered, function ($q) {
            $q->when($this->filter->start, fn($query) => $query->whereDate('start', '>=', $this->filter->start))
              ->when($this->filter->end, fn($query) => $query->whereDate('end', '<=', $this->filter->end))
              ->when($this->filter->user_id, fn($query) => $query->where('user_id', $this->filter->user_id))
              ->when($this->filter->is_open, fn($query) => $query->where('is_open', '1'))
              ->when($this->filter->event_type_id, fn($query) => $query->where('event_type_id', $this->filter->event_type_id));
        });

        $query->when($this->confirmed, function ($q) {
            $q->where('is_open', '=', '1');
        });

        $query->when($this->showOnlyMine, function ($q) {
            $q->where('user_id', Auth::id());
        });

        return $query;
    }
}
