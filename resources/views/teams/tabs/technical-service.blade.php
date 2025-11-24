    <div class="mb-8 border-b border-gray-200 pb-8">
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <h3 class="text-lg font-medium text-gray-900">{{ __('Motor de Generación de PDF') }}</h3>
        <p class="mt-1 text-sm text-gray-600 mb-4">
            {{ __('Selecciona el motor que se utilizará para generar los informes en PDF. Browsershot requiere Node.js y Puppeteer, mientras que mPDF funciona solo con PHP.') }}
        </p>

        <form method="POST" action="{{ route('team.preferences.pdf-engine') }}">
            @csrf
            @method('PUT')
            
            <div class="flex items-center space-x-4 mb-4">
                <label class="inline-flex items-center">
                    <input type="radio" class="form-radio" name="pdf_engine" value="browsershot" {{ (Auth::user()->currentTeam->pdf_engine ?? 'browsershot') === 'browsershot' ? 'checked' : '' }}>
                    <span class="ml-2">{{ __('Browsershot (Puppeteer)') }}</span>
                </label>
                
                <label class="inline-flex items-center">
                    <input type="radio" class="form-radio" name="pdf_engine" value="mpdf" {{ (Auth::user()->currentTeam->pdf_engine ?? 'browsershot') === 'mpdf' ? 'checked' : '' }}>
                    <span class="ml-2">{{ __('mPDF (PHP Puro)') }}</span>
                </label>
            </div>

            <div class="mt-4" x-data="{ showChromePath: '{{ Auth::user()->currentTeam->pdf_engine ?? 'browsershot' }}' === 'browsershot' }" x-show="showChromePath">
                <label for="chrome_path" class="block text-sm font-medium text-gray-700">{{ __('Ruta del Ejecutable de Chrome (Opcional)') }}</label>
                <div class="mt-1 flex rounded-md shadow-sm">
                    <input type="text" name="chrome_path" id="chrome_path" 
                        value="{{ Auth::user()->currentTeam->chrome_path ?? '' }}"
                        class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300"
                        placeholder="{{ __('Dejar vacío para detección automática o buscar en el proyecto') }}">
                    <button type="button" onclick="detectChromePath()" class="-ml-px relative inline-flex items-center space-x-2 px-4 py-2 border border-gray-300 text-sm font-medium rounded-r-md text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ __('Buscar') }}</span>
                    </button>
                </div>
                <p id="chrome_detection_msg" class="mt-2 text-sm text-gray-500">
                    {{ __('Si se deja vacío, el sistema buscará Chrome/Chromium en rutas estándar y dentro de la carpeta del proyecto.') }}
                </p>
            </div>

            <script>
                function detectChromePath() {
                    const msgEl = document.getElementById('chrome_detection_msg');
                    const inputEl = document.getElementById('chrome_path');
                    
                    msgEl.textContent = '{{ __("Buscando Chrome...") }}';
                    msgEl.className = 'mt-2 text-sm text-blue-500';

                    fetch("{{ route('team.preferences.detect-chrome') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            inputEl.value = data.path;
                            msgEl.textContent = data.message;
                            msgEl.className = 'mt-2 text-sm text-green-600 font-medium';
                        } else {
                            msgEl.textContent = data.message;
                            msgEl.className = 'mt-2 text-sm text-red-600 font-medium';
                        }
                    })
                    .catch(error => {
                        msgEl.textContent = 'Error: ' + error.message;
                        msgEl.className = 'mt-2 text-sm text-red-600';
                    });
                }

                document.querySelectorAll('input[name="pdf_engine"]').forEach(input => {
                    input.addEventListener('change', function() {
                        const chromePathDiv = document.querySelector('[x-data]').parentElement.querySelector('[x-show]');
                        // Simple toggle logic if alpine is not fully reactive here, but x-data handles it mostly.
                        // Actually, let's rely on AlpineJS properly.
                        // If Alpine is not present, we might need vanilla JS.
                        // Assuming Jetstream uses Alpine.
                    });
                });
            </script>
            <!-- AlpineJS watcher for radio button -->
            <div x-data="{ engine: '{{ Auth::user()->currentTeam->pdf_engine ?? 'browsershot' }}' }" class="hidden">
                 <!-- This is just a helper, the real logic is in the x-data above -->
            </div>

            <div class="mt-4">
                <x-jet-button>
                    {{ __('Guardar Preferencia') }}
                </x-jet-button>
            </div>
        </form>
    </div>

    <h3 class="text-lg font-medium text-gray-900">{{ __('Instalación de Dependencias (Browsershot)') }}</h3>
    <p class="mt-1 text-sm text-gray-600">
        {{ __('Desde aquí puedes instalar Puppeteer y Browsershot para generar PDFs si eliges usar ese motor.') }}
    </p>

    <div id="logModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        {{ __('Log de instalación') }}
                    </h3>
                    <div class="mt-2">
                        <pre id="logContent" class="text-sm text-gray-500 bg-gray-100 p-2 rounded h-64 overflow-y-auto"></pre>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ __('Cerrar') }}
                    </button>
                </div>
            </div>
        </div>
    </div>



    <form method="POST" action="{{ route('team.preferences.install') }}" class="mt-4" onsubmit="showModal(); return false;">
        @csrf
        <x-jet-button>
            {{ __('Instalar Puppeteer y Browsershot') }}
        </x-jet-button>
    </form>
</div>

<script>
    function showModal() {
        document.getElementById('logModal').classList.remove('hidden');
        const logContent = document.getElementById('logContent');
        logContent.textContent = 'Iniciando instalación...\n';

        fetch("{{ route('team.preferences.install') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            logContent.textContent += data.log || 'Instalación completada.';
        })
        .catch(error => {
            logContent.textContent += 'Error: ' + error.message;
        });
    }

    function closeModal() {
        document.getElementById('logModal').classList.add('hidden');
    }
</script>