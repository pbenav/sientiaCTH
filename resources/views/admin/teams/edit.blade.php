<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Team') }}: {{ $team->name }}
            </h2>
            <a href="{{ route('admin.teams.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                ← {{ __('Back to Teams') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Team Information -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Team Information') }}</h3>
                    
                    <form method="POST" action="{{ route('admin.teams.update', $team) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Team Name') }}</label>
                                <input 
                                    type="text" 
                                    name="name" 
                                    id="name" 
                                    value="{{ old('name', $team->name) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    required
                                >
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="event_retention_months" class="block text-sm font-medium text-gray-700">
                                    {{ __('Event Retention Period (months)') }}
                                </label>
                                <input 
                                    type="number" 
                                    name="event_retention_months" 
                                    id="event_retention_months" 
                                    value="{{ old('event_retention_months', $team->event_retention_months ?? 60) }}"
                                    min="1"
                                    max="120"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ __('Number of months to retain historical event records. Default is 60 months (5 years).') }}
                                </p>
                                @error('event_retention_months')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">
                                    {{ __('Update Team') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Team Owner -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Team Owner') }}</h3>
                    
                    @if($team->owner)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <img class="w-10 h-10 rounded-full" src="{{ $team->owner->profile_photo_url }}" alt="{{ $team->owner->name }}">
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $team->owner->name }} {{ $team->owner->family_name1 }}</div>
                                    <div class="text-sm text-gray-500">{{ $team->owner->email }}</div>
                                </div>
                            </div>
                            
                            <button 
                                onclick="document.getElementById('transfer-ownership-modal').classList.remove('hidden')"
                                class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 text-sm"
                            >
                                {{ __('Transfer Ownership') }}
                            </button>
                        </div>
                    @else
                        <div class="p-4 bg-yellow-50 rounded-lg mb-4">
                            <p class="text-sm text-yellow-800 mb-3">
                                {{ __('This team has no owner assigned.') }}
                            </p>
                            <button 
                                onclick="document.getElementById('assign-owner-modal').classList.remove('hidden')"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm"
                            >
                                {{ __('Assign Owner') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Team Members -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Team Members') }}</h3>
                        <button 
                            onclick="document.getElementById('add-member-modal').classList.remove('hidden')"
                            class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 text-sm"
                        >
                            {{ __('Add Member') }}
                        </button>
                    </div>

                    <div class="space-y-3">
                        @forelse($team->users as $user)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center flex-1">
                                    <img class="w-8 h-8 rounded-full" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }} {{ $user->family_name1 }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <!-- Role Selector -->
                                    <form method="POST" action="{{ route('admin.teams.update-member-role', [$team, $user]) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PUT')
                                        <select 
                                            name="role" 
                                            onchange="this.form.submit()"
                                            class="text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        >
                                            @foreach($roles as $role)
                                                <option value="{{ $role->key }}" {{ $user->membership->role === $role->key ? 'selected' : '' }}>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>

                                    <!-- Remove Button -->
                                    <form method="POST" action="{{ route('admin.teams.remove-member', [$team, $user]) }}" onsubmit="return confirm('{{ __('Are you sure you want to remove this user?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm">
                                            {{ __('Remove') }}
                                        </button>
                                    </form>

                                    <!-- Transfer Button -->
                                    <button 
                                        type="button" 
                                        onclick="openTransferModal('{{ route('admin.teams.transfer-user', [$team, $user]) }}', '{{ $user->name }}')"
                                        class="text-indigo-600 hover:text-indigo-900 text-sm"
                                    >
                                        {{ __('Transfer') }}
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center py-4">{{ __('No team members yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Pending Invitations -->
            @if($invitations->count() > 0)
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Pending Invitations') }}</h3>
                        
                        <div class="space-y-3">
                            @foreach($invitations as $invitation)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center flex-1">
                                        <div class="text-sm font-medium text-gray-900">{{ $invitation->email }}</div>
                                        <div class="ml-4 text-sm text-gray-500">{{ __('Role') }}: {{ ucfirst($invitation->role) }}</div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <!-- Accept Button (Force) -->
                                        <form method="POST" action="{{ route('admin.teams.accept-invitation', [$team, $invitation]) }}">
                                            @csrf
                                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm" onclick="return confirm('{{ __('Are you sure you want to force accept this invitation?') }}')">
                                                {{ __('Force Accept') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Delete Team -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Delete Team') }}</h3>
                    
                    <div class="space-y-4">
                        <p class="text-sm text-gray-600">
                            {{ __('Once a team is deleted, all of its resources and data will be permanently deleted.') }}
                            {{ __('Historical event records will be preserved.') }}
                        </p>
                        
                        @if($team->users->count() > 0)
                            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-800">
                                            <strong>{{ __('Warning') }}:</strong> {{ __('This team has :count member(s). Consider removing them first.', ['count' => $team->users->count()]) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <div class="flex justify-end">
                            <form method="POST" action="{{ route('admin.teams.destroy', $team) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this team? This action cannot be undone.') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                    {{ __('Delete Team') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Member Modal -->
    <div id="add-member-modal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('add-member-modal').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form method="POST" action="{{ route('admin.teams.add-member', $team) }}">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">{{ __('Add Team Member') }}</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="user_id" class="block text-sm font-medium text-gray-700">{{ __('User') }}</label>
                                <select 
                                    name="user_id" 
                                    id="user_id" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                                    <option value="">{{ __('Select a user...') }}</option>
                                    @foreach($availableUsers as $availableUser)
                                        <option value="{{ $availableUser->id }}">
                                            {{ $availableUser->name }} {{ $availableUser->family_name1 }} ({{ $availableUser->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-700">{{ __('Role') }}</label>
                                <select 
                                    name="role" 
                                    id="role" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                                    @foreach($roles as $role)
                                        <option value="{{ $role->key }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-800 text-base font-medium text-white hover:bg-gray-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            {{ __('Add') }}
                        </button>
                        <button type="button" onclick="document.getElementById('add-member-modal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Transfer Ownership Modal -->
    <div id="transfer-ownership-modal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('transfer-ownership-modal').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form method="POST" action="{{ route('admin.teams.transfer-ownership', $team) }}">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">{{ __('Transfer Team Ownership') }}</h3>
                        <p class="text-sm text-gray-600 mb-4">{{ __('The current owner will become a team admin.') }}</p>
                        
                        <div>
                            <label for="new_owner_id" class="block text-sm font-medium text-gray-700">{{ __('New Owner') }}</label>
                            <select 
                                name="new_owner_id" 
                                id="new_owner_id" 
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                                <option value="">{{ __('Select a new owner...') }}</option>
                                
                                {{-- Allow global admin to take ownership --}}
                                @if(Auth::user()->is_admin && $team->owner && Auth::user()->id !== $team->user_id)
                                    <option value="{{ Auth::user()->id }}" class="font-bold">
                                        {{ __('Transfer to me (Global Admin)') }}
                                    </option>
                                @endif
                                
                                @foreach($team->users as $member)
                                    <option value="{{ $member->id }}">
                                        {{ $member->name }} {{ $member->family_name1 }} ({{ $member->email }})
                                    </option>
                                @endforeach
                            </select>
                            
                            @if($team->users->count() === 0 && Auth::user()->is_admin)
                                <p class="mt-2 text-sm text-amber-600">
                                    {{ __('This team has no members. You can transfer ownership to yourself to manage or delete it.') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            {{ __('Transfer') }}
                        </button>
                        <button type="button" onclick="document.getElementById('transfer-ownership-modal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Transfer User Modal -->
    <div id="transfer-user-modal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('transfer-user-modal').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="transfer-user-form" method="POST" action="">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">{{ __('Transfer User') }}: <span id="transfer-user-name"></span></h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="target_team_id" class="block text-sm font-medium text-gray-700">{{ __('Target Team') }}</label>
                                <select 
                                    name="target_team_id" 
                                    id="target_team_id" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                                    <option value="">{{ __('Select a team...') }}</option>
                                    @foreach($allTeams as $t)
                                        @if($t->id !== $team->id)
                                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="transfer_role" class="block text-sm font-medium text-gray-700">{{ __('Role in New Team') }}</label>
                                <select 
                                    name="role" 
                                    id="transfer_role" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                                    <option value="">{{ __('Member (No Role)') }}</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->key }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            {{ __('Transfer User') }}
                        </button>
                        <button type="button" onclick="document.getElementById('transfer-user-modal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Assign Owner Modal -->
    <div id="assign-owner-modal" class="hidden fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('assign-owner-modal').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form method="POST" action="{{ route('admin.teams.assign-owner', $team) }}">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">{{ __('Assign Team Owner') }}</h3>
                        <p class="text-sm text-gray-600 mb-4">{{ __('Select a user to become the owner of this team.') }}</p>
                        
                        <div>
                            <label for="owner_id" class="block text-sm font-medium text-gray-700">{{ __('New Owner') }}</label>
                            <select 
                                name="owner_id" 
                                id="owner_id" 
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                                <option value="">{{ __('Select a new owner...') }}</option>
                                
                                {{-- Show admin user first --}}
                                @if(Auth::user()->is_admin)
                                    <option value="{{ Auth::user()->id }}" class="font-bold">
                                        {{ __('Assign to me (Global Admin)') }} - {{ Auth::user()->name }} {{ Auth::user()->family_name1 }}
                                    </option>
                                @endif
                                
                                {{-- Show current team members --}}
                                @if($team->users->count() > 0)
                                    <optgroup label="{{ __('Current Team Members') }}">
                                        @foreach($team->users as $member)
                                            @if($member->id !== Auth::user()->id)
                                                <option value="{{ $member->id }}">
                                                    {{ $member->name }} {{ $member->family_name1 }} ({{ $member->email }})
                                                </option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                @endif
                                
                                {{-- Show all other users --}}
                                @if(isset($allUsers) && $allUsers->count() > 0)
                                    <optgroup label="{{ __('All Users') }}">
                                        @foreach($allUsers as $user)
                                            @if($user->id !== Auth::user()->id && !$team->users->contains($user->id))
                                                <option value="{{ $user->id }}">
                                                    {{ $user->name }} {{ $user->family_name1 }} ({{ $user->email }})
                                                </option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            {{ __('Assign Owner') }}
                        </button>
                        <button type="button" onclick="document.getElementById('assign-owner-modal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openTransferModal(actionUrl, userName) {
            document.getElementById('transfer-user-form').action = actionUrl;
            document.getElementById('transfer-user-name').textContent = userName;
            document.getElementById('transfer-user-modal').classList.remove('hidden');
        }
    </script>
</x-app-layout>
