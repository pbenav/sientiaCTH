<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EventType;

class PunchController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'work_center_id' => 'required|exists:work_centers,id',
            'timestamp' => 'required|date',
            'type' => 'required|in:IN,OUT',
        ]);

        $workCenter = auth()->user()->currentTeam->workCenters()->find($request->work_center_id);

        if (!$workCenter) {
            return response()->json(['message' => 'Invalid work center'], 422);
        }

        $workEventType = auth()->user()->currentTeam
            ->eventTypes()
            ->where('is_workday_type', true)
            ->first();

        $event = new \App\Models\Event([
            'user_id' => auth()->id(),
            'work_center_id' => $request->work_center_id,
            'start' => $request->timestamp,
            'event_type_id' => optional($workEventType)->id,
            'description' => $request->type == 'IN' ? __('Check In') : __('Check Out'),
        ]);

        $event->save();

        return response()->json($event, 201);
    }
}
