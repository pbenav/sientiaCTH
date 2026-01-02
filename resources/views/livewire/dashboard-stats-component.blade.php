<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    {{-- Current & Next Shift --}}
    <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="flex items-center mb-2">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h4 class="text-sm font-semibold text-gray-700">{{ __('Horario') }}</h4>
        </div>
        @if($currentSlot)
            <div class="mt-2">
                <p class="text-xs text-gray-500">{{ __('Tramo actual') }}</p>
                <p class="text-lg font-bold text-green-600">{{ $currentSlot['start'] }} - {{ $currentSlot['end'] }}</p>
            </div>
        @endif
        @if($nextSlot)
            <div class="mt-2">
                <p class="text-xs text-gray-500">{{ __('Próximo tramo') }}</p>
                <p class="text-sm font-medium text-gray-700">{{ $nextSlot['start'] }} - {{ $nextSlot['end'] }}</p>
            </div>
        @endif
        @if(!$currentSlot && !$nextSlot)
            <p class="text-sm text-gray-500">{{ __('Sin horario hoy') }}</p>
        @endif
    </div>

    {{-- Today's Hours --}}
    <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="flex items-center mb-2">
            <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h4 class="text-sm font-semibold text-gray-700">{{ __('Hoy') }}</h4>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $todayNetHours }}</p>
        @if($todayPauseHours !== '00:00:00')
            <p class="text-xs text-gray-500 mt-1">({{ $todayHours }} - {{ $todayPauseHours }} pausas)</p>
        @else
            <p class="text-xs text-gray-500">{{ __('Trabajadas') }}</p>
        @endif
    </div>

    {{-- This Week --}}
    <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="flex items-center mb-2">
            <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h4 class="text-sm font-semibold text-gray-700">{{ __('Esta semana') }}</h4>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $weekNetHours }}</p>
        @if($weekPauseHours !== '00:00:00')
            <p class="text-xs text-gray-500 mt-1">({{ $weekHours }} - {{ $weekPauseHours }} pausas)</p>
        @else
            <p class="text-xs text-gray-500">{{ __('Trabajadas') }}</p>
        @endif
    </div>

    {{-- This Year --}}
    <div class="bg-white rounded-lg shadow-sm p-4">
        <div class="flex items-center mb-2">
            <svg class="w-5 h-5 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <h4 class="text-sm font-semibold text-gray-700">{{ __('Este año') }}</h4>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $yearDays }}</p>
        <p class="text-xs text-gray-500">{{ __('Días trabajados') }}</p>
    </div>
</div>
