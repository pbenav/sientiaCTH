<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page {
            margin: 1cm;
            size: A4 landscape;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #1F2937;
            line-height: 1.5;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #4F46E5;
        }
        
        .header h1 {
            color: #4F46E5;
            font-size: 24pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
        }
        
        .header p {
            color: #6B7280;
            font-size: 10pt;
        }
        
        .meta-info {
            margin-bottom: 20px;
            font-size: 11pt;
            color: #374151;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 9pt;
            font-weight: 600;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        th {
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 0.5px;
        }
        
        th.text-center {
            text-align: center;
        }
        
        th.text-right {
            text-align: right;
        }
        
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 8.5pt;
            vertical-align: top;
        }
        
        tbody tr:nth-child(even) {
            background-color: #F9FAFB;
        }
        
        tbody tr:hover {
            background-color: #F3F4F6;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .font-medium {
            font-weight: 600;
            color: #111827;
        }
        
        .footer {
            position: fixed;
            bottom: 10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #9CA3AF;
            padding-top: 10px;
            border-top: 1px solid #E5E7EB;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('Report of Events') }}</h1>
        <p>{{ __('Generated on') }}: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="meta-info">
        <strong>{{ __('Total Records') }}:</strong> 
        <span class="badge">{{ $events->count() }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">{{ __('Name') }}</th>
                <th class="text-center" style="width: 12%;">{{ __('Start') }}</th>
                <th class="text-center" style="width: 12%;">{{ __('End') }}</th>
                <th class="text-right" style="width: 14%;">{{ __('Duration') }}</th>
                <th style="width: 18%;">{{ __('Description') }}</th>
                <th style="width: 29%;">{{ __('Observations') }}</th>
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
