<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AuditLogComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $selectedLog = null;
    public $confirmingLogView = false;
    public $dateFrom;
    public $dateTo;

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function mount()
    {
        $this->dateFrom = now()->subMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function viewLog($id)
    {
        $log = DB::table('events_history')
            ->where('id', $id)
            ->first();

        // Parse user details 
        if ($log) {
            $this->selectedLog = (array) $log;
            
            $user = User::find($this->selectedLog['user_id']);
            $this->selectedLog['user_name'] = $user ? $user->name . ' ' . $user->family_name1 : 'Unknown User (' . $this->selectedLog['user_id'] . ')';
            
            // Format dates
            $this->selectedLog['formatted_date'] = \Carbon\Carbon::parse($this->selectedLog['created_at'])->format('d/m/Y H:i:s');
            
            // Extract event information from JSON
            $eventData = json_decode($this->selectedLog['modified_event'] ?? $this->selectedLog['original_event'], true);
            if ($eventData && is_array($eventData)) {
                // Event ID
                if (isset($eventData['id'])) {
                    $this->selectedLog['event_id'] = $eventData['id'];
                }
                
                // Affected user (user_id from event, not the one who modified)
                if (isset($eventData['user_id'])) {
                    $affectedUser = User::find($eventData['user_id']);
                    $this->selectedLog['affected_user'] = $affectedUser 
                        ? $affectedUser->name . ' ' . $affectedUser->family_name1 
                        : 'User ID: ' . $eventData['user_id'];
                }
                
                // Team
                if (isset($eventData['team_id'])) {
                    $team = \App\Models\Team::find($eventData['team_id']);
                    $this->selectedLog['team_name'] = $team ? $team->name : 'Team ID: ' . $eventData['team_id'];
                }
                
                // Work Center
                if (isset($eventData['work_center_id'])) {
                    $workCenter = \App\Models\WorkCenter::find($eventData['work_center_id']);
                    $this->selectedLog['work_center_name'] = $workCenter 
                        ? $workCenter->name . ' (' . $workCenter->code . ')' 
                        : 'Work Center ID: ' . $eventData['work_center_id'];
                }
            }
        } else {
            $this->selectedLog = null;
        }

        $this->confirmingLogView = true;
    }

    public function formatDiff($original, $modified)
    {
        $originalData = json_decode($original, true) ?? [];
        $modifiedData = json_decode($modified, true) ?? [];

        if (!is_array($originalData) || !is_array($modifiedData)) {
            return [];
        }

        $differences = [];

        // Check for changes and deletions
        foreach ($originalData as $key => $value) {
            if (array_key_exists($key, $modifiedData)) {
                if ($modifiedData[$key] != $value) {
                    $originalStr = $this->formatFieldValue($key, $value);
                    $modifiedStr = $this->formatFieldValue($key, $modifiedData[$key]);
                    $differences[$key] = [
                        'type' => 'changed',
                        'original' => $originalStr,
                        'modified' => $modifiedStr
                    ];
                }
            } else {
                $originalStr = $this->formatFieldValue($key, $value);
                $differences[$key] = [
                    'type' => 'deleted',
                    'original' => $originalStr,
                    'modified' => null
                ];
            }
        }

        // Check for additions
        foreach ($modifiedData as $key => $value) {
            if (!array_key_exists($key, $originalData)) {
                $modifiedStr = $this->formatFieldValue($key, $value);
                $differences[$key] = [
                    'type' => 'added',
                    'original' => null,
                    'modified' => $modifiedStr
                ];
            }
        }

        return $differences;
    }

    /**
     * Formatea el valor de un campo para mostrarlo de forma legible
     */
    private function formatFieldValue($key, $value)
    {
        // Si es null, retornar texto indicativo
        if ($value === null) {
            return '-';
        }

        // Si es array, convertir a JSON
        if (is_array($value)) {
            return json_encode($value);
        }

        // Formatear campos específicos
        switch ($key) {
            case 'authorized_by_id':
                if ($value) {
                    $user = User::find($value);
                    return $user ? $user->name . ' ' . $user->family_name1 : 'Usuario ID: ' . $value;
                }
                return '-';
            
            case 'user_id':
            case 'created_by':
            case 'updated_by':
                if ($value) {
                    $user = User::find($value);
                    return $user ? $user->name . ' ' . $user->family_name1 : 'Usuario ID: ' . $value;
                }
                return '-';
            
            case 'team_id':
                if ($value) {
                    $team = \App\Models\Team::find($value);
                    return $team ? $team->name : 'Equipo ID: ' . $value;
                }
                return '-';
            
            case 'work_center_id':
                if ($value) {
                    $workCenter = \App\Models\WorkCenter::find($value);
                    return $workCenter ? $workCenter->name . ' (' . $workCenter->code . ')' : 'Centro ID: ' . $value;
                }
                return '-';
            
            case 'event_type_id':
                if ($value) {
                    $eventType = \App\Models\EventType::find($value);
                    return $eventType ? $eventType->name : 'Tipo ID: ' . $value;
                }
                return '-';
            
            case 'is_open':
                return $value ? __('Abierto') : __('Cerrado');
            
            case 'is_authorized':
                return $value ? __('Autorizado') : __('No autorizado');
            
            case 'is_extra_hours':
                return $value ? __('Sí') : __('No');
            
            default:
                return (string)$value;
        }
    }

    public function render()
    {
        $user = Auth::user();
        
        $query = DB::table('events_history');

        // Apply filters
        if ($this->search) {
            $query->where(function($q) {
                $q->where('tablename', 'like', '%'.$this->search.'%')
                  ->orWhere('original_event', 'like', '%'.$this->search.'%')
                  ->orWhere('modified_event', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        // Role based filtering + Team filtering
        if ($user->is_admin) {
            // Global admin sees all (but can still filter by team if needed)
            if ($user->currentTeam) {
                $teamId = $user->currentTeam->id;
                $query->where(function($q) use ($teamId) {
                    $q->whereRaw("JSON_EXTRACT(modified_event, '$.team_id') = ?", [$teamId])
                      ->orWhereRaw("JSON_EXTRACT(original_event, '$.team_id') = ?", [$teamId]);
                });
            }
        } elseif ($user->isTeamAdmin() || $user->isInspector()) {
            // Filter by current team's events
            if ($user->currentTeam) {
                $teamId = $user->currentTeam->id;
                $query->where(function($q) use ($teamId) {
                    $q->whereRaw("JSON_EXTRACT(modified_event, '$.team_id') = ?", [$teamId])
                      ->orWhereRaw("JSON_EXTRACT(original_event, '$.team_id') = ?", [$teamId]);
                });
            } else {
                // No team, see nothing
                $query->where('id', -1);
            }
        } else {
            // Regular users shouldn't be here
            $query->where('id', -1);
        }

        $logs = $query->orderBy('created_at', 'desc')
                      ->paginate($this->perPage);

        // Extract event information from JSON for each log
        foreach ($logs as $log) {
            $eventData = json_decode($log->modified_event ?? $log->original_event, true);
            
            if ($eventData && is_array($eventData)) {
                $log->event_id = $eventData['id'] ?? null;
                $log->event_type_id = $eventData['event_type_id'] ?? null;
                $log->affected_user_id = $eventData['user_id'] ?? null;
                $log->team_id = $eventData['team_id'] ?? null;
                $log->work_center_id = $eventData['work_center_id'] ?? null;
            }
        }

        // Batch load related data
        $userIds = $logs->pluck('user_id')->unique()->filter();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        $eventTypeIds = $logs->pluck('event_type_id')->unique()->filter();
        $eventTypes = \App\Models\EventType::whereIn('id', $eventTypeIds)->get()->keyBy('id');

        $affectedUserIds = $logs->pluck('affected_user_id')->unique()->filter();
        $affectedUsers = User::whereIn('id', $affectedUserIds)->get()->keyBy('id');

        $workCenterIds = $logs->pluck('work_center_id')->unique()->filter();
        $workCenters = \App\Models\WorkCenter::whereIn('id', $workCenterIds)->get()->keyBy('id');

        // Enrich logs with names
        foreach ($logs as $log) {
            // Modified by user
            $u = $users->get($log->user_id);
            $log->user_name = $u ? $u->name . ' ' . $u->family_name1 : 'Unknown (' . $log->user_id . ')';
            
            // Event type
            if ($log->event_type_id) {
                $et = $eventTypes->get($log->event_type_id);
                $log->event_type_name = $et ? $et->name : 'ID: ' . $log->event_type_id;
            }
            
            // Affected user
            if ($log->affected_user_id) {
                $au = $affectedUsers->get($log->affected_user_id);
                $log->affected_user_name = $au ? $au->name . ' ' . $au->family_name1 : 'ID: ' . $log->affected_user_id;
            }
            
            // Work center
            if ($log->work_center_id) {
                $wc = $workCenters->get($log->work_center_id);
                $log->work_center_name = $wc ? $wc->name : 'ID: ' . $log->work_center_id;
            }
        }

        return view('livewire.audit-log-component', [
            'logs' => $logs
        ]);
    }
}
