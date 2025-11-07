@extends('mobile.layout')

@section('title', 'Informes')

@section('content')
<div class="p-4 space-y-6">
    
    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-sm p-4">
        <form method="GET" action="{{ route('mobile.reports') }}" class="space-y-4">
            <!-- Date Range -->
            <div class="flex space-x-3">
                <div class="flex-1">
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                    <input type="date" 
                           id="start_date" 
                           name="start_date" 
                           value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 btn-mobile">
                </div>
                <div class="flex-1">
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                    <input type="date" 
                           id="end_date" 
                           name="end_date" 
                           value="{{ request('end_date', now()->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 btn-mobile">
                </div>
            </div>

            <!-- Report Type -->
            <div>
                <label for="report_type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Informe</label>
                <select id="report_type" 
                        name="report_type" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 btn-mobile">
                    <option value="summary" {{ request('report_type') === 'summary' ? 'selected' : '' }}>Resumen General</option>
                    <option value="daily" {{ request('report_type') === 'daily' ? 'selected' : '' }}>Detalle Diario</option>
                    <option value="weekly" {{ request('report_type') === 'weekly' ? 'selected' : '' }}>Resumen Semanal</option>
                    <option value="monthly" {{ request('report_type') === 'monthly' ? 'selected' : '' }}>Resumen Mensual</option>
                </select>
            </div>

            <!-- Quick Date Ranges -->
            <div class="grid grid-cols-3 gap-2 text-sm">
                <button type="button" onclick="setDateRange('week')" class="bg-gray-100 text-gray-700 py-2 px-3 rounded-lg hover:bg-gray-200 btn-mobile">
                    Esta semana
                </button>
                <button type="button" onclick="setDateRange('month')" class="bg-gray-100 text-gray-700 py-2 px-3 rounded-lg hover:bg-gray-200 btn-mobile">
                    Este mes
                </button>
                <button type="button" onclick="setDateRange('year')" class="bg-gray-100 text-gray-700 py-2 px-3 rounded-lg hover:bg-gray-200 btn-mobile">
                    Este año
                </button>
            </div>

            <button type="submit" 
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-blue-700 transition duration-200 btn-mobile">
                Generar Informe
            </button>
        </form>
    </div>

    <!-- Overall Summary -->
    @if($overallStats)
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumen del Período</h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $overallStats['total_days'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Días Trabajados</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-green-600">{{ $overallStats['total_hours'] ?? '0:00' }}</div>
                <div class="text-sm text-gray-600">Horas Total</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $overallStats['avg_daily_hours'] ?? '0:00' }}</div>
                <div class="text-sm text-gray-600">Promedio Diario</div>
            </div>
            <div class="bg-orange-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-orange-600">{{ $overallStats['total_entries'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Total Fichajes</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Report Content based on type -->
    @if(request('report_type') === 'daily' || !request('report_type'))
        <!-- Daily Report -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900">Detalle Diario</h3>
            @forelse($dailyData ?? [] as $day)
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 border-b">
                        <div class="flex justify-between items-center">
                            <h4 class="font-semibold text-gray-900">
                                {{ \Carbon\Carbon::parse($day['date'])->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                            </h4>
                            <span class="text-sm font-medium {{ $day['total_hours'] ? 'text-green-600' : 'text-gray-500' }}">
                                {{ $day['total_hours'] ?? 'Sin datos' }}
                            </span>
                        </div>
                    </div>
                    <div class="p-4">
                        @if($day['entries'] && count($day['entries']) > 0)
                            <div class="space-y-2">
                                @foreach($day['entries'] as $entry)
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="flex items-center space-x-2">
                                            <span class="w-2 h-2 rounded-full {{ $entry['type'] === 'entrada' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                            <span>{{ ucfirst($entry['type']) }}</span>
                                        </span>
                                        <span class="font-medium">{{ $entry['time'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-sm">Sin fichajes registrados</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No hay datos</h3>
                    <p class="text-gray-600">No se encontraron datos para el período seleccionado.</p>
                </div>
            @endforelse
        </div>

    @elseif(request('report_type') === 'weekly')
        <!-- Weekly Report -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900">Resumen Semanal</h3>
            @forelse($weeklyData ?? [] as $week)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-semibold text-gray-900">
                            Semana {{ $week['week_number'] }} - {{ $week['year'] }}
                        </h4>
                        <span class="text-lg font-bold text-blue-600">{{ $week['total_hours'] ?? '0:00' }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <div class="text-lg font-bold text-gray-700">{{ $week['days_worked'] ?? 0 }}</div>
                            <div class="text-xs text-gray-600">Días</div>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <div class="text-lg font-bold text-gray-700">{{ $week['avg_daily_hours'] ?? '0:00' }}</div>
                            <div class="text-xs text-gray-600">Promedio</div>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <div class="text-lg font-bold text-gray-700">{{ $week['total_entries'] ?? 0 }}</div>
                            <div class="text-xs text-gray-600">Fichajes</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                    <p class="text-gray-600">No hay datos semanales para mostrar.</p>
                </div>
            @endforelse
        </div>

    @elseif(request('report_type') === 'monthly')
        <!-- Monthly Report -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900">Resumen Mensual</h3>
            @forelse($monthlyData ?? [] as $month)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-semibold text-gray-900">
                            {{ \Carbon\Carbon::create($month['year'], $month['month'])->locale('es')->isoFormat('MMMM [de] YYYY') }}
                        </h4>
                        <span class="text-lg font-bold text-blue-600">{{ $month['total_hours'] ?? '0:00' }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg text-center">
                            <div class="text-xl font-bold text-blue-600">{{ $month['days_worked'] ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Días Trabajados</div>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg text-center">
                            <div class="text-xl font-bold text-green-600">{{ $month['avg_daily_hours'] ?? '0:00' }}</div>
                            <div class="text-sm text-gray-600">Promedio Diario</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                    <p class="text-gray-600">No hay datos mensuales para mostrar.</p>
                </div>
            @endforelse
        </div>
    @endif

    <!-- Export Options -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Exportar Datos</h3>
        <div class="grid grid-cols-2 gap-4">
            <button onclick="exportData('pdf')" 
                    class="flex items-center justify-center space-x-2 bg-red-600 text-white py-3 px-4 rounded-lg hover:bg-red-700 transition duration-200 btn-mobile">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"></path>
                </svg>
                <span>PDF</span>
            </button>
            
            <button onclick="exportData('excel')" 
                    class="flex items-center justify-center space-x-2 bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 transition duration-200 btn-mobile">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                <span>Excel</span>
            </button>
        </div>
    </div>

</div>

<!-- Loading overlay for exports -->
<div id="exportLoadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <svg class="animate-spin h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-gray-700">Generando reporte...</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Set quick date ranges
    function setDateRange(range) {
        const today = new Date();
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');
        
        switch(range) {
            case 'week':
                const startOfWeek = new Date(today.setDate(today.getDate() - today.getDay()));
                startDate.value = startOfWeek.toISOString().split('T')[0];
                endDate.value = new Date().toISOString().split('T')[0];
                break;
            case 'month':
                const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
                startDate.value = startOfMonth.toISOString().split('T')[0];
                endDate.value = new Date().toISOString().split('T')[0];
                break;
            case 'year':
                const startOfYear = new Date(today.getFullYear(), 0, 1);
                startDate.value = startOfYear.toISOString().split('T')[0];
                endDate.value = new Date().toISOString().split('T')[0];
                break;
        }
        
        // Auto-submit after small delay
        setTimeout(() => {
            document.querySelector('form').submit();
        }, 100);
    }
    
    // Export data
    function exportData(format) {
        const loadingOverlay = document.getElementById('exportLoadingOverlay');
        loadingOverlay.classList.remove('hidden');
        
        // Get current form data
        const formData = new FormData(document.querySelector('form'));
        formData.append('export_format', format);
        
        // Create export URL
        const url = new URL('{{ route("mobile.reports.export") }}', window.location.origin);
        for (let [key, value] of formData.entries()) {
            url.searchParams.append(key, value);
        }
        
        // Download file
        const link = document.createElement('a');
        link.href = url.toString();
        link.download = `reporte_${format}_${new Date().toISOString().split('T')[0]}.${format}`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Hide loading after delay
        setTimeout(() => {
            loadingOverlay.classList.add('hidden');
        }, 2000);
    }
    
    // Auto-update report when type changes
    document.getElementById('report_type').addEventListener('change', function() {
        setTimeout(() => {
            document.querySelector('form').submit();
        }, 100);
    });
</script>
@endpush