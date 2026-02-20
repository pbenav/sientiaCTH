<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exports\EventsExport;
use App\Exports\EventsHistoryExport;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Reports Controller
 * 
 * Handles time tracking report generation with support for:
 * - PDF and Excel exports
 * - Configurable date ranges (with team-specific limits)
 * - Async report generation for large datasets
 * - Event grouping and filtering
 * - Multi-user report support
 * 
 * @version 1.0.0
 * @since 2025-01-10
 */
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
        $groupBy = $request->input('groupBy', 'none');
        $orderBy = $request->input('orderBy', 'start');

        $start = \Carbon\Carbon::parse($fromDate);
        $end = \Carbon\Carbon::parse($toDate);

        $maxMonths = $team->max_report_months ?? \App\Models\Team::DEFAULT_MAX_REPORT_MONTHS;
        $asyncThreshold = $team->async_report_threshold_months ?? \App\Models\Team::DEFAULT_ASYNC_THRESHOLD_MONTHS;
        
        // Check if this is a download request or just a preview
        $isDownload = $request->input('download', false);
        
        // For downloads, use the max limit. For previews, use the async threshold
        $limit = $isDownload ? $maxMonths : min($maxMonths, $asyncThreshold);

        if ($start->diffInDays($end) > ($limit * 30.5)) {
            $message = $isDownload 
                ? __('The date range cannot exceed :months months.', ['months' => $limit])
                : __('The date range cannot exceed :months months for preview.', ['months' => $limit]);
            abort(400, $message);
        }

        // Security check: only admins/inspectors can see other workers
        if (!$user->isTeamAdmin() && !$user->isInspector() && $workerId != $user->id) {
            abort(403);
        }

        $reportSource = $request->input('report_source', 'events');

        // Check for Chrome or similar browsers
        $userAgent = $request->header('User-Agent');
        $isChrome = false;
        if ($userAgent && (str_contains($userAgent, 'Chrome') || str_contains($userAgent, 'CriOS') || str_contains($userAgent, 'Chromium'))) {
            $isChrome = true;
        }

        if ($reportSource === 'statistics') {
            // Statistics reports require a single worker
            if (!$workerId || $workerId === '%') {
                abort(400, __('stats.select_specific_worker'));
            }

            // Check if async generation is needed for downloads
            if ($isDownload && $start->diffInDays($end) > ($asyncThreshold * 30.5)) {
                // Dispatch async job
                \App\Jobs\GenerateReportJob::dispatch(
                    $user->id,
                    $team->id,
                    $workerId,
                    $fromDate,
                    $toDate,
                    $eventTypeId,
                    'PDF',
                    'statistics', // reportSource parameter
                    'none', // groupBy
                    'start', // orderBy
                    $isChrome // isChrome
                );

                // Return HTML message instead of JSON
                $message = __('This report will be generated asynchronously and sent to your inbox.');
                $title = __('Please wait, the report may take a few minutes...');
                
                return response()->view('reports.async-message', [
                    'title' => $title,
                    'message' => $message
                ], 202);
            }

            try {
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
            } catch (\Exception $e) {
                return response("Error generando el informe de estadísticas PDF: " . $e->getMessage(), 500);
            }
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

            try {
                $exporter = new \App\Exports\EventsHistoryPdfExport($history, $fromDate, $toDate);
                $pdf = $exporter->generate();

                $fn = 'cth_auditoria_' . date('YmdHis') . '.pdf';

                return response($pdf, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $fn . '"',
                ]);
            } catch (\Exception $e) {
                return response("Error generando el informe PDF: " . $e->getMessage(), 500);
            }
        }

        // Get team timezone for accurate date filtering
        $teamTimezone = $team->timezone ?? 'UTC';
        
        // Convert user-selected dates to team timezone boundaries
        $fromDateTime = \Carbon\Carbon::parse($fromDate, $teamTimezone)->startOfDay();
        $toDateTime = \Carbon\Carbon::parse($toDate, $teamTimezone)->endOfDay();
        
        // Convert to UTC for database comparison
        $fromDateTimeUTC = $fromDateTime->copy()->setTimezone('UTC');
        $toDateTimeUTC = $toDateTime->copy()->setTimezone('UTC');
        
        $query = Event::query()
            ->with(['user', 'eventType'])
            ->where('team_id', $team->id)
            // Use timestamp comparison instead of whereDate to account for timezone
            ->where(function($q) use ($fromDateTimeUTC, $toDateTimeUTC) {
                $q->where('start', '<=', $toDateTimeUTC)
                  ->where('end', '>=', $fromDateTimeUTC);
            });

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

        // Check if async generation is needed for downloads (regular events)
        if ($isDownload && $start->diffInDays($end) > ($asyncThreshold * 30.5)) {
            // Dispatch async job
            \App\Jobs\GenerateReportJob::dispatch(
                $user->id,
                $team->id,
                $workerId,
                $fromDate,
                $toDate,
                $eventTypeId,
                'PDF',
                'events', // reportSource parameter
                $groupBy,
                $orderBy,
                $isChrome
            );

            // Return HTML message indicating async generation
            $message = __('This report will be generated asynchronously and sent to your inbox.');
            $title = __('Please wait, the report may take a few minutes...');
            
            return response()->view('reports.async-message', [
                'title' => $title,
                'message' => $message,
                'async' => true
            ], 202);
        }

        $events = $query->get();

        // Determine Work Center
        $workCenter = null;
        if ($workerId && $workerId !== '%') {
            $workerUser = User::find($workerId);
            if ($workerUser) {
                $defaultWorkCenterId = $workerUser->meta->where('meta_key', 'default_work_center_id_team_' . $team->id)->first();
                if ($defaultWorkCenterId) {
                    $workCenter = \App\Models\WorkCenter::find($defaultWorkCenterId->meta_value);
                }
            }
        }

        $exporter = new \App\Exports\EventsPdfExport($events, $team, $workCenter, $fromDate, $toDate, $groupBy, $orderBy);
        $pdf = $exporter->generate();

        $fn = 'cth_informe_' . date('YmdHis') . '.pdf';
        
        $download = $request->input('download', false);
        $disposition = $download ? 'attachment' : 'inline';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . $fn . '"',
        ]);
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        $team = $user->currentTeam;
        
        $workerId = $request->input('worker');
        $fromDate = $request->input('fromdate');
        $toDate = $request->input('todate');
        $eventTypeId = $request->input('event_type_id');
        $reportSource = $request->input('report_source', 'events');
        $rtype = $request->input('rtype', 'XLSX');

        // Security check
        if (!$user->isTeamAdmin() && !$user->isInspector() && $workerId != $user->id) {
            abort(403);
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
            $fn = 'cth_auditoria_' . date('YmdHis') . '.xlsx';

            return Excel::download(new EventsHistoryExport($history), $fn);
        }

        // Get team timezone for accurate date filtering
        $teamTimezone = $team->timezone ?? 'UTC';
        
        // Convert user-selected dates to team timezone boundaries
        $fromDateTime = \Carbon\Carbon::parse($fromDate, $teamTimezone)->startOfDay();
        $toDateTime = \Carbon\Carbon::parse($toDate, $teamTimezone)->endOfDay();
        
        // Convert to UTC for database comparison
        $fromDateTimeUTC = $fromDateTime->copy()->setTimezone('UTC');
        $toDateTimeUTC = $toDateTime->copy()->setTimezone('UTC');
        
        $query = Event::query()
            ->with(['user', 'eventType'])
            ->where('team_id', $team->id)
            // Use timestamp comparison instead of whereDate to account for timezone
            ->where(function($q) use ($fromDateTimeUTC, $toDateTimeUTC) {
                $q->where('start', '<=', $toDateTimeUTC)
                  ->where('end', '>=', $fromDateTimeUTC);
            });

        if ($workerId && $workerId !== '%') {
            $query->where('user_id', $workerId);
        } else {
            $teamUserIds = $team->allUsers()->pluck('id');
            $query->whereIn('user_id', $teamUserIds);
        }

        if ($eventTypeId && $eventTypeId !== 'All') {
            $query->where('event_type_id', $eventTypeId);
        }

        $events = $query->get();

        $extensions = [
            'XLSX' => 'xlsx',
            'XLS' => 'xlsx',
            'CSV' => 'csv',
            'ODS' => 'ods',
        ];
        
        $ext = $extensions[$rtype] ?? 'xlsx';
        $fn = 'cth_informe_' . date('YmdHis') . '.' . $ext;

        $types = [
            'XLSX' => \Maatwebsite\Excel\Excel::XLSX,
            'XLS' => \Maatwebsite\Excel\Excel::XLSX,
            'CSV' => \Maatwebsite\Excel\Excel::CSV,
            'ODS' => \Maatwebsite\Excel\Excel::ODS,
        ];

        return Excel::download(new EventsExport($events, $fromDate, $toDate, $teamTimezone), $fn, $types[$rtype] ?? \Maatwebsite\Excel\Excel::XLSX);
    }

    public function download($file)
    {
        // Security check: ensure file is in reports directory and user has access
        $path = 'reports/' . $file;
        
        if (!\Illuminate\Support\Facades\Storage::exists($path)) {
            abort(404);
        }

        $content = \Illuminate\Support\Facades\Storage::get($path);
        $mime = \Illuminate\Support\Facades\Storage::mimeType($path);

        if (!$mime) {
            $mime = 'application/pdf';
        }

        return response($content, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'attachment; filename="' . $file . '"'
        ]);
    }
}
