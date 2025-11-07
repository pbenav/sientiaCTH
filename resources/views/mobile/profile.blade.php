@extends('mobile.layout')

@section('title', 'Perfil')

@section('content')
<div class="p-4 space-y-6">
    
    <!-- User Profile Card -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center space-x-4 mb-6">
            <div class="h-16 w-16 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="h-8 w-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">{{ $user->name }}</h2>
                <p class="text-gray-600">{{ $user->email }}</p>
                <p class="text-sm text-gray-500">ID: {{ $user->user_code }}</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 gap-4">
            <div class="border-t pt-4">
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Centro de Trabajo:</dt>
                        <dd class="font-medium text-gray-900">{{ $workCenter->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Código Centro:</dt>
                        <dd class="font-medium text-gray-900">{{ $workCenter->code }}</dd>
                    </div>
                    @if($user->phone)
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Teléfono:</dt>
                        <dd class="font-medium text-gray-900">{{ $user->phone }}</dd>
                    </div>
                    @endif
                    @if($user->created_at)
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Miembro desde:</dt>
                        <dd class="font-medium text-gray-900">{{ $user->created_at->locale('es')->isoFormat('MMMM [de] YYYY') }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <!-- Work Statistics -->
    @if($workStats)
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Estadísticas de Trabajo</h3>
        
        <!-- Monthly Stats -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-700 mb-3">Este Mes</h4>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $workStats['this_month']['days_worked'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Días Trabajados</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $workStats['this_month']['total_hours'] ?? '0:00' }}</div>
                    <div class="text-sm text-gray-600">Horas Total</div>
                </div>
            </div>
        </div>

        <!-- Weekly Stats -->
        <div class="mb-6">
            <h4 class="text-md font-medium text-gray-700 mb-3">Esta Semana</h4>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-purple-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $workStats['this_week']['days_worked'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Días</div>
                </div>
                <div class="bg-orange-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-orange-600">{{ $workStats['this_week']['total_hours'] ?? '0:00' }}</div>
                    <div class="text-sm text-gray-600">Horas</div>
                </div>
            </div>
        </div>

        <!-- Average Stats -->
        @if(isset($workStats['averages']))
        <div>
            <h4 class="text-md font-medium text-gray-700 mb-3">Promedios</h4>
            <div class="space-y-3">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-600">Horas por día:</span>
                    <span class="font-semibold">{{ $workStats['averages']['hours_per_day'] ?? '0:00' }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-gray-600">Días por semana:</span>
                    <span class="font-semibold">{{ $workStats['averages']['days_per_week'] ?? '0' }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Team Information -->
    @if($teamInfo && count($teamInfo) > 0)
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Equipos de Trabajo</h3>
        <div class="space-y-3">
            @foreach($teamInfo as $team)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium">{{ $team['name'] }}</div>
                            @if($team['description'])
                                <div class="text-sm text-gray-600">{{ $team['description'] }}</div>
                            @endif
                        </div>
                    </div>
                    @if($team['is_current'])
                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Activo</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Settings Section -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Configuración</h3>
        <div class="space-y-3">
            
            <!-- Notifications -->
            <div class="flex items-center justify-between p-3 border rounded-lg">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
                    </svg>
                    <div>
                        <div class="font-medium">Notificaciones</div>
                        <div class="text-sm text-gray-600">Recordatorios de fichaje</div>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>

            <!-- Theme -->
            <div class="flex items-center justify-between p-3 border rounded-lg">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <div class="font-medium">Tema</div>
                        <div class="text-sm text-gray-600">Claro/Oscuro</div>
                    </div>
                </div>
                <select class="border border-gray-300 rounded-lg px-3 py-1 text-sm">
                    <option value="light">Claro</option>
                    <option value="dark">Oscuro</option>
                    <option value="auto">Automático</option>
                </select>
            </div>

            <!-- Language -->
            <div class="flex items-center justify-between p-3 border rounded-lg">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7 2a1 1 0 011 1v1h3a1 1 0 110 2H9.578a18.87 18.87 0 01-1.724 4.78c.29.354.596.696.914 1.026a1 1 0 11-1.44 1.389c-.188-.196-.373-.396-.554-.6a19.098 19.098 0 01-3.107 3.567 1 1 0 01-1.334-1.49 17.087 17.087 0 003.13-3.733 18.992 18.992 0 01-1.487-2.494 1 1 0 111.79-.89c.234.47.489.928.764 1.372.417-.934.752-1.913.997-2.927H3a1 1 0 110-2h3V3a1 1 0 011-1zm6 6a1 1 0 01.894.553l2.991 5.982a.869.869 0 01.02.037l.99 1.98a1 1 0 11-1.79.895L15.383 16h-4.764l-.724 1.447a1 1 0 11-1.788-.894l.99-1.98.019-.038 2.99-5.982A1 1 0 0113 8zm-1.382 6h2.764L13 11.236 11.618 14z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <div class="font-medium">Idioma</div>
                        <div class="text-sm text-gray-600">Español</div>
                    </div>
                </div>
                <select class="border border-gray-300 rounded-lg px-3 py-1 text-sm">
                    <option value="es">Español</option>
                    <option value="en">English</option>
                </select>
            </div>
        </div>
    </div>

    <!-- App Information -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Información de la App</h3>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-600">Versión:</span>
                <span class="font-medium">1.0.0</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Última actualización:</span>
                <span class="font-medium">{{ now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Soporte:</span>
                <a href="mailto:soporte@cth.com" class="font-medium text-blue-600 hover:text-blue-800">
                    Contactar
                </a>
            </div>
        </div>
    </div>

    <!-- Logout Button -->
    <div class="pb-4">
        <form action="{{ route('mobile.logout') }}" method="POST">
            @csrf
            <button type="submit" 
                    onclick="return confirm('¿Estás seguro de que quieres cerrar sesión?')"
                    class="w-full bg-red-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-red-700 transition duration-200 btn-mobile">
                Cerrar Sesión
            </button>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Handle settings changes
    document.addEventListener('DOMContentLoaded', function() {
        // Theme selector
        const themeSelect = document.querySelector('select[value="light"]').parentElement.querySelector('select');
        themeSelect.addEventListener('change', function() {
            // Save theme preference
            localStorage.setItem('theme', this.value);
            console.log('Theme changed to:', this.value);
        });
        
        // Language selector
        const langSelect = document.querySelector('select').nextElementSibling;
        if (langSelect) {
            langSelect.addEventListener('change', function() {
                // Save language preference
                localStorage.setItem('language', this.value);
                console.log('Language changed to:', this.value);
            });
        }
        
        // Load saved preferences
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            themeSelect.value = savedTheme;
        }
        
        const savedLang = localStorage.getItem('language');
        if (savedLang && langSelect) {
            langSelect.value = savedLang;
        }
    });
</script>
@endpush