<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CTH Mobile')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- PWA Meta -->
    <meta name="theme-color" content="#2563EB">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    
    <!-- Custom mobile styles -->
    <style>
        /* Hide scrollbars but keep functionality */
        ::-webkit-scrollbar {
            width: 4px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(0,0,0,0.2);
            border-radius: 2px;
        }
        
        /* Touch-friendly tap highlights */
        * {
            -webkit-tap-highlight-color: rgba(37, 99, 235, 0.1);
        }
        
        /* Mobile-optimized button styles */
        .btn-mobile {
            min-height: 48px;
            min-width: 48px;
            touch-action: manipulation;
        }
        
        /* Safe area for devices with notches */
        .safe-area-top {
            padding-top: env(safe-area-inset-top);
        }
        .safe-area-bottom {
            padding-bottom: env(safe-area-inset-bottom);
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-900 safe-area-top safe-area-bottom">
    
    <!-- Mobile Header -->
    <header class="bg-blue-600 text-white shadow-md sticky top-0 z-50">
        <div class="flex items-center justify-between px-4 py-3">
            <!-- Back/Menu Button -->
            <div class="flex items-center">
                @if(request()->routeIs('mobile.home'))
                    <div class="w-8 h-8 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                @else
                    <a href="{{ route('mobile.home') }}" class="w-8 h-8 flex items-center justify-center btn-mobile">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                @endif
                <h1 class="ml-3 text-lg font-semibold">@yield('title', 'CTH Mobile')</h1>
            </div>
            
            <!-- User Menu -->
            <div class="relative">
                <button onclick="toggleUserMenu()" class="btn-mobile rounded-full">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                    </svg>
                </button>
                
                <!-- Dropdown Menu -->
                <div id="userMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-2 hidden z-50">
                    <div class="px-4 py-2 text-sm text-gray-600 border-b">
                        @if(session('mobile_user_id'))
                            {{ \App\Models\User::find(session('mobile_user_id'))->name ?? 'Usuario' }}
                        @endif
                    </div>
                    <a href="{{ route('mobile.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Perfil</a>
                    <form action="{{ route('mobile.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="min-h-screen pb-20">
        @if(session('message'))
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 m-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">{{ session('message') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-400 p-4 m-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        @foreach($errors->all() as $error)
                            <p class="text-sm text-red-700">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        
        @yield('content')
    </main>

    <!-- Bottom Navigation (only on authenticated pages) -->
    @if(!request()->routeIs('mobile.auth') && session('mobile_authenticated'))
        <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 safe-area-bottom">
            <div class="flex">
                <a href="{{ route('mobile.home') }}" 
                   class="flex-1 py-3 px-2 text-center {{ request()->routeIs('mobile.home') ? 'text-blue-600 bg-blue-50' : 'text-gray-600' }} btn-mobile">
                    <svg class="w-6 h-6 mx-auto mb-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-9 9a1 1 0 001.414 1.414L2 12.414V17a1 1 0 001 1h5.586l-.293-.293A1 1 0 019 17h2a1 1 0 00.707-.293L11.414 16H14a1 1 0 001-1v-4.586l.293.293a1 1 0 001.414-1.414l-9-9z"></path>
                    </svg>
                    <span class="text-xs block">Inicio</span>
                </a>
                
                <a href="{{ route('mobile.history') }}" 
                   class="flex-1 py-3 px-2 text-center {{ request()->routeIs('mobile.history') ? 'text-blue-600 bg-blue-50' : 'text-gray-600' }} btn-mobile">
                    <svg class="w-6 h-6 mx-auto mb-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-xs block">Historial</span>
                </a>
                
                <a href="{{ route('mobile.schedule') }}" 
                   class="flex-1 py-3 px-2 text-center {{ request()->routeIs('mobile.schedule') ? 'text-blue-600 bg-blue-50' : 'text-gray-600' }} btn-mobile">
                    <svg class="w-6 h-6 mx-auto mb-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-xs block">Horario</span>
                </a>
                
                <a href="{{ route('mobile.reports') }}" 
                   class="flex-1 py-3 px-2 text-center {{ request()->routeIs('mobile.reports') ? 'text-blue-600 bg-blue-50' : 'text-gray-600' }} btn-mobile">
                    <svg class="w-6 h-6 mx-auto mb-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                    </svg>
                    <span class="text-xs block">Informes</span>
                </a>
            </div>
        </nav>
    @endif

    <!-- JavaScript -->
    <script>
        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('hidden');
        }
        
        // Close user menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('userMenu');
            const button = event.target.closest('button');
            
            if (button && button.onclick && button.onclick.toString().includes('toggleUserMenu')) {
                return; // Don't close if clicking the toggle button
            }
            
            if (!menu.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });
        
        // Prevent zoom on double tap
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function (event) {
            const now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
    </script>
    
    @stack('scripts')
</body>
</html>