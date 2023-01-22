<?php

namespace App\Http\Controllers;


use App\Models\Event;
use Illuminate\Http\Request;
use App\Exports\EventsExport;
use Laravel\Jetstream\HasTeams;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    use HasTeams;

    public $user;
    public $team;
    public $isTeamAdmin;
    public $isInspector;
    public $workers = [];

    public function export(Request $r)
    {
        $fn = 'events' . date('ymdhms');

        $params = [
            "worker" => $r->worker,
            "fromdate" => $r->fromdate,
            "todate" => $r->todate,
            "description" => $r->description
        ];

        if (is_null($params['worker'])) {
            $params['worker'] = Auth::user()->id;
        };

        switch ($r->rtype) {
            case 'XLS':
                return Excel::download(new EventsExport($params), $fn, \Maatwebsite\Excel\Excel::XLS, ['X-Vapor-Base64-Encode' => 'True']);
                break;

            case 'PDF':
                return Excel::download(new EventsExport($params), $fn, \Maatwebsite\Excel\Excel::DOMPDF);
                break;

            case 'CSV':
                return Excel::download(new EventsExport($params), $fn, \Maatwebsite\Excel\Excel::CSV);
                break;

            case 'ODS':
                return Excel::download(new EventsExport($params), $fn, \Maatwebsite\Excel\Excel::ODS);
                break;

            case 'HTML':
                return Excel::download(new EventsExport($params), $fn, \Maatwebsite\Excel\Excel::HTML);
                break;

            default:
                return Excel::download(new EventsExport($params), $fn, \Maatwebsite\Excel\Excel::DOMPDF);
                break;
        }
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