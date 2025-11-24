<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
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
    <div class="header">
        <h1>{{ trans('reports.Report of Events') }}</h1>
        <p class="subtitle">{{ $team->name }}</p>
        @if($workCenter)
            <p class="subtitle">{{ $workCenter->name }}</p>
        @endif
        <p>{{ trans('reports.Generated on') }}: {{ now()->format('d/m/Y H:i') }}</p>
        @if($startDate && $endDate)
            <p>{{ trans('reports.Period') }}: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        @endif
    </div>

    <div class="meta-info">
        <strong>{{ trans('reports.Total Records') }}:</strong> 
        <span class="badge">{{ $events->count() }}</span>
        <span style="margin-left: 20px;">
            <strong>{{ trans('reports.Total Duration') }}:</strong> 
            <span class="badge" style="background-color: #4F46E5;">{{ $totalDuration }}</span>
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
            @foreach($events as $event)
            <tr>
                <td class="font-medium">
                    {{ $event->user->name }} {{ $event->user->family_name1 }}
                </td>
                <td class="text-center">
                    {{ \Carbon\Carbon::parse($event->start)->format('d/m/Y H:i') }}
                </td>
                <td class="text-center">
                    {{ \Carbon\Carbon::parse($event->end)->format('d/m/Y H:i') }}
                </td>
                <td class="text-right font-medium">
                    {{ $event->getPeriod() }}
                </td>
                <td>
                    {{ $event->description }}
                </td>
                <td>
                    {{ $event->observations }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
