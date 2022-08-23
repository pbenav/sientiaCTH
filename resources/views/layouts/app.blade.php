<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">

    @livewireStyles

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}" defer></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="font-sans antialiased">
    <x-jet-banner />

    <div class="min-h-screen bg-gray-100">
        @livewire('navigation-menu')

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
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

    @stack('modals')

    @livewireScripts

    <!-- To throw notifications -->
    <script>
        Livewire.on('alert', function(message) {
            Swal.fire(
                'Good job!',
                message,
                'success'
            )
        })

        Livewire.on('confirmDeletion', event => {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) { 
                    Livewire.emitTo('get-time-registers', 'remove', event);
                    Swal.fire(
                        'Deleted!',
                        'Your event has been deleted.',
                        'success'
                    )
                }
            })
        })

        Livewire.on('confirmConfirmation', event => {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, confirm it!'
            }).then((result) => {
                if (result.isConfirmed) { 
                    Livewire.emitTo('get-time-registers', 'confirm', event);
                    Swal.fire(
                        'confirmed!',
                        'Your event has been confirmed.',
                        'success'
                    )
                }
            })
        })
    </script>

</body>

</html>
