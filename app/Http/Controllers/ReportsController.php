<?php

namespace App\Http\Controllers;


use App\Models\Event;
use Illuminate\Http\Request;
use App\Exports\EventsExport;
use Laravel\Jetstream\HasTeams;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    use HasTeams;
    
    public $user;
    public $team;
    public $isTeamAdmin;
    public $isInspector;
    public $actualUser;
    public $browsedUser;
    public $workers = [];
   
    public function export(Request $r) 
    {        
        $fn = 'events' . date('ymdhms') . '.pdf';
        //dd($r);
        $params = [
            "worker" => $r->worker,
            "month" => $r->month,
            "year" => $r->year,
            "description" => $r->description
        ];
        return Excel::download(new EventsExport($params), $fn, \Maatwebsite\Excel\Excel::DOMPDF);
    }

    public function index()
    {
        $this->user = Auth::user();
        $this->team = $this->user->currentTeam;
        $this->isTeamAdmin = $this->user->isTeamAdmin();
        $this->isInspector = $this->user->isInspector();
        if ($this->isTeamAdmin || $this->isInspector) {
            $this->workers = $this->team->allUsers();
        } else {
            $this->workers = $this->user;
        }

        return view('reports')->with([
            'workers' => $this->workers,
            'team' => $this->team,
            'isTeamAdmin' => $this->isTeamAdmin,
            'isInspector' => $this->isInspector
        ]);
    }
}
