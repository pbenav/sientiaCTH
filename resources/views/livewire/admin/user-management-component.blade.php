<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Gestión de Usuarios') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    @if($isGlobalAdmin)
                        {{ __('Administración global: todos los usuarios y equipos del sistema') }}
                    @else
                        {{ __('Administración de usuarios en tus equipos asignados') }}
                    @endif
                </p>
            </div>
            <button 
                wire:click="openCreateModal" 
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Nuevo Usuario') }}
            </button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash alerts -->
            @if (session()->has('message'))
                <div class="p-4 bg-green-50 border-l-4 border-green-400 text-green-700 rounded-md shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium">{{ session('message') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="p-4 bg-red-50 border-l-4 border-red-400 text-red-700 rounded-md shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Filters Bar -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Search input -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">{{ __('Buscar') }}</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input 
                                wire:model.live.debounce.300ms="search" 
                                type="text" 
                                class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm" 
                                placeholder="{{ __('Nombre, apellidos, DNI, email...') }}">
                        </div>
                    </div>

                    <!-- Team filter -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">{{ __('Filtrar por Equipo') }}</label>
                        <select 
                            wire:model.live="selectedTeam" 
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                            <option value="">{{ __('Todos mis equipos') }}</option>
                            @foreach ($administeredTeams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Role filter -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">{{ __('Filtrar por Rol') }}</label>
                        <select 
                            wire:model.live="selectedRole" 
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                            <option value="">{{ __('Todos los roles') }}</option>
                            @if($isGlobalAdmin)
                                <option value="global_admin">{{ __('Admin Global') }}</option>
                            @endif
                            <option value="team_admin">{{ __('Administrador de Equipo') }}</option>
                            <option value="user">{{ __('Usuario') }}</option>
                            <option value="inspect">{{ __('Inspector') }}</option>
                            <option value="editor">{{ __('Editor') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    {{ __('Usuario') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    {{ __('DNI / Código') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    {{ __('Equipos & Roles') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    {{ __('Vacaciones') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    {{ __('Acciones') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($users as $user)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <!-- Avatar & Name & Email -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full object-cover border border-gray-200" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-semibold text-gray-900 flex items-center gap-1.5">
                                                    {{ $user->full_name }}
                                                    @if($user->is_admin)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800" title="{{ __('Administrador Global') }}">
                                                            ROOT
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $user->email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- DNI & User code -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <div class="font-mono text-xs text-gray-900">{{ $user->dni ?: '—' }}</div>
                                        <div class="text-xs text-gray-500">{{ $user->user_code ?: '—' }}</div>
                                    </td>

                                    <!-- Teams & Roles -->
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <div class="flex flex-wrap gap-1.5 max-w-xs">
                                            @foreach($user->allTeams() as $team)
                                                @php
                                                    $isOwner = $user->ownsTeam($team);
                                                    $role = $isOwner ? 'owner' : ($team->users()->where('users.id', $user->id)->first()?->membership->role ?? 'user');
                                                    $canAdminThisTeam = in_array($team->id, $administeredTeamIds);
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs {{ $canAdminThisTeam ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-gray-100 text-gray-600' }}">
                                                    <span class="font-medium mr-1">{{ $team->name }}:</span>
                                                    @if($isOwner)
                                                        <span class="font-bold text-amber-700">Owner</span>
                                                    @elseif($role === 'admin')
                                                        <span class="font-bold text-indigo-700">Admin</span>
                                                    @elseif($role === 'inspect')
                                                        <span class="text-teal-700">Inspector</span>
                                                    @else
                                                        <span class="text-gray-600">{{ ucfirst($role) }}</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>

                                    <!-- Vacation days -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->vacation_calculation_type === 'working' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                                            {{ $user->vacation_calculation_type === 'working' ? 'Laborables (' . ($user->vacation_working_days ?? 22) . 'd)' : 'Naturales (30d)' }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        @if($isGlobalAdmin || (!$user->is_admin))
                                            <button 
                                                wire:click="openEditModal({{ $user->id }})" 
                                                class="text-indigo-600 hover:text-indigo-900 inline-flex items-center font-semibold text-xs">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                {{ __('Editar') }}
                                            </button>

                                            @if($user->id !== auth()->id())
                                                <button 
                                                    wire:click="confirmUserDeletion({{ $user->id }}, 'remove_from_team')" 
                                                    class="text-amber-600 hover:text-amber-900 inline-flex items-center font-semibold text-xs ml-2"
                                                    title="{{ __('Desvincular del equipo') }}">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6" />
                                                    </svg>
                                                    {{ __('Desvincular') }}
                                                </button>

                                                @if($isGlobalAdmin)
                                                    <button 
                                                        wire:click="confirmUserDeletion({{ $user->id }}, 'delete_user')" 
                                                        class="text-red-600 hover:text-red-900 inline-flex items-center font-semibold text-xs ml-2"
                                                        title="{{ __('Eliminar usuario permanentemente') }}">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        {{ __('Eliminar') }}
                                                    </button>
                                                @endif
                                            @endif
                                        @else
                                            <span class="text-xs text-gray-400 italic">{{ __('Protegido') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-10 h-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <p class="font-medium text-gray-600">{{ __('No se encontraron usuarios.') }}</p>
                                            <p class="text-xs text-gray-400 mt-1">{{ __('Prueba a ajustar los filtros o el término de búsqueda.') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($users->hasPages())
                    <div class="p-4 border-t border-gray-200">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Modal: Crear / Editar Usuario -->
    <x-dialog-modal wire:model="showingUserModal" maxWidth="2xl">
        <x-slot name="title">
            <span class="text-lg font-bold text-gray-900">
                {{ $isEditing ? __('Editar Usuario') : __('Crear Nuevo Usuario') }}
            </span>
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <!-- Nombre y Apellidos -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <x-label for="name" value="{{ __('Nombre') }} *" />
                        <x-input id="name" type="text" class="mt-1 block w-full text-sm" wire:model="name" required />
                        <x-input-error for="name" class="mt-1" />
                    </div>
                    <div>
                        <x-label for="family_name1" value="{{ __('Primer Apellido') }} *" />
                        <x-input id="family_name1" type="text" class="mt-1 block w-full text-sm" wire:model="family_name1" required />
                        <x-input-error for="family_name1" class="mt-1" />
                    </div>
                    <div>
                        <x-label for="family_name2" value="{{ __('Segundo Apellido') }}" />
                        <x-input id="family_name2" type="text" class="mt-1 block w-full text-sm" wire:model="family_name2" />
                        <x-input-error for="family_name2" class="mt-1" />
                    </div>
                </div>

                <!-- DNI, Código de Usuario y Email -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <x-label for="dni" value="{{ __('DNI / NIF') }}" />
                        <x-input id="dni" type="text" class="mt-1 block w-full text-sm" wire:model="dni" placeholder="12345678X" />
                        <x-input-error for="dni" class="mt-1" />
                    </div>
                    <div>
                        <x-label for="user_code" value="{{ __('Código Empleado') }}" />
                        <x-input id="user_code" type="text" class="mt-1 block w-full text-sm" wire:model="user_code" placeholder="USR000123" />
                        <x-input-error for="user_code" class="mt-1" />
                    </div>
                    <div>
                        <x-label for="email" value="{{ __('Correo Electrónico') }} *" />
                        <x-input id="email" type="email" class="mt-1 block w-full text-sm" wire:model="email" required />
                        <x-input-error for="email" class="mt-1" />
                    </div>
                </div>

                <!-- Equipo asignado y Rol -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <div>
                        <x-label for="team_id" value="{{ __('Equipo de Trabajo') }} *" />
                        <select id="team_id" wire:model="team_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                            <option value="">{{ __('Selecciona un equipo') }}</option>
                            @foreach ($administeredTeams as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error for="team_id" class="mt-1" />
                    </div>

                    <div>
                        <x-label for="team_role" value="{{ __('Rol en el Equipo') }} *" />
                        <select id="team_role" wire:model="team_role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                            <option value="user">{{ __('Usuario (Estándar)') }}</option>
                            <option value="admin">{{ __('Administrador del Equipo') }}</option>
                            <option value="inspect">{{ __('Inspector (Solo Lectura)') }}</option>
                            <option value="editor">{{ __('Editor') }}</option>
                        </select>
                        <x-input-error for="team_role" class="mt-1" />
                    </div>
                </div>

                <!-- Contraseña -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <x-label for="password" value="{{ $isEditing ? __('Nueva Contraseña (dejar en blanco para conservar)') : __('Contraseña *') }}" />
                        <x-input id="password" type="password" class="mt-1 block w-full text-sm" wire:model="password" autocomplete="new-password" />
                        <x-input-error for="password" class="mt-1" />
                    </div>
                    <div>
                        <x-label for="password_confirmation" value="{{ __('Confirmar Contraseña') }}" />
                        <x-input id="password_confirmation" type="password" class="mt-1 block w-full text-sm" wire:model="password_confirmation" autocomplete="new-password" />
                        <x-input-error for="password_confirmation" class="mt-1" />
                    </div>
                </div>

                <!-- Vacaciones y Geolocalización -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                    <div>
                        <x-label for="vacation_calculation_type" value="{{ __('Cálculo de Vacaciones') }}" />
                        <select id="vacation_calculation_type" wire:model="vacation_calculation_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                            <option value="natural">{{ __('Días Naturales (30 días estándar)') }}</option>
                            <option value="working">{{ __('Días Laborables') }}</option>
                        </select>
                        <x-input-error for="vacation_calculation_type" class="mt-1" />
                    </div>

                    @if($vacation_calculation_type === 'working')
                        <div>
                            <x-label for="vacation_working_days" value="{{ __('Días Laborables al año') }}" />
                            <x-input id="vacation_working_days" type="number" min="0" max="365" class="mt-1 block w-full text-sm" wire:model="vacation_working_days" />
                            <x-input-error for="vacation_working_days" class="mt-1" />
                        </div>
                    @endif
                </div>

                <!-- Opciones adicionales -->
                <div class="pt-2 flex flex-col space-y-2">
                    <label class="inline-flex items-center">
                        <input type="checkbox" wire:model="geolocation_enabled" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">{{ __('Habilitar geolocalización al fichar') }}</span>
                    </label>

                    @if($isGlobalAdmin)
                        <div class="p-3 bg-purple-50 rounded border border-purple-200">
                            <label class="inline-flex items-center">
                                <input type="checkbox" wire:model="is_admin" class="rounded border-purple-400 text-purple-600 shadow-sm focus:ring-purple-300">
                                <span class="ml-2 text-sm font-semibold text-purple-900">{{ __('Privilegios de Administrador Global (Root)') }}</span>
                            </label>
                            <p class="text-xs text-purple-600 mt-1 ml-6">
                                {{ __('Precaución: Este usuario tendrá acceso total a todos los equipos, configuraciones del sistema y usuarios.') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showingUserModal', false)" wire:loading.attr="disabled">
                {{ __('Cancelar') }}
            </x-secondary-button>

            <x-button class="ml-3" wire:click="saveUser" wire:loading.attr="disabled">
                {{ $isEditing ? __('Guardar Cambios') : __('Crear Usuario') }}
            </x-button>
        </x-slot>
    </x-dialog-modal>

    <!-- Modal: Confirmar Desvinculación o Eliminación -->
    <x-confirmation-modal wire:model="confirmingUserDeletion">
        <x-slot name="title">
            @if($deleteActionType === 'delete_user')
                {{ __('Eliminar Usuario Permanentemente') }}
            @else
                {{ __('Desvincular Usuario del Equipo') }}
            @endif
        </x-slot>

        <x-slot name="content">
            @if($deleteActionType === 'delete_user')
                {{ __('¿Estás seguro de que deseas eliminar permanentemente al usuario') }}
                <strong class="font-bold text-gray-900">{{ $userToDeleteName }}</strong>?
                {{ __('Esta acción no se puede deshacer y borrará todo su historial, registros y accesos.') }}
            @else
                {{ __('¿Estás seguro de que deseas desvincular a') }}
                <strong class="font-bold text-gray-900">{{ $userToDeleteName }}</strong>
                {{ __('de este equipo? El usuario dejará de tener acceso a los proyectos y fichajes del equipo.') }}
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('confirmingUserDeletion', false)" wire:loading.attr="disabled">
                {{ __('Cancelar') }}
            </x-secondary-button>

            <x-danger-button class="ml-3" wire:click="deleteUser" wire:loading.attr="disabled">
                @if($deleteActionType === 'delete_user')
                    {{ __('Eliminar Definitivamente') }}
                @else
                    {{ __('Desvincular del Equipo') }}
                @endif
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
