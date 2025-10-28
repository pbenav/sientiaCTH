<div>
    <x-jet-button wire:click="$set('showModal', true)">
        {{ __('Move') }}
    </x-jet-button>

    <x-jet-dialog-modal wire:model="showModal">
        <x-slot name="title">
            {{ __('Move User') }}
        </x-slot>

        <x-slot name="content">
            <p>{{ __('Select a destination team for') }} {{ $user->name }}.</p>

            <div class="mt-4">
                <x-jet-label for="destination_team" value="{{ __('Destination Team') }}" />
                <select id="destination_team" class="form-select block w-full mt-1" wire:model="destinationTeamId">
                    <option value="">{{ __('Select a team') }}</option>
                    @foreach($eligibleTeams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$set('showModal', false)">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-button class="ml-2" wire:click="moveUser" wire:loading.attr="disabled">
                {{ __('Move User') }}
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>
