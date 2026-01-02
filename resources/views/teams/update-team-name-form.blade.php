<x-jet-form-section submit="updateTeamName">
    <x-slot name="title">
        {{ __('Team Name') }}
    </x-slot>

    <x-slot name="description">
        {{ __('The team\'s name and owner information.') }}
    </x-slot>

    <x-slot name="form">
        <!-- Team Owner Information -->
        <div class="col-span-6">
            <x-jet-label value="{{ __('Team Owner') }}" />

            <div class="flex items-center mt-2">
                <img class="w-12 h-12 rounded-full object-cover" src="{{ $team->owner->profile_photo_url }}" alt="{{ $team->owner->name }}">

                <div class="ml-4 leading-tight">
                    <div>{{ $team->owner->name }}</div>
                    <div class="text-gray-700 text-sm">{{ $team->owner->email }}</div>
                </div>
            </div>
        </div>

        <!-- Team Name -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="name" value="{{ __('Team Name') }}" />

            <x-jet-input id="name"
                        type="text"
                        class="mt-1 block w-full"
                        wire:model.defer="state.name"
                        :disabled="! Gate::check('update', $team)" />

            <x-jet-input-error for="name" class="mt-2" />
        </div>

        <!-- Max Member Teams -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="max_member_teams" value="{{ __('Límite de equipos para miembros') }}" />
            
            <x-jet-input id="max_member_teams"
                        type="number"
                        class="mt-1 block w-full {{ !auth()->user()->is_admin ? 'bg-gray-100' : '' }}"
                        wire:model.defer="state.max_member_teams"
                        :disabled="!auth()->user()->is_admin" />

            <x-jet-input-error for="max_member_teams" class="mt-2" />
            
            <p class="text-sm text-gray-600 mt-2">
                {{ __('Define cuántos equipos pueden crear los miembros de este equipo (que tengan permiso para ello).') }}
                @if(!auth()->user()->is_admin)
                    <span class="text-amber-600 block mt-1 italic">{{ __('Solo un administrador global puede modificar este límite.') }}</span>
                @endif
            </p>
        </div>
    </x-slot>

    @if (Gate::check('update', $team))
        <x-slot name="actions">
            <x-jet-button class="bg-indigo-600 hover:bg-indigo-700">
                {{ __('Save') }}
            </x-jet-button>
        </x-slot>
    @endif
</x-jet-form-section>

<script>
    document.addEventListener('livewire:load', function () {
        Livewire.on('saved', function () {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '{{ __("Cambios guardados correctamente") }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        });
    });
</script>
