<div class="flex flex-col m-5 sm:m-10 w-">

    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Permisos') }}
        </h2>

    </x-slot>

    @if (session('info'))
        {{-- This div shows information attached to request if exists --}}
        <div class="flex items-center px-4 py-3 text-sm font-bold text-white bg-blue-500" role="alert">
            <svg class="mr-2 w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path
                    d="M12.432 0c1.34 0 2.01.912 2.01 1.957 0 1.305-1.164 2.512-2.679 2.512-1.269 0-2.009-.75-1.974-1.99C9.789 1.436 10.67 0 12.432 0zM8.309 20c-1.058 0-1.833-.652-1.093-3.524l1.214-5.092c.211-.814.246-1.141 0-1.141-.317 0-1.689.562-2.502 1.117l-.528-.88c2.572-2.186 5.531-3.467 6.801-3.467 1.057 0 1.233 1.273.705 3.23l-1.391 5.352c-.246.945-.141 1.271.106 1.271.317 0 1.357-.392 2.379-1.207l.6.814C12.098 19.02 9.365 20 8.309 20z" />
            </svg>
            <p>{{ __(session('info')) }}</p>
        </div>
    @endif

    {{-- Leaves main div --}}
    <div class="mx-auto w-full max-w-7xl">
        <div class="flex-wrap gap-2 mb-4 w-auto">
            <div>
                <x-jet-label value="Permisos para: {{ $user->name . ' ' . $user->family_name1 }}" />
            </div>

            <div class="flex flex-row flex-nowrap">
                <div>
                    <x-jet-label value="{{ __('Type') }}" class="required" />
                    <select class="pt-1 h-8 whitespace-nowrap form-control" required wire:model="type">
                        <option value="{{ __('Holidays') }}" {{ $type == 'Holidays' ? 'selected' : '' }}>
                            {{ __('Holidays') }}</option>
                        <option value="{{ __('Own Affairs') }}" {{ $type == 'Own affairs' ? 'selected' : '' }}>
                            {{ __('Own Affairs') }}</option>
                    </select>
                    <x-jet-input-error for='type' />
                </div>

                <div class="ml-2">
                    <x-jet-label value="{{ __('From date') }}" />
                    <x-datepicker class="pt-2 h-8 form-control" wire:model='fromdate' />
                    <x-jet-input-error for='fromdate' />
                </div>

                <div class="ml-2">
                    <x-jet-label value="{{ __('To date') }}" />
                    <x-datepicker class="pt-2 h-8 form-control" wire:model='todate' />
                    <x-jet-input-error class="whitespace-pre-wrap" for='todate' />
                </div>
            </div>

            <div class="">
                <div class="mr-4">
                    <x-jet-label value="{{ __('Description') }}" />
                    <input type="text" class="w-full form-control" wire:model='description' />
                    <x-jet-input-error for='description' />
                </div>
            </div>

            <div class="pt-1 h-8">
                <x-jet-button class="justify-center mt-4 h-8 bg-green-500 hover:bg-green-600"
                    wire:click='save'>{{ __('Save') }}</x-jet-button>
            </div>
        </div>

        <div>
            <table class="table mt-3">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($leaves as $leave)
                        <tr>
                            <td>{{ $leave->id }}</td>
                            <td>{{ $leave->user->name }}</td>
                            <td>{{ $leave->type }}</td>
                            <td>{{ $leave->fromdate }}</td>
                            <td>{{ $leave->todate }}</td>
                            <td>{{ $leave->description }}</td>
                            <td>
                                <button wire:click="edit({{ $leave->id }})" class="btn btn-warning">Edit</button>
                                <button wire:click="delete({{ $leave->id }})" class="btn btn-danger">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div id="calendar" wire:ignore>
            <p>Calendario</p>
        </div>
    </div>

    <?php   
    $events = $leaves->map(function ($leave) {
        return [
            'title' => $leave->type,
            'start' => $leave->fromdate,
            'end' => $leave->todate,
            'description' => __($leave->description),
            'id' => $leave->id,
        ];
    });
    ?>
    
    <script>
        document.addEventListener('livewire:load', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'multiMonthYear',
                events: {!! json_encode($events) !!},
                eventColor: '#f003',
                dateClick: function(info) {
                    window.alert("Date Clicked!");
                },
                eventClick: function(info) {
                    //cument.write(JSON.stringify(info.event));
                    window.alert("Event clicked: " + JSON.stringify(info.event));
                    Livewire.emit('edit', info.event.id);
                }
            });

            console.log(calendar);
            calendar.render();

            window.addEventListener('leaves-updated', event => {
                calendar.removeAllEvents();
                event.detail.leaves.forEach(function(leave) {
                    calendar.addEvent({
                        title: leave.type,
                        start: leave.fromdate,
                        end: leave.todate,
                        description: leave.description
                    });
                });
            });
        });
    </script>
</div>
