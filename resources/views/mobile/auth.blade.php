
@extends('mobile.layout')

@section('title', __('ui.auth.title'))

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
            <h1 class="text-2xl font-bold text-white mb-2">{{ __('ui.brand.name') }}</h1>
            <p class="text-blue-100">{{ __('ui.auth.subtitle') }}</p>
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
                            {{ __('ui.auth.flutter_notice') }}
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('mobile.auth.login') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- User Code only: removed requirement for work_center_code -->

                <!-- User Code -->
                <div>
                    <label for="user_code" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('ui.auth.user_code_label') }}
                    </label>
                    <input type="text" 
                           id="user_code" 
                           name="user_code" 
                           value="{{ old('user_code') }}"
                           placeholder="{{ __('ui.auth.user_code_placeholder') }}"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg btn-mobile">
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-semibold text-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 btn-mobile">
                    {{ __('ui.auth.login_button') }}
                </button>
            </form>

            <!-- Help Text -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    {{ __('ui.auth.help_contact') }}
                </p>
            </div>
        </div>

        <!-- Version Info -->
        <div class="text-center mt-6">
            <p class="text-blue-100 text-sm">
                {{ __('ui.footer.version', ['version' => '0.0.1', 'year' => date('Y')]) }}
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
    <span class="text-gray-700">{{ __('ui.loading.verifying') }}</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const loadingOverlay = document.getElementById('loadingOverlay');

        // No longer require or submit any work_center_code from the frontend.
        // We keep a light-weight form submit via fetch for UX but the server
        // will accept only the user_code and infer any work center server-side.

        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            // Log form data before submission (for debugging)
            const formData = new FormData(form);
            console.log('Form submission data:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: "${value}"`);
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
                showNotification('{{ __('ui.auth.received_data') }} ' + JSON.stringify(data), 'info');
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('{{ __('ui.auth.error_prefix') }} ' + error.message, 'error');
            })
            .finally(() => {
                loadingOverlay.classList.add('hidden');
            });
        });
    });
    
        // Optional: if Flutter writes friendly info to localStorage we can show
        // a non-blocking informational banner, but we will NOT submit any
        // work_center_code to the server from this form.
        const urlParams = new URLSearchParams(window.location.search);
        const flutterCenter = urlParams.get('work_center_name') || localStorage.getItem('cth_work_center_name');
        if (flutterCenter) {
            showNotification('{{ __('ui.notification.center_detected', ['center' => '']) }}'.replace(':center', flutterCenter), 'info');
        }
    
    // Keep a no-op setter so Flutter calls won't fail if present
    window.setWorkCenter = function(code, name) {
        console.log('Flutter setWorkCenter called (ignored for submission):', code, name);
        showNotification('{{ __('ui.notification.center_detected', ['center' => '']) }}'.replace(':center', (name || code)), 'info');
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