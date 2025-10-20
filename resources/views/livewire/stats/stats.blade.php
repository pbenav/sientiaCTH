<div class="flex flex-col m-5 sm:m-10 w-">

    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Stats') }}
        </h2>
    </x-slot>

    @if (session('info'))
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
        </div>

        <!-- Dashboard Grid -->
        <div class="flex flex-col gap-6">
            <!-- Top Row: Chart -->
            <div class="bg-white p-6 rounded-lg shadow-lg h-96">
                @if($hasData)
                    <livewire:livewire-column-chart key="{{ $columnChartModel->reactiveKey() }}" :column-chart-model='$columnChartModel' />
                @else
                    <div class="flex justify-center items-center h-full">
                        <p class="text-lg text-gray-500">{{ __('No events found for the selected filter.') }}</p>
                    </div>
                @endif
            </div>

            <!-- Middle Row: KPIs -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Punctuality Card -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="flex items-center">
                        <div class="bg-blue-500 p-3 rounded-full text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">{{ __('Punctuality') }}</h3>
                            <p class="text-2xl font-bold text-gray-800">{{ $dashboardData['punctuality'] ?? '0' }}%</p>
                        </div>
                    </div>
                </div>

                <!-- Scheduled Hours Card -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="flex items-center">
                        <div class="bg-gray-500 p-3 rounded-full text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">{{ __('Scheduled Hours') }}</h3>
                            <p class="text-2xl font-bold text-gray-800">{{ $scheduledHours }}</p>
                        </div>
                    </div>
                </div>

                <!-- Registered Hours Card -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="flex items-center">
                        <div class="bg-gray-500 p-3 rounded-full text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">{{ __('Registered Hours') }}</h3>
                            <p class="text-2xl font-bold text-gray-800">{{ $totalHours }}</p>
                        </div>
                    </div>
                </div>

                <!-- Extra Hours Card -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="flex items-center">
                        <div class="bg-green-500 p-3 rounded-full text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">{{ __('Extra Hours') }}</h3>
                            <p class="text-2xl font-bold text-gray-800">{{ $dashboardData['extra_hours'] ?? '0' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Absenteeism Card -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="flex items-center">
                        <div class="bg-red-500 p-3 rounded-full text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">{{ __('Absenteeism (days)') }}</h3>
                            <p class="text-2xl font-bold text-gray-800">{{ $dashboardData['absenteeism'] ?? '0' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Irregular Closures Card -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="flex items-center">
                        <div class="bg-yellow-500 p-3 rounded-full text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">{{ __('Irregular Closures') }}</h3>
                            <p class="text-2xl font-bold text-gray-800">{{ $dashboardData['automatically_closed_count'] ?? '0' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Confidence Card -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="flex items-center">
                        <div class="bg-purple-500 p-3 rounded-full text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">{{ __('Records Confidence') }}</h3>
                            <p class="text-2xl font-bold text-gray-800">{{ $dashboardData['avg_confidence'] ?? '0' }}%</p>
                            <p class="text-xs text-gray-500">{{ __('Min') }}: {{ $dashboardData['min_confidence'] ?? '0' }}% / {{ __('Max') }}: {{ $dashboardData['max_confidence'] ?? '0' }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Completion and Totals -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Completion Card -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="flex items-center">
                        <div class="bg-indigo-500 p-3 rounded-full text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-gray-500">{{ __('Workday Completion') }}</h3>
                            <p class="text-2xl font-bold text-gray-800">{{ round($dashboardData['percentage_completion'] ?? 0) }}%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="footer">
        <div class="text-tiny">{{ __('Query run time: ') }} {{ $elapsedTime }} {{ __('miliseconds') }}</div>
    </x-slot>

    <!-- Alpine Modal -->
    <div x-data="{ showModal: false, events: [] }" x-on:open-events-modal.window="events = $event.detail.events; showModal = true"
        x-show="showModal" style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden">
        <div x-show="showModal" class="fixed inset-0 transform" x-on:click="showModal = false">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div x-show="showModal"
            class="bg-white rounded-lg shadow-xl transform sm:w-full sm:max-w-2xl">
            <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg font-medium leading-6 text-gray-900">
                    {{ __('Events for selected day') }}
                </h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Event Type') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Description') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Start Time') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('End Time') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="event in events" :key="event.id">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="event.event_type ? event.event_type.name : 'N/A'"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="event.description"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="new Date(event.start).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="new Date(event.end).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})"></td>
                                </tr>
                            </template>
                            <template x-if="events.length === 0">
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-sm text-center text-gray-500">{{ __('No events for this day.') }}</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" @click="showModal = false"
                    class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    {{ __('Close') }}
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    @livewireChartsScripts
    @endpush

</div>