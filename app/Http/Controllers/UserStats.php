<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Charts\TimeRegistersChart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserStats extends Controller
{
    public function index()
    {
        $data = DB::table('events')->select('user_id', 'start', DB::raw('TIMESTAMPDIFF(hour, start, end) as duration'))
            ->where('user_id', Auth::user()->id)           
            ->get()
            ->pluck('duration','start');
        
        $chart = new TimeRegistersChart;
        $chart->labels($data->keys());
        $chart->dataset('Events', 'bar', $data->values());

        return view('user-stats', compact('chart'));
    }
}
