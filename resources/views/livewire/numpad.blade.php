<div class="mx-auto max-w-lg">
    <div class="p-4 rounded-lg shadow-lg">
        <div class="py-4 my-2 text-4xl text-center tracking-wider bg-gray-200 rounded-lg shadow-lg">
            <div class="h-8 ">
                {{ $user_code }}
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <button wire:click="addNumber('1')" class="btn-pad">1</button>
            <button wire:click="addNumber('2')" class="btn-pad">2</button>
            <button wire:click="addNumber('3')" class="btn-pad">3</button>
            <button wire:click="addNumber('4')" class="btn-pad">4</button>
            <button wire:click="addNumber('5')" class="btn-pad">5</button>
            <button wire:click="addNumber('6')" class="btn-pad">6</button>
            <button wire:click="addNumber('7')" class="btn-pad">7</button>
            <button wire:click="addNumber('8')" class="btn-pad">8</button>
            <button wire:click="addNumber('9')" class="btn-pad">9</button>
            <button wire:click="addNumber('0')" class="col-span-3 btn-pad">0</button>
        </div>
        <div class="mt-4">
            <button class="btn-code">{{ __('Insert code') }}</button>
        </div>
        <div class="mt-4 w-full text-center">
            <button wire:click="resetDialer" class="btn-aux">{{ __('Reset') }}</button>
            <button wire:click="delete" class="btn-aux">{{ __('Delete') }}</button>
        </div>
    </div>
</div>
