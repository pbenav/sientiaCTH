<div class="space-y-6">
    
    <!-- User Information Header -->
    @auth
    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <div class="text-white">
                <!-- Nombre del Usuario -->
                <h3 class="text-lg font-semibold mb-1">{{ $this->getUserInfo()['full_name'] }}</h3>
                
                <!-- Team and Work Center Information -->
                <div class="text-xs text-white mb-1 flex justify-between items-start gap-2">
                    <!-- Team Information -->
                    <div class="flex-1 truncate">
                        @if($team)
                            <strong>{{ $team }}</strong>
                        @endif
                    </div>
                    <!-- Work Center Information -->
                    <div class="flex-1 text-right truncate">
                        @if($workCenter)
                            {{ $workCenter }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Date & Time Display -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800">
            <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-300">
                <div class="flex items-center">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    <span>{{ $this->getCurrentDateTime()->translatedFormat('l, j \d\e F \d\e Y') }}</span>
                </div>
                <div class="flex items-center font-mono text-lg">
                    <i class="fas fa-clock mr-2"></i>
                    <span>{{ $this->getCurrentDateTime()->format('H:i:s') }}</span>
                </div>
            </div>
        </div>
    </div>
    @endauth

    <!-- Status Messages -->
    @if($message)
        <div class="p-4 rounded-lg {{ $messageType === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : ($messageType === 'error' ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-blue-100 text-blue-800 border border-blue-200') }}">
            <div class="flex items-center">
                <i class="mr-2 {{ $messageType === 'success' ? 'fas fa-check-circle' : ($messageType === 'error' ? 'fas fa-exclamation-circle' : 'fas fa-info-circle') }}"></i>
                {{ $message }}
            </div>
        </div>
    @endif

    <!-- Current Shift Status -->
    @if(($clockData['action'] ?? '') === 'clock_out' && isset($clockData['started_at']))
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-3 animate-pulse"></div>
                    <div>
                        <h4 class="font-medium text-blue-900 dark:text-blue-100">{{ __('Current shift') }}</h4>
                        <p class="text-blue-700 dark:text-blue-300 text-sm">
                            {{ __('Started at') }}: {{ $clockData['started_at'] }}
                            @if($clockData['current_slot'] ?? false)
                                <span class="ml-2 px-2 py-1 text-xs bg-green-100 text-green-800 rounded">{{ __('In schedule') }}</span>
                            @else
                                <span class="ml-2 px-2 py-1 text-xs bg-orange-100 text-orange-800 rounded">{{ __('Outside schedule') }}</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Clock Action Button -->
    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 p-6">
        @if($clockData['can_clock'] ?? false)
            @if(!$showConfirmation)
                <button 
                    wire:click="$set('showConfirmation', true)"
                    class="w-full px-6 py-4 text-lg font-semibold text-white transition-all duration-200 rounded-lg shadow-md transform hover:scale-105 {{ ($clockData['action'] ?? '') === 'clock_in' ? 'bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800' : 'bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800' }} focus:outline-none focus:ring-2 focus:ring-offset-2 {{ ($clockData['action'] ?? '') === 'clock_in' ? 'focus:ring-green-500' : 'focus:ring-red-500' }}">
                    <div class="flex items-center justify-center">
                        <i class="mr-3 text-xl {{ ($clockData['action'] ?? '') === 'clock_in' ? 'fas fa-sign-in-alt' : 'fas fa-sign-out-alt' }}"></i>
                        <span>{{ ($clockData['action'] ?? '') === 'clock_in' ? __('Clock In') : __('Clock Out') }}</span>
                    </div>
                </button>
                
                <!-- Action Description -->
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400 text-center">
                    {{ ($clockData['action'] ?? '') === 'clock_in' ? __('Ready to start your shift') : __('Ready to end your shift') }}
                </p>
            @else
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400 mr-2"></i>
                        <p class="text-yellow-800 dark:text-yellow-200 font-medium">
                            {{ ($clockData['action'] ?? '') === 'clock_in' ? __('Confirm Clock In') : __('Confirm Clock Out') }}
                        </p>
                    </div>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-4">
                        {{ ($clockData['action'] ?? '') === 'clock_in' ? __('Are you sure you want to clock in now?') : __('Are you sure you want to clock out now?') }}
                    </p>
                    <div class="flex space-x-3">
                        <button 
                            wire:click="handleClockAction"
                            class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <i class="fas fa-check mr-2"></i>{{ __('Confirm') }}
                        </button>
                        <button 
                            wire:click="$set('showConfirmation', false)"
                            class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                            <i class="fas fa-times mr-2"></i>{{ __('Cancel') }}
                        </button>
                    </div>
                </div>
            @endif
        @else
            <div class="bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                @if(($clockData['action'] ?? '') === 'confirm_exceptional_clock_in')
                    @if(!$showConfirmation)
                        <div class="text-center">
                            <div class="flex items-center justify-center text-yellow-600 dark:text-yellow-400 mb-3">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <p class="font-medium">Fuera de horario</p>
                            </div>
                            <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">
                                {{ $clockData['message'] ?? 'Está fuera de su horario laboral. ¿Desea realizar un fichaje excepcional?' }}
                            </p>
                            @if(isset($clockData['next_slot']))
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded p-3 mb-4">
                                <p class="text-xs text-blue-700 dark:text-blue-300">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Próximo horario: {{ $clockData['next_slot']['start'] }} - {{ $clockData['next_slot']['end'] }}
                                    @if(isset($clockData['next_slot']['minutes_until']))
                                        ({{ $clockData['next_slot']['minutes_until'] }} minutos)
                                    @endif
                                </p>
                            </div>
                            @endif
                            <button 
                                wire:click="$set('showConfirmation', true)"
                                class="w-full px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors duration-200">
                                <i class="fas fa-exclamation-triangle mr-2"></i>{{ $clockData['button_text'] ?? 'Fichaje Excepcional' }}
                            </button>
                        </div>
                    @else
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <div class="flex items-center mb-4">
                                <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400 mr-2"></i>
                                <p class="text-yellow-800 dark:text-yellow-200 font-medium">Confirmar Fichaje Excepcional</p>
                            </div>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-4">
                                Está fuera de su horario laboral. ¿Confirma que desea realizar un fichaje excepcional?
                            </p>
                            <div class="bg-yellow-100 dark:bg-yellow-900/40 border border-yellow-300 dark:border-yellow-700 rounded p-3 mb-4">
                                <p class="text-xs text-yellow-800 dark:text-yellow-200">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    El fichaje excepcional requerirá autorización del administrador.
                                </p>
                            </div>
                            <div class="flex space-x-3">
                                <button 
                                    wire:click="confirmExceptionalClockIn"
                                    class="flex-1 px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors duration-200">
                                    <i class="fas fa-check mr-2"></i>Confirmar
                                </button>
                                <button 
                                    wire:click="$set('showConfirmation', false)"
                                    class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                                    <i class="fas fa-times mr-2"></i>Cancelar
                                </button>
                            </div>
                        </div>
                    @endif
                @elseif(($clockData['action'] ?? '') === 'redirect_to_events')
                    <div class="text-center">
                        <div class="flex items-center justify-center text-orange-600 dark:text-orange-400 mb-3">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <p class="font-medium">{{ $clockData['message'] ?? __('Cannot clock in/out at this time') }}</p>
                        </div>
                        <button 
                            onclick="window.location.href='{{ route('events') }}'"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <i class="fas fa-calendar-alt mr-2"></i>{{ $clockData['button_text'] ?? __('Go to Events') }}
                        </button>
                    </div>
                @elseif(($clockData['action'] ?? '') === 'redirect_to_profile')
                    <div class="text-center">
                        <div class="flex items-center justify-center text-blue-600 dark:text-blue-400 mb-3">
                            <i class="fas fa-cog mr-2"></i>
                            <p class="font-medium">{{ $clockData['message'] ?? __('No work schedule configured') }}</p>
                        </div>
                        <button 
                            onclick="
                                console.log('Redirecting to:', '{{ $clockData['redirect_url'] ?? route('profile.show') . '?tab=preferences#work-schedule-section' }}');
                                window.location.href='{{ $clockData['redirect_url'] ?? route('profile.show') . '?tab=preferences#work-schedule-section' }}';
                            "
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <i class="fas fa-user-cog mr-2"></i>{{ $clockData['button_text'] ?? __('Configure Schedule') }}
                        </button>
                    </div>
                @else
                    <div class="flex items-center justify-center text-gray-500 dark:text-gray-400">
                        <i class="fas fa-clock mr-2"></i>
                        <p class="text-center">
                            {{ $clockData['message'] ?? __('Cannot clock in/out at this time') }}
                        </p>
                    </div>
                @endif
            </div>
        @endif
        
        <!-- Refresh Button -->
        <div class="text-center mt-4">
            <button wire:click="refreshClockData" 
                    class="text-sm text-gray-500 hover:text-gray-700 underline transition-colors duration-200">
                <i class="fas fa-sync-alt mr-1"></i>{{ __('Refresh Status') }}
            </button>
        </div>
    </div>

</div>

<script>
    // Auto-refresh time every minute
    setInterval(function() {
        @this.call('refreshClockData');
    }, 60000);
</script>
