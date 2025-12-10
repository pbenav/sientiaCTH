<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Team Administration') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <!-- Search Bar -->
                    <div class="mb-6">
                        <form method="GET" action="{{ route('admin.teams.index') }}" class="flex gap-2">
                            <input 
                                type="text" 
                                name="search" 
                                value="{{ $search }}" 
                                placeholder="{{ __('Search teams by name or owner...') }}"
                                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">
                                {{ __('Search') }}
                            </button>
                            @if($search)
                                <a href="{{ route('admin.teams.index') }}" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                                    {{ __('Clear') }}
                                </a>
                            @endif
                        </form>
                    </div>

                    <!-- Stats -->
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                        <div class="text-sm text-gray-600">
                            {{ __('Total Teams') }}: <span class="font-bold">{{ $teams->total() }}</span>
                            @if($search)
                                <span class="ml-4">{{ __('Filtered Results') }}: <span class="font-bold">{{ $teams->count() }}</span></span>
                            @endif
                        </div>
                    </div>

                    <!-- Teams Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Team Name') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Owner') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Members') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Type') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Created') }}
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($teams as $team)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $team->name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($team->owner)
                                                <div class="text-sm text-gray-900">{{ $team->owner->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $team->owner->email }}</div>
                                            @else
                                                <div class="text-sm text-gray-500 italic">{{ __('No owner') }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $team->users->count() + 1 }} {{-- +1 for owner --}}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($team->personal_team)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    {{ __('Personal') }}
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    {{ __('Shared') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $team->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('admin.teams.edit', $team) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                {{ __('Manage') }}
                                            </a>
                                            @if(!$team->personal_team)
                                                <form method="POST" action="{{ route('admin.teams.destroy', $team) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this team?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                                        {{ __('Delete') }}
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            {{ __('No teams found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $teams->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
