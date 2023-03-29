<?php

namespace App\Http\Livewire;

use App\Models\Team;
use App\Models\User;
use Livewire\Component;
use App\Exports\EventsExport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;


class ReportsComponent extends Component
{
    public User $user;
    public Team $team;
    public bool $isTeamAdmin;
    public bool $isInspector;
    public $workers;
    public $worker;
    public $fromdate;
    public $todate;
    public string $description;
    public string $rtype;
    public $rtypes = ["PDF" => "Dompdf", "XLS" => "Xls", "CSV" => "Csv", "ODS" => "Ods", "HTML" => "Html"];
    public $descriptions = ["All", "Workday", "Pause", "Others"];

    protected $rules = [
        "worker" => 'required',
        "fromdate" => 'bail|required|date|before_or_equal:todate',
        "todate" => 'required|date|before_or_equal:now',
        "description" => 'required|string',
        "rtype" => 'required',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function export()
    {   
        $params = [
            "worker" => $this->worker,
            "fromdate" => $this->fromdate,
            "todate" => $this->todate,
            "description" => __($this->description),
        ];

        $this->validate();
        
        $ext = strtoLower($this->rtype);
        $fn = 'events_' . date('ymdhms'). '.' . $ext;

        return Excel::download(new EventsExport($params), $fn, $this->rtypes[$this->rtype]);
    }

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
        $this->description = 'All';
        $this->rtype = 'PDF';
    }

    public function render()
    {
        return view('livewire.reports')->with([
            'workers' => $this->workers,
        ]);
    }
}