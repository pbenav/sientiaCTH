<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Work Center') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <form action="{{ route('work_centers.update', $workCenter) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div>
                            <x-jet-label for="name" value="{{ __('Name') }}" />
                            <x-jet-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $workCenter->name)" required autofocus />
                        </div>

                        <div class="mt-4">
                            <x-jet-label for="code" value="{{ __('Code') }}" />
                            <x-jet-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code', $workCenter->code)" required />
                        </div>

                        <div class="mt-4">
                            <x-jet-label for="address" value="{{ __('Address') }}" />
                            <textarea id="address" name="address" class="form-input rounded-md shadow-sm mt-1 block w-full">{{ old('address', $workCenter->address) }}</textarea>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-jet-button>
                                {{ __('Update') }}
                            </x-jet-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
