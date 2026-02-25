<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">

    @livewireStyles

    @stack('styles')

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <script src=" https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js "></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js" charset="utf-8" defer></script>

    <!-- Alpine.js x-cloak style -->
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <!-- Alpine.js for dashboard customization -->
    @stack('alpine-stores')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="font-sans antialiased">
    <x-jet-banner />

    {{-- Impersonation Banner --}}
    @if (session()->has('impersonator_id'))
        <div class="bg-gradient-to-r from-yellow-400 via-orange-500 to-red-500 text-white shadow-lg sticky top-0 z-50">
            <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between py-3">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-user-secret text-2xl animate-pulse"></i>
                        <div>
                            <p class="font-bold text-sm">
                                {{ __('Viewing as') }}: {{ Auth::user()->name }} {{ Auth::user()->family_name1 }}
                            </p>
                            <p class="text-xs opacity-90">
                                {{ __('You are impersonating this user') }}
                            </p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('impersonate.leave') }}">
                        @csrf
                        <button type="submit"
                            class="flex items-center space-x-2 bg-white text-red-600 hover:bg-red-50 px-4 py-2 rounded-lg font-medium text-sm transition shadow-md hover:shadow-lg">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>{{ __('Exit Impersonation') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="h-auto bg-gray-100">
        @livewire('navigation-menu')

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="px-4 py-6 mx-auto max-w-[90rem] sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            <div>
                {{ $slot }}
            </div>
        </main>
    </div>

    <!-- Page Footer -->
    @if (isset($footer))
        <footer class="fixed bottom-0 w-full text-xs bg-white h-min border-grey">
            <div class="px-4 py-2 max-w-7xl py-1mx-auto sm:px-6 lg:px-8">
                {{ $footer }}
            </div>
        </footer>
    @endif

    @stack('modals')

    @livewireScripts

    <!-- Tag to include scripts pushed from components with push -->
    @stack('scripts')

    @auth
        <script>
            window.addEventListener('new-notification', event => {
                if ({{ Auth::user()->notify_new_messages ? 'true' : 'false' }}) {
                    Swal.fire({
                        title: "{{ __('sweetalert.new_notification.title') }}",
                        text: "{{ __('sweetalert.new_notification.text') }}",
                        icon: 'info',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                    });
                }
            });
        </script>

        <!-- SweetAlert2 Listeners -->
        <script>
            // Listener for 'alertFail' events
            window.addEventListener('alertFail', event => {
                Swal.fire({
                    icon: 'info',
                    title: "{{ __('sweetalert.alert_fail.title') }}",
                    text: event.detail.message,
                    showConfirmButton: true,
                    confirmButtonText: "{{ __('sweetalert.ok_button') }}",
                });
            });

            // Listener for simple success alerts
            window.addEventListener('swal:alert', event => {
                Swal.fire({
                    title: event.detail.title,
                    text: event.detail.text,
                    icon: event.detail.icon,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            });

            // Listener for modals that require a page reload on close
            window.addEventListener('swal:modal-reload', event => {
                Swal.fire({
                    icon: event.detail.type || 'info',
                    title: event.detail.title,
                    text: event.detail.text,
                    showConfirmButton: true,
                    confirmButtonText: "{{ __('sweetalert.ok_button') }}",
                }).then((result) => {
                    window.location.reload();
                });
            });

            // Listener for session-flashed alerts
            @if (session()->has('alert'))
                Swal.fire({
                    title: '{{ session('alert') }}',
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            @endif

            @if (session()->has('alert-success'))
                Swal.fire({
                    title: "{{ session('alert-success') }}",
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            @endif

            @if (session()->has('alert-fail'))
                Swal.fire({
                    icon: 'error',
                    title: "{{ __('Error') }}",
                    text: "{{ session('alert-fail') }}",
                    showConfirmButton: true,
                    confirmButtonText: "{{ __('sweetalert.ok_button') }}",
                });
            @endif
        </script>

        {{-- Geolocation capture for web events (global) --}}
        @if (Auth::user()->geolocation_enabled)
            <script>
                // Global variable to cache geolocation
                window.cachedGeoPosition = null;

                // Capture geolocation on page load
                document.addEventListener('DOMContentLoaded', function() {
                    if (navigator.geolocation) {
                        console.log('[GPS] Requesting location on page load...');
                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                window.cachedGeoPosition = {
                                    latitude: position.coords.latitude,
                                    longitude: position.coords.longitude
                                };
                                console.log('[GPS] Location cached globally:', window.cachedGeoPosition.latitude, window
                                    .cachedGeoPosition.longitude);
                            },
                            function(error) {
                                console.warn('[GPS] Initial capture failed:', error.message);
                            }, {
                                enableHighAccuracy: true,
                                timeout: 10000,
                                maximumAge: 60000
                            }
                        );
                    } else {
                        console.warn('[GPS] Geolocation not supported by this browser');
                    }
                });
            </script>
        @endif
    @endauth
</body>

</html>
