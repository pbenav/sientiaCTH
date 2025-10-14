<div>
    <x-jet-form-section submit="updateDefaultWorkCenter">
        <x-slot name="title">
            {{ __('Default Work Center') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Select your default work center. This will be used as the default when creating new events.') }}
        </x-slot>

        <x-slot name="form">
            <div class="col-span-6 sm:col-span-4">
                <x-jet-label for="work_center" value="{{ __('Work Center') }}" />
                <select id="work_center" wire:model.defer="defaultWorkCenterId" class="form-select rounded-md shadow-sm mt-1 block w-full">
                    <option value="">{{ __('None') }}</option>
                    @foreach ($workCenters as $workCenter)
                        <option value="{{ $workCenter->id }}">{{ $workCenter->name }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot>

        <x-slot name="actions">
            <x-jet-action-message class="mr-3" on="saved">
                {{ __('Saved.') }}
            </x-jet-action-message>

            <x-jet-button>
                {{ __('Save') }}
            </x-jet-button>
        </x-slot>
    </x-jet-form-section>
</div>
