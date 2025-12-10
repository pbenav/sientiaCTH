<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <h3 class="text-lg font-medium text-gray-900">Gestión de Mensajes</h3>
        <div class="flex gap-2 mt-2 sm:mt-0">
            <button wire:click="toggleComposeForm" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue">
                {{ $showComposeForm ? 'Cancelar' : 'Redactar' }}
            </button>
            <button wire:click="composeToAll" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-green-600 border border-transparent rounded-lg hover:bg-green-700 focus:outline-none focus:shadow-outline-green">
                Mensaje a todos
            </button>
        </div>
    </div>

    @if ($showComposeForm)
        <div class="mb-6 p-4 bg-white rounded-lg shadow-md">
            <form wire:submit.prevent="sendMessage">
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <label for="recipients" class="block text-sm font-medium text-gray-700">Destinatarios</label>
                        <button type="button" 
                                wire:click="selectAllTeam" 
                                class="text-xs px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded transition">
                            <i class="fas fa-users mr-1"></i>
                            Seleccionar todo el equipo
                        </button>
                    </div>
                    <select id="recipients" wire:model="recipients" multiple class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" size="6">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} {{ $user->family_name1 }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        {{ count($recipients) }} {{ count($recipients) === 1 ? 'destinatario seleccionado' : 'destinatarios seleccionados' }}
                    </p>
                </div>
                <div class="mb-4">
                    <label for="subject" class="block text-sm font-medium text-gray-700">Asunto</label>
                    <input type="text" id="subject" wire:model="subject" class="block w-full mt-1">
                </div>
                <div class="mb-4">
                    <label for="body" class="block text-sm font-medium text-gray-700">Mensaje</label>
                    <textarea id="body" wire:model="body" rows="5" class="block w-full mt-1"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue">
                        Enviar
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
            <div class="flex flex-wrap border-b border-gray-200">
                <button wire:click="showInbox" class="px-4 py-2 -mb-px text-sm font-medium leading-5 @if($view === 'inbox') text-blue-600 border-b-2 border-blue-600 @else text-gray-500 border-b-2 border-transparent @endif hover:text-gray-700 hover:border-gray-300 focus:outline-none">
                    Bandeja de entrada
                </button>
                <button wire:click="showSent" class="px-4 py-2 text-sm font-medium leading-5 @if($view === 'sent') text-blue-600 border-b-2 border-blue-600 @else text-gray-500 border-b-2 border-transparent @endif hover:text-gray-700 hover:border-gray-300 focus:outline-none">
                    Enviados
                </button>
                <button wire:click="showTrash" class="px-4 py-2 text-sm font-medium leading-5 @if($view === 'trash') text-blue-600 border-b-2 border-blue-600 @else text-gray-500 border-b-2 border-transparent @endif hover:text-gray-700 hover:border-gray-300 focus:outline-none">
                    Papelera
                </button>
                <button wire:click="showAlerts" class="px-4 py-2 text-sm font-medium leading-5 @if($view === 'alerts') text-blue-600 border-b-2 border-blue-600 @else text-gray-500 border-b-2 border-transparent @endif hover:text-gray-700 hover:border-gray-300 focus:outline-none">
                    Alertas
                </button>
            </div>

            <div class="mt-4">
                <div class="flex justify-between items-center mb-4 space-x-4">
                    <div class="flex items-center space-x-4">
                        @if ($view === 'inbox' || $view === 'sent' || $view === 'alerts')
                        <div class="flex items-center">
                            <input type="checkbox" wire:model="selectAll" class="mr-2">
                            <span>{{ __('Select all') }}</span>
                        </div>
                        @endif

                        @if ($view === 'inbox' && count($selectedMessages) > 0)
                            <div class="flex items-center">
                                <select wire:model="bulkAction" class="form-control mr-2">
                                    <option value="">{{ __('Bulk Action') }}</option>
                                    <option value="markAsRead">{{ __('Mark as read') }}</option>
                                    <option value="delete">{{ __('Delete') }}</option>
                                </select>
                                <button wire:click="applyBulkAction" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue">
                                    {{ __('Apply') }}
                                </button>
                            </div>
                        @endif
                        @if ($view === 'sent' && count($selectedMessages) > 0)
                            <div class="flex items-center">
                                <select wire:model="bulkAction" class="form-control mr-2">
                                    <option value="">{{ __('Bulk Action') }}</option>
                                    <option value="delete">{{ __('Delete') }}</option>
                                </select>
                                <button wire:click="applyBulkAction" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue">
                                    {{ __('Apply') }}
                                </button>
                            </div>
                        @endif
                        @if ($view === 'alerts' && count($selectedNotifications) > 0)
                            <div class="flex items-center">
                                <select wire:model="bulkAlertAction" class="form-control mr-2">
                                    <option value="">{{ __('Bulk Action') }}</option>
                                    <option value="delete">{{ __('Delete') }}</option>
                                </select>
                                <button wire:click="applyBulkAlertAction" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue">
                                    {{ __('Apply') }}
                                </button>
                            </div>
                        @endif
                    </div>

                    @if ($view === 'trash' && !$messageList->isEmpty())
                        <button wire:click="emptyTrash" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-red-600 border border-transparent rounded-lg active:bg-red-600 hover:bg-red-700 focus:outline-none focus:shadow-outline-red">
                            Vaciar papelera
                        </button>
                    @endif
                </div>
                @if ($messageList->isEmpty())
                    <p class="text-gray-500">No hay mensajes en esta carpeta.</p>
                @elseif ($view === 'alerts')
                    <div class="space-y-4">
                        @foreach ($messageList as $notification)
                            <div class="p-4 bg-white rounded-lg shadow-md flex items-center justify-between" wire:key="'notification-{{ $notification->id }}'">
                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="selectedNotifications" value="{{ $notification->id }}" class="mr-4">
                                    <a href="{{ $notification->data['url'] ?? '#' }}">
                                        {{ $notification->data['message'] }}
                                    </a>
                                </div>
                                <button wire:click="deleteNotification('{{ $notification->id }}')" class="text-sm text-red-600 hover:text-red-800">
                                    {{ __('Delete') }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($messageList as $message)
    <div class="p-4 bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow cursor-pointer" 
         wire:key="{{ 'message-' . $view . '-' . $message->id }}"
         onclick="window.location.href='{{ route('messages') }}?view={{ $view }}&message={{ $message->id }}'">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center">
                @if ($message->sender_id === Auth::id())
                    {{-- Sent Message --}}
                    <input type="checkbox" wire:model="selectedMessages" value="{{ $message->id }}" class="mr-4" onclick="event.stopPropagation()">
                    <div class="ml-4">
                        <p class="font-semibold text-gray-700">
                            Para:
                            @foreach ($message->recipients as $recipient)
                                {{ $recipient->name }} {{ $recipient->family_name1 }}@if (!$loop->last), @endif
                            @endforeach
                        </p>
                        <p class="text-sm text-gray-500">{{ $message->subject }}</p>
                    </div>
                @else
                    {{-- Received Message --}}
                    @if ($view === 'inbox' && isset($message->pivot) && $message->pivot->read_at === null)
                        <input type="checkbox" wire:model="selectedMessages" value="{{ $message->id }}" class="mr-4" onclick="event.stopPropagation()">
                    @endif
                    <img class="w-10 h-10 rounded-full object-cover" src="{{ $message->sender->profile_photo_url }}" alt="{{ $message->sender->name }}">
                    <div class="ml-4">
                        <p class="font-semibold text-gray-700">{{ $message->sender->name }} {{ $message->sender->family_name1 }}</p>
                        <p class="text-sm text-gray-500">{{ $message->subject }}</p>
                    </div>
                @endif
            </div>
            <div class="flex items-center text-sm text-gray-500">
                {{ $message->created_at->format('d/m/Y H:i') }}
            </div>
        </div>
        <div class="mt-4 text-gray-600">
            {!! $message->body !!}
        </div>
        <div class="mt-4 flex flex-wrap items-center justify-end space-x-4">
            @if ($view !== 'trash')
                @if ($message->sender_id !== Auth::id())
                    @if (isset($message->pivot) && $message->pivot->read_at === null)
                        <button wire:click="markAsRead({{ $message->id }})" class="text-sm text-green-600 hover:text-green-800" onclick="event.stopPropagation()">
                            Marcar como leído
                        </button>
                    @endif
                    <button wire:click="replyTo({{ $message->id }})" class="text-sm text-blue-600 hover:text-blue-800" onclick="event.stopPropagation()">
                        Responder
                    </button>
                @endif
                <button wire:click="deleteMessage({{ $message->id }})" class="text-sm text-red-600 hover:text-red-800" onclick="event.stopPropagation()">
                    Eliminar
                </button>
            @else
                <button wire:click="restoreMessage({{ $message->id }})" class="text-sm text-blue-600 hover:text-blue-800" onclick="event.stopPropagation()">
                    Restaurar
                </button>
                <button wire:click="forceDeleteMessage({{ $message->id }})" class="text-sm text-red-600 hover:text-red-800" onclick="event.stopPropagation()">
                    Eliminar permanentemente
                </button>
            @endif
        </div>
    </div>
@endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
