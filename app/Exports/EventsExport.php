<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EventsExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles
{
    use Exportable;
    public $worker;
    public $month;
    public $year;
    public $description;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function __construct($params)
    {
        $this->worker = $params['worker'];
        $this->month = $params['month'];
        $this->year = $params['year'];
        $this->description = $params['description'];
    }

    public function query()
    {
        return User::query()
            ->join('events', 'users.id', 'events.user_id')
            ->select('events.id', 'users.name', 'users.family_name1', 'events.start', 'events.end', 'events.description')
            ->where('users.id', $this->worker)
            ->whereYear('start', $this->year)
            ->whereMonth('start', $this->month)
            ->where('description', 'like', $this->description)
            ->orderBy('events.start');
    }

    public function headings(): array
    {
        return [
            'Event id',
            'Name',
            'LastName',
            'Event Start',
            'Event End',
            'Event description'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A3);
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
}
