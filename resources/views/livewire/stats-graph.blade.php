<div>
    <h1 class="ml-10">{{ __('Charts & Stats by month') }}</h1>
    <div class="flex ml-10">{{ __('Total worked hours: ') }} {{ $totalHours }}</div>
    <div>
        <select name="selectedMonth" id="selectedMonth" class="border ml-10" wire:model="selectedMonth">
            @foreach ($availableMonths as $month)
                <option value="{{ $month }}">{{ $month }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex">
        <div class="w-1/2">
            {!! $chart->container() !!}
        </div>
    </div>


    {!! $chart->script() !!}
</div>
