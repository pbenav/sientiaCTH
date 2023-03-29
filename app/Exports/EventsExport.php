<?php

namespace App\Exports;

use App\Models\Event;
use App\Traits\TimeDiff;
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

class EventsExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles, WithMapping
{
    use Exportable;
    use TimeDiff;
    public $worker;
    public $fromdate;
    public $todate;
    public $description;
    public $observations;
    public $user;
    public $team;


    public function __construct($params)
    {
        $this->worker = $params['worker'];
        $this->fromdate = $params['fromdate'];
        $this->todate = $params['todate'];
        $this->description = $params['description'] == __('All') ? '%' : __($params['description']);
        $this->user = Auth::user();
        $this->team = $this->user->currentTeam;
    }

    public function query()
    {
        //$data = DB::table('events')
        $data = Event::select('events.user_id', 'users.name', 'users.family_name1', 'events.id',
                              'events.start', 'events.end', 'events.description', 'events.observations')
            ->selectRaw('TIMESTAMPDIFF(hour, start, end) as duration')
            ->join('users', 'events.user_id', 'users.id')
            ->when(($this->worker != "%"), fn ($query) => $query->where('users.id', $this->worker))
            ->whereDate('start', '>=', $this->fromdate)
            ->whereDate('end', '<=', $this->todate)
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
        $name = $event->name . ' ' . $event->family_name1;
        return [
            $event->user_id,
            $name,
            $event->id,
            $event->start,
            $event->end,            
            $this->timeDiff($event->start, $event->end, true),
            $event->description,
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
                    'color' => ['argb' => 'FFaaaaaa']
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