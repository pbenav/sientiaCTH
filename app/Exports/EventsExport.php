<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromCollection;

class EventsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()    
    {
        $datos = Event::with('user')->get();
        return $datos;
    }
}
