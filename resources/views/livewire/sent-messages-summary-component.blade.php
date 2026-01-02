<div class="bg-white rounded-lg shadow-sm p-4 flex flex-col h-full">
    <div class="flex justify-between items-center mb-3">
        <div class="flex items-center gap-2">
            <h3 class="text-lg font-medium text-gray-900">{{ __('Sent Messages') }}</h3>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                {{ $messages->count() }}
            </span>
        </div>
        <a href="{{ route('messages') }}?view=sent" class="text-sm text-blue-600 hover:text-blue-800">
            {{ __('Ver todos') }} →
        </a>
    </div>
    
    @if($messages->count() > 0)
        <div class="space-y-2 overflow-y-auto" style="max-height: 320px; min-height: 320px;">
            @foreach($messages as $message)
                <a href="{{ route('messages') }}?view=sent&message={{ $message->id }}" class="block p-3 hover:bg-gray-50 rounded-lg transition">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                <i class="fas fa-paper-plane text-green-600 text-sm"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-baseline">
                                <p class="text-sm font-semibold text-gray-900 truncate">
                                    {{ __('To (recipient)') }}: 
                                    @if($message->recipients->count() === 1)
                                        {{ $message->recipients->first()->name }} {{ $message->recipients->first()->family_name1 }}
                                    @else
                                        {{ $message->recipients->count() }} {{ __('recipients') }}
                                    @endif
                                </p>
                                <span class="text-xs text-gray-500 ml-2">
                                    {{ $message->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 truncate mt-1">{{ $message->subject }}</p>
                            <p class="text-xs text-gray-500 mt-2 line-clamp-2 break-words">
                                {{ Str::limit(strip_tags($message->body), 100) }}
                            </p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <p class="text-sm text-gray-500 text-center py-4">{{ __('No has enviado mensajes') }}</p>
    @endif
</div>
