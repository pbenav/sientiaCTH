<div x-data="initNumpad()" x-init="iniciar()">

    @livewire('add-event')

    @if (session('info'))
        {{-- This div shows information attached to request if exists --}}
        <div class="flex items-center bg-blue-500 text-white text-sm font-bold px-4 py-3" role="alert">
            <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path
                    d="M12.432 0c1.34 0 2.01.912 2.01 1.957 0 1.305-1.164 2.512-2.679 2.512-1.269 0-2.009-.75-1.974-1.99C9.789 1.436 10.67 0 12.432 0zM8.309 20c-1.058 0-1.833-.652-1.093-3.524l1.214-5.092c.211-.814.246-1.141 0-1.141-.317 0-1.689.562-2.502 1.117l-.528-.88c2.572-2.186 5.531-3.467 6.801-3.467 1.057 0 1.233 1.273.705 3.23l-1.391 5.352c-.246.945-.141 1.271.106 1.271.317 0 1.357-.392 2.379-1.207l.6.814C12.098 19.02 9.365 20 8.309 20z" />
            </svg>
            <p>{{ __(session('info')) }}</p>
        </div>
    @endif

    <div class="max-w-lg mx-auto">
        <div class="content-center">
            <div class="mt-2">
                <x-clock>Reloj</x-clock>
            </div>
            <div class="w-auto mb-4 text-center">
                <form wire:submit.prevent="insertCode">
                    <input type="password" id="user_code" x-model="user_code" class="btn btn-pad"/>
                </form>
            </div>

            <div id="buttons" class="grid grid-cols-3 gap-4">
                <button @click="addCode('1')" class="btn-pad">1</button>
                <button @click="addCode('2')" class="btn-pad">2</button>
                <button @click="addCode('3')" class="btn-pad">3</button>
                <button @click="addCode('4')" class="btn-pad">4</button>
                <button @click="addCode('5')" class="btn-pad">5</button>
                <button @click="addCode('6')" class="btn-pad">6</button>
                <button @click="addCode('7')" class="btn-pad">7</button>
                <button @click="addCode('8')" class="btn-pad">8</button>
                <button @click="addCode('9')" class="btn-pad">9</button>
                <button @click="addCode('0')" class="col-span-3 btn-pad">0</button>
            </div>
            <div class="mt-4">
                <button type="submit" wire:click="insertCode" class="btn-code">{{ __('Insert code') }}</button>
            </div>
            <div class="mt-0 text-center content-center">
                <button @click="resetCode()" class="mt-4 btn-aux w-min">{{ __('Reset') }}</button>
                <button @click="deleteCode()" class="mt-4 btn-aux w-min sm:ml-4 sm:mt-0 ">{{ __('Delete') }}</button>
            </div>
        </div>

    </div>

    <script>
        window.onload = (event) => {
            document.getElementById("user_code").focus();
        };

        function initNumpad(event) {
            return {
                user_code: @entangle('user_code').defer,
                iniciar: function() {
                    this.user_code = '';
                },
                addCode: function(s) {
                    this.user_code += s;
                },
                resetCode: function() {
                    this.user_code = '';
                },
                deleteCode: function() {
                    this.user_code = this.user_code.slice(0, -1);
                },
            }
        }
    </script>
</div>
