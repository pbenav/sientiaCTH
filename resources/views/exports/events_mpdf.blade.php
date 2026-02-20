<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: sans-serif;
            font-size: 9pt;
            color: #1F2937;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #4F46E5;
        }

        .header h1 {
            color: #4F46E5;
            font-size: 20pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 12pt;
            font-weight: bold;
            color: #374151;
            margin-bottom: 2px;
        }

        .header p {
            color: #6B7280;
            font-size: 10pt;
            margin: 0;
        }

        .meta-info {
            margin-bottom: 15px;
            font-size: 10pt;
            color: #374151;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: bold;
            background-color: #667eea;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead {
            background-color: #667eea;
            color: white;
        }

        th {
            padding: 10px 5px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
            border-bottom: 1px solid #E5E7EB;
        }

        th.text-center {
            text-align: center;
        }

        th.text-right {
            text-align: right;
        }

        td {
            padding: 8px 5px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 8.5pt;
            vertical-align: top;
        }

        tbody tr:nth-child(even) {
            background-color: #F9FAFB;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-medium {
            font-weight: bold;
            color: #111827;
        }
    </style>
</head>

<body>
    <htmlpageheader name="otherPagesHeader">
        <div style="text-align: right; font-size: 8pt; color: #9CA3AF; padding-top: 5px;">
            {{ trans('reports.Generated on') }}: {{ now()->format('d/m/Y H:i') }}
        </div>
    </htmlpageheader>
    <sethtmlpageheader name="otherPagesHeader" value="on" show-this-page="0" />

    <div class="header">
        <h1>{{ trans('reports.Report of Events') }}</h1>
        <p class="subtitle">{{ $team->name }}</p>
        @if ($workCenter)
            <p class="subtitle">{{ $workCenter->name }}</p>
        @endif
        <p>{{ trans('reports.Generated on') }}: {{ now()->format('d/m/Y H:i') }}</p>
        @if ($startDate && $endDate)
            <p>{{ trans('reports.Period') }}: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} -
                {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        @endif
    </div>

    <div class="meta-info">
        <strong>{{ trans('reports.Total Records') }}:</strong>
        <span class="badge">{{ $totalRecords }}</span>
        <span style="margin-left: 20px;">
            <strong>{{ trans('reports.Total Duration') }}:</strong>
            <span class="badge" style="background-color: #4F46E5;">{{ $totalDuration }}</span>
            @if (isset($equivalentDaysStr) && !empty($equivalentDaysStr))
                <span style="color: #6B7280; margin-left: 5px; font-weight: 500;">{{ $equivalentDaysStr }}</span>
            @endif
        </span>
    </div>

    <table autosize="1">
        <thead>
            <tr>
                <th style="width: 15%;">{{ trans('reports.Name') }}</th>
                <th class="text-center" style="width: 12%;">{{ trans('reports.Start') }}</th>
                <th class="text-center" style="width: 12%;">{{ trans('reports.End') }}</th>
                <th class="text-right" style="width: 15%;">{{ trans('reports.Duration') }}</th>
                <th style="width: 17%;">{{ trans('reports.Description') }}</th>
                <th style="width: 29%;">{{ trans('reports.Observations') }}</th>
            </tr>
        </thead>
        <tbody>
            @if (isset($groupBy) && $groupBy !== 'none')
                @foreach ($events as $groupName => $groupEvents)
                    @if (!$loop->first && $groupBy === 'user')
        </tbody>
    </table>
    <div style="page-break-before: always;"></div>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">{{ trans('reports.Name') }}</th>
                <th class="text-center" style="width: 12%;">{{ trans('reports.Start') }}</th>
                <th class="text-center" style="width: 12%;">{{ trans('reports.End') }}</th>
                <th class="text-right" style="width: 15%;">{{ trans('reports.Duration') }}</th>
                <th style="width: 17%;">{{ trans('reports.Description') }}</th>
                <th style="width: 29%;">{{ trans('reports.Observations') }}</th>
            </tr>
        </thead>
        <tbody>
            @endif
            <tr style="background-color: #E0E7FF; border-bottom: 2px solid #C7D2FE;">
                <td colspan="6" style="padding: 8px 12px; font-weight: bold; color: #3730A3;">
                    {{ $groupName }}
                </td>
            </tr>
            @foreach ($groupEvents as $event)
                <tr>
                    <td class="font-medium">
                        {{ $event->user->family_name1 }} {{ $event->user->family_name2 }}, {{ $event->user->name }}
                        @if ($event->user->dni)
                            <br><span style="color: #6B7280; font-size: 8pt; font-weight: normal;">DNI:
                                {{ $event->user->dni }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($event->start, 'UTC')->setTimezone($team->timezone ?: config('app.timezone'))->format('d/m/Y H:i') }}
                    </td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($event->end, 'UTC')->setTimezone($team->timezone ?: config('app.timezone'))->format('d/m/Y H:i') }}
                    </td>
                    <td class="text-right font-medium">
                        {{ $event->getPeriodForUser($event->user) }}
                    </td>
                    <td>
                        {{ $event->description }}
                    </td>
                    <td>
                        {{ $event->observations }}
                    </td>
                </tr>
            @endforeach

            @php
                // Calculate totals for this group
                $totalSeconds = 0;
                $pauseSeconds = 0;
                foreach ($groupEvents as $event) {
                    $duration = \Carbon\Carbon::parse($event->start)->diffInSeconds(\Carbon\Carbon::parse($event->end));
                    $totalSeconds += $duration;

                    // Check if it's a pause event
    if ($event->eventType && stripos($event->eventType->name, 'pausa') !== false) {
                        $pauseSeconds += $duration;
                    }
                }
                $netSeconds = $totalSeconds - $pauseSeconds;

                $totalHours = floor($totalSeconds / 3600);
                $totalMinutes = floor(($totalSeconds % 3600) / 60);

                $pauseHours = floor($pauseSeconds / 3600);
                $pauseMinutes = floor(($pauseSeconds % 3600) / 60);

                $netHours = floor($netSeconds / 3600);
                $netMinutes = floor(($netSeconds % 3600) / 60);
            @endphp

            <tr style="background-color: #F3F4F6; border-top: 2px solid #D1D5DB; font-weight: bold;">
                <td colspan="3" style="text-align: right; padding: 8px 12px; color: #374151;">
                    {{ trans('reports.Totals') }}:
                </td>
                <td class="text-right" style="padding: 8px 12px; color: #1F2937;">
                    {{ $totalHours }}h {{ $totalMinutes }}m
                    @if (isset($groupEquivalentDays) && isset($groupEquivalentDays[$groupName]) && !empty($groupEquivalentDays[$groupName]))
                        <br><span
                            style="color: #6B7280; font-size: 8pt; font-weight: 500;">{{ $groupEquivalentDays[$groupName] }}</span>
                    @endif
                </td>
                <td colspan="2" style="padding: 8px 12px; color: #6B7280; font-size: 8pt;">
                    {{ trans('reports.Pauses') }}: {{ $pauseHours }}h {{ $pauseMinutes }}m |
                    {{ trans('reports.Net') }}: {{ $netHours }}h {{ $netMinutes }}m
                </td>
            </tr>
            @endforeach
        @else
            @foreach ($events as $event)
                <tr>
                    <td class="font-medium">
                        {{ $event->user->family_name1 }} {{ $event->user->family_name2 }}, {{ $event->user->name }}
                        @if ($event->user->dni)
                            <br><span style="color: #6B7280; font-size: 8pt; font-weight: normal;">DNI:
                                {{ $event->user->dni }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($event->start, 'UTC')->setTimezone($team->timezone ?: config('app.timezone'))->format('d/m/Y H:i') }}
                    </td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($event->end, 'UTC')->setTimezone($team->timezone ?: config('app.timezone'))->format('d/m/Y H:i') }}
                    </td>
                    <td class="text-right font-medium">
                        {{ $event->getPeriodForUser($event->user) }}
                    </td>
                    <td>
                        {{ $event->description }}
                    </td>
                    <td>
                        {{ $event->observations }}
                    </td>
                </tr>
            @endforeach
            @endif
        </tbody>
    </table>
</body>

</html>
