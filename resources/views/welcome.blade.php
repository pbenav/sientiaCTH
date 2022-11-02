<x-guest-layout>
    <div
        class="flex justify-center min-h-screen py-4 bg-gray-100 flexrelative items-top dark:bg-gray-900 sm:items-center sm:pt-0">
        <!-- Topbar login and register links -->
        @if (Route::has('login'))
            <div class="flex fixed top-0 right-0 px-6 py-4 sm:block">
                @auth
                    <a href="{{ url('dashboard') }}"
                        class="text-sm text-gray-700 underline dark:text-gray-500">{{ __('Dashboard') }}</a>
                    <form class="inline-flex" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="#" class="ml-4 text-sm text-gray-700 underline dark:text-gray-500"
                            onclick="event.preventDefault();
                                            this.closest('form').submit()">
                            {{ __('Logout') }}
                        </a>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                        class="text-sm text-gray-700 underline dark:text-gray-500">{{ __('Log in') }}</a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="ml-4 text-sm text-gray-700 underline dark:text-gray-500">{{ __('Register') }}</a>
                    @endif
                @endauth
            </div>
        @endif

        <!-- Main content -->
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            <!-- Numpad -->
            <div class="px-4 mt-8 overflow-hidden bg-white shadow dark:bg-gray-800 sm:rounded-lg">
                @livewire('numpad')
            </div>

            <!-- Footer -->
            <div class="flex justify-center mt-4 sm:items-center sm:justify-between">
                <!-- Bottom links -->
                <div class="text-sm text-center text-gray-500 sm:text-left">
                    <div class="flex items-center">

                        <a href="/login" class="ml-1">
                            <i class="ml-1 fas fa-sign-in"></i>
                            {{ __('Login') }}
                        </a>

                        <a href="/register" class="ml-3">
                            <i class="ml-1 fas fa-user-plus"></i>
                            {{ __('Register') }}
                        </a>
                    </div>
                </div>

                <!-- Versions -->
                <div class="ml-4 text-sm text-center text-gray-500 sm:text-right sm:ml-0">
                    {{ config('app.name') }} v{{ env('APP_VER') }} (PHP v{{ PHP_VERSION }})
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>