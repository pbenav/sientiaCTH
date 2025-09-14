<div wire:poll.5s>
    <x-jet-action-section>
        <x-slot name="title">
            {{ __('IPs Bloqueadas') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Aquí puedes ver y gestionar las IPs que han sido bloqueadas debido a demasiados intentos fallidos de inicio de sesión.') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-6">
                @if (session()->has('message'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('message') }}</span>
                    </div>
                @endif
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('IP') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Fecha del Bloqueo') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Tiempo de Bloqueo (segundos)') }}
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">{{ __('Acciones') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($blockedIps as $ip)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $ip->ip_address }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $ip->timestamp->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $ip->lockout_time }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click.prevent="unblock({{ $ip->id }})" class="text-indigo-600 hover:text-indigo-900">{{ __('Desbloquear') }}</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    {{ __('No hay IPs bloqueadas en este momento.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $blockedIps->links() }}
                </div>
            </div>
        </x-slot>
    </x-jet-action-section>
</div>
