<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Message') }}
            </h2>
            <a href="{{ route('messages') }}" class="text-sm text-blue-600 hover:text-blue-800">
                ← Volver a mensajes
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[90rem] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <!-- Message Header -->
                <div class="border-b pb-4 mb-4">
                    <div class="flex items-center mb-2">
                        <img class="w-12 h-12 rounded-full object-cover" 
                             src="{{ $message->sender->profile_photo_url }}" 
                             alt="{{ $message->sender->name }}">
                        <div class="ml-4">
                            <p class="font-semibold text-gray-900">{{ $message->sender->name }} {{ $message->sender->family_name1 }}</p>
                            <p class="text-sm text-gray-500">{{ $message->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mt-4">{{ $message->subject }}</h3>
                    <p class="text-sm text-gray-600">
                        Para: 
                        @foreach ($message->recipients as $recipient)
                            {{ $recipient->name }} {{ $recipient->family_name1 }}@if (!$loop->last), @endif
                        @endforeach
                    </p>
                </div>

                <!-- Message Body -->
                <div class="prose max-w-none">
                    @php
                        // Only parse as Markdown if content doesn't contain HTML tags
                        $body = $message->body;
                        if (strip_tags($body) === $body) {
                            // No HTML tags found, it's Markdown
                            // Convert literal \n to actual newlines
                            $body = str_replace('\\n', "\n", $body);
                            $body = \Illuminate\Support\Str::markdown($body);
                        }
                    @endphp
                    {!! $body !!}
                </div>

                <!-- Actions -->
                <div class="mt-6 flex space-x-4">
                    @if ($message->sender_id !== Auth::id())
                        <a href="{{ route('messages') }}?reply={{ $message->id }}" 
                           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Responder
                        </a>
                    @endif
                    <a href="{{ route('messages') }}" 
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
