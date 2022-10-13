@props(['label'])
<div x-data="datepicker(@entangle($attributes->wire('model')))" class="relative">
    {{-- Anonymous component. No need to create a class component --}}
    <div class="flex flex-col custom-textarea">
        <label>{{ $label }}</label>
        <div class="flex items-center gap-2">
            <input type="text" x-ref="datepicker" x-model="value">
            <span class="cursor-pointer underline" x-on:click="reset">
                <i class="ml-2 fas fa-calendar-xmark"></i>
            </span>
        </div>
    </div>
</div>

@once  
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('datepicker', (model) => ({
                value: model,
                init() {
                    this.pickr = flatpickr(this.$refs.datepicker, {
                        locale: "es",/login
                        enableTime: true,
                        dateFormat: "Y-m-d H:i:s",
                        minDate: "2020-01",
                        maxDate: "today",
                        time_24hr: true,
                        minTime: "08:00",
                        maxTime: "20:00",
                        dateFormat: "d.m.Y H:i",
                        disableMobile: "true",
                        static: false,
                    })
                    this.$watch('value', function(newValue) {
                        this.pickr.setDate(newValue);
                    }.bind(this));
                },
                reset() {
                    this.value = null;
                }
            }))
        })
        
    </script>
@endonce
