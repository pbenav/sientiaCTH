@extends('mobile.layout')

@section('title', 'Inicio')

@section('content')
<div class="p-4 space-y-6 max-w-md mx-auto">
    
    <!-- Welcome Section -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center space-x-4">
            <div class="h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="h-6 w-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900">
                    Hola, {{ $user->name }}
                </h2>
                <p class="text-sm text-gray-600">
                    {{ $workCenter->name }}
                </p>
            </div>
        </div>
    </div>

    <!-- Current Status Card -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Estado Actual</h3>
        
        <div class="space-y-4">
            <!-- Current Time -->
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Hora actual:</span>
                <span class="font-semibold" id="currentTime">{{ now()->format('H:i:s') }}</span>
            </div>
            
            <!-- Clock Status -->
            @if($clockData['can_clock'] ?? false)
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Estado:</span>
                    <div class="text-right">
                        @if(($clockData['action'] ?? '') === 'clock_in')
                            <span class="text-green-600 font-semibold">Listo para fichar entrada</span>
                        @elseif(($clockData['action'] ?? '') === 'working_options')
                            <span class="text-blue-600 font-semibold">Trabajando</span>
                        @elseif(($clockData['action'] ?? '') === 'resume_workday')
                            <span class="text-orange-600 font-semibold">En pausa</span>
                        @elseif(($clockData['action'] ?? '') === 'confirm_exceptional_clock_in')
                            <span class="text-yellow-600 font-semibold">Fuera de horario</span>
                        @else
                            <span class="text-red-600 font-semibold">Listo para fichar salida</span>
                        @endif
                    </div>
                </div>
            @else
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Estado:</span>
                    <span class="text-orange-600 font-semibold">{{ $clockData['message'] ?? 'No se puede fichar' }}</span>
                </div>
            @endif

            <!-- Today's Hours -->
            <div class="flex justify-between items-center border-t pt-4">
                <span class="text-gray-600">Horas trabajadas hoy:</span>
                <span class="font-semibold text-lg">{{ $todayStats['worked_hours'] ?? '0:00' }}</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones Rápidas</h3>
        
        <div class="space-y-4">
            <!-- Clock Action Button - Changes based on state -->
            @if(($clockData['can_clock'] ?? false) || (($clockData['action'] ?? '') === 'confirm_exceptional_clock_in'))
                @if(($clockData['action'] ?? '') === 'working_options')
                    <!-- Working State - Show pause and clock out buttons -->
                    <div class="flex flex-col space-y-3">
                        <!-- Pause Button -->
                        <button onclick="performClockAction('pause')" 
                                class="bg-orange-500 text-white p-4 rounded-lg font-semibold text-center hover:bg-orange-600 transition duration-200 flex flex-col items-center min-h-[80px] max-w-xs">
                            <svg class="w-6 h-6 mb-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm4-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm">Pausar</span>
                        </button>
                        
                        <!-- Clock Out Button -->
                        <button onclick="performClockAction('clock_out')" 
                                class="bg-red-500 text-white p-4 rounded-lg font-semibold text-center hover:bg-red-600 transition duration-200 flex flex-col items-center min-h-[80px] max-w-xs">
                            <svg class="w-6 h-6 mb-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm">Fin Jornada</span>
                        </button>
                    </div>
                @elseif(($clockData['action'] ?? '') === 'resume_workday')
                    <!-- Resume from Pause State -->
                    <button onclick="performClockAction()" 
                            class="w-full bg-blue-500 text-white p-4 rounded-lg font-semibold text-center hover:bg-blue-600 transition duration-200 flex flex-col items-center min-h-[80px] max-w-sm">
                        <svg class="w-6 h-6 mb-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Continuar Trabajo</span>
                    </button>
                @elseif(($clockData['action'] ?? '') === 'confirm_exceptional_clock_in')
                    <!-- Exceptional Clock In State -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-center text-yellow-800 mb-3">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="font-medium">{{ __('Outside schedule') }}</span>
                        </div>
                        <p class="text-sm text-yellow-700 text-center mb-4">
                            {{ $clockData['message'] ?? __('You are outside your work schedule. Do you want to make an exceptional clock-in?') }}
                        </p>
                        @if(isset($clockData['next_slot']))
                        <div class="bg-yellow-100 border border-yellow-300 rounded p-3 mb-4">
                            <p class="text-xs text-yellow-800">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                                Próximo horario: {{ $clockData['next_slot']['start'] }} - {{ $clockData['next_slot']['end'] }}
                                @if(isset($clockData['next_slot']['minutes_until']))
                                    ({{ $clockData['next_slot']['minutes_until'] }} minutos)
                                @endif
                            </p>
                        </div>
                        @endif
                        <button onclick="performClockAction('confirm_exceptional_clock_in')" 
                                class="w-full bg-yellow-500 text-white p-4 rounded-lg font-semibold text-center hover:bg-yellow-600 transition duration-200 flex flex-col items-center min-h-[80px] max-w-sm">
                            <svg class="w-6 h-6 mb-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Fichaje Excepcional</span>
                        </button>
                    </div>
                @elseif(($clockData['action'] ?? '') === 'clock_in')
                    <!-- Clock In State -->
                    <button onclick="performClockAction()" 
                            class="w-full bg-green-500 text-white p-4 rounded-lg font-semibold text-center hover:bg-green-600 transition duration-200 flex flex-col items-center min-h-[80px] max-w-sm">
                        <svg class="w-6 h-6 mb-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Inicio de Jornada</span>
                    </button>
                @else
                    <!-- Clock Out State -->
                    <button onclick="performClockAction()" 
                            class="w-full bg-red-500 text-white p-4 rounded-lg font-semibold text-center hover:bg-red-600 transition duration-200 flex flex-col items-center min-h-[80px] max-w-sm">
                        <svg class="w-6 h-6 mb-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Fin de Jornada</span>
                    </button>
                @endif
            @else
                <!-- Cannot Clock -->
                <button disabled 
                        class="w-full bg-gray-400 text-white p-4 rounded-lg font-semibold text-center cursor-not-allowed flex flex-col items-center min-h-[80px] max-w-sm">
                    <svg class="w-6 h-6 mb-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                    <span>{{ $clockData['message'] ?? 'No se puede fichar' }}</span>
                </button>
            @endif

    <!-- Today's Summary -->
    @if($todayStats)
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumen de Hoy</h3>
        
        <div class="grid grid-cols-2 gap-4 text-center">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">{{ $todayStats['total_entries'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Entradas</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{ $todayStats['total_exits'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Salidas</div>
            </div>
        </div>
    </div>
    @endif

</div>

<!-- Loading overlay for clock actions -->
<div id="clockLoadingOverlay" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-xl p-8 flex flex-col items-center space-y-4 shadow-2xl max-w-sm mx-auto">
        <!-- Animated clock icon -->
        <div class="relative">
            <svg class="animate-spin h-12 w-12 text-blue-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <!-- Pulsing dot -->
            <div class="absolute top-0 left-0 w-3 h-3 bg-green-500 rounded-full animate-ping"></div>
        </div>
        
        <!-- Loading message -->
        <div class="text-center">
            <h3 class="text-lg font-semibold text-gray-900 mb-2" id="loadingTitle">Procesando fichaje...</h3>
            <p class="text-sm text-gray-600" id="loadingMessage">Por favor espera un momento</p>
        </div>
        
        <!-- Progress indicator -->
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-blue-600 h-2 rounded-full animate-pulse" style="width: 60%"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Update current time every second
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('es-ES', { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit' 
        });
        document.getElementById('currentTime').textContent = timeString;
    }
    
    setInterval(updateTime, 1000);
    updateTime(); // Initial call

    // Clock action function
    async function performClockAction(action = null) {
        const loadingOverlay = document.getElementById('clockLoadingOverlay');
        const loadingTitle = document.getElementById('loadingTitle');
        const loadingMessage = document.getElementById('loadingMessage');
        
        let isSuccess = false;
        
        // Set loading message based on action
        let title, message;
        if (action === 'pause') {
            title = 'Iniciando pausa...';
            message = 'Registrando el inicio de tu descanso';
        } else if (action === 'clock_out') {
            title = 'Finalizando jornada...';
            message = 'Cerrando tu jornada laboral del día';
        } else if (action === 'confirm_exceptional_clock_in') {
            title = 'Procesando fichaje excepcional...';
            message = 'Registrando entrada fuera de horario laboral';
        } else {
            // Default clock in or resume
            const currentAction = '{{ $clockData["action"] ?? "" }}';
            if (currentAction === 'clock_in') {
                title = 'Iniciando jornada...';
                message = 'Registrando el inicio de tu jornada laboral';
            } else if (currentAction === 'resume_workday') {
                title = 'Reanudando trabajo...';
                message = 'Finalizando pausa y continuando jornada';
            } else {
                title = 'Procesando fichaje...';
                message = 'Actualizando tu estado laboral';
            }
        }
        
        loadingTitle.textContent = title;
        loadingMessage.textContent = message;
        
        try {
            // Show loading
            loadingOverlay.classList.remove('hidden');
            
            // Disable all clock buttons
            document.querySelectorAll('button[onclick*="performClockAction"]').forEach(btn => {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            });
            
            // Get CSRF token
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Prepare request data
            const requestData = {
                work_center_code: '{{ $workCenter->code }}',
                user_code: '{{ $user->user_code }}'
            };
            
            if (action) {
                requestData.action = action;
            }
            
            // Make API call
            const response = await fetch('/api/v1/mobile/clock', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(requestData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                isSuccess = true;
                
                // Update loading message for success
                loadingTitle.textContent = '¡Fichaje completado!';
                loadingMessage.textContent = result.message;
                
                // Show success message
                showNotification('Fichaje registrado correctamente: ' + result.message, 'success');
                
                // Reload page to update stats and button state
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                // Update loading message for error
                loadingTitle.textContent = 'Error en el fichaje';
                loadingMessage.textContent = result.message;
                
                showNotification('Error: ' + result.message, 'error');
                
                // Hide loading after showing error
                setTimeout(() => {
                    loadingOverlay.classList.add('hidden');
                }, 2000);
            }
        } catch (error) {
            console.error('Clock action error:', error);
            
            // Update loading message for connection error
            loadingTitle.textContent = 'Error de conexión';
            loadingMessage.textContent = 'Verifica tu conexión e intenta de nuevo';
            
            showNotification('Error de conexión. Inténtalo de nuevo.', 'error');
            
            // Hide loading after showing error
            setTimeout(() => {
                loadingOverlay.classList.add('hidden');
            }, 3000);
        } finally {
            // Re-enable all clock buttons (only if not reloading)
            if (!isSuccess) {
                document.querySelectorAll('button[onclick*="performClockAction"]').forEach(btn => {
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                });
            }
        }
    }
    
    // Notification function
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 left-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500 text-white' : 
            type === 'error' ? 'bg-red-500 text-white' : 
            'bg-blue-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
</script>
@endpush