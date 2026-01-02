<div class="space-y-6" id="smart-clock-container" data-geo-enabled="{{ optional(auth()->user())->geolocation_enabled ? 'true' : 'false' }}">
    
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
                    <span id="current-date">{{ $this->getCurrentDateTime()->translatedFormat('l, j \d\e F \d\e Y') }}</span>
                </div>
                <div class="flex items-center font-mono text-lg">
                    <i class="fas fa-clock mr-2"></i>
                    <span id="current-time">{{ $this->getCurrentDateTime()->format('H:i:s') }}</span>
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
    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 overflow-hidden">
        <div class="p-4 sm:p-6">
            @if($showClockOutConfirmation)
                <!-- Clock Out Confirmation Modal -->
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-sign-out-alt text-red-600 dark:text-red-400 mr-2"></i>
                        <p class="text-red-800 dark:text-red-200 font-medium">{{ __('Confirm End Workday') }}</p>
                    </div>
                    <p class="text-sm text-red-700 dark:text-red-300 mb-4">
                        {{ __('Are you sure you want to end your workday?') }}
                    </p>
                    <div class="bg-red-100 dark:bg-red-900/40 border border-red-300 dark:border-red-700 rounded p-3 mb-4">
                        <p class="text-xs text-red-800 dark:text-red-200">
                            <i class="fas fa-info-circle mr-1"></i>
                            {{ __('This will close your current work event and you will need to clock in again to continue working.') }}
                        </p>
                    </div>
                    <div class="flex flex-col gap-3">
                        <button
                            onclick="callSmartClockMethod('confirmClockOut', this)"
                            class="w-full flex justify-center items-center px-4 py-3 text-sm font-medium text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 rounded-lg hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
                            <i class="fas fa-check mr-2"></i><span>{{ __('Confirm') }}</span>
                        </button>
                        <button
                            wire:click="cancelClockOut"
                            class="w-full flex justify-center items-center px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                            <i class="fas fa-times mr-2"></i><span>{{ __('Cancel') }}</span>
                        </button>
                    </div>
                </div>
            @elseif($clockData['can_clock'] ?? false)
                <!-- Working Options State - Show pause and clock out buttons -->
                @if(($clockData['action'] ?? '') === 'working_options')
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-3 animate-pulse"></div>
                                <div>
                                    <h4 class="font-medium text-green-900 dark:text-green-100">{{ __('Working') }}</h4>
                                    <p class="text-green-700 dark:text-green-300 text-sm">
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
                        <!-- Working Actions -->
                        <div class="flex flex-col gap-3">
                            <!-- Pause Button -->
                            @if($clockData['show_pause_option'] ?? false)
                            <button 
                                wire:click="pauseWorkday"
                                class="flex items-center justify-center min-w-[160px] px-4 py-3 text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 rounded-lg transition-all duration-200 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 min-h-[48px]">
                                <i class="fas fa-pause mr-2 text-sm"></i>
                                <span class="text-sm font-medium">{{ __('Pause Workday') }}</span>
                            </button>
                            @endif
                            <!-- Clock Out Button -->
                            @if($clockData['show_clock_out_option'] ?? false && ($clockData['action'] ?? '') !== 'resume_workday')
                            <button 
                                wire:click="clockOutFromWork"
                                class="flex items-center justify-center min-w-[160px] px-4 py-3 text-white bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 rounded-lg transition-all duration-200 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 min-h-[48px]">
                                <i class="fas fa-sign-out-alt mr-2 text-sm"></i>
                                <span class="text-sm font-medium">{{ __('End Workday') }}</span>
                            </button>
                            @endif
                        </div>
                    </div>
                <!-- Resume from Pause State -->
                @elseif(($clockData['action'] ?? '') === 'resume_workday')
                    <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-orange-500 rounded-full mr-3 animate-pulse"></div>
                                <div>
                                    <h4 class="font-medium text-orange-900 dark:text-orange-100">{{ __('On Pause') }}</h4>
                                    <p class="text-orange-700 dark:text-orange-300 text-sm">
                                        {{ __('Paused at') }}: {{ $clockData['paused_at'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <button 
                            onclick="callSmartClockMethod('handleClockAction', this)"
                            class="w-full px-6 py-4 text-lg font-semibold text-white transition-all duration-200 rounded-lg hover:shadow-lg bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex justify-center items-center">
                            <div class="flex items-center justify-center">
                                <i class="fas fa-play mr-3 text-lg"></i>
                                <span>{{ __('Resume Work') }}</span>
                            </div>
                        </button>
                        <p class="mt-3 text-sm text-orange-600 dark:text-orange-400 text-center">
                            {{ __('Ready to resume your workday') }}
                        </p>
                    </div>
                <!-- Regular Clock In/Out States -->
                @elseif(!$showConfirmation)
                    <button 
                        wire:click="$set('showConfirmation', true)"
                        class="w-full px-6 py-4 text-lg font-semibold text-white transition-all duration-200 rounded-lg hover:shadow-lg {{ ($clockData['action'] ?? '') === 'clock_in' ? 'bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800' : 'bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800' }} focus:outline-none focus:ring-2 focus:ring-offset-2 {{ ($clockData['action'] ?? '') === 'clock_in' ? 'focus:ring-green-500' : 'focus:ring-red-500' }}">
                        <div class="flex items-center justify-center">
                            <i class="mr-3 text-lg {{ ($clockData['action'] ?? '') === 'clock_in' ? 'fas fa-sign-in-alt' : 'fas fa-sign-out-alt' }}"></i>
                            <span>{{ ($clockData['action'] ?? '') === 'clock_in' ? __('Clock In') : __('Clock Out') }}</span>
                        </div>
                    </button>
                    
                    <!-- Action Description -->
                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-400 text-center">
                        {{ ($clockData['action'] ?? '') === 'clock_in' ? __('Ready to start your shift') : __('Ready to end your shift') }}
                    </p>
            @else

                @php
                    $isClockIn = ($clockData['action'] ?? '') === 'clock_in';
                    $colorName = $isClockIn ? 'green' : 'red';
                    $iconName = $isClockIn ? 'sign-in-alt' : 'sign-out-alt';
                @endphp
                <div class="bg-{{ $colorName }}-50 dark:bg-{{ $colorName }}-900/20 border border-{{ $colorName }}-200 dark:border-{{ $colorName }}-800 rounded-lg p-4">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-{{ $iconName }} text-{{ $colorName }}-600 dark:text-{{ $colorName }}-400 mr-2"></i>
                        <p class="text-{{ $colorName }}-800 dark:text-{{ $colorName }}-200 font-medium">
                            {{ $isClockIn ? __('Confirm Clock In') : __('Confirm Clock Out') }}
                        </p>
                    </div>
                    <p class="text-sm text-{{ $colorName }}-700 dark:text-{{ $colorName }}-300 mb-4">
                        {{ $isClockIn ? __('Are you sure you want to clock in now?') : __('Are you sure you want to clock out now?') }}
                    </p>
                    <div class="flex flex-col gap-3">
                        <button
                            onclick="callSmartClockMethod('handleClockAction', this)"
                            class="w-full flex justify-center items-center px-4 py-3 text-sm font-medium text-white bg-gradient-to-r from-{{ $colorName }}-600 to-{{ $colorName }}-700 hover:from-{{ $colorName }}-700 hover:to-{{ $colorName }}-800 rounded-lg hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{ $colorName }}-500 transition-all duration-200">
                            <i class="fas fa-check mr-2"></i><span>{{ __('Confirm') }}</span>
                        </button>
                        <button
                            wire:click="$set('showConfirmation', false)"
                            class="w-full flex justify-center items-center px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                            <i class="fas fa-times mr-2"></i><span>{{ __('Cancel') }}</span>
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
                            <div class="flex flex-col gap-3">
                                <button
                                    onclick="callSmartClockMethod('confirmExceptionalClockIn', this)"
                                    class="w-full flex justify-center items-center px-4 py-3 text-sm font-medium text-white bg-gradient-to-r from-yellow-600 to-yellow-700 hover:from-yellow-700 hover:to-yellow-800 rounded-lg hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all duration-200">
                                    <i class="fas fa-check mr-2"></i><span>Confirmar</span>
                                </button>
                                <button
                                    wire:click="$set('showConfirmation', false)"
                                    class="w-full flex justify-center items-center px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                                    <i class="fas fa-times mr-2"></i><span>Cancelar</span>
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
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 rounded-lg hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
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
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 rounded-lg hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
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
                        class="text-sm text-gray-500 hover:text-blue-600 underline transition-colors duration-200 hover:no-underline focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded px-2 py-1">
                    <i class="fas fa-sync-alt mr-1"></i>{{ __('Refresh Status') }}
                </button>
            </div>
        </div>
    </div>

    <script>
        // Dynamic clock functionality
        function updateDynamicClock() {
            var now = new Date();
            
            // Format time (HH:MM:SS)
            var hours = now.getHours().toString().padStart(2, '0');
            var minutes = now.getMinutes().toString().padStart(2, '0');
            var seconds = now.getSeconds().toString().padStart(2, '0');
            var timeString = hours + ':' + minutes + ':' + seconds;
            
            // Update time display
            var timeElement = document.getElementById('current-time');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
            
            // Update date only when it changes (at midnight)
            var dateElement = document.getElementById('current-date');
            if (dateElement && now.getHours() === 0 && now.getMinutes() === 0 && now.getSeconds() === 0) {
                // Refresh the entire component to get the new date
                @this.call('refreshClockData');
            }
        }
        
        // Start the dynamic clock (legacy function for compatibility)
        function startDynamicClock() {
            initializeClock();
        }
        
        // Auto-refresh clock data every minute (for status updates)
        setInterval(function() {
            @this.call('refreshClockData');
        }, 60000);
        
        // Initialize the dynamic clock when the page loads
        function initializeClock() {
            // Clear any existing intervals to prevent duplicates
            if (window.clockInterval) {
                clearInterval(window.clockInterval);
            }
            
            updateDynamicClock(); // Update immediately
            window.clockInterval = setInterval(updateDynamicClock, 1000); // Update every second
        }
        
        // Initialize the dynamic clock when the page loads
        document.addEventListener('DOMContentLoaded', initializeClock);
        
        // Also restart the clock when Livewire updates the component
        document.addEventListener('livewire:load', initializeClock);
        document.addEventListener('livewire:update', initializeClock);
        
        // Function to call SmartClock methods with GPS
        function callSmartClockMethod(methodName, element) {
            console.log('[GPS] Calling ' + methodName + '...');
            
            // UI Feedback: Show loading state immediately
            // NO PHP HERE - HARDCODED TEXT FOR SAFETY
            const processingText = "Procesando...";
            const originalContent = element.innerHTML;
            
            element.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i>' + processingText;
            element.disabled = true;
            element.classList.add('opacity-75', 'cursor-not-allowed');
            
            // Get the Livewire component
            const componentRoot = element.closest('[wire\\:id]');
            if (!componentRoot) {
                 console.error('[GPS] Component root not found');
                 alert('Error: Component not found. Please refresh.');
                 // Restore button
                 element.innerHTML = originalContent;
                 element.disabled = false;
                 element.classList.remove('opacity-75', 'cursor-not-allowed');
                 return;
            }
            const componentId = componentRoot.getAttribute('wire:id');
            const component = Livewire.find(componentId);
            
            if (!component) {
                console.error('[GPS] Livewire component not found');
                alert('Error: Livewire component not found. Please refresh.');
                // Restore button
                element.innerHTML = originalContent;
                element.disabled = false;
                element.classList.remove('opacity-75', 'cursor-not-allowed');
                return;
            }
            
            // Get geolocation preference from data attribute
            const container = document.getElementById('smart-clock-container');
            const geoEnabledAttr = container ? container.getAttribute('data-geo-enabled') : 'false';
            const userHasGeoEnabled = geoEnabledAttr === 'true';
            
            if (!userHasGeoEnabled) {
                console.log('[GPS] Geolocation disabled for user, calling without GPS');
                component.call(methodName, null, null);
                return;
            }
            
            if (navigator.geolocation) {
                // Check if we have a recent cached position (less than 10 minutes old)
                if (window.cachedGeoPosition && (Date.now() - (window.cachedGeoPosition.timestamp || 0)) < 600000) {
                    console.log('[GPS] Using cached location (age: ' + Math.round((Date.now() - window.cachedGeoPosition.timestamp) / 1000) + 's)');
                    component.call(methodName, window.cachedGeoPosition.latitude, window.cachedGeoPosition.longitude);
                    return;
                }
                
                console.log('[GPS] Requesting fresh location...');
                
                // Helper function to try getting location with fallback
                const getBestAvailableLocation = (onSuccess, onError) => {
                    // Method 1: Try High Accuracy first (ideal for mobiles/outdoors)
                    // Short timeout (5s) to fail fast if no satellite fix
                    console.log('[GPS] Attempt 1: High Accuracy');
                    navigator.geolocation.getCurrentPosition(
                        onSuccess, 
                        function(errorHigh) {
                            console.warn('[GPS] High Accuracy failed:', errorHigh.message);
                            
                            // Method 2: Fallback to Low Accuracy (WiFi/IP)
                            // Reduced timeout (4s) for better UX
                            console.log('[GPS] Attempt 2: Low Accuracy Fallback');
                            navigator.geolocation.getCurrentPosition(
                                onSuccess,
                                onError,
                                {
                                    enableHighAccuracy: false, 
                                    timeout: 4000, 
                                    maximumAge: 0
                                }
                            );
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 1000, // Reduced to 1s - if no instant lock, fallback to IP immediately
                            maximumAge: 0
                        }
                    );
                };

                getBestAvailableLocation(
                    function(position) {
                        console.log('[GPS] Location captured:', position.coords.latitude, position.coords.longitude);
                        
                        // Update global cache
                        window.cachedGeoPosition = {
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            timestamp: Date.now()
                        };
                        
                        // Call method with GPS
                        component.call(methodName, position.coords.latitude, position.coords.longitude);
                    },
                    function(error) {
                        console.warn('[GPS] All attempts failed:', error.message);
                        
                        // Fallback: try cached or null
                        if (window.cachedGeoPosition) {
                            console.log('[GPS] Using cached location fallback');
                            component.call(methodName, window.cachedGeoPosition.latitude, window.cachedGeoPosition.longitude);
                        } else {
                            console.log('[GPS] Calling without GPS (error)');
                            component.call(methodName, null, null);
                        }
                    }
                );
            } else {
                console.warn('[GPS] Geolocation not supported');
                component.call(methodName, null, null);
            }
        }
        
        // GPS Pre-loading System
        // Helper for independent preloading using same logic
        function performGPSPreload() {
             navigator.geolocation.getCurrentPosition(
                function(position) {
                    window.cachedGeoPosition = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        timestamp: Date.now()
                    };
                    console.log('[GPS Preload] High Accuracy Location cached');
                },
                function(error) {
                    // Fallback on preload too
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            window.cachedGeoPosition = {
                                latitude: position.coords.latitude,
                                longitude: position.coords.longitude,
                                timestamp: Date.now()
                            };
                            console.log('[GPS Preload] Low Accuracy Location cached');
                        },
                        function(err) {
                            console.warn('[GPS Preload] Failed:', err.message);
                        },
                        { enableHighAccuracy: false, timeout: 4000, maximumAge: 0 }
                    );
                },
                { enableHighAccuracy: true, timeout: 1000, maximumAge: 0 }
            );
        }

        // Pre-load GPS location on page load and update periodically
        function preloadGPSLocation() {
            // Check if user has geolocation enabled
            const userHasGPS = {{ Auth::user()->geolocation_enabled ? 'true' : 'false' }};
            
            if (!userHasGPS || !navigator.geolocation) {
                console.log('[GPS Preload] GPS disabled or not supported');
                return;
            }
            
            console.log('[GPS Preload] Fetching location in background...');
            performGPSPreload();
        }
        
        // Preload GPS on page load
        document.addEventListener('DOMContentLoaded', function() {
            preloadGPSLocation();
            
            // Update GPS location every 5 minutes
            setInterval(preloadGPSLocation, 300000); // 5 minutes
        });
        
        // Also preload when Livewire loads
        document.addEventListener('livewire:load', preloadGPSLocation);
    </script>
</div>
