<x-guest-layout>
    <div class="flex justify-center py-4 min-h-screen bg-gray-100 flex relative items-top dark:bg-gray-900 sm:items-center sm:pt-0"
        style="background-image: url('{{ config('view.login_background_image') }}'); background-repeat: no-repeat; background-size: cover; background-position: left;">
        <!-- Topbar login and register links -->
        @if (Route::has('login'))
            <div class="flex fixed top-0 right-0 px-6 py-4 items-center">
                <div
                    class="flex items-center bg-white/10 dark:bg-gray-800/50 rounded-full px-2 py-1 border border-white/10 mr-4">
                    <a href="{{ route('set-locale', 'es') }}"
                        class="px-2 py-1 text-xs font-bold transition-colors {{ app()->getLocale() == 'es' ? 'text-blue-400' : 'text-gray-400 hover:text-white' }}">ES</a>
                    <span class="text-white/10 text-xs">|</span>
                    <a href="{{ route('set-locale', 'en') }}"
                        class="px-2 py-1 text-xs font-bold transition-colors {{ app()->getLocale() == 'en' ? 'text-blue-400' : 'text-gray-400 hover:text-white' }}">EN</a>
                </div>
                @auth
                    <a href="{{ url('events') }}"
                        class="text-sm text-gray-700 underline dark:text-gray-500">{{ __('Events') }}</a>
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
        <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">

            <!-- Numpad -->
            <div class="p-4 mt-8 bg-gray-500 shadow dark:bg-gray-800 sm:rounded-lg">
                @livewire('numpad')

                <!-- Landing Link -->
                <div class="mt-6 flex justify-center">
                    <a href="{{ route('landing') }}"
                        class="text-xs font-semibold text-blue-400 hover:text-blue-300 transition-all flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ __('Discover :app: What is this?', ['app' => config('app.name')]) }}
                    </a>
                </div>

                <!-- Footer -->
                <div class="flex justify-center mt-4 sm:items-center sm:justify-between">
                    <!-- Bottom links -->
                    <div class="text-sm text-center text-gray-500 sm:text-left">
                        <div class="flex items-center justify-center">
                            <a href="{{ route('login') }}" class="ml-1">
                                <i class="ml-1 fas fa-sign-in"></i>
                                {{ __('Login') }}
                            </a>

                            <a href="{{ route('register') }}" class="ml-3">
                                <i class="ml-1 fas fa-user-plus"></i>
                                {{ __('Register') }}
                            </a>
                        </div>
                    </div>

                    <!-- Versions -->
                    <div class="ml-4 text-sm text-center text-gray-500 flex items-center gap-4">
                        <a href="https://www.patreon.com/cw/sientiaCTH_ControlHorario" target="_blank"
                            class="text-orange-500 hover:text-orange-400 font-medium transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M2.912 8.411c-.312 0-.533.225-.533.533v11.43c0 .312.225.533.533.533H21.09c.312 0 .533-.225.533-.533V8.944c0-.312-.225-.533-.533-.533H2.912zm0-2.666H21.09c1.782 0 3.2 1.418 3.2 3.2v11.43c0 1.782-1.418 3.2-3.2 3.2H2.912C1.13 23.535-.285 22.117-.285 20.335V8.944c0-1.782 1.418-3.2 3.2-3.2zM4.156 2.666c0-.533.433-.966.966-.966h13.754c.533 0 .966.433.966.966s-.433.966-.966.966H5.122c-.533 0-.966-.433-.966-.966z" />
                            </svg>
                            {{ __('Support on Patreon') }}
                        </a>
                        <a href="https://cv.sientia.com" target="_blank">©{{ config('app.name') }}
                            v{{ config('app.app_version') }} </a>
                    </div>
                </div>
            </div>

        </div>
        <div>
            <a class="hidden"
                href="https://www.freepik.es/foto-gratis/mujer-joven-emocionada-gran-reloj-mano-esperando-fiesta-cumpleanos-comienza-pie-pared-decorada-retrato-primer-plano-nina-alegre-regocija-al-final-jornada-laboral_10214113.htm#query=jornada%20laboral&position=0&from_view=search">Imagen
                de lookstudio en Freepik</a>
        </div>
    </div>
</x-guest-layout>
