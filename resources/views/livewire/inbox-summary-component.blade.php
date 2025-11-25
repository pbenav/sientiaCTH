<div class="bg-white rounded-lg shadow-sm p-4">
    <div class="flex justify-between items-center mb-3">
        <h3 class="text-lg font-medium text-gray-900">Mensajes recibidos</h3>
        <a href="{{ route('messages') }}" class="text-sm text-blue-600 hover:text-blue-800">
            {{ __('Ver todos') }} →
        </a>
    </div>
    
    @if($messages->count() > 0)
        <div class="space-y-2">
            @foreach($messages as $message)
                <a href="{{ route('messages') }}?view=inbox&message={{ $message->id }}" class="block p-3 hover:bg-gray-50 rounded-lg transition">
                    <div class="flex items-start space-x-3">
                        <img class="w-8 h-8 rounded-full object-cover flex-shrink-0" 
                             src="{{ $message->sender->profile_photo_url }}" 
                             alt="{{ $message->sender->name }}">
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-baseline">
                                <p class="text-sm font-semibold text-gray-900 truncate">
                                    {{ $message->sender->name }} {{ $message->sender->family_name1 }}
                                </p>
                                <span class="text-xs text-gray-500 ml-2">
                                    {{ $message->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 truncate mt-1">
                                {{ $message->subject }}
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <p class="text-sm text-gray-500 text-center py-4">{{ __('No hay mensajes nuevos') }}</p>
    @endif
</div>
