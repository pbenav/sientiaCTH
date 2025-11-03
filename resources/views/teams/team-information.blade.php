<x-jet-action-section>
    <x-slot name="title">
        {{ __('Team Information') }}
    </x-slot>

    <x-slot name="description">
        {{ __('General information about this team and your role in it.') }}
    </x-slot>

    <x-slot name="content">
        <div class="space-y-6">
            <!-- Team Creation Date -->
            <div>
                <x-jet-label value="{{ __('Created') }}" />
                <div class="mt-1 text-sm text-gray-600">
                    {{ $team->created_at->isoFormat('LL') }}
                    ({{ $team->created_at->diffForHumans() }})
                </div>
            </div>

            <!-- Team Members Count -->
            <div>
                <x-jet-label value="{{ __('Team Members') }}" />
                <div class="mt-1 text-sm text-gray-600">
                    {{ $team->allUsers()->count() }} {{ __('members') }}
                </div>
            </div>

            <!-- Current User Role -->
            <div>
                <x-jet-label value="{{ __('Your Role') }}" />
                <div class="mt-1 text-sm text-gray-600">
                    @if ($team->owner->id === auth()->id())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            {{ __('Owner') }}
                        </span>
                    @else
                        @php
                            $membership = $team->teamInvitations->where('email', auth()->user()->email)->first()
                                ?? $team->users->find(auth()->id())?->membership;
                        @endphp
                        @if ($membership)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $membership->role }}
                            </span>
                        @endif
                    @endif
                </div>
            </div>

            <!-- User Permissions -->
            <div>
                <x-jet-label value="{{ __('Your Permissions') }}" />
                <div class="mt-2 space-y-2">
                    <div class="flex items-center text-sm">
                        @if (Gate::check('update', $team))
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-600">{{ __('Can update team settings') }}</span>
                        @else
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-400">{{ __('Cannot update team settings') }}</span>
                        @endif
                    </div>

                    <div class="flex items-center text-sm">
                        @if (Gate::check('addTeamMember', $team))
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-600">{{ __('Can add team members') }}</span>
                        @else
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-400">{{ __('Cannot add team members') }}</span>
                        @endif
                    </div>

                    <div class="flex items-center text-sm">
                        @if (Gate::check('removeTeamMember', $team))
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-600">{{ __('Can remove team members') }}</span>
                        @else
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-400">{{ __('Cannot remove team members') }}</span>
                        @endif
                    </div>

                    <div class="flex items-center text-sm">
                        @if (Gate::check('delete', $team))
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-600">{{ __('Can delete team') }}</span>
                        @else
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-400">{{ __('Cannot delete team') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Team Type -->
            @if ($team->personal_team)
            <div>
                <x-jet-label value="{{ __('Team Type') }}" />
                <div class="mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        {{ __('Personal Team') }}
                    </span>
                </div>
            </div>
            @endif
        </div>
    </x-slot>
</x-jet-action-section>
