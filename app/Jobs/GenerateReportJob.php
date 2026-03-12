<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Team;
use App\Models\Event;
use App\Models\Message;
use App\Exports\EventsExport;
use App\Exports\EventsPdfExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $teamId;
    protected $worker;
    protected $fromdate;
    protected $todate;
    protected $eventTypeId;
    protected $rtype;
    protected $reportSource;
    protected $groupBy;
    protected $orderBy;
    protected $isChrome;

    /**
     * Create a new job instance.
     */
    public function __construct($userId, $teamId, $worker, $fromdate, $todate, $eventTypeId, $rtype, $reportSource = 'events', $groupBy = 'none', $orderBy = 'start', $isChrome = false)
    {
        $this->userId = $userId;
        $this->teamId = $teamId;
        $this->worker = $worker;
        $this->fromdate = $fromdate;
        $this->todate = $todate;
        $this->eventTypeId = $eventTypeId;
        $this->rtype = $rtype;
        $this->reportSource = $reportSource;
        $this->groupBy = $groupBy;
        $this->orderBy = $orderBy;
        $this->isChrome = $isChrome;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $user = User::find($this->userId);
            $team = Team::find($this->teamId);

            if (!$user || !$team) {
                throw new \Exception('User or team not found');
            }

            // Handle statistics reports differently
            if ($this->reportSource === 'statistics') {
                $fileName = 'sientiaCTH_estadisticas_' . date('YmdHis') . '.pdf';
                $filePath = 'reports/' . $fileName;

                $exporter = new \App\Exports\StatsPdfExport(
                    $this->userId,
                    $this->teamId,
                    $this->worker,
                    $this->fromdate,
                    $this->todate,
                    $this->eventTypeId
                );
                
                $pdf = $exporter->generate();
                Storage::put($filePath, $pdf);

                // Create download URL and send message
                $downloadUrl = route('reports.download', ['file' => $fileName]);
                
                $body = __('The report for period :from to :to is ready for download. :link', [
                    'from' => $this->fromdate,
                    'to' => $this->todate,
                    'link' => '<a href="' . $downloadUrl . '" class="text-blue-600 underline" target="_blank">' . __('Download') . '</a>'
                ]);

                if ($this->isChrome) {
                    $body .= '<br><br><small style="color: #f59e0b;"><strong>' . __('Chrome users') . ':</strong> ' . __('If you cannot download reports, make sure popup blocker is disabled.') . '</small>';
                }

                $message = Message::create([
                    'sender_id' => 1, // System/Admin
                    'subject' => __('Your report is ready'),
                    'body' => $body,
                ]);
                $message->recipients()->attach($this->userId);
                return;
            }

            // Get team timezone for accurate date filtering
            $teamTimezone = $team->timezone ?: config('app.timezone');
            
            // Convert user-selected dates to team timezone boundaries
            $fromDateTime = \Carbon\Carbon::parse($this->fromdate, $teamTimezone)->startOfDay();
            $toDateTime = \Carbon\Carbon::parse($this->todate, $teamTimezone)->endOfDay();
            
            // Convert to UTC for database comparison
            $fromDateTimeUTC = $fromDateTime->copy()->setTimezone('UTC');
            $toDateTimeUTC = $toDateTime->copy()->setTimezone('UTC');
            
            // Query events for regular reports
            $query = Event::query()
                ->with(['user', 'eventType'])
                ->where('team_id', $team->id)
                // Use timestamp comparison instead of whereDate to account for timezone
                ->where(function($q) use ($fromDateTimeUTC, $toDateTimeUTC) {
                    $q->where('start', '<=', $toDateTimeUTC)
                      ->where('end', '>=', $fromDateTimeUTC);
                });

            if ($this->worker && $this->worker !== '%') {
                $query->where('user_id', $this->worker);
            } else {
                $teamUserIds = $team->allUsers()->pluck('id');
                $query->whereIn('user_id', $teamUserIds);
            }

            if ($this->eventTypeId && $this->eventTypeId !== 'All') {
                $query->where('event_type_id', $this->eventTypeId);
            }

            $events = $query->orderBy('start')->get();

            // Generate the file
            $ext = strtolower($this->rtype);
            $fileName = 'sientiaCTH_informe_' . date('YmdHis') . '.' . $ext;
            $filePath = 'reports/' . $fileName;

            if ($this->rtype === 'PDF') {
                // Determine Work Center
                $workCenter = null;
                if ($this->worker && $this->worker !== '%') {
                    $workerUser = User::find($this->worker);
                    if ($workerUser) {
                        $defaultWorkCenterId = $workerUser->meta->where('meta_key', 'default_work_center_id_team_' . $this->teamId)->first();
                        if ($defaultWorkCenterId) {
                            $workCenter = \App\Models\WorkCenter::find($defaultWorkCenterId->meta_value);
                        }
                    }
                }

                $exporter = new EventsPdfExport(
                    $events,
                    $team,
                    $workCenter,
                    $this->fromdate,
                    $this->todate,
                    $this->groupBy,
                    $this->orderBy
                );
                $pdf = $exporter->generate();

                // Store PDF
                Storage::put($filePath, $pdf);
            } else {
                // Get team timezone for event clipping
                $teamTimezone = $team->timezone ?: config('app.timezone');
                
                // Store other formats
                Excel::store(
                    new EventsExport($events, $this->fromdate, $this->todate, $teamTimezone),
                    $filePath,
                    'local',
                    $this->getRtypeConstant($this->rtype)
                );
            }

            // Create download URL
            $downloadUrl = route('reports.download', ['file' => $fileName]);

            $body = __('The report for period :from to :to is ready for download. :link', [
                'from' => $this->fromdate,
                'to' => $this->todate,
                'link' => '<a href="' . $downloadUrl . '" class="text-blue-600 underline" target="_blank">' . __('Download') . '</a>'
            ]);

            if ($this->isChrome) {
                $body .= '<br><br><small style="color: #f59e0b;"><strong>' . __('Chrome users') . ':</strong> ' . __('If you cannot download reports, make sure popup blocker is disabled.') . '</small>';
            }

            // Send message to user
            $message = Message::create([
                'sender_id' => 1, // System/Admin
                'subject' => __('Your report is ready'),
                'body' => $body,
            ]);

            $message->recipients()->attach($this->userId);

        } catch (\Exception $e) {
            // Send error message to user
            $message = Message::create([
                'sender_id' => 1, // System/Admin
                'subject' => __('Report generation failed'),
                'body' => __('There was an error generating your report. Please try again or contact support.') . "\n\n" . __('Error') . ': ' . $e->getMessage(),
            ]);

            $message->recipients()->attach($this->userId);

            // Re-throw to mark job as failed
            throw $e;
        }
    }

    /**
     * Get the Excel type constant for the given report type.
     */
    private function getRtypeConstant($rtype)
    {
        $types = [
            "XLS" => \Maatwebsite\Excel\Excel::XLSX,
            "CSV" => \Maatwebsite\Excel\Excel::CSV,
            "ODS" => \Maatwebsite\Excel\Excel::ODS,
            "HTML" => \Maatwebsite\Excel\Excel::HTML,
        ];

        return $types[$rtype] ?? \Maatwebsite\Excel\Excel::XLSX;
    }
}
