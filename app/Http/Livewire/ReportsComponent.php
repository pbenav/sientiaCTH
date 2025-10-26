<?php

namespace App\Http\Livewire;

use App\Models\Team;
use App\Models\User;
use Livewire\Component;
use App\Exports\EventsExport;
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
    public int $worker;
    public string $fromdate;
    public string $todate;
    public $event_type_id;
    public string $rtype;
    public array $rtypes = ["PDF" => "Dompdf", "XLS" => "Xls", "CSV" => "Csv", "ODS" => "Ods", "HTML" => "Html"];
    public $eventTypes;

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
        $params = [
            "worker" => $this->worker,
            "fromdate" => $this->fromdate,
            "todate" => $this->todate,
            "event_type_id" => $this->event_type_id,
        ];

        $this->validate();

        $ext = strtoLower($this->rtype);
        $fn = 'events_' . date('ymdhms'). '.' . $ext;

        return Excel::download(new EventsExport($params), $fn, $this->rtypes[$this->rtype]);
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
