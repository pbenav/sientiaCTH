<?php

namespace App\Exports;

use App\Models\Event;
use App\Traits\TimeDiff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithProperties;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EventsExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles, WithMapping, WithProperties
{
    use Exportable;
    use TimeDiff;
    public $worker;
    public $month;
    public $year;
    public $description;
    public $user;
    public $team;


    public function __construct($params)
    {
        $this->worker = $params['worker'];
        $this->month = $params['month'];
        $this->year = $params['year'];
        $this->description = $params['description'];
        $this->user = Auth::user();
        $this->team = $this->user->currentTeam;
    }

    public function query()
    {
        //$data = DB::table('events')
        $data = Event::select('events.user_id', 'users.name', 'users.family_name1', 'events.id', 'events.start', 'events.end', 'events.description')
            ->selectRaw('TIMESTAMPDIFF(hour, start, end) as duration')
            ->join('users', 'events.user_id', 'users.id')
            ->where('users.id', $this->worker)
            ->whereYear('start', $this->year)
            ->whereMonth('start', $this->month)
            ->where('description', 'like', $this->description)
            ->orderBy('events.start');
        return $data;
    }

    public function headings(): array
    {
        return [
            [
                'Id',
                __('Name'),
                __('Family Name 1'),
                __('Event'),
                __('Start date'),
                __('End date'),
                __('Duration'),
                __('Description')
            ]
        ];
    }
    public function map($event): array
    {
        return [
            $event->user_id,
            $event->name,
            $event->family_name1,
            $event->id,
            $event->start,
            $event->end,
            $this->getPeriod($event),
            $event->description
        ];
    }

    public function title(): string
    {
        return 'Month ' . $this->month;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getHeaderFooter()->setFirstHeader('&C&HPlease treat this document as confidential!');
        $sheet->getHeaderFooter()->setFirstFooter('&L&B' . $sheet->getTitle() . '&RPage &P of &N');

        return [
            'A1:H1' => [
                'font' => [
                    'bold' => true,
                    'size' => 10,
                    'name' => 'Times',
                    'color' => ['argb' => 'FFFFFFFF']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['argb' => 'FF000000']
                ],
                'borders' => [
                    'inside' => [
                        'borderStyle' => Border::BORDER_NONE,
                        'color' => ['argb' => 'FF000000']
                    ],
                    'outline' => [
                        'borderStyle' => Border::BORDER_THICK,
                        'color' => ['argb' => 'FF000000']
                    ],
                ],
            ]
        ];
    }

    public function getPeriod($event)
    {
        return $this->timeDiff($event->start, $event->end);
    }

    public function properties(): array
    {
        return [
            'creator' => 'CTH',
            'lastModifiedBy' => $this->user,
            'title' => __('Events Export'),
            'description' => __('Latest Events'),
            'subject' => __('Events'),
            'category' => 'Invoices',
            'manager' => $this->user,
            'company' => $this->team,
        ];
    }

}