@extends('mobile.layout')

@section('title', 'Horario')

@section('content')
<div class="p-4 space-y-6">
    
    <!-- Current Week Navigation -->
    <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="flex items-center justify-between mb-4">
            <button onclick="changeWeek(-1)" class="p-2 rounded-lg hover:bg-gray-100 btn-mobile">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            
            <div class="text-center">
                <h3 class="font-semibold text-gray-900" id="weekTitle">
                    {{ $currentWeek['start']->locale('es')->isoFormat('D [de] MMMM') }} - 
                    {{ $currentWeek['end']->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
                </h3>
                <p class="text-sm text-gray-600">Semana {{ $currentWeek['week_number'] }}</p>
            </div>
            
            <button onclick="changeWeek(1)" class="p-2 rounded-lg hover:bg-gray-100 btn-mobile">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
        
        <!-- Quick Week Navigation -->
        <div class="flex space-x-2 text-sm">
            <button onclick="goToCurrentWeek()" class="flex-1 bg-blue-100 text-blue-700 py-2 px-3 rounded-lg hover:bg-blue-200 btn-mobile">
                Esta semana
            </button>
            <button onclick="goToNextWeek()" class="flex-1 bg-gray-100 text-gray-700 py-2 px-3 rounded-lg hover:bg-gray-200 btn-mobile">
                Próxima
            </button>
        </div>
    </div>

    <!-- Weekly Schedule -->
    <div class="space-y-4">
        @foreach($weekSchedule as $dayData)
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <!-- Day Header -->
                <div class="px-4 py-3 {{ $dayData['is_today'] ? 'bg-blue-50 border-l-4 border-blue-500' : 'bg-gray-50' }}">
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="font-semibold {{ $dayData['is_today'] ? 'text-blue-900' : 'text-gray-900' }}">
                                {{ $dayData['day_name'] }}
                            </h4>
                            <p class="text-sm {{ $dayData['is_today'] ? 'text-blue-600' : 'text-gray-600' }}">
                                {{ $dayData['date']->locale('es')->isoFormat('D [de] MMMM') }}
                                @if($dayData['is_today'])
                                    <span class="ml-1 text-xs bg-blue-500 text-white px-2 py-1 rounded-full">HOY</span>
                                @endif
                            </p>
                        </div>
                        @if($dayData['total_hours'])
                            <div class="text-right">
                                <div class="font-semibold {{ $dayData['is_today'] ? 'text-blue-900' : 'text-gray-900' }}">
                                    {{ $dayData['total_hours'] }}
                                </div>
                                <div class="text-xs {{ $dayData['is_today'] ? 'text-blue-600' : 'text-gray-500' }}">
                                    horas
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Day Schedule -->
                <div class="p-4">
                    @if($dayData['schedule'] && count($dayData['schedule']) > 0)
                        <div class="space-y-3">
                            @foreach($dayData['schedule'] as $shift)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                        <div>
                                            <div class="font-medium">{{ $shift['team_name'] ?? 'Turno' }}</div>
                                            @if($shift['description'])
                                                <div class="text-sm text-gray-600">{{ $shift['description'] }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold">
                                            {{ $shift['start_time'] }} - {{ $shift['end_time'] }}
                                        </div>
                                        @if($shift['duration'])
                                            <div class="text-sm text-gray-600">{{ $shift['duration'] }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif($dayData['is_holiday'])
                        <div class="text-center py-4">
                            <svg class="w-8 h-8 mx-auto mb-2 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            <p class="text-orange-600 font-medium">Día festivo</p>
                            <p class="text-sm text-gray-600">{{ $dayData['holiday_name'] ?? 'Festivo' }}</p>
                        </div>
                    @elseif($dayData['is_weekend'])
                        <div class="text-center py-4">
                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0v-.5A1.5 1.5 0 0114.5 6c.526 0 .988-.27 1.256-.679a6.012 6.012 0 011.912 2.706A3.001 3.001 0 0116 11.5a3.001 3.001 0 01-1.668.471A3.001 3.001 0 0112 14.5a3.001 3.001 0 01-4 0 3.001 3.001 0 01-2.332-2.029A3.001 3.001 0 014 11.5a3.001 3.001 0 01.332-1.473z" clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-gray-600 font-medium">Fin de semana</p>
                            <p class="text-sm text-gray-500">No hay horario programado</p>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-gray-600 font-medium">Sin horario</p>
                            <p class="text-sm text-gray-500">No hay turnos programados</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Weekly Summary -->
    @if($weekSummary)
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumen Semanal</h3>
        <div class="grid grid-cols-2 gap-4 text-center">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">{{ $weekSummary['scheduled_hours'] ?? '0:00' }}</div>
                <div class="text-sm text-gray-600">Horas Programadas</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{ $weekSummary['working_days'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Días Laborables</div>
            </div>
        </div>
    </div>
    @endif

</div>

<!-- Loading overlay for week changes -->
<div id="scheduleLoadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <svg class="animate-spin h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-gray-700">Cargando horario...</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentWeekOffset = 0;
    
    // Change week function
    function changeWeek(direction) {
        currentWeekOffset += direction;
        loadWeekSchedule();
    }
    
    // Go to current week
    function goToCurrentWeek() {
        currentWeekOffset = 0;
        loadWeekSchedule();
    }
    
    // Go to next week
    function goToNextWeek() {
        currentWeekOffset = 1;
        loadWeekSchedule();
    }
    
    // Load week schedule (would make AJAX call in full implementation)
    function loadWeekSchedule() {
        const loadingOverlay = document.getElementById('scheduleLoadingOverlay');
        loadingOverlay.classList.remove('hidden');
        
        // In a full implementation, this would make an AJAX call
        // For now, we'll reload the page with the new week offset
        const url = new URL(window.location);
        url.searchParams.set('week_offset', currentWeekOffset);
        
        setTimeout(() => {
            window.location.href = url.toString();
        }, 500);
    }
    
    // Initialize week offset from URL if present
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const weekOffset = urlParams.get('week_offset');
        if (weekOffset) {
            currentWeekOffset = parseInt(weekOffset);
        }
    });
</script>
@endpush