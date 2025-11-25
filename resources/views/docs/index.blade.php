<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📚 {{ $title }}
                <img src="{{ asset('images/cth-logo.png') }}" alt="CTH Logo" class="mx-auto my-4 max-w-[200px]" />
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[90rem] mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Sidebar -->
                <div class="w-full lg:w-64 flex-shrink-0">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4 sticky top-4">
                        <h3 class="font-semibold text-gray-900 mb-4">{{ __('Documents') }}</h3>
                        <nav class="space-y-1">
                            <a href="{{ route('docs.index') }}" 
                               class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('docs.index') ? 'bg-blue-50 text-blue-700' : 'text-gray-900 hover:bg-gray-50' }}">
                                {{ __('Home') }}
                            </a>
                            
                            @if(isset($files))
                                @foreach($files as $category => $categoryFiles)
                                    @if($category !== 'root')
                                        <div class="mt-4">
                                            <h4 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                {{ ucfirst($category) }}
                                            </h4>
                                            <div class="mt-2 space-y-1">
                                                @foreach($categoryFiles as $file)
                                                    <a href="{{ $file['url'] }}" 
                                                       class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->url() === $file['url'] ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                                        {{ $file['label'] }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <div class="mt-2 space-y-1">
                                            @foreach($categoryFiles as $file)
                                                <a href="{{ $file['url'] }}" 
                                                   class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->url() === $file['url'] ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                                    {{ $file['label'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </nav>
                    </div>
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No content available</h3>
                                <p class="mt-1 text-sm text-gray-500">Select a document from the sidebar to start reading.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
