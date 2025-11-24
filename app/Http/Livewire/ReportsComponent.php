<?php

namespace App\Http\Livewire;

use App\Models\Team;
use App\Models\User;
use App\Models\Event;
use Livewire\Component;
use App\Exports\EventsExport;
use App\Exports\EventsPdfExport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

/**
 * A Livewire component for generating and exporting reports.
 *
 * This component provides a form for users to select a worker, date range,
 * event type, and report type, and then export the corresponding data.
 */
class ReportsComponent extends Component
{
    public User $user;
    public Team $team;
    public bool $isTeamAdmin;
    public bool $isInspector;
    public $workers;
    public $worker;
    public string $fromdate;
    public string $todate;
    public $event_type_id;
    public string $rtype;
    public array $rtypes = [
        "PDF" => \Maatwebsite\Excel\Excel::DOMPDF,
        "XLS" => \Maatwebsite\Excel\Excel::XLSX,
        "CSV" => \Maatwebsite\Excel\Excel::CSV,
        "ODS" => \Maatwebsite\Excel\Excel::ODS,
        "HTML" => \Maatwebsite\Excel\Excel::HTML,
    ];
    public $eventTypes;
    public $pdfUrl = null;

    /**
     * The validation rules for the component.
     *
     * @var array
     */
    protected $rules = [
        "worker" => 'required',
        "fromdate" => 'bail|required|date|before_or_equal:todate',
        "todate" => 'required|date|before_or_equal:now',
        "event_type_id" => 'required',
        "rtype" => 'required',
    ];

    /**
     * Mounts the component and initializes necessary data.
     *
     * This method is called once when the component is initialized.
     * It sets up user, team, permissions, workers, and date range.
     */
    public function mount()
    {
        $this->user = User::find(Auth::user()->id);
        $this->team = $this->user->currentTeam;
        $this->isTeamAdmin = $this->user->isTeamAdmin();
        $this->isInspector = $this->user->isInspector();
        if ($this->isTeamAdmin || $this->isInspector) {
            $this->workers = $this->team->allUsers();
        }
        $this->worker = $this->user->id;
        $this->fromdate = date('Y-m-01');
        $this->todate = date('Y-m-d');
        $this->eventTypes = $this->team->eventTypes;
        $this->event_type_id = 'All';
        $this->rtype = 'PDF';
    }

    /**
     * Validates a single property of the component.
     *
     * @param string $propertyName The name of the property to validate.
     * @return void
     */
    public function updated(string $propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Exports the report based on selected parameters.
     *
     * This method validates the input data, constructs the filename, and returns the Excel file download.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        $this->validate();

        $start = \Carbon\Carbon::parse($this->fromdate);
        $end = \Carbon\Carbon::parse($this->todate);

        if ($start->diffInDays($end) > 92) { // Approx 3 months
             $this->addError('todate', __('The date range cannot exceed 3 months.'));
             return;
        }

        $query = Event::query()
            ->with(['user', 'eventType'])
            ->whereDate('start', '>=', $this->fromdate)
            ->whereDate('end', '<=', $this->todate);

        if ($this->worker && $this->worker !== '%') {
            $query->where('user_id', $this->worker);
        } else {
             $teamUserIds = $this->team->allUsers()->pluck('id');
             $query->whereIn('user_id', $teamUserIds);
        }

        if ($this->event_type_id && $this->event_type_id !== 'All') {
            $query->where('event_type_id', $this->event_type_id);
        }

        $events = $query->orderBy('start')->get();

        $ext = strtoLower($this->rtype);
        $fn = 'cth_informe_' . date('YmdHis'). '.' . $ext;

        if ($this->rtype === 'PDF') {
            return Excel::download(new EventsPdfExport($events), $fn, $this->rtypes[$this->rtype]);
        }

        return Excel::download(new EventsExport($events), $fn, $this->rtypes[$this->rtype]);
    }

    public function generatePreview()
    {
        $this->validate();

        $start = \Carbon\Carbon::parse($this->fromdate);
        $end = \Carbon\Carbon::parse($this->todate);

        if ($start->diffInDays($end) > 92) {
             $this->addError('todate', __('The date range cannot exceed 3 months.'));
             return;
        }
        
        $this->pdfUrl = route('reports.preview', [
            'worker' => $this->worker, 
            'fromdate' => $this->fromdate, 
            'todate' => $this->todate, 
            'event_type_id' => $this->event_type_id
        ]);
    }

    /**
     * Renders the component view.
     *
     * This method is responsible for rendering the Livewire component's view and passing necessary data to it.
     *
     * @return \Illuminate\View\View The rendered view.
     */
    public function render()
    {
        return view('livewire.reports.reports')->with([
            'workers' => $this->workers,
        ]);
    }
}
