<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Auditoría') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                <!-- Filters -->
                <div class="flex flex-col md:flex-row gap-4 mb-6">
                    <div class="w-full md:w-1/3">
                        <x-jet-input type="text" class="w-full" placeholder="{{ __('Buscar...') }}" wire:model.debounce.300ms="search" />
                    </div>
                    <div class="w-full md:w-1/3 flex gap-2">
                        <x-jet-input type="date" class="w-1/2" wire:model="dateFrom" />
                        <x-jet-input type="date" class="w-1/2" wire:model="dateTo" />
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('ID Evento') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Tipo') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Usuario Afectado') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Centro de Trabajo') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Modificado Por') }}
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Fecha') }}
                                </th>
                                <th scope="col" class="relative px-4 py-3">
                                    <span class="sr-only">{{ __('Acciones') }}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                        @if($log->event_id)
                                            <span class="text-indigo-600">#{{ $log->event_id }}</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                        {{ $log->event_type_name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                        {{ $log->affected_user_name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        {{ $log->work_center_name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                        {{ $log->user_name }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        <button wire:click="viewLog({{ $log->id }})" class="text-indigo-600 hover:text-indigo-900">
                                            <i class="fas fa-eye mr-1"></i> {{ __('Ver') }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        {{ __('No se encontraron registros de auditoría.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <x-jet-dialog-modal wire:model="confirmingLogView" maxWidth="6xl">
        <x-slot name="title">
            {{ __('Detalles de Auditoría') }}
        </x-slot>

        <x-slot name="content">
            @if($selectedLog)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Modified By') }}</p>
                        <p class="text-lg text-gray-900">{{ $selectedLog['user_name'] }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Date') }}</p>
                        <p class="text-lg text-gray-900">{{ $selectedLog['formatted_date'] }}</p>
                    </div>
                    @if(isset($selectedLog['event_id']))
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Event ID') }}</p>
                        <p class="text-lg text-gray-900">#{{ $selectedLog['event_id'] }}</p>
                    </div>
                    @endif
                </div>

                @if(isset($selectedLog['affected_user']) || isset($selectedLog['team_name']) || isset($selectedLog['work_center_name']))
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 pb-4 border-b border-gray-200">
                    @if(isset($selectedLog['affected_user']))
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Affected User') }}</p>
                        <p class="text-sm text-gray-900">{{ $selectedLog['affected_user'] }}</p>
                    </div>
                    @endif
                    @if(isset($selectedLog['team_name']))
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Team') }}</p>
                        <p class="text-sm text-gray-900">{{ $selectedLog['team_name'] }}</p>
                    </div>
                    @endif
                    @if(isset($selectedLog['work_center_name']))
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('Work Center') }}</p>
                        <p class="text-sm text-gray-900">{{ $selectedLog['work_center_name'] }}</p>
                    </div>
                    @endif
                </div>
                @endif

                <div class="border-t border-gray-200 pt-4">
                    <h4 class="text-md font-medium text-gray-900 mb-2">{{ __('Cambios Detectados') }}</h4>
                    
                    @php
                        $differences = $this->formatDiff($selectedLog['original_event'], $selectedLog['modified_event']);
                    @endphp

                    @if(count($differences) > 0)
                        <div class="bg-gray-50 rounded-md p-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 table-fixed">
                                <thead>
                                    <tr>
                                        <th style="width: 20%;" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Campo') }}</th>
                                        <th style="width: 40%;" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Valor Anterior') }}</th>
                                        <th style="width: 40%;" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Valor Nuevo') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($differences as $key => $diff)
                                        <tr>
                                            <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $key }}</td>
                                            <td class="px-4 py-2 text-sm text-red-600 font-mono break-all">
                                                @if($diff['type'] == 'added')
                                                    <span class="text-gray-400 italic">{{ __('(Nuevo)') }}</span>
                                                @else
                                                    {{ $diff['original'] }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-sm text-green-600 font-mono break-all">
                                                @if($diff['type'] == 'deleted')
                                                    <span class="text-gray-400 italic">{{ __('(Eliminado)') }}</span>
                                                @else
                                                    {{ $diff['modified'] }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 italic">{{ __('No se detectaron diferencias legibles o es una inserción/borrado completo.') }}</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                             <div>
                                <h5 class="text-xs font-bold uppercase text-gray-500 mb-1">{{ __('Original JSON') }}</h5>
                                <pre class="bg-gray-100 p-2 rounded text-xs overflow-auto max-h-40">{{ $selectedLog['original_event'] }}</pre>
                             </div>
                             <div>
                                <h5 class="text-xs font-bold uppercase text-gray-500 mb-1">{{ __('Modified JSON') }}</h5>
                                <pre class="bg-gray-100 p-2 rounded text-xs overflow-auto max-h-40">{{ $selectedLog['modified_event'] }}</pre>
                             </div>
                        </div>
                    @endif
                </div>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('confirmingLogView', false)" wire:loading.attr="disabled">
                {{ __('Cerrar') }}
            </x-jet-secondary-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>
