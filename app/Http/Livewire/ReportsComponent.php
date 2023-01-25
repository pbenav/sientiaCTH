<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Exports\EventsExport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;


class ReportsComponent extends Component
{
    public $user;
    public $team;
    public $isTeamAdmin;
    public $isInspector;
    public $workers = [];
    public $worker;
    public $fromdate;
    public $todate;
    public $description;
    public $rtype;
    public $rtypes = ["XLS" => "Xls", "PDF" => "Dompdf", "CSV" => "Csv", "ODS" => "Ods", "HTML" => "Html"];
    public $descriptions = ["All", "Workday", "Pause", "Others"];

    protected $rules = [
        "worker" => 'required',
        "fromdate" => 'bail|required|date|before_or_equal:todate',
        "todate" => 'required|date|before_or_equal:now',
        "description" => 'required|string',
        "rtype" => 'required',
    ];

    protected $validationAttributes = [
            'fromdate' => 'fecha desde',
            'todate' => 'fecha hasta',            
        ];

    protected $messages = [
            'fromdate.before_or_equal' => 'La fecha desde debe ser anterior a hasta.',            
            'todate.before_or_equal' => 'La fecha hasta debe ser como máximo la de hoy.',
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
        $this->user = Auth::user();
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
        $this->rtype = 'XLS';
    }

    public function render()
    {
        return view('livewire.reports')->with([
            'workers' => $this->workers,
        ]);
    }
}