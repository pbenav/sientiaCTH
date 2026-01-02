<div>
    {{-- Tabs Navigation --}}
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button wire:click="setActiveTab('permissions')"
                    class="@if($activeTab === 'permissions') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-key mr-2"></i>{{ __('Permissions') }}
                </button>
                <button wire:click="setActiveTab('roles')"
                    class="@if($activeTab === 'roles') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-user-tag mr-2"></i>{{ __('Roles') }}
                </button>
                <button wire:click="setActiveTab('users')"
                    class="@if($activeTab === 'users') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-users mr-2"></i>{{ __('User Permissions') }}
                </button>
            </nav>
        </div>
    </div>

    {{-- Permissions Tab --}}
    @if($activeTab === 'permissions')
        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
                {{-- Header --}}
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Manage Permissions') }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ __('Define granular permissions for your application') }}</p>
                    </div>
                    <button wire:click="createPermission" class="btn-primary">
                        <i class="fas fa-plus mr-2"></i>{{ __('Create Permission') }}
                    </button>
                </div>

                {{-- Filters --}}
                <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-jet-input type="text" wire:model.debounce.300ms="searchPermissions" 
                        placeholder="{{ __('Search permissions...') }}" class="w-full" />
                    <select wire:model="categoryFilter" class="border-gray-300 rounded-md shadow-sm w-full">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}">{{ __(ucfirst(str_replace('_', ' ', $category))) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Permissions List --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Permission Name') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Category') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Description') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Context Required') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Type') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($permissionsPaginated as $permission)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $permission->display_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $permission->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                            {{ __(ucfirst(str_replace('_', ' ', $permission->category))) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $permission->description ?: '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($permission->requires_context)
                                            <span class="text-green-600"><i class="fas fa-check-circle"></i></span>
                                        @else
                                            <span class="text-gray-400"><i class="fas fa-times-circle"></i></span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($permission->is_system)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ __('System') }}
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ __('Custom') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button wire:click="editPermission({{ $permission->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if(!$permission->is_system)
                                            <button wire:click="deletePermission({{ $permission->id }})" 
                                                onclick="return confirm('{{ __('Are you sure you want to delete this permission?') }}')" 
                                                class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        {{ __('No permissions found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $permissionsPaginated->links() }}
                </div>
            </div>
        </div>
    @endif

    {{-- Roles Tab --}}
    @if($activeTab === 'roles')
        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
                {{-- Header --}}
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Manage Roles') }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ __('Create custom roles and assign permissions') }}</p>
                    </div>
                    <button wire:click="createRole" class="btn-primary">
                        <i class="fas fa-plus mr-2"></i>{{ __('Create Role') }}
                    </button>
                </div>

                {{-- Search --}}
                <div class="mb-4">
                    <x-jet-input type="text" wire:model.debounce.300ms="searchRoles" placeholder="{{ __('Search roles...') }}" class="w-full md:w-1/3" />
                </div>

                {{-- Roles List --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Role Name') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Description') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Permissions') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Type') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($roles as $role)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $role->display_name }}
                                            </div>
                                            @if($role->is_system)
                                                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    {{ __('System') }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $role->description ?: '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ $role->permissions_count }} {{ __('permissions') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($role->team_id)
                                            <span class="text-indigo-600">{{ __('Custom') }}</span>
                                        @else
                                            <span class="text-gray-600">{{ __('Global') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button wire:click="editRole({{ $role->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if(!$role->is_system && $role->team_id === $team->id)
                                            <button wire:click="deleteRole({{ $role->id }})" 
                                                onclick="return confirm('{{ __('Are you sure you want to delete this role?') }}')" 
                                                class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        {{ __('No roles found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $roles->links() }}
                </div>
            </div>
        </div>
    @endif

    {{-- Users Tab --}}
    @if($activeTab === 'users')
        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
                {{-- Header --}}
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Manage User Permissions') }}</h3>
                    <p class="mt-1 text-sm text-gray-600">{{ __('Assign roles and direct permissions to team members') }}</p>
                </div>

                {{-- Search --}}
                <div class="mb-4">
                    <x-jet-input type="text" wire:model.debounce.300ms="searchUsers" placeholder="{{ __('Search users...') }}" class="w-full md:w-1/3" />
                </div>

                {{-- Users List --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('User') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Email') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Assigned Role') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($teamUsers as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $user->name }} {{ $user->family_name1 }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select wire:change="assignRoleToUser({{ $user->id }}, $event.target.value)" 
                                            class="text-sm border-gray-300 rounded-md">
                                            <option value="">{{ __('No custom role') }}</option>
                                            @foreach($roles as $role)
                                                @if($role->team_id === $team->id || $role->team_id === null)
                                                    <option value="{{ $role->id }}" 
                                                        @if(isset($userRoles[$user->id]) && $userRoles[$user->id] == $role->id) selected @endif>
                                                        {{ $role->display_name }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button wire:click="manageUserPermissions({{ $user->id }})" class="text-indigo-600 hover:text-indigo-900">
                                            <i class="fas fa-key mr-1"></i>{{ __('Direct Permissions') }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                        {{ __('No users found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $teamUsers->links() }}
                </div>
            </div>
        </div>
    @endif

    {{-- Role Modal --}}
    <x-jet-dialog-modal wire:model="showRoleModal" maxWidth="4xl">
        <x-slot name="title">
            {{ $editingRole ? __('Edit Role') : __('Create New Role') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                {{-- Display Name --}}
                <div>
                    <x-jet-label for="roleForm.display_name" value="{{ __('Display Name') }}" />
                    <x-jet-input id="roleForm.display_name" type="text" class="mt-1 block w-full" wire:model.defer="roleForm.display_name" />
                    @error('roleForm.display_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Description --}}
                <div>
                    <x-jet-label for="roleForm.description" value="{{ __('Description') }}" />
                    <textarea id="roleForm.description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" 
                        rows="3" wire:model.defer="roleForm.description"></textarea>
                    @error('roleForm.description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Permissions --}}
                <div>
                    <x-jet-label value="{{ __('Permissions') }}" class="mb-3" />
                    <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg p-4">
                        @foreach($permissions as $category => $categoryPermissions)
                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-sm font-semibold text-gray-700 capitalize">
                                        {{ __(ucfirst(str_replace('_', ' ', $category))) }}
                                    </h4>
                                    <button type="button"
                                        wire:click="toggleCategoryPermissions('{{ $category }}')"
                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                        {{ __('Select All / None') }}
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    @foreach($categoryPermissions as $permission)
                                        <label class="flex items-center space-x-2 p-2 hover:bg-gray-50 rounded">
                                            <input type="checkbox" 
                                                value="{{ $permission->id }}" 
                                                wire:model.defer="selectedRolePermissions"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-gray-700">{{ $permission->display_name }}</div>
                                                @if($permission->description)
                                                    <div class="text-xs text-gray-500">{{ $permission->description }}</div>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('showRoleModal', false)">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-button class="ml-3" 
                wire:click.prevent="saveRole" 
                wire:loading.attr="disabled"
                onclick="console.log('Button clicked', @this.roleForm)">
                <span wire:loading.remove wire:target="saveRole">{{ __('Save Role') }}</span>
                <span wire:loading wire:target="saveRole">{{ __('Saving...') }}</span>
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>

    {{-- User Permissions Modal --}}
    <x-jet-dialog-modal wire:model="showUserPermissionsModal" maxWidth="4xl">
        <x-slot name="title">
            {{ __('Manage Direct Permissions') }}
            @if($selectedUser)
                - {{ $selectedUser->name }} {{ $selectedUser->family_name1 }}
            @endif
        </x-slot>

        <x-slot name="content">
            <div class="mb-4 p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-blue-700">
                    <i class="fas fa-info-circle mr-2"></i>
                    {{ __('Direct permissions override role permissions. Use sparingly for special cases.') }}
                </p>
            </div>

            <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg p-4">
                @foreach($permissions as $category => $categoryPermissions)
                    <div class="mb-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2 capitalize">
                            {{ __(ucfirst(str_replace('_', ' ', $category))) }}
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($categoryPermissions as $permission)
                                <label class="flex items-center space-x-2 p-2 hover:bg-gray-50 rounded">
                                    <input type="checkbox" 
                                        value="{{ $permission->id }}" 
                                        wire:model.defer="userDirectPermissions"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-700">{{ $permission->display_name }}</div>
                                        @if($permission->description)
                                            <div class="text-xs text-gray-500">{{ $permission->description }}</div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('showUserPermissionsModal', false)">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-button class="ml-3" wire:click="saveUserPermissions">
                {{ __('Save Permissions') }}
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>

    {{-- Permission Modal --}}
    <x-jet-dialog-modal wire:model="showPermissionModal" maxWidth="2xl">
        <x-slot name="title">
            {{ $editingPermission ? __('Edit Permission') : __('Create New Permission') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                {{-- Info Box --}}
                <div class="p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        {{ __('Permissions are granular actions that users can perform. Group them by category for better organization.') }}
                    </p>
                </div>

                {{-- Permission Name (only for new) --}}
                @if(!$editingPermission)
                    <div>
                        <x-jet-label for="permissionForm.name" value="{{ __('Permission Name') }}" />
                        <x-jet-input id="permissionForm.name" type="text" class="mt-1 block w-full" wire:model.defer="permissionForm.name" />
                        <p class="mt-1 text-xs text-gray-500">{{ __('Internal identifier (e.g., events.create, users.delete)') }}</p>
                        @error('permissionForm.name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                @else
                    <div>
                        <x-jet-label value="{{ __('Permission Name') }}" />
                        <div class="mt-1 px-3 py-2 bg-gray-100 rounded-md text-sm text-gray-700">
                            {{ $permissionForm['name'] }}
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Cannot be changed') }}</p>
                    </div>
                @endif

                {{-- Display Name --}}
                <div>
                    <x-jet-label for="permissionForm.display_name" value="{{ __('Display Name') }}" />
                    <x-jet-input id="permissionForm.display_name" type="text" class="mt-1 block w-full" wire:model.defer="permissionForm.display_name" />
                    <p class="mt-1 text-xs text-gray-500">{{ __('Human-readable name shown in the interface') }}</p>
                    @error('permissionForm.display_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Category --}}
                <div>
                    <x-jet-label for="permissionForm.category" value="{{ __('Category') }}" />
                    <div class="mt-1 flex gap-2">
                        <select id="permissionForm.category" 
                            class="flex-1 border-gray-300 rounded-md shadow-sm" 
                            wire:model.defer="permissionForm.category">
                            <option value="">{{ __('Select or type new...') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}">{{ __(ucfirst(str_replace('_', ' ', $category))) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ __('Group permissions by category (events, users, reports, etc.)') }}</p>
                    @error('permissionForm.category') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Description --}}
                <div>
                    <x-jet-label for="permissionForm.description" value="{{ __('Description') }}" />
                    <textarea id="permissionForm.description" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" 
                        rows="3" 
                        wire:model.defer="permissionForm.description"></textarea>
                    @error('permissionForm.description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Requires Context --}}
                <div>
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" 
                            wire:model.defer="permissionForm.requires_context"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <div>
                            <div class="text-sm font-medium text-gray-700">{{ __('Requires Context') }}</div>
                            <div class="text-xs text-gray-500">{{ __('Permission depends on team, project, or resource context') }}</div>
                        </div>
                    </label>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('showPermissionModal', false)">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-button class="ml-3" wire:click="savePermission">
                {{ __('Save Permission') }}
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>
