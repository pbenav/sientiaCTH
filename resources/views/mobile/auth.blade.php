@extends('mobile.layout')

@section('title', 'Autenticación')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 bg-gradient-to-br from-blue-500 to-blue-700">
    <div class="max-w-md w-full">
        
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="mx-auto h-16 w-16 bg-white rounded-full flex items-center justify-center mb-4 shadow-lg">
                <svg class="h-8 w-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white mb-2">CTH Mobile</h1>
            <p class="text-blue-100">Accede a tu área personal</p>
        </div>

        <!-- Authentication Form -->
        <div class="bg-white rounded-lg shadow-xl p-6">
            
            <!-- Info Message -->
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Los datos de autenticación se obtienen desde la aplicación Flutter
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('mobile.auth.login') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Work Center Code (Hidden - comes from Flutter) -->
                <input type="hidden" id="work_center_code" name="work_center_code" value="">
                
                <!-- Work Center Display -->
                <div id="workCenterDisplay" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Centro de Trabajo
                    </label>
                    <div class="bg-gray-50 border border-gray-300 rounded-lg p-3">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <div class="font-medium text-gray-900" id="workCenterInfo">
                                    Centro detectado via NFC
                                </div>
                                <div class="text-sm text-gray-600" id="workCenterCode"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manual Work Center Input (fallback) -->
                <div id="manualWorkCenterInput">
                    <label for="manual_work_center_code" class="block text-sm font-medium text-gray-700 mb-2">
                        Código de Centro de Trabajo
                    </label>
                    <input type="text" 
                           id="manual_work_center_code" 
                           name="manual_work_center_code" 
                           value="{{ old('work_center_code') }}"
                           placeholder="Ej: OC-001"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg btn-mobile">
                    <p class="mt-1 text-sm text-gray-500">
                        Solo necesario si no se detectó automáticamente
                    </p>
                </div>

                <!-- User Code -->
                <div>
                    <label for="user_code" class="block text-sm font-medium text-gray-700 mb-2">
                        Código de Usuario
                    </label>
                    <input type="text" 
                           id="user_code" 
                           name="user_code" 
                           value="{{ old('user_code') }}"
                           placeholder="Tu código de usuario"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg btn-mobile">
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-semibold text-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 btn-mobile">
                    Acceder
                </button>
            </form>

            <!-- Help Text -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    ¿Necesitas ayuda? 
                    <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">
                        Contacta con soporte
                    </a>
                </p>
            </div>
        </div>

        <!-- Version Info -->
        <div class="text-center mt-6">
            <p class="text-blue-100 text-sm">
                Versión 1.0.0 - {{ date('Y') }}
            </p>
        </div>
    </div>
</div>

<!-- Loading state -->
<div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <svg class="animate-spin h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-gray-700">Verificando credenciales...</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const loadingOverlay = document.getElementById('loadingOverlay');
        
        // Check for work center code from Flutter (URL parameters or localStorage)
        checkForFlutterData();
        
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            // Log form data before submission
            const formData = new FormData(form);
            console.log('Form submission data:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: "${value}"`);
            }
            
            // Ensure work_center_code is set from manual input if needed
            const workCenterCode = document.getElementById('work_center_code');
            const manualCode = document.getElementById('manual_work_center_code');
            
            console.log('workCenterCode value:', workCenterCode.value);
            console.log('manualCode value:', manualCode ? manualCode.value : 'null');
            
            if (!workCenterCode.value && manualCode && manualCode.value) {
                workCenterCode.value = manualCode.value.toUpperCase();
                console.log('Set workCenterCode from manual input:', workCenterCode.value);
            }
            
            // Show loading
            loadingOverlay.classList.remove('hidden');
            
            // Submit form via fetch to get JSON response
            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Server response:', data);
                showNotification('Datos recibidos: ' + JSON.stringify(data), 'info');
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error: ' + error.message, 'error');
            })
            .finally(() => {
                loadingOverlay.classList.add('hidden');
            });
        });
        
        // Auto-uppercase work center codes in manual input
        const manualWorkCenterInput = document.getElementById('manual_work_center_code');
        if (manualWorkCenterInput) {
            manualWorkCenterInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        }
        
        // Auto-focus on user code if work center is already set
        // Commenting out auto-focus to avoid potential issues
        /*
        if (document.getElementById('work_center_code').value) {
            document.getElementById('user_code').focus();
        } else {
            document.getElementById('manual_work_center_code').focus();
        }
        */
    });
    
    // Check for data passed from Flutter app
    function checkForFlutterData() {
        let workCenterCode = null;
        let workCenterName = null;
        
        // Method 1: URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        workCenterCode = urlParams.get('work_center_code') || urlParams.get('center');
        workCenterName = urlParams.get('work_center_name') || urlParams.get('name');
        
        // Method 2: localStorage (set by Flutter WebView)
        if (!workCenterCode) {
            workCenterCode = localStorage.getItem('cth_work_center_code');
            workCenterName = localStorage.getItem('cth_work_center_name');
        }
        
        // Method 3: sessionStorage
        if (!workCenterCode) {
            workCenterCode = sessionStorage.getItem('cth_work_center_code');
            workCenterName = sessionStorage.getItem('cth_work_center_name');
        }
        
        if (workCenterCode) {
            setWorkCenterFromFlutter(workCenterCode, workCenterName);
        }
    }
    
    // Set work center data received from Flutter
    function setWorkCenterFromFlutter(code, name) {
        // Set the hidden input
        document.getElementById('work_center_code').value = code;
        
        // Update the display
        document.getElementById('workCenterCode').textContent = code;
        document.getElementById('workCenterInfo').textContent = name || `Centro ${code}`;
        
        // Show the work center display and hide manual input
        document.getElementById('workCenterDisplay').classList.remove('hidden');
        document.getElementById('manualWorkCenterInput').classList.add('hidden');
        
        // Focus on user code input
        setTimeout(() => {
            document.getElementById('user_code').focus();
        }, 100);
        
        console.log('Work center set from Flutter:', code, name);
    }
    
    // Function that Flutter can call to set work center data
    window.setWorkCenter = function(code, name) {
        setWorkCenterFromFlutter(code, name);
    };
    
    // Function that Flutter can call to get current auth status
    window.getAuthStatus = function() {
        return {
            workCenterSet: !!document.getElementById('work_center_code').value,
            userCodeSet: !!document.getElementById('user_code').value
        };
    };
    
    // Notification function
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 left-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500 text-white' : 
            type === 'error' ? 'bg-red-500 text-white' : 
            type === 'warning' ? 'bg-orange-500 text-white' :
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