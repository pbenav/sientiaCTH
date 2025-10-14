<div class="p-6">
    <div class="flex items-center justify-end">
        <a href="{{ route('teams.work_centers.create', $team) }}">
            <x-jet-button>
                {{ __('Create Work Center') }}
            </x-jet-button>
        </a>
    </div>
</div>

<div class="p-6">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ __('Name') }}
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ __('Code') }}
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ __('Address') }}
                </th>
                <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">{{ __('Edit') }}</span>
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach ($workCenters as $workCenter)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $workCenter->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $workCenter->code }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $workCenter->address }}, {{ $workCenter->city }}, {{ $workCenter->postal_code }}, {{ $workCenter->state }}, {{ $workCenter->country }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('work_centers.edit', $workCenter) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                        <form action="{{ route('work_centers.destroy', $workCenter) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
