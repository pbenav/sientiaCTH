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

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <script src=" https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js "></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js" charset="utf-8" defer></script>
</head>

<body class="font-sans antialiased">
    <x-jet-banner />

    <div class="h-auto bg-gray-100">
        @livewire('navigation-menu')

        <!-- Page Heading -->
        @if (isset($header))
        <header class="bg-white shadow">
            <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
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
            Livewire.on('NewMessage', () => {
                if ({{ Auth::user()->notify_new_messages ? 'true' : 'false' }}) {
                    Swal.fire({
                        title: 'Nuevo mensaje',
                        text: 'Has recibido un nuevo mensaje.',
                        icon: 'info',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                    });
                }
            });

            Echo.private('App.Models.User.' + {{ Auth::id() }})
                .listen('NewMessageReceived', (e) => {
                    Livewire.emit('NewMessage');
                });
        </script>
    @endauth
</body>

</html>
