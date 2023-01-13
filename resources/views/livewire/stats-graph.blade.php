    <div class="flex-col m-5 sm:m-10">
        <x-slot name="header">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Stats') }}
            </h2>

        </x-slot>

        @if (session('info'))
            {{-- This div shows information attached to request if exists --}}
            <div class="flex items-center bg-blue-500 text-white text-sm font-bold px-4 py-3" role="alert">
                <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <path
                        d="M12.432 0c1.34 0 2.01.912 2.01 1.957 0 1.305-1.164 2.512-2.679 2.512-1.269 0-2.009-.75-1.974-1.99C9.789 1.436 10.67 0 12.432 0zM8.309 20c-1.058 0-1.833-.652-1.093-3.524l1.214-5.092c.211-.814.246-1.141 0-1.141-.317 0-1.689.562-2.502 1.117l-.528-.88c2.572-2.186 5.531-3.467 6.801-3.467 1.057 0 1.233 1.273.705 3.23l-1.391 5.352c-.246.945-.141 1.271.106 1.271.317 0 1.357-.392 2.379-1.207l.6.814C12.098 19.02 9.365 20 8.309 20z" />
                </svg>
                <p>{{ __(session('info')) }}</p>
            </div>
        @endif

        {{-- Stats main div --}}
        <div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">


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
                        <option value="%">{{ __('All') }}</option>
                        <option value="{{ __('Workday') }}">{{ __('Workday') }}</option>
                        <option value="{{ __('Pause') }}">{{ __('Pause') }}</option>
                        <option value="{{ __('Others') }}">{{ __('Others') }}</option>
                    </select>
                    <x-jet-input-error for='description' />
                </div>

                <div class="whitespace-nowrap">
                    <x-jet-label>{{ __('Total hours worked in ') }}
                        {{ __(date('F', mktime(0, 0, 0, $selectedMonth, 10))) }}: </x-jet-label>
                    <x-jet-label class="w-min text-black h-8 pt-1 px-2 form-control">{{ $totalHours }} {{ __('hours') }}
                    </x-jet-label>
                </div>
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
