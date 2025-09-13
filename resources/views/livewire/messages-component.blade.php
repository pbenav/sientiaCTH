<div class="p-6 mx-auto w-full max-w-6xl">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-semibold text-gray-700">Mensajes</h1>
        <button wire:click="toggleComposeForm" class="mt-2 sm:mt-0 px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue">
            {{ $showComposeForm ? 'Cancelar' : 'Redactar' }}
        </button>
    </div>

    @if ($showComposeForm)
        <div class="mt-6 p-4 bg-white rounded-lg shadow-md">
            <form wire:submit.prevent="sendMessage">
                <div class="mb-4">
                    <label for="recipients" class="block text-sm font-medium text-gray-700">Destinatarios</label>
                    <select id="recipients" wire:model="recipients" multiple class="block w-full mt-1">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
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
    @else
        <div class="mt-6">
    @endif
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
            <div class="flex justify-end mb-4 space-x-4">
                @if ($view === 'inbox' && count($selectedMessages) > 0)
                    <button wire:click="markSelectedAsRead" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-green-600 border border-transparent rounded-lg active:bg-green-600 hover:bg-green-700 focus:outline-none focus:shadow-outline-green">
                        Marcar seleccionados como leídos ({{ count($selectedMessages) }})
                    </button>
                @endif
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
                        <div class="p-4 bg-white rounded-lg shadow-md">
                            <a href="{{ $notification->data['url'] }}">
                                {{ $notification->data['message'] }}
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($messageList as $message)
                        <div class="p-4 bg-white rounded-lg shadow-md">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center">
                                    @if ($message->sender_id === Auth::id())
                                        {{-- Sent Message --}}
                                        <div class="ml-4">
                                            <p class="font-semibold text-gray-700">
                                                Para:
                                                @foreach ($message->recipients as $recipient)
                                                    {{ $recipient->name }}@if (!$loop->last), @endif
                                                @endforeach
                                            </p>
                                            <p class="text-sm text-gray-500">{{ $message->subject }}</p>
                                        </div>
                                    @else
                                        {{-- Received Message --}}
                                        <img class="w-10 h-10 rounded-full object-cover" src="{{ $message->sender->profile_photo_url }}" alt="{{ $message->sender->name }}">
                                        <div class="ml-4">
                                            <p class="font-semibold text-gray-700">{{ $message->sender->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $message->subject }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    @if ($view === 'inbox' && isset($message->pivot) && $message->pivot->read_at === null)
                                        <input type="checkbox" wire:model="selectedMessages" value="{{ $message->id }}" class="mr-4">
                                    @endif
                                    {{ $message->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                            <div class="mt-4 text-gray-600">
                                {!! nl2br(e($message->body)) !!}
                            </div>
                            <div class="mt-4 flex flex-wrap items-center justify-end space-x-4">
                                @if ($view !== 'trash')
                                    @if ($message->sender_id !== Auth::id())
                                        @if (isset($message->pivot) && $message->pivot->read_at === null)
                                            <button wire:click="markAsRead({{ $message->id }})" class="text-sm text-green-600 hover:text-green-800">
                                                Marcar como leído
                                            </button>
                                        @endif
                                        <button wire:click="replyTo({{ $message->id }})" class="text-sm text-blue-600 hover:text-blue-800">
                                            Responder
                                        </button>
                                    @endif
                                    <button wire:click="deleteMessage({{ $message->id }})" class="text-sm text-red-600 hover:text-red-800">
                                        Eliminar
                                    </button>
                                @else
                                    <button wire:click="restoreMessage({{ $message->id }})" class="text-sm text-blue-600 hover:text-blue-800">
                                        Restaurar
                                    </button>
                                    <button wire:click="forceDeleteMessage({{ $message->id }})" class="text-sm text-red-600 hover:text-red-800">
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
    @if (!$showComposeForm)
</div>
    @endif
