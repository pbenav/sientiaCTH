<div class="p-6 mx-auto w-full max-w-6xl">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-700">Mensajes</h1>
        <button wire:click="toggleComposeForm" class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue">
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
        <div class="flex border-b border-gray-200">
            <button wire:click="showInbox" class="px-4 py-2 -mb-px text-sm font-medium leading-5 @if($view === 'inbox') text-blue-600 border-b-2 border-blue-600 @else text-gray-500 border-b-2 border-transparent @endif hover:text-gray-700 hover:border-gray-300 focus:outline-none">
                Bandeja de entrada
            </button>
            <button wire:click="showSent" class="px-4 py-2 text-sm font-medium leading-5 @if($view === 'sent') text-blue-600 border-b-2 border-blue-600 @else text-gray-500 border-b-2 border-transparent @endif hover:text-gray-700 hover:border-gray-300 focus:outline-none">
                Enviados
            </button>
            <button wire:click="showTrash" class="px-4 py-2 text-sm font-medium leading-5 @if($view === 'trash') text-blue-600 border-b-2 border-blue-600 @else text-gray-500 border-b-2 border-transparent @endif hover:text-gray-700 hover:border-gray-300 focus:outline-none">
                Papelera
            </button>
        </div>

        <div class="mt-4">
            @if ($messageList->isEmpty())
                <p class="text-gray-500">No hay mensajes en esta carpeta.</p>
            @else
                <div class="space-y-4">
                    @foreach ($messageList as $message)
                        <div class="p-4 bg-white rounded-lg shadow-md">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    @if ($view !== 'sent')
                                        <img class="w-10 h-10 rounded-full object-cover" src="{{ $message->sender->profile_photo_url }}" alt="{{ $message->sender->name }}">
                                        <div class="ml-4">
                                            <p class="font-semibold text-gray-700">{{ $message->sender->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $message->subject }}</p>
                                        </div>
                                    @else
                                        <div class="ml-4">
                                            <p class="font-semibold text-gray-700">
                                                Para:
                                                @foreach ($message->recipients as $recipient)
                                                    {{ $recipient->name }}@if (!$loop->last), @endif
                                                @endforeach
                                            </p>
                                            <p class="text-sm text-gray-500">{{ $message->subject }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $message->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                            <div class="mt-4 text-gray-600">
                                {!! nl2br(e($message->body)) !!}
                            </div>
                            <div class="mt-4 flex items-center justify-end">
                                @if ($view !== 'trash')
                                    <button wire:click="deleteMessage({{ $message->id }})" class="text-sm text-red-600 hover:text-red-800">
                                        Eliminar
                                    </button>
                                @else
                                    <button wire:click="restoreMessage({{ $message->id }})" class="text-sm text-blue-600 hover:text-blue-800">
                                        Restaurar
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
