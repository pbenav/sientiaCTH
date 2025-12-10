<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
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
        @if($fromDate && $toDate)
            <p>{{ __('Period') }}: {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}</p>
        @endif
    </div>

    <div class="meta-info">
        <strong>{{ __('Worker') }}:</strong> 
        <span class="badge">{{ $browsedUser->name }} {{ $browsedUser->family_name1 }}</span>
        &nbsp;&nbsp;
        @if($eventTypeId && $eventTypeId !== 'All')
            @php
                $selectedEventType = $eventTypes->firstWhere('id', $eventTypeId);
            @endphp
            @if($selectedEventType)
                <strong>{{ __('Event Type') }}:</strong> 
                <span class="badge" style="background-color: {{ $selectedEventType->color }};">{{ $selectedEventType->name }}</span>
                &nbsp;&nbsp;
            @endif
        @endif
        <strong>{{ __('Total Hours') }}:</strong> 
        <span class="badge">{{ $totalHours }}</span>
        &nbsp;&nbsp;
        <strong>{{ __('Total Days') }}:</strong> 
        <span class="badge">{{ $totalDays }}</span>
    </div>

    {{-- Chart Summary (simplified for mPDF) --}}
    @if(!empty($chartData))
        <div class="chart-container">
            <div class="chart-title">{{ __('Registered hours') }}</div>
            <div class="chart-summary">
                @php
                    $dayCount = count($chartData);
                    $totalHoursChart = 0;
                    foreach ($chartData as $day => $types) {
                        foreach ($types as $type => $data) {
                            $totalHoursChart += $data['hours'] ?? 0;
                        }
                    }
                    $avgHours = $dayCount > 0 ? round($totalHoursChart / $dayCount, 2) : 0;
                @endphp
                <strong>{{ __('Days with activity') }}:</strong> {{ $dayCount }} &nbsp;|&nbsp;
                <strong>{{ __('Total hours') }}:</strong> {{ round($totalHoursChart, 2) }}h &nbsp;|&nbsp;
                <strong>{{ __('Average per day') }}:</strong> {{ $avgHours }}h
            </div>
        </div>
    @endif

    {{-- KPIs: Workday Compliance --}}
    <div class="section-title">{{ __('stats.workday_compliance') }}</div>
    <table class="kpi-table">
        <tr>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('Punctuality') }}</div>
                    <div class="kpi-value">{{ $dashboardData['punctuality'] ?? '0' }}%</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('stats.entry') }}</div>
                    <div class="kpi-value">{{ $dashboardData['punctuality_entry_pct'] ?? '0' }}%</div>
                    <div class="kpi-subtitle">{{ $dashboardData['punctuality_entry_minutes'] ?? '0' }} {{ __('stats.min') }}</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('stats.exit') }}</div>
                    <div class="kpi-value">{{ $dashboardData['punctuality_exit_pct'] ?? '0' }}%</div>
                    <div class="kpi-subtitle">{{ $dashboardData['punctuality_exit_minutes'] ?? '0' }} {{ __('stats.min') }}</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('stats.combined') }}</div>
                    <div class="kpi-value">{{ $dashboardData['punctuality_combined_pct'] ?? '0' }}%</div>
                    <div class="kpi-subtitle">{{ $dashboardData['punctuality_entry_backdate_minutes'] ?? '0' }} {{ __('stats.min') }} backdate</div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('Workday Completion') }}</div>
                    <div class="kpi-value">{{ round($dashboardData['percentage_completion'] ?? 0) }}%</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('Extra Hours') }}</div>
                    <div class="kpi-value">{{ $dashboardData['extra_hours'] ?? '0' }}</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('Absenteeism (days)') }}</div>
                    <div class="kpi-value">{{ $dashboardData['absenteeism'] ?? '0' }}</div>
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
                    <div class="kpi-value">{{ $scheduledHours }}</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('Registered Hours') }}</div>
                    <div class="kpi-value">{{ $totalHours }}</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('Records Confidence') }}</div>
                    <div class="kpi-value">{{ $dashboardData['avg_confidence'] ?? '0' }}%</div>
                    <div class="kpi-subtitle">{{ __('Min') }}: {{ $dashboardData['min_confidence'] ?? '0' }}% / {{ __('Max') }}: {{ $dashboardData['max_confidence'] ?? '0' }}%</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('Exceptional Clock-ins') }}</div>
                    <div class="kpi-value">{{ $dashboardData['exceptional_events_count'] ?? '0' }}</div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="kpi-card">
                    <div class="kpi-title">{{ __('Automatic Closures') }}</div>
                    <div class="kpi-value">{{ $dashboardData['automatically_closed_count'] ?? '0' }}</div>
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
