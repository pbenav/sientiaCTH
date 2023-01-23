<x-app-layout>
    <div class="flex-col m-5 sm:m-10">
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

        {{-- Stats main div --}}
        <div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">

            <form action="{{ route('reports.export') }}" method="post">
                @csrf
                <div class="w-auto m-auto flex flex-row flex-wrap gap-2 ml-2 mb-4">

                    @if ($isTeamAdmin or $isInspector)
                        <div class="">
                            <x-jet-label value="{{ __('Worker') }}" />
                            <select class="form-control pt-1 h-8 whitespace-nowrap" name="worker" id="worker">
                                @foreach ($workers as $w)
                                    <option value={{ $w->id }}>{{ $w->name . ' ' . $w->family_name1 }}</option>
                                @endforeach
                            </select>
                            <x-jet-input-error for='worker' />
                        </div>
                    @endif

                    <div class="flex flex-nowrap">
                        <div>
                            <x-jet-label value="{{ __('From date') }}" />
                            <x-datepicker class="form-control h-8" value="{{ date('Y-m-01') }}" name="fromdate"
                                id="fromdate" />
                        </div>

                        <div class="ml-2">
                            <x-jet-label value="{{ __('To date') }}" />
                            <x-datepicker class="form-control h-8" value="{{ today() }}" name="todate"
                                id="todate" />
                        </div>
                    </div>

                    <div>
                        <x-jet-label value="{{ __('Description') }}" />
                        <select class="form-control pt-1 h-8 whitespace-nowrap" name="description" id="description">
                            <option value="%">{{ __('All') }}</option>
                            <option value="{{ __('Workday') }}">{{ __('Workday') }}</option>
                            <option value="{{ __('Pause') }}">{{ __('Pause') }}</option>
                            <option value="{{ __('Others') }}">{{ __('Others') }}</option>
                        </select>
                        <x-jet-input-error for='description' />
                    </div>

                    <div>
                        <x-jet-label value="{{ __('Type') }}" />
                        <select class="form-control pt-1 h-8 whitespace-nowrap" name="rtype" id="rtype">
                            <option value="XLS">XLS</option>
                            <option value="PDF">PDF</option>
                            <option value="CSV">CSV</option>
                            <option value="ODS">ODS</option>
                            <option value="HTML">HTML</option>
                            <option value="DOMPDF">DOMPDF</option>
                        </select>
                        <x-jet-input-error for='rtype' />
                    </div>

                    <div class="h-8 pt-1">
                        <x-jet-button class="h-8 mt-4 bg-green-500">{{ __('Download') }}</x-jet-button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</x-app-layout>
