    <div class="mb-8 border-b border-gray-200 pb-8">
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


            <!-- Información de Rutas Detectadas (Solo información) -->
            <div class="mt-4" x-data="{ showPaths: '{{ Auth::user()->currentTeam->pdf_engine ?? 'browsershot' }}' === 'browsershot' }" x-show="showPaths">
                <h4 class="text-sm font-medium text-gray-700 mb-3">{{ __('Rutas Detectadas (Solo información)') }}</h4>
                
                <!-- Chrome Path Detection -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">{{ __('Ruta de Chrome/Chromium') }}</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <input type="text" id="chrome_path_display" readonly
                            class="bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300"
                            placeholder="{{ __('Click en Buscar para detectar...') }}">
                        <button type="button" onclick="detectChromePath()" class="-ml-px relative inline-flex items-center space-x-2 px-4 py-2 border border-gray-300 text-sm font-medium rounded-r-md text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ __('Buscar') }}</span>
                        </button>
                    </div>
                    <p id="chrome_detection_msg" class="mt-2 text-sm text-gray-500">
                        {{ __('El sistema detectará automáticamente la ruta de Chrome/Chromium instalado.') }}
                    </p>
                </div>

                <!-- Node.js Path Detection -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">{{ __('Ruta de Node.js') }}</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <input type="text" id="node_path_display" readonly
                            class="bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300"
                            placeholder="{{ __('Click en Buscar para detectar...') }}">
                        <button type="button" onclick="detectNodePath()" class="-ml-px relative inline-flex items-center space-x-2 px-4 py-2 border border-gray-300 text-sm font-medium rounded-r-md text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ __('Buscar') }}</span>
                        </button>
                    </div>
                    <p id="node_detection_msg" class="mt-2 text-sm text-gray-500">
                        {{ __('El sistema detectará automáticamente la ruta de Node.js instalado.') }}
                    </p>
                </div>

                <!-- NPM Path Detection -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">{{ __('Ruta de NPM') }}</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <input type="text" id="npm_path_display" readonly
                            class="bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300"
                            placeholder="{{ __('Click en Buscar para detectar...') }}">
                        <button type="button" onclick="detectNpmPath()" class="-ml-px relative inline-flex items-center space-x-2 px-4 py-2 border border-gray-300 text-sm font-medium rounded-r-md text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ __('Buscar') }}</span>
                        </button>
                    </div>
                    <p id="npm_detection_msg" class="mt-2 text-sm text-gray-500">
                        {{ __('El sistema detectará automáticamente la ruta de NPM instalado.') }}
                    </p>
                </div>

                <p class="text-xs text-gray-400 italic">
                    {{ __('Nota: Estas rutas son solo informativas. El sistema las detecta automáticamente al generar PDFs.') }}
                </p>
            </div>

            <script>
                function detectChromePath() {
                    const msgEl = document.getElementById('chrome_detection_msg');
                    const inputEl = document.getElementById('chrome_path_display');
                    
                    msgEl.textContent = '{{ __("Buscando Chrome...") }}';
                    msgEl.className = 'mt-2 text-sm text-blue-500';

                    fetch("{{ route('team.preferences.detect-chrome') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
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

                function detectNpmPath() {
                    const msgEl = document.getElementById('npm_detection_msg');
                    const inputEl = document.getElementById('npm_path_display');
                    
                    msgEl.textContent = '{{ __("Buscando NPM...") }}';
                    msgEl.className = 'mt-2 text-sm text-blue-500';

                    fetch("{{ route('team.preferences.detect-npm') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
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

                function detectNodePath() {
                    const msgEl = document.getElementById('node_detection_msg');
                    const inputEl = document.getElementById('node_path_display');
                    
                    msgEl.textContent = '{{ __("Buscando Node.js...") }}';
                    msgEl.className = 'mt-2 text-sm text-blue-500';

                    fetch("{{ route('team.preferences.detect-node') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
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
                        // AlpineJS handles the visibility automatically
                    });
                });
            </script>
            <!-- End of path detection section -->

            <div class="mt-4">
                <x-jet-button class="bg-indigo-600 hover:bg-indigo-700">
                    {{ __("Guardar Preferencia") }}
                </x-jet-button>
            </div>
        </form>
    </div>

    <div class="mb-8 border-b border-gray-200 pb-8">
        <h3 class="text-lg font-medium text-gray-900">{{ __('Max Report Time Limit') }}</h3>
        <p class="mt-1 text-sm text-gray-600 mb-4">
            {{ __('Set the maximum time range (in months) for report generation.') }}
        </p>

        <form method="POST" action="{{ route('team.preferences.report-limits') }}">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="max_report_months" class="block text-sm font-medium text-gray-700">{{ __('Max Report Time Limit') }} ({{ __('Months') }})</label>
                    <input type="number" name="max_report_months" id="max_report_months" 
                        value="{{ Auth::user()->currentTeam->max_report_months ?? \App\Models\Team::DEFAULT_MAX_REPORT_MONTHS }}"
                        min="1" max="{{ \App\Models\Team::ABSOLUTE_MAX_REPORT_MONTHS }}"
                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    <p class="mt-2 text-sm text-gray-500">
                        {{ __('Maximum allowed range for any report. Absolute limit is :max months.', ['max' => \App\Models\Team::ABSOLUTE_MAX_REPORT_MONTHS]) }}
                    </p>
                </div>

                <div>
                    <label for="async_report_threshold_months" class="block text-sm font-medium text-gray-700">{{ __('Async Report Threshold') }} ({{ __('Months') }})</label>
                    <input type="number" name="async_report_threshold_months" id="async_report_threshold_months" 
                        value="{{ Auth::user()->currentTeam->async_report_threshold_months ?? \App\Models\Team::DEFAULT_ASYNC_THRESHOLD_MONTHS }}"
                        min="1" max="{{ \App\Models\Team::ABSOLUTE_MAX_REPORT_MONTHS }}"
                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    <p class="mt-2 text-sm text-gray-500">
                        {{ __('Reports exceeding this period will be generated asynchronously. Leave empty to use default (6 months).') }}
                    </p>
                </div>
            </div>

            <div class="mt-4">
                <x-jet-button class="bg-indigo-600 hover:bg-indigo-700">
                    {{ __('Guardar Preferencia') }}
                </x-jet-button>
            </div>
        </form>
    </div>

    @if (Auth::user()->is_admin)
        <div class="mb-8 border-b border-gray-200 pb-8">
            <h3 class="text-lg font-medium text-gray-900">{{ __('Mail Server Configuration') }}</h3>
            <p class="mt-1 text-sm text-gray-600 mb-4">
                {{ __('Configure SMTP settings for email notifications. Only global administrators can access this section.') }}
            </p>

            <a href="{{ route('admin.mail-settings') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                {{ __('Configure Mail Server') }}
            </a>
        </div>
    @endif

    @if (Auth::user()->is_admin)
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
        <x-jet-button class="bg-indigo-600 hover:bg-indigo-700">
            {{ __("Instalar Puppeteer y Browsershot") }}
        </x-jet-button>
    </form>
    @endif
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

    // SweetAlert toast for flash messages
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        @if(session('error'))
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: '{{ session('error') }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        @endif
    });
</script>