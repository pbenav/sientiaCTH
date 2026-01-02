<div>
    @if($showModal)
    <!-- Modal Backdrop -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-40" wire:click="closeModal"></div>

    <!-- Modal -->
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg" @click.stop>
                
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-white">
                                {{ __('Request Event Reopening') }}
                            </h3>
                        </div>
                        <button wire:click="closeModal" class="text-white hover:text-gray-200 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="bg-white px-6 py-4">
                    @if($event)
                    <!-- Event Info Summary -->
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="text-sm">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold text-gray-700">{{ __('Event') }} #{{ $event->id }}</span>
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-lock mr-1"></i>{{ __('Closed') }}
                                </span>
                            </div>
                            <div class="text-gray-600 space-y-1">
                                <div><strong>{{ __('Date') }}:</strong> {{ \Carbon\Carbon::parse($event->start)->format('d/m/Y H:i') }}</div>
                                <div><strong>{{ __('Type') }}:</strong> {{ $event->eventType->name ?? __('Unknown') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Reason Input -->
                    <div class="mb-4">
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('Reason for reopening request') }} <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            wire:model.defer="reason" 
                            id="reason"
                            rows="4" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none"
                            placeholder="{{ __('Please explain why you need to reopen this event') }}"
                        ></textarea>
                        @error('reason')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            {{ __('Minimum :min characters required', ['min' => 10]) }} 
                            <span class="float-right">{{ strlen($reason) }}/500</span>
                        </p>
                    </div>

                    <!-- Info Alert -->
                    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm text-blue-800">
                                {{ __('Your request will be sent to all team administrators. They will review it and may reopen the event if appropriate.') }}
                            </p>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                    <button 
                        wire:click="closeModal" 
                        type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors"
                    >
                        {{ __('Cancel') }}
                    </button>
                    <button 
                        wire:click="requestReopening" 
                        type="button"
                        class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg hover:from-orange-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>{{ __('Send Request') }}
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif
</div>
