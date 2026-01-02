<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\User;

class EventsHistoryExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $history;

    public function __construct($history)
    {
        $this->history = $history;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            'D' => ['alignment' => ['wrapText' => true, 'vertical' => 'top']],
            'E' => ['alignment' => ['wrapText' => true, 'vertical' => 'top']],
            'F' => ['alignment' => ['wrapText' => true, 'vertical' => 'top']],
            1   => ['font' => ['bold' => true]],
        ];
    }

    public function collection()
    {
        return $this->history;
    }

    public function headings(): array
    {
        return [
            __('User'),
            __('Table'),
            __('Date'),
            __('Original Data'),
            __('Modified Data'),
            __('Differences'),
        ];
    }

    public function map($record): array
    {
        $user = User::find($record->user_id);
        $userName = $user ? $user->name . ' ' . $user->family_name1 : 'Unknown User (' . $record->user_id . ')';

        $diff = $this->calculateDiff($record->original_event, $record->modified_event);

        return [
            $userName,
            $record->tablename,
            \Carbon\Carbon::parse($record->created_at)->format('d/m/Y H:i:s'),
            $record->original_event,
            $record->modified_event,
            $diff,
        ];
    }

    private function calculateDiff($original, $modified): string
    {
        $originalData = json_decode($original, true) ?? [];
        $modifiedData = json_decode($modified, true) ?? [];

        if (!is_array($originalData) || !is_array($modifiedData)) {
            return '';
        }

        $differences = [];

        // Check for changes and deletions
        foreach ($originalData as $key => $value) {
            if (array_key_exists($key, $modifiedData)) {
                if ($modifiedData[$key] != $value) {
                    $originalStr = is_array($value) ? json_encode($value) : (string)$value;
                    $modifiedStr = is_array($modifiedData[$key]) ? json_encode($modifiedData[$key]) : (string)$modifiedData[$key];
                    $differences[] = __('Field') . ": $key | " . __('Old Value') . ": '$originalStr' | " . __('New Value') . ": '$modifiedStr'";
                }
            } else {
                $originalStr = is_array($value) ? json_encode($value) : (string)$value;
                $differences[] = __('Field') . ": $key | " . __('Old Value') . ": '$originalStr' | " . __('New Value') . ": " . __('(Eliminado)');
            }
        }

        // Check for additions
        foreach ($modifiedData as $key => $value) {
            if (!array_key_exists($key, $originalData)) {
                $modifiedStr = is_array($value) ? json_encode($value) : (string)$value;
                $differences[] = __('Field') . ": $key | " . __('Old Value') . ": " . __('(Nuevo)') . " | " . __('New Value') . ": '$modifiedStr'";
            }
        }

        return implode("\n", $differences);
    }
}
