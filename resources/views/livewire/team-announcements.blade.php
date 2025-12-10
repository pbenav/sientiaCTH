<div>
    @if ($announcements->count() > 0)
        <div class="space-y-3">
            @foreach ($announcements as $announcement)
                <div class="p-4 bg-white rounded-lg shadow-sm border-l-4 border-blue-500" x-data="{ expanded: false }">
                    <h3 class="text-base font-bold text-gray-900">{{ $announcement->title }}</h3>
                    <div class="mt-2 prose prose-sm max-w-none text-gray-700">
                        <div x-show="!expanded">
                            @if($announcement->format === 'markdown')
                                <p>{{ Str::limit(strip_tags(Str::markdown($announcement->content)), 100) }}</p>
                            @else
                                <p>{{ Str::limit(strip_tags($announcement->content), 100) }}</p>
                            @endif
                            <button @click="expanded = true" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-1">
                                {{ __('Ver más') }} →
                            </button>
                        </div>
                        <div x-show="expanded" x-transition>
                            @if($announcement->format === 'markdown')
                                {!! Str::markdown($announcement->content) !!}
                            @else
                                {!! $announcement->content !!}
                            @endif
                            <button @click="expanded = false" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2">
                                ← {{ __('Ver menos') }}
                            </button>
                        </div>
                    </div>
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
    @endif
</div>
