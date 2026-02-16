{{-- Message Thread Item Component --}}
@php
    $isUnread = $view === 'inbox' && isset($message->pivot) && $message->pivot->read_at === null;
    $isSent = $message->sender_id === Auth::id();
    $hasReplies = $message->replies->isNotEmpty();
    $isCollapsed = in_array($message->id, $collapsedThreads);
    $recipientsCount = $message->recipients->count();
@endphp

<div class="rounded-lg shadow-sm border {{ $isUnread ? 'bg-blue-50 border-blue-200' : 'bg-white border-gray-200' }} {{ $level > 0 ? 'ml-8 mt-2' : '' }}" 
     wire:key="message-{{ $view }}-{{ $message->id }}"
     x-data="{ expanded: false, showAllRecipients: false }">
    
    {{-- Main Message --}}
    <div class="p-4">
        <div class="flex items-start justify-between">
            <div class="flex items-start flex-1 min-w-0">
                {{-- Checkbox for selection --}}
                @if ($level === 0 && $view !== 'trash')
                    @if ($isSent || ($view === 'inbox' && $isUnread))
                        <input type="checkbox" wire:model="selectedMessages" value="{{ $message->id }}" class="mr-3 mt-1 flex-shrink-0" onclick="event.stopPropagation()">
                    @endif
                @endif

                {{-- Avatar (only for received messages) --}}
                @if (!$isSent)
                    <img class="w-10 h-10 rounded-full object-cover flex-shrink-0 mr-3" src="{{ $message->sender->profile_photo_url }}" alt="{{ $message->sender->name }}">
                @endif

                {{-- Message Content --}}
                <div class="flex-1 min-w-0">
                    {{-- Sender/Recipients Info --}}
                    <div class="flex items-center gap-2 mb-1">
                        @if ($isSent)
                            {{-- Sent Message - Show Recipients --}}
                            <span class="text-xs text-gray-500 font-medium">Para:</span>
                                @if ($recipientsCount > 0)
                                    <div class="text-sm text-gray-700">
                                        @foreach ($message->recipients as $recipient)
                                            <span x-show="{{ ($recipientsCount > 3 && $loop->iteration > 2) ? 'showAllRecipients' : 'true' }}" 
                                                  @if($recipientsCount > 3 && $loop->iteration > 2) style="display: none;" @endif>
                                                <span class="font-medium">{{ $recipient->name }} {{ $recipient->family_name1 }}</span>@if (!$loop->last), @endif
                                            </span>
                                        @endforeach
                                        
                                        @if ($recipientsCount > 3)
                                            <button @click="showAllRecipients = !showAllRecipients" class="text-blue-600 hover:text-blue-800 text-xs font-medium ml-1">
                                                <span x-show="!showAllRecipients">y {{ $recipientsCount - 2 }} más <i class="fas fa-chevron-down text-xs"></i></span>
                                                <span x-show="showAllRecipients" style="display: none;">ver menos <i class="fas fa-chevron-up text-xs"></i></span>
                                            </button>
                                        @endif
                                    </div>
                                @endif
                        @else
                            {{-- Received Message - Show Sender --}}
                            <span class="font-semibold text-gray-900">{{ $message->sender->name }} {{ $message->sender->family_name1 }}</span>
                        @endif
                        
                        {{-- Unread indicator --}}
                        @if ($isUnread)
                            <span class="w-2 h-2 bg-blue-600 rounded-full flex-shrink-0"></span>
                        @endif
                    </div>

                    {{-- Subject --}}
                    <h4 class="text-sm {{ $isUnread ? 'font-bold' : 'font-semibold' }} text-gray-900 mb-1 truncate">
                        {{ $message->subject }}
                    </h4>

                    {{-- Message Preview/Full Body --}}
                    <div class="text-sm text-gray-600 break-words">
                        @php
                            $bodyContent = $message->body;
                            if (strip_tags($bodyContent) === $bodyContent) {
                                $bodyContent = str_replace('\\n', "\n", $bodyContent);
                                $bodyContent = \Illuminate\Support\Str::markdown($bodyContent);
                            }
                            $isLong = strlen(strip_tags($bodyContent)) > 150;
                        @endphp
                        
                        <div x-show="!expanded" class="line-clamp-2">
                            {!! \Illuminate\Support\Str::limit(strip_tags($bodyContent), 150) !!}
                        </div>
                        <div x-show="expanded" style="display: none;">
                            {!! $bodyContent !!}
                        </div>
                        
                        @if ($isLong)
                            <button @click="expanded = !expanded" class="text-blue-600 hover:text-blue-800 text-xs font-medium mt-1">
                                <span x-show="!expanded">Leer más</span>
                                <span x-show="expanded" style="display: none;">Leer menos</span>
                            </button>
                        @endif
                    </div>

                    {{-- Timestamp and Reply Count --}}
                    <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                        <span>{{ $message->created_at->format('d/m/Y H:i') }}</span>
                        @if ($hasReplies && $level === 0)
                            <button wire:click="toggleThread({{ $message->id }})" class="flex items-center gap-1 text-blue-600 hover:text-blue-800 font-medium">
                                <i class="fas fa-comments"></i>
                                <span>{{ $message->replies->count() }} {{ $message->replies->count() === 1 ? 'respuesta' : 'respuestas' }}</span>
                                <i class="fas fa-chevron-{{ $isCollapsed ? 'down' : 'up' }} text-xs"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Timestamp (desktop) --}}
            <div class="hidden sm:block text-xs text-gray-500 ml-4 flex-shrink-0">
                {{ $message->created_at->diffForHumans() }}
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="mt-3 flex flex-wrap items-center justify-end gap-2 border-t border-gray-100 pt-3">
            @if ($view !== 'trash')
                @if (!$isSent)
                    @if ($isUnread)
                        <button wire:click="markAsRead({{ $message->id }})" class="text-xs px-3 py-1 text-green-600 hover:text-green-800 hover:bg-green-50 rounded transition">
                            <i class="fas fa-check mr-1"></i> Marcar como leído
                        </button>
                    @endif
                    <button wire:click="replyTo({{ $message->id }})" class="text-xs px-3 py-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition">
                        <i class="fas fa-reply mr-1"></i> Responder
                    </button>
                @endif
                <button wire:click="deleteMessage({{ $message->id }})" class="text-xs px-3 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition">
                    <i class="fas fa-trash mr-1"></i> Eliminar
                </button>
            @else
                <button wire:click="restoreMessage({{ $message->id }})" class="text-xs px-3 py-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition">
                    <i class="fas fa-undo mr-1"></i> Restaurar
                </button>
                <button wire:click="forceDeleteMessage({{ $message->id }})" class="text-xs px-3 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition">
                    <i class="fas fa-trash-alt mr-1"></i> Eliminar permanentemente
                </button>
            @endif
        </div>
    </div>

    {{-- Nested Replies --}}
    @if ($hasReplies && !$isCollapsed && $level === 0)
        <div class="border-t border-gray-200 bg-gray-50 p-3">
            @foreach ($message->replies as $reply)
                @include('livewire.partials.message-thread-item', ['message' => $reply, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>
