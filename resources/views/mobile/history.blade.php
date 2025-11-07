@extends('mobile.layout')

@section('title', 'Historial')

@section('content')
<div class="p-4 space-y-6">
    
    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-sm p-4">
        <form method="GET" action="{{ route('mobile.history') }}" class="space-y-4">
            <div class="flex space-x-3">
                <div class="flex-1">
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                    <input type="date" 
                           id="start_date" 
                           name="start_date" 
                           value="{{ request('start_date', now()->subDays(30)->format('Y-m-d')) }}"
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
            <button type="submit" 
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-blue-700 transition duration-200 btn-mobile">
                Filtrar
            </button>
        </form>
    </div>

    <!-- Summary Stats -->
    @if($summaryStats)
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumen del Período</h3>
        <div class="grid grid-cols-2 gap-4 text-center">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-xl font-bold text-blue-600">{{ $summaryStats['total_days'] }}</div>
                <div class="text-sm text-gray-600">Días</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-xl font-bold text-green-600">{{ $summaryStats['total_hours'] }}</div>
                <div class="text-sm text-gray-600">Horas Total</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Clock History -->
    <div class="space-y-4">
        @forelse($clockHistory as $date => $dayEvents)
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <!-- Date Header -->
                <div class="bg-gray-50 px-4 py-3 border-b">
                    <div class="flex justify-between items-center">
                        <h4 class="font-semibold text-gray-900">
                            {{ \Carbon\Carbon::parse($date)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                        </h4>
                        @if(isset($dayEvents['summary']))
                            <span class="text-sm text-gray-600">
                                {{ $dayEvents['summary']['worked_hours'] ?? '0:00' }}
                            </span>
                        @endif
                    </div>
                </div>
                
                <!-- Events for the day -->
                <div class="p-4 space-y-3">
                    @if(isset($dayEvents['events']) && count($dayEvents['events']) > 0)
                        @foreach($dayEvents['events'] as $event)
                            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $event['action'] === 'entrada' ? 'bg-green-100' : 'bg-red-100' }}">
                                        @if($event['action'] === 'entrada')
                                            <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-medium">
                                            {{ $event['action'] === 'entrada' ? 'Entrada' : 'Salida' }}
                                        </div>
                                        @if(isset($event['team']))
                                            <div class="text-sm text-gray-500">{{ $event['team'] }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold">
                                        {{ \Carbon\Carbon::parse($event['datetime'])->format('H:i') }}
                                    </div>
                                    @if(isset($event['location']))
                                        <div class="text-xs text-gray-500">{{ $event['location'] }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-gray-500">
                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Sin fichajes registrados
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No hay datos</h3>
                <p class="text-gray-600">No se encontraron fichajes en el período seleccionado.</p>
                <button onclick="resetDates()" 
                        class="mt-4 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200 btn-mobile">
                    Ver últimos 30 días
                </button>
            </div>
        @endforelse
    </div>

    <!-- Load More Button (if applicable) -->
    @if(isset($hasMorePages) && $hasMorePages)
        <div class="text-center">
            <button onclick="loadMoreHistory()" 
                    id="loadMoreBtn"
                    class="bg-gray-100 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-200 transition duration-200 btn-mobile">
                Cargar más
            </button>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    // Reset dates to last 30 days
    function resetDates() {
        const today = new Date();
        const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
        
        document.getElementById('start_date').value = thirtyDaysAgo.toISOString().split('T')[0];
        document.getElementById('end_date').value = today.toISOString().split('T')[0];
        
        // Submit form
        document.querySelector('form').submit();
    }
    
    // Load more history (if pagination is implemented)
    function loadMoreHistory() {
        const btn = document.getElementById('loadMoreBtn');
        btn.textContent = 'Cargando...';
        btn.disabled = true;
        
        // This would be implemented with AJAX if needed
        // For now, just reload with expanded dates
        setTimeout(() => {
            btn.textContent = 'Cargar más';
            btn.disabled = false;
        }, 1000);
    }
    
    // Auto-submit form when dates change
    document.getElementById('start_date').addEventListener('change', function() {
        if (this.value && document.getElementById('end_date').value) {
            // Add small delay to allow for quick date range selection
            setTimeout(() => {
                if (Date.now() - lastDateChange > 2000) {
                    document.querySelector('form').submit();
                }
            }, 2000);
        }
    });
    
    document.getElementById('end_date').addEventListener('change', function() {
        if (this.value && document.getElementById('start_date').value) {
            // Add small delay to allow for quick date range selection
            setTimeout(() => {
                if (Date.now() - lastDateChange > 2000) {
                    document.querySelector('form').submit();
                }
            }, 2000);
        }
    });
    
    let lastDateChange = Date.now();
    
    // Update lastDateChange timestamp
    document.getElementById('start_date').addEventListener('input', () => lastDateChange = Date.now());
    document.getElementById('end_date').addEventListener('input', () => lastDateChange = Date.now());
</script>
@endpush