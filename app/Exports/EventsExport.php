<?php

namespace App\Exports;

use App\Models\Event;
use App\Traits\TimeDiff;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EventsExport implements FromQuery, WithHeadings, WithStyles, WithMapping, ShouldQueue
{
    use Exportable;
    use TimeDiff;
    public $worker;
    public $fromdate;
    public $todate;
    public $event_type_id;
    public $observations;
    public $user;
    public $team;


    public function __construct($params)
    {
        $this->worker = $params['worker'];
        $this->fromdate = $params['fromdate'];
        $this->todate = $params['todate'];
        $this->event_type_id = $params['event_type_id'] == 'All' ? '%' : $params['event_type_id'];
        $this->user = Auth::user();
        $this->team = $this->user->currentTeam;
    }

    public function query()
    {
        //$data = DB::table('events')
        // Check if period is greater than 120 days.
        return Event::query()
            ->with(['user', 'eventType']) // Eager load relationships
            ->when(($this->worker != "%"), function ($query) {
                $query->where('user_id', $this->worker);
            })
            ->whereDate('start', '>=', $this->fromdate)
            ->whereDate('end', '<=', $this->todate)
            ->when(($this->event_type_id != "%"), function ($query) {
                $query->where('event_type_id', $this->event_type_id);
            })
            ->orderBy('start');
    }

    public function headings(): array
    {
        return [
            [
                'Id',
                __('Name'),
                __('Event'),
                __('Start date'),
                __('End date'),
                __('Duration'),
                __('Description'),
                __('Observations')
            ]
        ];
    }
    public function map($event): array
    {
        $name = $event->user ? $event->user->name . ' ' . $event->user->family_name1 : 'N/A';
        $description = $event->eventType ? $event->eventType->name : $event->description;

        return [
            $event->user_id,
            $name,
            $event->id,
            $event->start,
            $event->end,
            $this->timeDiff($event->start, $event->end, true),
            $description,
            $event->observations
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getHeaderFooter()->setFirstHeader('&C&HPlease treat this document as confidential!');
        $sheet->getHeaderFooter()->setFirstFooter('&L&B' . $sheet->getTitle() . '&RPage &P of &N');
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Helvetica');
        $sheet->getParent()->getDefaultStyle()->getFont()->setSize('8');        
        $sheet->getParent()->getDefaultStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $cols = array(
            'A' => 10,
            'B' => 38,
            'C' => 10,
            'D' => 28,
            'E' => 28,
            'F' => 32,
            'G' => 24,
            'H' => 34,
        );

        foreach($cols as $c => $w) {            
            $sheet->getParent()->getActiveSheet()->getColumnDimension($c)
            ->setAutoSize(false)->setWidth((float)$w);
            
        }
        //dump($sheet->getParent()->getActiveSheet()->getColumnDimension('B'));

        return [
            'A:ZZ'=> [
                'font' => [
                    'name' => 'Helvetica',
                    'bold' => false,
                    'size' => 10,
                    'color' => ['argb' => '00000000'],
                    'underline' => false
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_TOP
                ]
            ],         
            'H' => [
                'autoSize' => true,
                'width' => '50',
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['argb' => 'FFCCCCCC']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_TOP
                ]
            ],           
            'A1:H1' => [ // Heading                
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'underline' => true
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
            ],
        ];
    }
}