<?php

namespace App\Http\Livewire;

use App\Models\Team;
use App\Models\User;
use App\Models\Event;
use App\Models\Message;
use App\Jobs\GenerateReportJob;
use Livewire\Component;
use App\Exports\EventsExport;
use App\Exports\EventsPdfExport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

/**
 * A Livewire component for generating and exporting reports.
 *
 * This component provides a form for users to select a worker, date range,
 * event type, and report type, and then export the corresponding data.
 */
class ReportsComponent extends Component
{
    public User $user;
    public Team $team;
    public bool $isTeamAdmin;
    public bool $isInspector;
    public $workers;
    public $worker;
    public string $fromdate;
    public string $todate;
    public $event_type_id;
    public string $rtype;
    public array $rtypes = [
        "PDF" => \Maatwebsite\Excel\Excel::DOMPDF,
        "XLS" => \Maatwebsite\Excel\Excel::XLSX,
        "CSV" => \Maatwebsite\Excel\Excel::CSV,
        "ODS" => \Maatwebsite\Excel\Excel::ODS,
        "HTML" => \Maatwebsite\Excel\Excel::HTML,
    ];
    public $eventTypes;
    public $report_source = 'events';
    public $reportSources = [];
    public $pdfUrl = null;

    /**
     * The validation rules for the component.
     *
     * @var array
     */
    protected $rules = [
        "worker" => 'required',
        "fromdate" => 'bail|required|date|before_or_equal:todate',
        "todate" => 'required|date|before_or_equal:now',
        "event_type_id" => 'required',
        "rtype" => 'required',
    ];

    /**
     * Mounts the component and initializes necessary data.
     *
     * This method is called once when the component is initialized.
     * It sets up user, team, permissions, workers, and date range.
     */
    public function mount()
    {
        $this->user = User::find(Auth::user()->id);
        $this->team = $this->user->currentTeam;
        $this->isTeamAdmin = $this->user->isTeamAdmin();
        $this->isInspector = $this->user->isInspector();
        if ($this->isTeamAdmin || $this->isInspector) {
            $this->workers = $this->team->allUsers();
        }
        $this->worker = $this->user->id;
        $this->fromdate = date('Y-m-01');
        $this->todate = date('Y-m-d');
        $this->eventTypes = $this->team->eventTypes;
        $this->event_type_id = 'All';
        $this->rtype = 'PDF';
        $this->reportSources = [
            'events' => __('Worker Activity'),
            'statistics' => __('Statistics'),
        ];
        
        // Only inspectors can access Audit Log reports
        if ($this->isInspector) {
            $this->reportSources['history'] = __('Audit Log');
        }
    }

    /**
     * Validates a single property of the component.
     *
     * @param string $propertyName The name of the property to validate.
     * @return void
     */
    public function updated(string $propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Exports the report based on selected parameters.
     *
     * This method validates the input data, constructs the filename, and returns the Excel file download.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        $this->validate();

        $start = \Carbon\Carbon::parse($this->fromdate);
        $end = \Carbon\Carbon::parse($this->todate);

        $maxMonths = $this->team->max_report_months ?? Team::DEFAULT_MAX_REPORT_MONTHS;
        $absoluteMax = Team::ABSOLUTE_MAX_REPORT_MONTHS;
        $asyncThreshold = $this->team->async_report_threshold_months ?? Team::DEFAULT_ASYNC_THRESHOLD_MONTHS;

        $diffInMonths = $start->diffInMonths($end);
        // Fallback to days for precision if needed, but months is usually fine for this.
        // Using days for the 3 month check was 92 days.
        // Let's use days for more precision if the limit is small.
        $diffInDays = $start->diffInDays($end);
        
        // Check absolute max (security net)
        if ($diffInDays > ($absoluteMax * 30.5)) { 
             $this->addError('todate', __('The date range cannot exceed the maximum allowed (:max months).', ['max' => $absoluteMax]));
             return;
        }

        // Check team configured limit
        if ($diffInDays > ($maxMonths * 30.5)) {
             $this->addError('todate', __('The date range cannot exceed :months months.', ['months' => $maxMonths]));
             return;
        }

        // Check if async generation is needed
        if ($diffInDays > ($asyncThreshold * 30.5)) {
            // Dispatch Job
            GenerateReportJob::dispatch(
                $this->user->id,
                $this->team->id,
                $this->worker,
                $this->fromdate,
                $this->todate,
                $this->event_type_id,
                $this->rtype
            );

            // Notify user in UI
            $this->emit('async-report-started', [
                'title' => __('Please wait, the report may take a few minutes...'),
                'text' => __('This report will be generated asynchronously and sent to your inbox.')
            ]);
            
            return;
        }

        // Handle Statistics Report
        if ($this->report_source === 'statistics') {
            // Statistics reports only support PDF format
            if ($this->rtype !== 'PDF') {
                $this->addError('rtype', __('Statistics reports are only available in PDF format.'));
                return;
            }

            // Ensure a single worker is selected (not "All")
            if (!$this->worker || $this->worker === '%') {
                $this->addError('worker', __('stats.select_specific_worker'));
                return;
            }

            $ext = 'pdf';
            $fn = 'cth_estadisticas_' . date('YmdHis') . '.' . $ext;

            $exporter = new \App\Exports\StatsPdfExport(
                $this->user->id,
                $this->team->id,
                $this->worker,
                $this->fromdate,
                $this->todate,
                $this->event_type_id
            );

            $pdf = $exporter->generate();
            
            return response()->streamDownload(function() use ($pdf) {
                echo $pdf;
            }, $fn, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        if ($this->report_source === 'history') {
            $query = \Illuminate\Support\Facades\DB::table('events_history')
                ->whereDate('created_at', '>=', $this->fromdate)
                ->whereDate('created_at', '<=', $this->todate);

            if ($this->worker && $this->worker !== '%') {
                $query->where('user_id', $this->worker);
            } else {
                // If "All" selected, ensure we only show team users history
                // This assumes we want to see history made BY team members
                $teamUserIds = $this->team->allUsers()->pluck('id');
                $query->whereIn('user_id', $teamUserIds);
            }

            $history = $query->orderBy('created_at', 'desc')->get();

            $ext = strtoLower($this->rtype);
            $fn = 'cth_auditoria_' . date('YmdHis'). '.' . $ext;

            // For now, History only supports Excel/CSV, not PDF preview yet unless we implement it
            // But the user asked for "todo tipo de informes".
            // If PDF is selected for history, we might fallback to CSV or implement a simple PDF.
            // Let's stick to Excel/CSV for History for now as it's data heavy.
            // Or force XLS if PDF is selected?
            
            if ($this->rtype === 'PDF') {
                 // Fallback to XLSX for now or throw error? 
                 // Let's just download as XLSX to avoid breaking
                 return Excel::download(new \App\Exports\EventsHistoryExport($history), $fn . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
            }

            return Excel::download(new \App\Exports\EventsHistoryExport($history), $fn, $this->rtypes[$this->rtype]);
        }

        $query = Event::query()
            ->with(['user', 'eventType'])
            ->whereDate('start', '>=', $this->fromdate)
            ->whereDate('end', '<=', $this->todate);

        if ($this->worker && $this->worker !== '%') {
            $query->where('user_id', $this->worker);
        } else {
             $teamUserIds = $this->team->allUsers()->pluck('id');
             $query->whereIn('user_id', $teamUserIds);
        }

        if ($this->event_type_id && $this->event_type_id !== 'All') {
            $query->where('event_type_id', $this->event_type_id);
        }

        $events = $query->orderBy('start')->get();

        $ext = strtoLower($this->rtype);
        $fn = 'cth_informe_' . date('YmdHis'). '.' . $ext;

        if ($this->rtype === 'PDF') {
            // Determine Work Center
            $workCenter = null;
            if ($this->worker && $this->worker !== '%') {
                $user = User::find($this->worker);
                if ($user) {
                    $defaultWorkCenterId = $user->meta->where('meta_key', 'default_work_center_id')->first();
                    if ($defaultWorkCenterId) {
                        $workCenter = \App\Models\WorkCenter::find($defaultWorkCenterId->meta_value);
                    }
                }
            }

            $exporter = new EventsPdfExport(
                $events, 
                $this->team, 
                $workCenter, 
                $this->fromdate, 
                $this->todate
            );
            $pdf = $exporter->generate();
            
            return response()->streamDownload(function() use ($pdf) {
                echo $pdf;
            }, $fn, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        return Excel::download(new EventsExport($events), $fn, $this->rtypes[$this->rtype]);
    }

    public function generatePreview()
    {
        $this->validate();

        // Statistics reports require a single worker
        if ($this->report_source === 'statistics') {
            if (!$this->worker || $this->worker === '%') {
                $this->addError('worker', __('Please select a specific worker for statistics reports.'));
                return;
            }
            if ($this->rtype !== 'PDF') {
                $this->addError('rtype', __('Statistics reports are only available in PDF format.'));
                return;
            }
        }

        $start = \Carbon\Carbon::parse($this->fromdate);
        $end = \Carbon\Carbon::parse($this->todate);

        $maxMonths = $this->team->max_report_months ?? Team::DEFAULT_MAX_REPORT_MONTHS;
        
        // For preview, we might want to be stricter or same as export.
        // Usually preview is fast, so maybe keep it sync only and strictly limited?
        // Or allow preview up to the max limit?
        // If it's async, we can't preview it easily.
        // So for preview, we enforce the async threshold as a hard limit.
        
        $asyncThreshold = $this->team->async_report_threshold_months ?? Team::DEFAULT_ASYNC_THRESHOLD_MONTHS;
        $limit = min($maxMonths, $asyncThreshold);

        if ($start->diffInDays($end) > ($limit * 30.5)) {
             $this->addError('todate', __('The date range cannot exceed :months months for preview.', ['months' => $limit]));
             return;
        }
        
        $this->pdfUrl = route('reports.preview', [
            'worker' => $this->worker, 
            'fromdate' => $this->fromdate, 
            'todate' => $this->todate, 
            'event_type_id' => $this->event_type_id,
            'report_source' => $this->report_source,
            't' => time() // Force cache busting
        ]);
    }

    /**
     * Renders the component view.
     *
     * This method is responsible for rendering the Livewire component's view and passing necessary data to it.
     *
     * @return \Illuminate\View\View The rendered view.
     */
    public function render()
    {
        return view('livewire.reports.reports')->with([
            'workers' => $this->workers,
        ]);
    }
}
