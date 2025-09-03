<div>
    <form wire:submit.prevent="save">
        @if (session()->has('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        <div class="shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 bg-white sm:p-6">
                <div class="grid grid-cols-6 gap-6">
                    @foreach ($schedule as $index => $item)
                        <div class="col-span-6 sm:col-span-2">
                            <label for="start_time_{{ $index }}" class="block text-sm font-medium text-gray-700">Hora de inicio</label>
                            <input type="time" wire:model="schedule.{{ $index }}.start" id="start_time_{{ $index }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div class="col-span-6 sm:col-span-2">
                            <label for="end_time_{{ $index }}" class="block text-sm font-medium text-gray-700">Hora de fin</label>
                            <input type="time" wire:model="schedule.{{ $index }}.end" id="end_time_{{ $index }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div class="col-span-6 sm:col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Días</label>
                            <div class="mt-2 grid grid-cols-4 gap-2">
                                @foreach(['L', 'M', 'X', 'J', 'V', 'S', 'D'] as $day)
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" wire:model="schedule.{{ $index }}.days" value="{{ $day }}" class="form-checkbox h-5 w-5 text-indigo-600">
                                        <span class="ml-2 text-gray-700">{{ $day }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-span-6 sm:col-span-1 flex items-end">
                            <button type="button" wire:click="removeScheduleRow({{ $index }})" class="text-red-600 hover:text-red-900">Eliminar</button>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                <button type="button" wire:click="addScheduleRow" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Añadir tramo
                </button>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Guardar
                </button>
            </div>
        </div>
    </form>
</div>
