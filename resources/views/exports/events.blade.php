<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page {
            margin: 1cm 1cm;
            size: landscape;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #1F2937; /* Gray-800 */
            background-color: #FFFF00; /* YELLOW DEBUG */
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #FF0000; /* RED FOR DEBUG */
            padding-bottom: 15px;
        }
        .header h1 {
            color: #4F46E5;
            margin: 0;
            font-size: 18pt;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 5px 0 0;
            color: #6B7280; /* Gray-500 */
            font-size: 9pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            table-layout: fixed;
        }
        th {
            background-color: #F3F4F6; /* Gray-100 */
            color: #374151; /* Gray-700 */
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 6px;
            text-align: left;
            border-bottom: 2px solid #E5E7EB; /* Gray-200 */
            font-size: 8pt;
            vertical-align: middle;
        }
        td {
            padding: 8px 6px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 8pt;
            vertical-align: top;
            color: #4B5563; /* Gray-600 */
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
        .font-medium {
            font-weight: 600;
            color: #111827; /* Gray-900 */
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #9CA3AF;
            border-top: 1px solid #E5E7EB;
            padding-top: 10px;
        }
        .meta-info {
            margin-bottom: 15px;
            font-size: 10pt;
            color: #374151;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7pt;
            font-weight: bold;
            background-color: #E0E7FF;
            color: #4338CA;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('Report of Events') }}</h1>
        <p>{{ __('Generated on') }}: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="meta-info">
        <strong>{{ __('Total Records') }}:</strong> <span class="badge">{{ $events->count() }}</span>
    </div>

    <div style="width: 100%;">
    <table style="width: 100%; table-layout: fixed;">
        <thead>
            <tr>
                <th style="width: 15%;">{{ __('Name') }}</th>
                <th style="width: 10%;" class="text-center">{{ __('Start') }}</th>
                <th style="width: 10%;" class="text-center">{{ __('End') }}</th>
                <th style="width: 10%;" class="text-right">{{ __('Duration') }}</th>
                <th style="width: 27.5%;">{{ __('Description') }}</th>
                <th style="width: 27.5%;">{{ __('Observations') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($events as $event)
            <tr>
                <td class="font-medium">{{ $event->user->name }} {{ $event->user->family_name1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($event->start)->format('d/m/Y H:i') }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($event->end)->format('d/m/Y H:i') }}</td>
                <td class="text-right font-medium">{{ $event->getPeriod() }}</td>
                <td>{{ $event->description }}</td>
                <td>{{ $event->observations }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    <div class="footer">
        sientiaCTH - Control de Tiempo y Horarios | {{ __('Page') }} <span class="page-number"></span>
    </div>
    
    <script type="text/php">
        if (isset($pdf)) {
            $text = "{PAGE_NUM} / {PAGE_COUNT}";
            $size = 8;
            $font = $fontMetrics->getFont("Helvetica");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 20;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>
</html>
