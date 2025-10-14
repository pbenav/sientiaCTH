<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PunchController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'work_center_id' => 'required|exists:work_centers,id',
            'timestamp' => 'required|date',
            'type' => 'required|in:IN,OUT',
        ]);

        $event = new \App\Models\Event([
            'user_id' => auth()->id(),
            'work_center_id' => $request->work_center_id,
            'start' => $request->timestamp,
            'event_type_id' => $request->type == 'IN' ? 1 : 2,
            'description' => $request->type == 'IN' ? 'Entrada' : 'Salida',
        ]);

        $event->save();

        return response()->json($event, 201);
    }
}
