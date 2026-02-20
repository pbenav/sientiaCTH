<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            color: #1F2937;
            line-height: 1.4;
            padding: 15px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #4F46E5;
        }

        .header h1 {
            color: #4F46E5;
            font-size: 20pt;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .header p {
            color: #6B7280;
            font-size: 9pt;
        }

        .meta-info {
            margin-bottom: 15px;
            font-size: 9pt;
            color: #374151;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
            background-color: #667eea;
            color: white;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #374151;
            margin: 15px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #E5E7EB;
        }

        .page-break-before {
            page-break-before: always;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table.kpi-table td {
            width: 25%;
            padding: 8px;
            vertical-align: top;
        }

        .kpi-card {
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 6px;
            padding: 8px;
            height: 100%;
        }

        .kpi-title {
            font-size: 7.5pt;
            color: #6B7280;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .kpi-value {
            font-size: 16pt;
            font-weight: bold;
            color: #111827;
        }

        .kpi-subtitle {
            font-size: 7pt;
            color: #9CA3AF;
            margin-top: 2px;
        }

        .chart-container {
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 15px;
        }

        .chart-title {
            font-size: 10pt;
            font-weight: bold;
            color: #374151;
            margin-bottom: 8px;
        }

        .chart-summary {
            font-size: 8pt;
            color: #6B7280;
            margin-top: 8px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ __('Statistics Report') }}</h1>
        <h2 style="font-size: 12pt; color: #374151; margin: 4px 0;">{{ $team->name }}</h2>
        <p>{{ __('Generated on') }}: {{ now()->format('d/m/Y H:i') }}</p>
        @if ($fromDate && $toDate)
            <p>{{ __('Period') }}: {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} -
                {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}</p>
        @endif
    </div>

    <div class="meta-info">
        <strong>{{ __('Worker') }}:</strong>
        <span
            class="badge">{{ $browsedUser->dni ? $browsedUser->dni . ' - ' : '' }}{{ trim($browsedUser->family_name1 . ' ' . $browsedUser->family_name2 . ', ' . $browsedUser->name) }}</span>
        &nbsp;&nbsp;
        @if ($eventTypeId && $eventTypeId !== 'All')
            @php
                $selectedEventType = $eventTypes->firstWhere('id', $eventTypeId);
            @endphp
            @if ($selectedEventType)
                <strong>{{ __('Event Type') }}:</strong>
                <span class="badge"
                    style="background-color: {{ $selectedEventType->color }};">{{ $selectedEventType->name }}</span>
                &nbsp;&nbsp;
            @endif
        @endif
        <strong>{{ __('Total Hours') }}:</strong>
        <span class="badge">{{ $totalHoursFmt }}</span>
        &nbsp;&nbsp;
        <strong>{{ __('Total Days') }}:</strong>
        <span class="badge">{{ $totalDays }}</span>
    </div>

    {{-- Chart Section --}}
    @if (!empty($chartData))
        <div class="chart-container">
            <div class="chart-title">{{ __('Registered hours') }}</div>

            @php
                // 1. Preparar datos
                $days = array_keys($chartData);
                $values = [];
                $maxHours = 0;

                foreach ($chartData as $day => $types) {
                    $dayTotal = 0;
                    foreach ($types as $type => $data) {
                        $dayTotal += $data['hours'] ?? 0;
                    }
                    $values[] = $dayTotal;
                    if ($dayTotal > $maxHours) {
                        $maxHours = $dayTotal;
                    }
                }

                // Asegurar un máximo razonable para el eje Y
                $maxHours = max($maxHours, 1);
                $yMax = ceil($maxHours * 1.1);

                // 2. Configuración SVG - Aumentado para A4 apaisado
                $width = 1000; // Aumentado para aprovechar el ancho de página
                $height = 350; // Aumentado para mejor visualización
                $padding = ['top' => 30, 'right' => 30, 'bottom' => 30, 'left' => 40];

                $graphWidth = $width - $padding['left'] - $padding['right'];
                $graphHeight = $height - $padding['top'] - $padding['bottom'];

                $count = count($values);
                $stepX = $count > 1 ? $graphWidth / ($count - 1) : $graphWidth;

                // 3. Generar puntos y barras
                $points = [];
                $barWidth = $stepX * 0.6;

                foreach ($values as $i => $val) {
                    $x = $padding['left'] + $i * $stepX;
                    $y = $padding['top'] + $graphHeight - ($val / $yMax) * $graphHeight;
                    $barHeight = ($val / $yMax) * $graphHeight;

                    $points[] = [
                        'x' => $x,
                        'y' => $y,
                        'val' => $val,
                        'label' => $days[$i],
                        'barHeight' => $barHeight,
                    ];
                }

                // String para la polilínea
                $polylinePoints = '';
                foreach ($points as $p) {
                    $polylinePoints .= "{$p['x']},{$p['y']} ";
                }

                // Colores
                $lineColor = '#4F46E5';
                $barColor = '#818CF8';

                // Calcular resumen
                $dayCount = count($chartData);
                $totalHoursChart = array_sum($values);
                $avgHours = $dayCount > 0 ? round($totalHoursChart / $dayCount, 2) : 0;
            @endphp

            <svg viewBox="0 0 {{ $width }} {{ $height }}"
                style="width: 100%; height: auto; font-family: DejaVu Sans, sans-serif;">

                {{-- Grid Lines (Horizontal) --}}
                @for ($i = 0; $i <= 5; $i++)
                    @php
                        $gridY = $padding['top'] + ($graphHeight * $i) / 5;
                        $labelVal = round($yMax * (1 - $i / 5), 1);
                    @endphp
                    <line x1="{{ $padding['left'] }}" y1="{{ $gridY }}" x2="{{ $width - $padding['right'] }}"
                        y2="{{ $gridY }}" stroke="#E5E7EB" stroke-width="1" />
                    <text x="{{ $padding['left'] - 5 }}" y="{{ $gridY + 4 }}" font-size="10" fill="#9CA3AF"
                        text-anchor="end">{{ $labelVal }}h</text>
                @endfor

                {{-- Bars --}}
                @foreach ($points as $p)
                    @if ($p['val'] > 0)
                        <rect x="{{ $p['x'] - $barWidth / 2 }}" y="{{ $p['y'] }}" width="{{ $barWidth }}"
                            height="{{ $p['barHeight'] }}" fill="{{ $barColor }}" opacity="0.7" />
                    @endif
                @endforeach

                {{-- Data Line --}}
                <polyline points="{{ $polylinePoints }}" fill="none" stroke="{{ $lineColor }}"
                    stroke-width="2" />

                {{-- Data Points & Labels --}}
                @foreach ($points as $p)
                    {{-- X Axis Label --}}
                    <text x="{{ $p['x'] }}" y="{{ $height - 5 }}" font-size="9" fill="#6B7280"
                        text-anchor="middle">{{ $p['label'] }}</text>

                    {{-- Point Circle --}}
                    <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="3" fill="white"
                        stroke="{{ $lineColor }}" stroke-width="2" />

                    {{-- Value Label (only if > 0) --}}
                    @if ($p['val'] > 0)
                        <text x="{{ $p['x'] }}" y="{{ $p['y'] - 8 }}" font-size="8" font-weight="bold"
                            fill="#374151" text-anchor="middle">{{ round($p['val'], 1) }}</text>
                    @endif
                @endforeach
            </svg>

            <div class="chart-summary">
                <strong>{{ __('Days with activity') }}:</strong> {{ $dayCount }} &nbsp;|&nbsp;
                <strong>{{ __('Total hours') }}:</strong> {{ round($totalHoursChart, 2) }}h &nbsp;|&nbsp;
                <strong>{{ __('Average per day') }}:</strong> {{ $avgHours }}h
            </div>
        </div>
    @endif

    {{-- Page break before KPIs section --}}
    <pagebreak />

    {{-- KPIs: Workday Compliance --}}
    <div class="section-title">{{ __('stats.workday_compliance') }}</div>
    <table class="kpi-table">
        <tr>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('Punctuality') }}</div>
                    <div>
                        <span class="kpi-value">{{ $dashboardData['punctuality'] ?? '0' }}%</span>
                        <span class="kpi-value"
                            style="font-size: 13pt; color: #9CA3AF; margin-left: 5px;">{{ $dashboardData['real_punctuality'] ?? '0' }}%</span>
                    </div>
                    <div class="kpi-subtitle">Cumplimiento general de horario</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('stats.entry') }}</div>
                    <div>
                        <span class="kpi-value">{{ $dashboardData['punctuality_entry_pct'] ?? '0' }}%</span>
                        <span class="kpi-value"
                            style="font-size: 13pt; color: #9CA3AF; margin-left: 5px;">{{ $dashboardData['punctuality_entry_real_pct'] ?? '0' }}%</span>
                    </div>
                    <div class="kpi-subtitle">Retraso: {{ $dashboardData['punctuality_entry_minutes'] ?? '0m 0s' }} |
                        Verif: {{ $dashboardData['punctuality_entry_backdate_minutes'] ?? '0m 0s' }}</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('stats.exit') }}</div>
                    <div>
                        <span class="kpi-value">{{ $dashboardData['punctuality_exit_pct'] ?? '0' }}%</span>
                        <span class="kpi-value"
                            style="font-size: 13pt; color: #9CA3AF; margin-left: 5px;">{{ $dashboardData['punctuality_exit_real_pct'] ?? '0' }}%</span>
                    </div>
                    <div class="kpi-subtitle">Adelanto: {{ $dashboardData['punctuality_exit_minutes'] ?? '0m 0s' }} |
                        Verif: {{ $dashboardData['punctuality_exit_backdate_minutes'] ?? '0m 0s' }}</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('stats.combined') }}</div>
                    <div class="kpi-value">{{ $dashboardData['punctuality_combined_pct'] ?? '0' }}%</div>
                    <div class="kpi-subtitle">Puntualidad entrada y salida</div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('Workday Completion') }}</div>
                    <div class="kpi-value">{{ round($dashboardData['percentage_completion'] ?? 0) }}%</div>
                    <div class="kpi-subtitle">Horas registradas vs programadas</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('Extra Hours') }} ({{ __('Balance') }})</div>
                    <div class="kpi-value">{{ $dashboardData['extra_hours_fmt'] ?? '0h 00m' }}</div>
                    <div class="kpi-subtitle">Exceso sobre horas programadas</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('Absenteeism (days)') }}</div>
                    <div class="kpi-value">{{ $dashboardData['absenteeism'] ?? '0' }}</div>
                    <div class="kpi-subtitle">Días sin registros de trabajo</div>
                </div>
            </td>
            <td></td>
        </tr>
    </table>

    {{-- KPIs: Hours and Records --}}
    <div class="section-title">{{ __('stats.hours_and_records') }}</div>
    <table class="kpi-table">
        <tr>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('Scheduled Hours') }}</div>
                    <div class="kpi-value">{{ $scheduledHoursFmt }}</div>
                    <div class="kpi-subtitle">Horas según horario laboral</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('Registered Hours') }}</div>
                    <div class="kpi-value">{{ $totalHoursFmt }}</div>
                    <div class="kpi-subtitle">Horas realmente trabajadas</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('Records Confidence') }}</div>
                    <div class="kpi-value">{{ $dashboardData['avg_confidence'] ?? '0' }}%</div>
                    <div class="kpi-subtitle">{{ __('Min') }}: {{ $dashboardData['min_confidence'] ?? '0' }}% /
                        {{ __('Max') }}: {{ $dashboardData['max_confidence'] ?? '0' }}%</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('Exceptional Clock-ins') }}</div>
                    <div class="kpi-value">{{ $dashboardData['exceptional_events_count'] ?? '0' }}</div>
                    <div class="kpi-subtitle">Fichajes fuera de horario</div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('Automatic Closures') }}</div>
                    <div class="kpi-value">{{ $dashboardData['automatically_closed_count'] ?? '0' }}</div>
                    <div class="kpi-subtitle">Eventos cerrados automáticamente</div>
                </div>
            </td>
            <td colspan="3"></td>
        </tr>
    </table>

    {{-- Authorizable Events --}}
    @if (!empty($dashboardData['authorizable_events']) && count($dashboardData['authorizable_events']) > 0)
        <div class="section-title">{{ __('stats.authorizable_events') }} ({{ now()->year }})</div>
        <table class="kpi-table">
            @foreach (array_chunk($dashboardData['authorizable_events'], 4) as $row)
                <tr>
                    @foreach ($row as $authEvent)
                        <td>
                            <div class="kpi-card">
                                <div class="kpi-title">{{ $authEvent['description'] }}</div>
                                <div class="kpi-value">{{ $authEvent['days'] }}</div>
                                <div class="kpi-subtitle">{{ __('stats.days_in') }} {{ now()->year }}</div>
                            </div>
                        </td>
                    @endforeach
                    @for ($i = count($row); $i < 4; $i++)
                        <td></td>
                    @endfor
                </tr>
            @endforeach
        </table>
    @endif
</body>

</html>
