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
            line-height: 1.4;
            padding: 15px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
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
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
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
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        
        .meta-info-item {
            margin: 3px 10px;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: 600;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .kpi-card {
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .kpi-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 6px;
        }
        
        .kpi-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            font-size: 16px;
            color: white;
        }
        
        .kpi-icon.blue { background-color: #3B82F6; }
        .kpi-icon.green { background-color: #10B981; }
        .kpi-icon.indigo { background-color: #6366F1; }
        .kpi-icon.red { background-color: #EF4444; }
        .kpi-icon.gray { background-color: #6B7280; }
        .kpi-icon.purple { background-color: #A855F7; }
        .kpi-icon.yellow { background-color: #F59E0B; }
        .kpi-icon.orange { background-color: #F97316; }
        
        .kpi-title {
            font-size: 7.5pt;
            color: #6B7280;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .kpi-value {
            font-size: 18pt;
            font-weight: 700;
            color: #111827;
            margin-top: 2px;
        }
        
        .kpi-subtitle {
            font-size: 7pt;
            color: #9CA3AF;
            margin-top: 2px;
        }
        
        .section-title {
            font-size: 12pt;
            font-weight: 600;
            color: #374151;
            margin: 15px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #E5E7EB;
        }
        
        .page-break-before {
            page-break-before: always;
        }
        
        .chart-container {
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .chart-title {
            font-size: 10pt;
            font-weight: 600;
            color: #374151;
            margin-bottom: 10px;
        }
        
        .chart-bars {
            display: flex;
            align-items: flex-end;
            height: 300px;
            border-bottom: 2px solid #E5E7EB;
            border-left: 2px solid #E5E7EB;
            padding: 10px 0 0 10px;
            gap: 4px;
        }
        
        .chart-bar-group {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
        }
        
        .chart-bar {
            width: 100%;
            border-radius: 4px 4px 0 0;
            position: relative;
            min-height: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: height 0.3s ease;
        }
        
        .chart-label {
            font-size: 6.5pt;
            color: #6B7280;
            margin-top: 4px;
            text-align: center;
            transform: rotate(-45deg);
            transform-origin: center;
            white-space: nowrap;
        }
        
        .chart-value {
            font-size: 6pt;
            font-weight: 600;
            color: #374151; /* Darker color for better contrast outside bar */
            position: absolute;
            top: -18px; /* Move slightly higher */
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255,255,255,0.9); /* Light background */
            padding: 1px 4px;
            border-radius: 3px;
            white-space: nowrap;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .legend {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
            font-size: 7.5pt;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
        }
        
        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 3px;
            margin-right: 5px;
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
        <div class="meta-info-item">
            <strong>{{ __('Worker') }}:</strong> 
            <span class="badge">{{ $browsedUser->name }} {{ $browsedUser->family_name1 }}</span>
        </div>
        @if($eventTypeId && $eventTypeId !== 'All')
            @php
                $selectedEventType = $eventTypes->firstWhere('id', $eventTypeId);
            @endphp
            @if($selectedEventType)
                <div class="meta-info-item">
                    <strong>{{ __('Event Type') }}:</strong> 
                    <span class="badge" style="background-color: {{ $selectedEventType->color }};">{{ $selectedEventType->name }}</span>
                </div>
            @endif
        @endif
        <div class="meta-info-item">
            <strong>{{ __('Total Hours') }}:</strong> 
            <span class="badge" style="background: linear-gradient(135deg, #4F46E5 0%, #4338ca 100%);">{{ $totalNetHoursFmt }}</span>
            @if($totalPauseHours > 0)
                <span style="font-size: 7.5pt; color: #6B7280; margin-left: 5px;">({{ $totalHoursFmt }} - {{ $totalPauseHours }} pausas)</span>
            @endif
        </div>
        <div class="meta-info-item">
            <strong>{{ __('Total Days') }}:</strong> 
            <span class="badge" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%);">{{ $totalDays }}</span>
        </div>
    </div>

    {{-- Chart Section --}}
    @if(!empty($chartData))
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
                
                // Asegurar un máximo razonable para el eje Y (mínimo 1h, y un poco de margen arriba)
                $maxHours = max($maxHours, 1);
                $yMax = ceil($maxHours * 1.1); 
                
                // 2. Configuración SVG
                $width = 1000; // Ancho interno del SVG
                $height = 300; // Alto interno del SVG
                $padding = ['top' => 30, 'right' => 30, 'bottom' => 30, 'left' => 40];
                
                $graphWidth = $width - $padding['left'] - $padding['right'];
                $graphHeight = $height - $padding['top'] - $padding['bottom'];
                
                $count = count($values);
                $stepX = $count > 1 ? $graphWidth / ($count - 1) : $graphWidth;
                
                // 3. Generar puntos y barras
                $points = [];
                $barWidth = ($stepX * 0.6); // Ancho de barra (60% del espacio disponible)
                
                foreach ($values as $i => $val) {
                    $x = $padding['left'] + ($i * $stepX);
                    // Invertir Y porque SVG 0,0 es arriba-izquierda
                    $y = $padding['top'] + $graphHeight - (($val / $yMax) * $graphHeight);
                    $barHeight = ($val / $yMax) * $graphHeight;
                    
                    $points[] = [
                        'x' => $x, 
                        'y' => $y, 
                        'val' => $val, 
                        'label' => $days[$i],
                        'barHeight' => $barHeight
                    ];
                }
                
                // String para la polilínea
                $polylinePoints = "";
                foreach ($points as $p) {
                    $polylinePoints .= "{$p['x']},{$p['y']} ";
                }
                
                // Colores
                $lineColor = '#4F46E5'; // Indigo 600
                $barColor = '#818CF8';  // Indigo 400
                $barColorLight = '#C7D2FE'; // Indigo 200
            @endphp

            <div style="width: 100%; overflow-x: auto;">
                <svg viewBox="0 0 {{ $width }} {{ $height }}" preserveAspectRatio="none" style="width: 100%; height: 300px; font-family: sans-serif;">
                    
                    {{-- Grid Lines (Horizontal) --}}
                    @for ($i = 0; $i <= 5; $i++)
                        @php
                            $gridY = $padding['top'] + ($graphHeight * $i / 5);
                            $labelVal = round($yMax * (1 - $i/5), 1);
                        @endphp
                        <line x1="{{ $padding['left'] }}" y1="{{ $gridY }}" x2="{{ $width - $padding['right'] }}" y2="{{ $gridY }}" stroke="#E5E7EB" stroke-width="1" />
                        <text x="{{ $padding['left'] - 5 }}" y="{{ $gridY + 4 }}" font-size="10" fill="#9CA3AF" text-anchor="end">{{ $labelVal }}h</text>
                    @endfor

                    {{-- Bars --}}
                    @foreach ($points as $p)
                        @if($p['val'] > 0)
                            <rect x="{{ $p['x'] - ($barWidth / 2) }}" 
                                  y="{{ $p['y'] }}" 
                                  width="{{ $barWidth }}" 
                                  height="{{ $p['barHeight'] }}" 
                                  fill="{{ $barColor }}" 
                                  opacity="0.7"
                                  rx="2" ry="2" /> {{-- Rounded corners top --}}
                        @endif
                    @endforeach

                    {{-- Data Line --}}
                    <polyline points="{{ $polylinePoints }}" fill="none" stroke="{{ $lineColor }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />

                    {{-- Data Points & Labels --}}
                    @foreach ($points as $p)
                        {{-- X Axis Label --}}
                        <text x="{{ $p['x'] }}" y="{{ $height - 5 }}" font-size="10" fill="#6B7280" text-anchor="middle">{{ $p['label'] }}</text>
                        
                        {{-- Point Circle --}}
                        <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="3" fill="white" stroke="{{ $lineColor }}" stroke-width="2" />
                        
                        {{-- Value Label (only if > 0) --}}
                        @if($p['val'] > 0)
                            <text x="{{ $p['x'] }}" y="{{ $p['y'] - 8 }}" font-size="9" font-weight="bold" fill="#374151" text-anchor="middle">{{ round($p['val'], 1) }}</text>
                        @endif
                    @endforeach
                </svg>
            </div>
            
            {{-- Legend --}}
            <div class="legend" style="justify-content: center; margin-top: 5px;">
                <div class="legend-item">
                    <div class="legend-color" style="background-color: {{ $barColor }};"></div>
                    <span>{{ __('Registered Hours') }}</span>
                </div>
            </div>
        </div>
    @endif

    {{-- KPIs: Workday Compliance --}}
    <div class="section-title page-break-before">{{ __('stats.workday_compliance') }}</div>
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-icon blue">⏰</div>
                <div>
                    <div class="kpi-title">{{ __('Punctuality') }}</div>
                    <div style="display: flex; gap: 8px; align-items: baseline;">
                        <span class="kpi-value">{{ $dashboardData['punctuality'] ?? '0' }}%</span>
                        <span class="kpi-value" style="font-size: 14pt; color: #9CA3AF;">{{ $dashboardData['real_punctuality'] ?? '0' }}%</span>
                    </div>
                    <div class="kpi-subtitle">Cumplimiento general de horario</div>
                </div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-icon blue">→</div>
                <div>
                    <div class="kpi-title">{{ __('stats.entry') }}</div>
                    <div style="display: flex; gap: 8px; align-items: baseline;">
                        <span class="kpi-value">{{ $dashboardData['punctuality_entry_pct'] ?? '0' }}%</span>
                        <span class="kpi-value" style="font-size: 14pt; color: #9CA3AF;">{{ $dashboardData['punctuality_entry_real_pct'] ?? '0' }}%</span>
                    </div>
                    <div class="kpi-subtitle">Retraso: {{ $dashboardData['punctuality_entry_minutes'] ?? '0m 0s' }} <span style="color: #D1D5DB">|</span> Verif: {{ $dashboardData['punctuality_entry_backdate_minutes'] ?? '0m 0s' }}</div>
                </div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-icon green">←</div>
                <div>
                    <div class="kpi-title">{{ __('stats.exit') }}</div>
                    <div style="display: flex; gap: 8px; align-items: baseline;">
                        <span class="kpi-value">{{ $dashboardData['punctuality_exit_pct'] ?? '0' }}%</span>
                        <span class="kpi-value" style="font-size: 14pt; color: #9CA3AF;">{{ $dashboardData['punctuality_exit_real_pct'] ?? '0' }}%</span>
                    </div>
                    <div class="kpi-subtitle">Adelanto: {{ $dashboardData['punctuality_exit_minutes'] ?? '0m 0s' }} <span style="color: #D1D5DB">|</span> Verif: {{ $dashboardData['punctuality_exit_backdate_minutes'] ?? '0m 0s' }}</div>
                </div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-icon indigo">✓</div>
                <div>
                    <div class="kpi-title">{{ __('stats.combined') }}</div>
                    <div class="kpi-value">{{ $dashboardData['punctuality_combined_pct'] ?? '0' }}%</div>
                    <div class="kpi-subtitle">Puntualidad entrada y salida</div>
                </div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-icon indigo">✓</div>
                <div>
                    <div class="kpi-title">{{ __('Workday Completion') }}</div>
                    <div class="kpi-value">{{ round($dashboardData['percentage_completion'] ?? 0) }}%</div>
                    <div class="kpi-subtitle">Horas registradas vs programadas</div>
                </div>
            </div>
        </div>

            <div class="kpi-card-header">
                <div class="kpi-icon green">+</div>
                <div>
                    <div class="kpi-title">{{ __('Extra Hours') }} ({{ __('Balance') }})</div>
                    <div class="kpi-value">{{ $dashboardData['extra_hours_fmt'] ?? '0h 00m' }}</div>
                    <div class="kpi-subtitle">Exceso sobre horas programadas</div>
                </div>
            </div>

        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-icon red">✗</div>
                <div>
                    <div class="kpi-title">{{ __('Absenteeism (days)') }}</div>
                    <div class="kpi-value">{{ $dashboardData['absenteeism'] ?? '0' }}</div>
                    <div class="kpi-subtitle">Días sin registros de trabajo</div>
                </div>
            </div>
        </div>
    </div>

    {{-- KPIs: Hours and Records --}}
    <div class="section-title">{{ __('stats.hours_and_records') }}</div>
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-icon gray">⏰</div>
                <div>
                    <div class="kpi-title">{{ __('Scheduled Hours') }}</div>
                    <div class="kpi-value">{{ $scheduledHoursFmt }}</div>
                    <div class="kpi-subtitle">Horas según horario laboral</div>
                </div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-icon gray">⏰</div>
                <div>
                    <div class="kpi-title">{{ __('Registered Hours') }}</div>
                    <div class="kpi-value">{{ $totalHoursFmt }}</div>
                    <div class="kpi-subtitle">Horas realmente trabajadas</div>
                </div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-icon purple">✓</div>
                <div>
                    <div class="kpi-title">{{ __('Records Confidence') }}</div>
                    <div class="kpi-value">{{ $dashboardData['avg_confidence'] ?? '0' }}%</div>
                    <div class="kpi-subtitle">{{ __('Min') }}: {{ $dashboardData['min_confidence'] ?? '0' }}% / {{ __('Max') }}: {{ $dashboardData['max_confidence'] ?? '0' }}%</div>
                </div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-icon yellow">⚠</div>
                <div>
                    <div class="kpi-title">{{ __('Exceptional Clock-ins') }}</div>
                    <div class="kpi-value">{{ $dashboardData['exceptional_events_count'] ?? '0' }}</div>
                    <div class="kpi-subtitle">Fichajes fuera de horario</div>
                </div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-icon orange">🤖</div>
                <div>
                    <div class="kpi-title">{{ __('Automatic Closures') }}</div>
                    <div class="kpi-value">{{ $dashboardData['automatically_closed_count'] ?? '0' }}</div>
                    <div class="kpi-subtitle">Eventos cerrados automáticamente</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Authorizable Events --}}
    @if (!empty($dashboardData['authorizable_events']) && count($dashboardData['authorizable_events']) > 0)
        <div class="section-title">{{ __('stats.authorizable_events') }} ({{ now()->year }})</div>
        <div class="kpi-grid">
            @foreach ($dashboardData['authorizable_events'] as $authEvent)
                <div class="kpi-card">
                    <div class="kpi-card-header">
                        <div class="kpi-icon" style="background-color: {{ $authEvent['color'] ?? '#9333ea' }};">✓</div>
                        <div>
                            <div class="kpi-title">{{ $authEvent['description'] }}</div>
                            <div class="kpi-value">{{ $authEvent['days'] }}</div>
                            <div class="kpi-subtitle">{{ __('stats.days_in') }} {{ now()->year }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</body>
</html>
