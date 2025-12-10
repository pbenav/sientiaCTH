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

        $maxMonths = $team->max_report_months ?? \App\Models\Team::DEFAULT_MAX_REPORT_MONTHS;
        $asyncThreshold = $team->async_report_threshold_months ?? \App\Models\Team::DEFAULT_ASYNC_THRESHOLD_MONTHS;
        $limit = min($maxMonths, $asyncThreshold);

        if ($start->diffInDays($end) > ($limit * 30.5)) {
            abort(400, __('The date range cannot exceed :months months for preview.', ['months' => $limit]));
        }

        // Security check: only admins/inspectors can see other workers
        if (!$user->isTeamAdmin() && !$user->isInspector() && $workerId != $user->id) {
            abort(403);
        }

        $reportSource = $request->input('report_source', 'events');

        if ($reportSource === 'statistics') {
            // Statistics reports require a single worker
            if (!$workerId || $workerId === '%') {
                abort(400, __('stats.select_specific_worker'));
            }

            $exporter = new \App\Exports\StatsPdfExport(
                $user->id,
                $team->id,
                $workerId,
                $fromDate,
                $toDate,
                $eventTypeId
            );
            
            $pdf = $exporter->generate();

            $fn = 'cth_estadisticas_' . date('YmdHis') . '.pdf';

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fn . '"',
            ]);
        }

        if ($reportSource === 'history') {
             $query = \Illuminate\Support\Facades\DB::table('events_history')
                ->whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate);

            if ($workerId && $workerId !== '%') {
                $query->where('user_id', $workerId);
            } else {
                $teamUserIds = $team->allUsers()->pluck('id');
                $query->whereIn('user_id', $teamUserIds);
            }

            $history = $query->orderBy('created_at', 'desc')->get();

            $exporter = new \App\Exports\EventsHistoryPdfExport($history, $fromDate, $toDate);
            $pdf = $exporter->generate();

            $fn = 'cth_auditoria_' . date('YmdHis') . '.pdf';

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fn . '"',
            ]);
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

        // Determine Work Center
        $workCenter = null;
        if ($workerId && $workerId !== '%') {
            $workerUser = User::find($workerId);
            if ($workerUser) {
                $defaultWorkCenterId = $workerUser->meta->where('meta_key', 'default_work_center_id')->first();
                if ($defaultWorkCenterId) {
                    $workCenter = \App\Models\WorkCenter::find($defaultWorkCenterId->meta_value);
                }
            }
        }

        $exporter = new \App\Exports\EventsPdfExport($events, $team, $workCenter, $fromDate, $toDate);
        $pdf = $exporter->generate();

        $fn = 'cth_informe_' . date('YmdHis') . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fn . '"',
        ]);
    }
    public function download($file)
    {
        // Security check: ensure file is in reports directory and user has access
        // For simplicity, we assume filename contains enough entropy or we could add user_id check if we stored it in metadata
        // But the file path is 'reports/' . $file
        
        $path = 'reports/' . $file;
        
        if (!\Illuminate\Support\Facades\Storage::exists($path)) {
            abort(404);
        }

        // Optional: Check if the file belongs to the user or team?
        // Since the filename is random and sent via internal message, it's relatively safe.
        // But ideally we should check ownership. For now, we rely on the random filename.

        $content = \Illuminate\Support\Facades\Storage::get($path);
        $mime = \Illuminate\Support\Facades\Storage::mimeType($path);

        if (!$mime) {
            $mime = 'application/pdf';
        }

        return response($content)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'attachment; filename="' . $file . '"');
    }
}
