    <div>
        <div class="container mx-auto space-y-4 p-4 sm:p-0 mt-8">
            <span class='flex w-min border-2 xs:text-2xl border-grey-100 whitespace-nowrap'>
                <x-jet-label>Horas totales trabajadas en el mes {{ __(date("F", mktime(0, 0, 0, $selectedMonth , 10))) }}: </x-jet-label>
                <x-jet-label class="mx-4">{{ $totalHours }}</x-jet-label>
            </span>

            <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                <div class="w-auto shadow rounded p-4 border bg-white flex-1" style="height: 32rem;">
                    <livewire:livewire-column-chart :column-chart-model='$columnChartModel' />
                </div>
            </div>
        </div>

        @push('scripts')
            @livewireChartsScripts
        @endpush
    </div>
