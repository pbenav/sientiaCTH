<div class="flex flex-col m-5 sm:m-10">
    
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Reports') }}
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

    {{-- Reports main div --}}
    <div class="w-full max-w-7xl mx-auto">
        <div class="w-auto flex flex-row flex-wrap gap-2 mb-4">

            @if ($isTeamAdmin or $isInspector)
                <div>
                    <x-jet-label value="{{ __('Worker') }}" />
                    <select class="form-control pt-1 h-8 whitespace-nowrap" wire:model='worker'>
                        <option value="%">{{ __('All') }}</option>
                        @foreach ($workers as $w)
                            <option value="{{ $w->id }}" {{ $w->id == $worker ? 'selected' : '' }}>
                                {{ $w->name . ' ' . $w->family_name1 }}</option>
                        @endforeach
                    </select>
                    <x-jet-input-error for='worker' />
                </div>
            @endif

            <div class="flex flex-nowrap">
                <div>
                    <x-jet-label value="{{ __('From date') }}" />
                    <x-datepicker class="form-control h-8 pt-2" wire:model='fromdate' />
                    <x-jet-input-error for='fromdate' />
                </div>

                <div class="ml-2">
                    <x-jet-label value="{{ __('To date') }}" />
                    <x-datepicker class="form-control h-8 pt-2" wire:model='todate' name="todate" id="todate" />
                    <x-jet-input-error class="whitespace-pre-wrap" for='todate' />
                </div>
            </div>

            <div>
                <x-jet-label value="{{ __('Event Type') }}" />
                <select class="form-control pt-1 h-8 whitespace-nowrap" wire:model='event_type_id'>
                    <option value="All">{{ __('All') }}</option>
                    @foreach ($eventTypes as $eventType)
                        <option value="{{ $eventType->id }}">
                            {{ $eventType->name }}
                        </option>
                    @endforeach
                </select>
                <x-jet-input-error for='event_type_id' />
            </div>

            <div>
                <x-jet-label value="{{ __('Type') }}" />
                <select class="form-control pt-1 h-8 whitespace-nowrap" wire:model='rtype'>
                    @foreach ($rtypes as $rtype_key => $rtype_val)
                        <option value="{{ $rtype_key }}">
                            {{ $rtype_key }}</option>
                    @endforeach
                </select>
                <x-jet-input-error for='rtype' />
            </div>

            <div class="h-8 pt-1 flex gap-2 ml-auto">
                <x-jet-button class="h-8 mt-4 bg-indigo-500 hover:bg-indigo-600 justify-center"
                    wire:click='generatePreview' wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="generatePreview">{{ __('Generate Report') }}</span>
                    <span wire:loading wire:target="generatePreview" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('Generating...') }}
                    </span>
                </x-jet-button>

                <x-jet-button class="h-8 mt-4 bg-green-500 hover:bg-green-600 justify-center"
                    wire:click='export' wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="export">{{ __('Download') }}</span>
                    <span wire:loading wire:target="export" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('Generating...') }}
                    </span>
                </x-jet-button>
            </div>
        </div>
    </div>

    {{-- PDF Preview --}}
    @if($pdfUrl)
        <div class="w-full min-h-screen mt-6 bg-gray-100 rounded-lg shadow-lg overflow-hidden">
            <iframe 
                src="{{ $pdfUrl }}" 
                class="w-full min-h-screen"
                frameborder="0"
            ></iframe>
        </div>
    @endif
</div>
