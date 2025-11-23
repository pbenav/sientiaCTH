<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page {
            margin: 1cm 1cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #4F46E5; /* Indigo-600 */
            padding-bottom: 10px;
        }
        .header h1 {
            color: #4F46E5;
            margin: 0;
            font-size: 16pt;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 8pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed; /* Enforce fixed widths */
        }
        th {
            background-color: #F3F4F6; /* Gray-100 */
            color: #1F2937; /* Gray-800 */
            font-weight: bold;
            padding: 6px 4px;
            text-align: center; /* Headers centered */
            border-bottom: 1px solid #9CA3AF; /* Gray-400 */
            font-size: 8pt;
            vertical-align: middle;
        }
        td {
            padding: 6px 4px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 8pt;
            vertical-align: top;
            text-align: left; /* Default left alignment */
            word-wrap: break-word;
        }
        tr:nth-child(even) {
            background-color: #F9FAFB; /* Gray-50 */
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 7pt;
            color: #9CA3AF;
            border-top: 1px solid #E5E7EB;
            padding-top: 5px;
        }
        .meta-info {
            margin-bottom: 10px;
            font-size: 9pt;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('Report of Events') }}</h1>
        <p>{{ __('Generated on') }}: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="meta-info">
        <strong>{{ __('Total Records') }}:</strong> {{ $events->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 20%;">{{ __('Name') }}</th>
                <th style="width: 13%;">{{ __('Start') }}</th>
                <th style="width: 13%;">{{ __('End') }}</th>
                <th style="width: 10%;">{{ __('Duration') }}</th>
                <th style="width: 22%;">{{ __('Description') }}</th>
                <th style="width: 22%;">{{ __('Observations') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($events as $event)
            <tr>
                <td>{{ $event->user->name }} {{ $event->user->family_name1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($event->start)->format('d/m/Y H:i') }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($event->end)->format('d/m/Y H:i') }}</td>
                <td class="text-right">{{ $event->getPeriod() }}</td>
                <td>{{ $event->description }}</td>
                <td>{{ $event->observations }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        CTH - Control de Tiempo y Horarios
    </div>
</body>
</html>
