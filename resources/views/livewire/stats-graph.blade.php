    <div class="flex-col m-5 sm:m-10">

        <div class="w-auto m-auto flex flex-row flex-wrap gap-2 ml-2 mb-4">

            <div class="">
                @if ($isTeamAdmin or $isInspector)
                    <x-jet-label value="{{ __('Worker') }}" />
                    <select class="form-control pt-1 h-8 whitespace-nowrap" wire:model="browsedUser">
                        @foreach ($workers as $w)
                            <option value={{ $w->id }}>{{ $w->name . ' ' . $w->family_name1 }}</option>
                        @endforeach
                    </select>
                    <x-jet-input-error for='worker' />
                @endif
            </div>

            <div class="flex">
                <div class="">
                    <x-jet-label value="{{ __('Month') }}" />
                    <select class="form-control pt-1 h-8 whitespace-nowrap" wire:model="selectedMonth">
                        <option value="1">{{ __('January') }}</option>
                        <option value="2">{{ __('February') }}</option>
                        <option value="3">{{ __('March') }}</option>
                        <option value="4">{{ __('April') }}</option>
                        <option value="5">{{ __('May') }}</option>
                        <option value="6">{{ __('June') }}</option>
                        <option value="7">{{ __('July') }}</option>
                        <option value="8">{{ __('August') }}</option>
                        <option value="9">{{ __('September') }}</option>
                        <option value="10">{{ __('October') }}</option>
                        <option value="11">{{ __('November') }}</option>
                        <option value="12">{{ __('December') }}</option>
                    </select>
                    <x-jet-input-error for='month' />
                </div>
                <div class="ml-2">
                    <x-jet-label value="{{ __('Year') }}" />
                    <select class="form-control pt-1 h-8 whitespace-nowrap" wire:model="selectedYear">
                        <option value="2022">{{ __('2022') }}</option>
                        <option value="2023">{{ __('2023') }}</option>
                    </select>
                    <x-jet-input-error for='year' />
                </div>
            </div>

            <div class="">
                <x-jet-label value="{{ __('Description') }}" />
                <select class="form-control pt-1 h-8 whitespace-nowrap" wire:model="description">
                    <option value="{{ __('Workday') }}">{{ __('Workday') }}</option>
                    <option value="{{ __('Pause') }}">{{ __('Pause') }}</option>
                    <option value="{{ __('Others') }}">{{ __('Others') }}</option>
                </select>
                <x-jet-input-error for='description' />
            </div>

            <div class="whitespace-nowrap">
                <x-jet-label>{{ __('Total hours worked in ') }}
                    {{ __(date('F', mktime(0, 0, 0, $selectedMonth, 10))) }}: </x-jet-label>
                <x-jet-label class="w-min h-8 p-1 form-control">{{ $totalHours }} {{ __('hours') }}
                </x-jet-label>
            </div>
        </div>

    <div class="">
        <div class="w-auto h-96 shadow rounded p-4 border bg-white">
            <livewire:livewire-column-chart key="{{ $columnChartModel->reactiveKey() }}" :column-chart-model='$columnChartModel' />
        </div>
    </div>

    @push('scripts')
        @livewireChartsScripts
    @endpush

    </div>
