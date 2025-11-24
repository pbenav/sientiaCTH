<?php

namespace App\Http\Controllers;

use App\Exports\EventsExport;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    public function preview(Request $request)
    {
        $user = Auth::user();
        $team = $user->currentTeam;
        
        $workerId = $request->input('worker');
        $fromDate = $request->input('fromdate');
        $toDate = $request->input('todate');
        $eventTypeId = $request->input('event_type_id');

        $start = \Carbon\Carbon::parse($fromDate);
        $end = \Carbon\Carbon::parse($toDate);

        if ($start->diffInDays($end) > 92) {
            abort(400, __('The date range cannot exceed 3 months.'));
        }

        // Security check: only admins/inspectors can see other workers
        if (!$user->isTeamAdmin() && !$user->isInspector() && $workerId != $user->id) {
            abort(403);
        }

        $query = Event::query()
            ->with(['user', 'eventType'])
            ->whereDate('start', '>=', $fromDate)
            ->whereDate('end', '<=', $toDate);

        if ($workerId && $workerId !== '%') {
            $query->where('user_id', $workerId);
        } else {
            // If "All" selected, ensure we only show team users
            $teamUserIds = $team->allUsers()->pluck('id');
            $query->whereIn('user_id', $teamUserIds);
        }

        if ($eventTypeId && $eventTypeId !== 'All') {
            $query->where('event_type_id', $eventTypeId);
        }

        $events = $query->orderBy('start')->get();

        $pdf = Excel::raw(new \App\Exports\EventsPdfExport($events), \Maatwebsite\Excel\Excel::DOMPDF);

        $fn = 'cth_informe_' . date('YmdHis') . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fn . '"',
        ]);
    }
}
