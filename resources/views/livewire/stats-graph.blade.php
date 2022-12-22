    <div class="flex m-5 sm:m-10">
        <div class="flex flex-col w-full gap-2 ml-2">
            <div class="">
                <span class='form-control whitespace-nowrap'>
                    <x-jet-label>Horas totales trabajadas en el mes
                        {{ __(date('F', mktime(0, 0, 0, $selectedMonth, 10))) }}: </x-jet-label>
                    <x-jet-label class="w-min my-2 p-2 border-2 form-control">{{ $totalHours }}</x-jet-label>
                </span>
            </div>
            <div class="whitespace-nowrap">
                <x-jet-label value="{{ __('Month') }}" />
                <select class="form-control pt-1 h-8 whitespace-nowrap"  wire:model="selectedMonth">
                    <option value="10">{{ __('October') }}</option>
                    <option value="11">{{ __('November') }}</option>
                    <option value="12">{{ __('December') }}</option>
                </select>
                <x-jet-input-error for='description' />
            </div>
            <div class="whitespace-nowrap">
                <x-jet-label value="{{ __('Description') }}" />
                <select
                    class="form-control pt-1 h-8 whitespace-nowrap wire:model="description">
                    <option value="{{ __('Workday') }}">{{ __('Workday') }}</option>
                    <option value="{{ __('Pause') }}">{{ __('Pause') }}</option>
                    <option value="{{ __('Others') }}">{{ __('Others') }}</option>
                </select>
                <x-jet-input-error for='description' />
            </div>
            <div class="">
                <div class="w-auto h-96 shadow rounded p-4 border bg-white">
                    <livewire:livewire-column-chart key="{{ $columnChartModel->reactiveKey() }}" :column-chart-model='$columnChartModel' />
                </div>
            </div>
        </div>

        @push('scripts')
            @livewireChartsScripts
        @endpush
    </div>
