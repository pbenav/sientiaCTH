<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\User;

class EventsHistoryExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $history;

    public function __construct($history)
    {
        $this->history = $history;
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
        ];
    }

    public function map($record): array
    {
        $user = User::find($record->user_id);
        $userName = $user ? $user->name . ' ' . $user->family_name1 : 'Unknown User (' . $record->user_id . ')';

        return [
            $userName,
            $record->tablename,
            \Carbon\Carbon::parse($record->created_at)->format('d/m/Y H:i:s'),
            $record->original_event,
            $record->modified_event,
        ];
    }
}
