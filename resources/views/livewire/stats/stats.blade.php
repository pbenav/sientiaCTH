<div class="flex flex-col m-5 sm:m-10 w-">

    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Stats') }}
        </h2>

    </x-slot>

    @if (session('info'))
    {{-- This div shows information attached to request if exists --}}
    <div class="flex items-center px-4 py-3 text-sm font-bold text-white bg-blue-500" role="alert">
        <svg class="mr-2 w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <path d="M12.432 0c1.34 0 2.01.912 2.01 1.957 0 1.305-1.164 2.512-2.679 2.512-1.269 0-2.009-.75-1.974-1.99C9.789 1.436 10.67 0 12.432 0zM8.309 20c-1.058 0-1.833-.652-1.093-3.524l1.214-5.092c.211-.814.246-1.141 0-1.141-.317 0-1.689.562-2.502 1.117l-.528-.88c2.572-2.186 5.531-3.467 6.801-3.467 1.057 0 1.233 1.273.705 3.23l-1.391 5.352c-.246.945-.141 1.271.106 1.271.317 0 1.357-.392 2.379-1.207l.6.814C12.098 19.02 9.365 20 8.309 20z" />
        </svg>
        <p>{{ __(session('info')) }}</p>
    </div>
    @endif

    {{-- Stats main div --}}
    <div class="mx-auto w-full max-w-7xl">
        <div class="flex flex-row flex-wrap gap-2 mb-4 w-auto">

            @if ($isTeamAdmin or $isInspector)
            <div>
                <x-jet-label value="{{ __('Worker') }}" />
                <select class="pt-1 h-8 whitespace-nowrap form-control" wire:model="browsedUser">
                    @foreach ($workers as $w)
                    <option value={{ $w->id }}>{{ $w->name . ' ' . $w->family_name1 }}</option>
                    @endforeach
                </select>
                <x-jet-input-error for='worker' />
            </div>
            @endif

            <div class="flex flex-row gap-2">
                <div>
                    <x-jet-label value="{{ __('Month') }}" />
                    <select class="pt-1 h-8 whitespace-nowrap form-control" wire:model="selectedMonth" wire:change="getData">
                        <option {{ $selectedMonth == 1 ? "selected value=$selectedMonth" : '' }} value="1">
                            {{ __('January') }}
                        </option>
                        <option {{ $selectedMonth == 2 ? "selected value=$selectedMonth" : '' }} value="2">
                            {{ __('February') }}
                        </option>
                        <option {{ $selectedMonth == 3 ? "selected value=$selectedMonth" : '' }} value="3">
                            {{ __('March') }}
                        </option>
                        <option {{ $selectedMonth == 4 ? "selected value=$selectedMonth" : '' }} value="4">
                            {{ __('April') }}
                        </option>
                        <option {{ $selectedMonth == 5 ? "selected value=$selectedMonth" : '' }} value="5">
                            {{ __('May') }}
                        </option>
                        <option {{ $selectedMonth == 6 ? "selected value=$selectedMonth" : '' }} value="6">
                            {{ __('June') }}
                        </option>
                        <option {{ $selectedMonth == 7 ? "selected value=$selectedMonth" : '' }} value="7">
                            {{ __('July') }}
                        </option>
                        <option {{ $selectedMonth == 8 ? "selected value=$selectedMonth" : '' }} value="8">
                            {{ __('August') }}
                        </option>
                        <option {{ $selectedMonth == 9 ? "selected value=$selectedMonth" : '' }} value="9">
                            {{ __('September') }}
                        </option>
                        <option {{ $selectedMonth == 10 ? "selected value=$selectedMonth" : '' }} value="10">
                            {{ __('October') }}
                        </option>
                        <option {{ $selectedMonth == 11 ? "selected value=$selectedMonth" : '' }} value="11">
                            {{ __('November') }}
                        </option>
                        <option {{ $selectedMonth == 12 ? "selected value=$selectedMonth" : '' }} value="12">
                            {{ __('December') }}
                        </option>
                    </select>
                    <x-jet-input-error for='month' />
                </div>
                <div>
                    <x-jet-label value="{{ __('Year') }}" />
                    <select class="pt-1 h-8 whitespace-nowrap form-control" wire:model="selectedYear">
                        @foreach (range(2022, date('Y')) as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                    <x-jet-input-error for='year' />
                </div>
            </div>

            <div class="">
                <x-jet-label value="{{ __('Event Type') }}" />
                <select class="pt-1 h-8 whitespace-nowrap form-control" wire:model="eventTypeId">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($eventTypes as $eventType)
                        <option value="{{ $eventType->id }}">{{ $eventType->name }}</option>
                    @endforeach
                </select>
                <x-jet-input-error for='eventTypeId' />
            </div>

            <div class="whitespace-nowrap">
                <x-jet-label>{{ __('Total hours registered in ') }}
                    {{ __(date('F', mktime(0, 0, 0, $selectedMonth, 10))) }}: </x-jet-label>
                <x-jet-label class="px-2 pt-1 w-min h-8 text-black form-control">{{ $totalHours }}
                    {{ __('hours') }}
                </x-jet-label>
            </div>
        </div>

        <div class="">
            <div class="p-4 w-auto h-96 bg-white rounded border shadow">
                <livewire:livewire-column-chart key="{{ $columnChartModel->reactiveKey() }}" :column-chart-model='$columnChartModel' />
            </div>
        </div>
    </div>

    <x-slot name="footer">
        <div class="text-tiny">{{ __('Query run time: ') }} {{ $elapsedTime }} {{ __('miliseconds') }}</div>
    </x-slot>

    @push('scripts')
    @livewireChartsScripts
    @endpush

</div>