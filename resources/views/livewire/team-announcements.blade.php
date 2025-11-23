<div>
    @if ($announcements->count() > 0)
        <div class="p-4 mx-auto mt-4 w-full bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg shadow-sm" x-data="{ open: false }">
            <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
                <div class="flex items-center space-x-2">
                    <div class="bg-blue-500 p-2 rounded-full">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                    </div>
                    <p class="text-lg font-bold text-blue-800">{{ __('Team Announcements') }}</p>
                </div>
                <svg :class="open ? 'rotate-180' : ''" class="w-5 h-5 text-blue-600 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>

            <div x-show="open" x-transition class="mt-4 space-y-3">
                @foreach ($announcements as $announcement)
                    <div class="p-4 bg-white rounded-lg shadow-sm border-l-4 border-blue-500">
                        <h3 class="text-base font-bold text-gray-900">{{ $announcement->title }}</h3>
                        <div class="mt-2 prose prose-sm max-w-none text-gray-700">{!! $announcement->content !!}</div>
                        <div class="mt-3 flex flex-wrap gap-3 text-xs text-gray-500">
                            @if ($announcement->start_date || $announcement->end_date)
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    @if ($announcement->start_date && $announcement->end_date)
                                        {{ $announcement->start_date->format('d/m/Y') }} - {{ $announcement->end_date->format('d/m/Y') }}
                                    @elseif ($announcement->start_date)
                                        {{ __('From') }} {{ $announcement->start_date->format('d/m/Y') }}
                                    @else
                                        {{ __('Until') }} {{ $announcement->end_date->format('d/m/Y') }}
                                    @endif
                                </span>
                            @endif
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                {{ $announcement->creator->name }} · {{ $announcement->created_at->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
