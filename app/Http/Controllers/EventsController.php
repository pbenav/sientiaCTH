<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\EventsExport;
use Maatwebsite\Excel\Facades\Excel;

class EventsController extends Controller
{
    public function export() 
    {
        return Excel::download(new EventsExport, 'events.xlsx');
    }
}
