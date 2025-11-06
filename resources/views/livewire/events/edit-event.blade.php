<div>
    <!-- Event detail. Modal table -->
    <x-jet-dialog-modal wire:model="showModalEditEvent" maxWidth="2xl">

        <x-slot name='title'>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold">{{ __('Edit event') }} #{{ $event->id }}</h3>
                    <p class="text-sm text-gray-600">{{ $user->name }} {{ $user->family_name1 }}</p>
                </div>
                <div class="text-right text-sm text-gray-500">
                    @if($event->workCenter)
                        <div>📍 {{ $event->workCenter->name }}</div>
                    @endif
                    @if($event->eventType)
                        <div class="flex items-center justify-end mt-1">
                            @php
                                $eventColor = '#3788d8'; // Default color
                                if ($event->is_exceptional) {
                                    $eventColor = auth()->user()->currentTeam->special_event_color ?? '#DC2626';
                                } elseif ($event->eventType) {
                                    if ($event->eventType->color) {
                                        $eventColor = $event->eventType->color;
                                    } elseif (!$event->eventType->is_workday_type) {
                                        $eventColor = auth()->user()->currentTeam->special_event_color ?? '#EA8000';
                                    }
                                } else {
                                    $eventColor = auth()->user()->currentTeam->special_event_color ?? '#EA8000';
                                }
                            @endphp
                            <span class="inline-block w-3 h-3 rounded-full mr-1" style="background-color: {{ $eventColor }}"></span>
                            {{ $event->eventType->name }}
                        </div>
                    @endif
                </div>
            </div>
        </x-slot>

        <x-slot name='content'>
            <!-- Context Info Bar -->
            @if($workScheduleHint)
                <div class="mb-4 p-2 border-l-4 border-blue-400 bg-blue-50 rounded text-sm">
                    <span class="font-medium text-blue-800">{{ __('Work Schedule Hint') }}:</span>
                    <span class="text-gray-700">{{ $workScheduleHint }}</span>
                </div>
            @endif

            <!-- Main Form Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <!-- Left Column: Date & Time -->
                <div class="space-y-4">
                    @if (isset($event->eventType) && $event->eventType->is_all_day)
                        {{-- All-day event: show only date inputs --}}
                        <div>
                            <x-jet-label value="{{ __('Start date') }}" class="text-sm font-medium" />
                            <x-jet-input type="date" wire:model="start_date" class="mt-1" {{ $canBeModified ? '' : 'disabled' }} />
                            <x-jet-input-error for="start_date" />
                        </div>
                        <div>
                            <x-jet-label value="{{ __('End date') }}" class="text-sm font-medium" />
                            <x-jet-input type="date" wire:model="end_date" class="mt-1" {{ $canBeModified ? '' : 'disabled' }} />
                            <x-jet-input-error for="end_date" />
                        </div>
                    @else
                        {{-- Non-all-day event: compact date and time --}}
                        <div>
                            <x-jet-label value="{{ __('Start') }}" class="text-sm font-medium mb-2" />
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <input type="date" 
                                           class="form-control w-full text-sm border-gray-300 rounded-md shadow-sm" 
                                           wire:model.live="start_date" 
                                           {{ $canBeModified ? '' : 'disabled' }} />
                                    <x-jet-input-error for="start_date" />
                                </div>
                                <div>
                                    <input type="time" 
                                           class="form-control w-full text-sm border-gray-300 rounded-md shadow-sm" 
                                           wire:model.live="start_time" 
                                           {{ $canBeModified ? '' : 'disabled' }} 
                                           step="300" />
                                    <x-jet-input-error for="start_time" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <x-jet-label value="{{ __('End') }}" class="text-sm font-medium mb-2" />
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <input type="date" 
                                           class="form-control w-full text-sm border-gray-300 rounded-md shadow-sm" 
                                           wire:model.live="end_date" 
                                           {{ $canBeModified ? '' : 'disabled' }} />
                                    <x-jet-input-error for="end_date" />
                                </div>
                                <div>
                                    <input type="time" 
                                           class="form-control w-full text-sm border-gray-300 rounded-md shadow-sm" 
                                           wire:model.live="end_time" 
                                           {{ $canBeModified ? '' : 'disabled' }} 
                                           step="300" />
                                    <x-jet-input-error for="end_time" />
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Column: Description & Observations -->
                <div class="space-y-4">
                    <div>
                        <x-jet-label value="{{ __('Description') }}" class="text-sm font-medium" />
                        <x-jet-input class="block w-full mt-1 text-sm" 
                                     wire:model.defer="event.description"
                                     placeholder="{{ __('Add a description') }}"
                                     maxlength="255"
                                     {{ $canBeModified ? '' : 'disabled' }} />
                        <x-jet-input-error for='event.description' />
                        <div class="text-xs text-gray-500 mt-1">
                            {{ __('If empty, event type name will be used') }}
                        </div>
                    </div>

                    <div>
                        <x-jet-label value="{{ __('Observations') }}" class="text-sm font-medium" />
                        <textarea class="w-full mt-1 text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                         wire:model.defer="event.observations"
                         rows="3"
                         placeholder="{{ __('event.observations.placeholder') }}"
                         maxlength="255"
                         {{ $canBeModified ? '' : 'disabled' }}></textarea>
                        <x-jet-input-error for='event.observations' />
                    </div>
                </div>
            </div>

        </x-slot>

        <x-slot name='footer'>
            <x-jet-secondary-button wire:click="$set('showModalEditEvent', false)" wire:target="GetTimeRegisters">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            @if($canBeModified)
                <x-jet-danger-button onclick="confirmDelete({{ $event->id }})" wire:loading.attr="disabled"
                    class="justify-center ml-2">
                    {{ __('Delete Event') }}
                </x-jet-danger-button>

                <x-jet-button wire:click="update" wire:loading.attr="disabled"
                    class="justify-center ml-2 bg-green-500 hover:bg-green-600 disabled:bg-gray-500">
                    {{ __('Update event') }}
                </x-jet-button>
            @endif
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
