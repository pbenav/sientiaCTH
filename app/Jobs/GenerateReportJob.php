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

    /**
     * Create a new job instance.
     */
    public function __construct($userId, $teamId, $worker, $fromdate, $todate, $eventTypeId, $rtype)
    {
        $this->userId = $userId;
        $this->teamId = $teamId;
        $this->worker = $worker;
        $this->fromdate = $fromdate;
        $this->todate = $todate;
        $this->eventTypeId = $eventTypeId;
        $this->rtype = $rtype;
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

            // Query events
            $query = Event::query()
                ->with(['user', 'eventType'])
                ->whereDate('start', '>=', $this->fromdate)
                ->whereDate('end', '<=', $this->todate);

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
            $fileName = 'cth_informe_' . date('YmdHis') . '.' . $ext;
            $filePath = 'reports/' . $fileName;

            if ($this->rtype === 'PDF') {
                // Determine Work Center
                $workCenter = null;
                if ($this->worker && $this->worker !== '%') {
                    $workerUser = User::find($this->worker);
                    if ($workerUser) {
                        $defaultWorkCenterId = $workerUser->meta->where('meta_key', 'default_work_center_id')->first();
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
                    $this->todate
                );
                $pdf = $exporter->generate();

                // Store PDF
                Storage::put($filePath, $pdf);
            } else {
                // Store other formats
                Excel::store(
                    new EventsExport($events),
                    $filePath,
                    'local',
                    $this->getRtypeConstant($this->rtype)
                );
            }

            // Create download URL
            $downloadUrl = route('reports.download', ['file' => $fileName]);

            // Send message to user
            $message = Message::create([
                'sender_id' => $this->userId, // Send as self/system
                'subject' => __('Your report is ready'),
                'body' => __('The report for period :from to :to is ready for download. :link', [
                    'from' => $this->fromdate,
                    'to' => $this->todate,
                    'link' => '<a href="' . $downloadUrl . '" class="text-blue-600 underline" target="_blank">' . __('Download') . '</a>'
                ]),
            ]);

            $message->recipients()->attach($this->userId);

        } catch (\Exception $e) {
            // Send error message to user
            $message = Message::create([
                'sender_id' => $this->userId,
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
