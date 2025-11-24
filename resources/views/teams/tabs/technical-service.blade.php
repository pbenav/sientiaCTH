<div>
    <h3 class="text-lg font-medium text-gray-900">{{ __('Servicio técnico') }}</h3>
    <p class="mt-1 text-sm text-gray-600">
        {{ __('Desde aquí puedes instalar Puppeteer y Browsershot para generar PDFs.') }}
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

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

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