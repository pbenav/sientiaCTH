    <div>
        <div class="container mx-auto space-y-4 p-4 sm:p-0 mt-8">
            <div class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full">
            <span class='whitespace-nowrap'>
                <x-jet-label>Horas totales trabajadas en el mes {{ __(date("F", mktime(0, 0, 0, $selectedMonth , 10))) }}: </x-jet-label>
                <x-jet-label class="mx-4">{{ $totalHours }}</x-jet-label>
            </span>
            <div class='w-1/3 border-2 border-grey-100 whitespace-nowrap'>
                <x-jet-label value="{{ __('Month') }}"/>
                <select class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full" wire:model="selectedMonth">
                    <option value="10">{{ __('October') }}</option>
                    <option value="11">{{ __('November') }}</option>
                    <option value="12">{{ __('December') }}</option>
                </select>
                <x-jet-input-error for='description' />
            </div>
            <div class='w-1/3 border-2 border-grey-100 whitespace-nowrap'>
                <x-jet-label value="{{ __('Description') }}"/>
                <select class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full" wire:model="description">
                    <option value="{{ __('Workday') }}">{{ __('Workday') }}</option>
                    <option value="{{ __('Pause') }}">{{ __('Pause') }}</option>
                    <option value="{{ __('Others') }}">{{ __('Others') }}</option>
                </select>
                <x-jet-input-error for='description' />
            </div>
        </div>

            <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                <div class="w-auto shadow rounded p-4 border bg-white flex-1" style="height: 32rem;">
                    <livewire:livewire-column-chart key="{{ $columnChartModel->reactiveKey() }}" :column-chart-model='$columnChartModel' />
                </div>
            </div>
        </div>

        @push('scripts')
            @livewireChartsScripts
        @endpush
    </div>
