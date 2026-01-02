<x-jet-form-section submit="updateGeolocationPreferences">
    <x-slot name="title">
        {{ __('Geolocation Preferences') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Enable GPS geolocation tracking for clock-in and clock-out events.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4 space-y-2">
            <label for="geolocation_enabled" class="flex items-center">
                <x-jet-checkbox 
                    id="geolocation_enabled" 
                    wire:model.defer="state.geolocation_enabled"
                    x-on:change="handleGeolocationToggle($event)" />
                <span class="ml-2 text-sm text-gray-600">{{ __('Enable GPS geolocation for clock-in/out') }}</span>
            </label>
            <p class="text-xs text-gray-500 ml-6 mt-1">
                {{ __('When enabled, your GPS coordinates will be recorded each time you clock in or out from the mobile app.') }}
            </p>
            <div id="geolocation-status" class="ml-6 mt-2" wire:ignore></div>
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-jet-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-jet-action-message>

        <x-jet-button>
            {{ __('Save') }}
        </x-jet-button>
    </x-slot>
</x-jet-form-section>

<script>
function handleGeolocationToggle(event) {
    const checkbox = event.target;
    const statusDiv = document.getElementById('geolocation-status');
    
    // Si se está activando (checked = true)
    if (checkbox.checked) {
        // Verificar si el navegador soporta geolocalización
        if (!navigator.geolocation) {
            statusDiv.innerHTML = `
                <div class="flex items-center p-3 bg-red-50 border border-red-200 rounded-md">
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm text-red-700">{{ __('Geolocation is not supported by this browser.') }}</span>
                </div>
            `;
            checkbox.checked = false;
            @this.set('state.geolocation_enabled', false);
            return;
        }
        
        // Mostrar mensaje de carga
        statusDiv.innerHTML = `
            <div class="flex items-center p-3 bg-blue-50 border border-blue-200 rounded-md">
                <svg class="animate-spin h-5 w-5 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm text-blue-700">{{ __('Requesting location permission...') }}</span>
            </div>
        `;
        
        // Solicitar permisos de geolocalización
        navigator.geolocation.getCurrentPosition(
            function(position) {
                // Éxito: se concedieron los permisos
                statusDiv.innerHTML = `
                    <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-md">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm text-green-700">{{ __('Location permission granted!') }} (Lat: ${position.coords.latitude.toFixed(4)}, Lng: ${position.coords.longitude.toFixed(4)})</span>
                        </div>
                        <a href="https://www.google.com/maps/search/?api=1&query=${position.coords.latitude},${position.coords.longitude}" 
                           target="_blank"
                           class="ml-2 px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs rounded-md transition-colors flex items-center gap-1 flex-shrink-0"
                           title="{{ __('View on Google Maps') }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ __('Map') }}
                        </a>
                    </div>
                `;
                // Mantener el checkbox activado y actualizar el estado de Livewire
                checkbox.checked = true;
                @this.set('state.geolocation_enabled', true);
            },
            function(error) {
                // Error: se denegaron los permisos o hubo un problema
                let errorMessage = '{{ __("Location permission denied.") }}';
                
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = '{{ __("Location permission denied. Please enable location in your browser settings.") }}';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = '{{ __("Location information unavailable.") }}';
                        break;
                    case error.TIMEOUT:
                        errorMessage = '{{ __("Location request timed out.") }}';
                        break;
                }
                
                statusDiv.innerHTML = `
                    <div class="flex items-center p-3 bg-red-50 border border-red-200 rounded-md">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm text-red-700">${errorMessage}</span>
                    </div>
                `;
                
                // Desactivar el checkbox si se deniegan los permisos
                checkbox.checked = false;
                @this.set('state.geolocation_enabled', false);
            },
            {
                enableHighAccuracy: true,
                timeout: 15000, // Increased from 10000ms to 15000ms (15 seconds)
                maximumAge: 0
            }
        );
    } else {
        // Si se está desactivando, limpiar el mensaje
        statusDiv.innerHTML = '';
    }
}

// Al cargar la página, si el checkbox está habilitado, mostrar la ubicación actual
document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.getElementById('geolocation_enabled');
    const statusDiv = document.getElementById('geolocation-status');
    
    if (checkbox && checkbox.checked && statusDiv && navigator.geolocation) {
        statusDiv.innerHTML = `
            <div class="flex items-center p-3 bg-blue-50 border border-blue-200 rounded-md">
                <svg class="animate-spin h-5 w-5 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm text-blue-700">{{ __('Getting current location...') }}</span>
            </div>
        `;
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                statusDiv.innerHTML = `
                    <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-md">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="text-sm text-green-700">
                                {{ __('Current location:') }} 
                                <strong>Lat:</strong> ${position.coords.latitude.toFixed(6)}, 
                                <strong>Lng:</strong> ${position.coords.longitude.toFixed(6)}
                                <span class="text-xs text-green-600 ml-2">(±${Math.round(position.coords.accuracy)}m)</span>
                            </span>
                        </div>
                        <a href="https://www.google.com/maps/search/?api=1&query=${position.coords.latitude},${position.coords.longitude}" 
                           target="_blank"
                           class="ml-2 px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs rounded-md transition-colors flex items-center gap-1 flex-shrink-0"
                           title="{{ __('View on Google Maps') }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ __('Map') }}
                        </a>
                    </div>
                `;
            },
            function(error) {
                let errorMessage = '{{ __("Could not get current location. Please check browser permissions.") }}';
                
                if (error.code === 1) {
                    errorMessage = '{{ __("Location permission denied. Please enable location in your browser settings.") }}';
                } else if (error.code === 2) {
                    errorMessage = '{{ __("Location information unavailable.") }}';
                } else if (error.code === 3) {
                    errorMessage = '{{ __("Location request timed out.") }}';
                }
                
                statusDiv.innerHTML = `
                    <div class="flex items-center p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                        <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <span class="text-sm text-yellow-700">${errorMessage}</span>
                    </div>
                `;
            },
            {
                enableHighAccuracy: false,
                timeout: 15000, // Increased from 10000ms to 15000ms (15 seconds)
                maximumAge: 300000
            }
        );
    }
});
</script>

