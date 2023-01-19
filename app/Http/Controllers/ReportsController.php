<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use App\Exports\EventsExport;
use Laravel\Jetstream\HasTeams;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Request;

class ReportsController extends Controller
{
    use HasTeams;

    public $filter;
    public $user;
    public $team;
    public $isTeamAdmin;
    public $isInspector;
    public $actualUser;
    public $browsedUser;
    public $workers = [];
   
    public function export(Request $r) 
    {        
        return Excel::download(new EventsExport($r), 'events.xlsx');
    }

    public function index()
    {
        $this->filter = new Event([
            "start" => date('Y-m-01'),
            "end" => date('Y-m-t'),
            "name" => "",
            "family_name1" => "",
            "is_open" => false,
            "description" => __('All'),
        ]);        
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
            'filter' => $this->filter,
            'isTeamAdmin' => $this->isTeamAdmin,
            'isInspector' => $this->isInspector
        ]);
    }
}
