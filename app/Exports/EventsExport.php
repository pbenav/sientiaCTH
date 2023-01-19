<?php

namespace App\Exports;

use App\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;

class EventsExport implements FromQuery
{

    use Exportable;


    public $worker;
    public $month;
    public $year;
    public $description;
    /**
     * @return \Illuminate\Support\Collection
     */
    public function __construct(Request $r)
    {
        $this->worker = $r->worker;
        $this->month = $r->month;
        $this->year = $r->year;
        $this->description = $r->description;
    }


    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation|\Illuminate\Database\Query\Builder
     */
    public function query()
    {

        return User::query()
            ->join('events', 'users.id', 'events.user_id' )
            ->where('users.id', $this->worker)
            ->select('users.name', 'users.family_name1', 'events.id', 'events.start', 'events.end', 'events.description', 'events.is_open');
    }
}