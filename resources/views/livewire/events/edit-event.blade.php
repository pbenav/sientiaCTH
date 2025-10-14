<div>
    <!-- Event detail. Modal table -->
    <x-jet-dialog-modal wire:model="showModalEditEvent">

        <x-slot name='title'>
            {{ __('Edit event') }}: <span>{{ $event->id }}</span>
            <p>{{ __('Worker')}}: {{ $user->name }} {{ $user->family_name1 }}</p>
        </x-slot>

        <x-slot name='content'>
            <div class="mb-4">
                <x-jet-label value="{{ __('Event Type') }}" />
                <div class="px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    {{ $event->eventType->name ?? '' }}
                </div>
            </div>

            @if($event->workCenter)
                <div class="mb-4 p-2 border border-blue-300 bg-blue-100 rounded">
                    <x-jet-label value="{{ __('Work Center') }}" class="font-bold text-blue-600" />
                    <div class="px-3 py-2 border border-gray-300 rounded-md shadow-sm font-bold">
                        {{ $event->workCenter->name }}
                    </div>
                </div>
            @endif

            <div class="text-sm text-gray-500 font-bold text-blue-600 mb-4">{{ $workScheduleHint }}</div>

            @if (isset($event->eventType) && $event->eventType->is_all_day)
                {{-- All-day event: show only date inputs --}}
                <div class="mb-4">
                    <x-jet-label value="{{ __('Start date') }}" />
                    <x-jet-input type="date" wire:model="start_date" />
                    <x-jet-input-error for="start_date" />
                </div>
                <div class="mb-4">
                    <x-jet-label value="{{ __('End date') }}" />
                    <x-jet-input type="date" wire:model="end_date" />
                    <x-jet-input-error for="end_date" />
                </div>
            @else
                {{-- Non-all-day event: show datetime inputs --}}
                <div class="mb-4">
                    <x-jet-label value="{{ __('Start date and time') }}" />
                    <x-jet-input type="datetime-local" wire:model="start_datetime" />
                    <x-jet-input-error for="start_datetime" />
                </div>
                <div class="mb-4">
                    <x-jet-label value="{{ __('End date and time') }}" />
                    <x-jet-input type="datetime-local" wire:model="end_datetime" />
                    <x-jet-input-error for="end_datetime" />
                    <div class="text-sm text-gray-500">{{ $workScheduleHint }}</div>
                </div>
            @endif

            <div class="mx-auto mb-4">
                <x-jet-label value="{{ __('Observations') }}" />
                <textarea class="w-full form-control"
                 wire:model.defer="event.observations"
                 rows="4"
                 placeholder="{{ __('Observations') }}"
                 name="observations"
                 id="observations"
                 maxlength="255"></textarea>
                <x-jet-input-error for='event.observations' />
            </div>

        </x-slot>

        <x-slot name='footer'>
            <x-jet-secondary-button wire:click="$set('showModalEditEvent', false)" wire:target="GetTimeRegisters">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-danger-button onclick="confirmDelete({{ $event->id }})" wire:loading.attr="disabled"
                class="justify-center ml-2">
                {{ __('Delete Event') }}
            </x-jet-danger-button>

            <x-jet-button wire:click="update" wire:loading.attr="disabled"
                class="justify-center ml-2 bg-green-500 hover:bg-green-600 disabled:bg-gray-500">
                {{ __('Update event') }}
            </x-jet-button>
        </x-slot>

    </x-jet-dialog-modal>
</div>

@push('scripts')
<script>
    function confirmDelete(eventId) {
        Swal.fire({
            title: "{{ __('sweetalert.edit_event.delete_confirmation.title') }}",
            text: "{{ __('sweetalert.edit_event.delete_confirmation.text') }}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: "{{ __('sweetalert.edit_event.delete_confirmation.confirmButtonText') }}",
            cancelButtonText: "{{ __('sweetalert.edit_event.delete_confirmation.cancelButtonText') }}"
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('delete', eventId);
            }
        });
    }
</script>
@endpush
