<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $isGlobalAdmin ? __('Dashboard Global de Administración') : __('Panel de Administración de Equipos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- KPIs Generales -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Usuarios -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 flex items-center border-b-4 border-indigo-500">
                    <div class="p-3 rounded-full bg-indigo-100 text-indigo-500 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <div>
                        <p class="mb-2 text-sm font-medium text-gray-600">Total Usuarios</p>
                        <p class="text-3xl font-bold text-gray-800">{{ number_format($totalUsers) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $globalAdmins }} administradores globales</p>
                    </div>
                </div>

                <!-- Equipos -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 flex items-center border-b-4 border-blue-500">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div>
                        <p class="mb-2 text-sm font-medium text-gray-600">Total Equipos</p>
                        <p class="text-3xl font-bold text-gray-800">{{ number_format($totalTeams) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $sharedTeams }} compartidos / {{ $personalTeams }} pers.</p>
                    </div>
                </div>

                <!-- Eventos -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 flex items-center border-b-4 border-green-500">
                    <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="mb-2 text-sm font-medium text-gray-600">Eventos Registrados</p>
                        <p class="text-3xl font-bold text-gray-800">{{ number_format($totalEvents) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ number_format($recentActivity) }} en los últimos 7 días</p>
                    </div>
                </div>

                <!-- Alertas -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 flex items-center border-b-4 border-amber-500">
                    <div class="p-3 rounded-full bg-amber-100 text-amber-500 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <p class="mb-2 text-sm font-medium text-gray-600">Equipos Huérfanos</p>
                        <p class="text-3xl font-bold {{ $orphanTeamsCount > 0 ? 'text-amber-600' : 'text-gray-800' }}">{{ $orphanTeamsCount }}</p>
                        <p class="text-xs text-gray-500 mt-1">Sin propietario asignado</p>
                    </div>
                </div>
            </div>

            <!-- Contenido detallado -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Equipos más activos -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Equipos Más Activos</h3>
                        <p class="text-sm text-gray-500">Top 5 equipos por volumen de eventos</p>
                    </div>
                    <div class="p-6">
                        @if($mostActiveTeams->count() > 0)
                            <div class="space-y-4">
                                @foreach($mostActiveTeams as $team)
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                                <span class="text-indigo-600 font-bold text-sm">{{ substr($team->name, 0, 2) }}</span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $team->name }}
                                                    @if($team->personal_team)
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 ml-1">Personal</span>
                                                    @endif
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $team->owner ? $team->owner->name : 'Sin propietario' }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            {{ number_format($team->events_count) }} eventos
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">No hay datos suficientes.</p>
                        @endif
                        
                        <div class="mt-6 text-center">
                            <a href="{{ route('admin.teams.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                                Ver todos los equipos &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Atajos Administrativos -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">Acciones Rápidas</h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <a href="{{ route('admin.users.index') }}" class="border border-gray-200 rounded-lg p-4 hover:bg-blue-50 hover:border-blue-200 transition-colors group">
                            <div class="flex items-center mb-2">
                                <svg class="w-6 h-6 text-blue-500 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                <span class="font-medium text-gray-900">Gestión de Usuarios</span>
                            </div>
                            <p class="text-sm text-gray-500">{{ $isGlobalAdmin ? 'Administrar permisos y cuentas de usuarios en el sistema.' : 'Administrar empleados y roles en tus equipos.' }}</p>
                        </a>
                        
                        @if($isGlobalAdmin)
                            <a href="{{ route('admin.teams.index') }}" class="border border-gray-200 rounded-lg p-4 hover:bg-indigo-50 hover:border-indigo-200 transition-colors group">
                                <div class="flex items-center mb-2">
                                    <svg class="w-6 h-6 text-indigo-500 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    <span class="font-medium text-gray-900">Gestión de Equipos</span>
                                </div>
                                <p class="text-sm text-gray-500">Administrar todos los equipos, transferir propiedades y ajustar retenciones.</p>
                            </a>

                            <a href="{{ route('admin.settings') }}" class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center mb-2">
                                    <svg class="w-6 h-6 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span class="font-medium text-gray-900">Configuración</span>
                                </div>
                                <p class="text-sm text-gray-500">Ajustes generales del sistema CTH.</p>
                            </a>

                            <a href="{{ route('admin.mail-settings') }}" class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center mb-2">
                                    <svg class="w-6 h-6 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    <span class="font-medium text-gray-900">Correo Electrónico</span>
                                </div>
                                <p class="text-sm text-gray-500">Plantillas y configuración del servidor SMTP.</p>
                            </a>
                        @endif

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
