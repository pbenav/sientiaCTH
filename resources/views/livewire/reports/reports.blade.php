<div class="flex flex-col m-5 sm:m-10">
    
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Reports') }}
        </h2>

    </x-slot>

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

    {{-- Reports main div --}}
    <div class="w-full max-w-7xl mx-auto">
        <div class="w-auto flex flex-row flex-wrap gap-2 mb-4">

            <div>
                <x-jet-label value="{{ __('Report Source') }}" />
                <select class="form-control pt-1 h-8 whitespace-nowrap" wire:model='report_source'>
                    @foreach ($reportSources as $sourceKey => $sourceName)
                        <option value="{{ $sourceKey }}">
                            {{ $sourceName }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if ($isTeamAdmin or $isInspector)
                <div>
                    <x-jet-label value="{{ __('Worker') }}" />
                    <select class="form-control pt-1 h-8 whitespace-nowrap" wire:model='worker'>
                        <option value="%">{{ __('All') }}</option>
                        @foreach ($workers as $w)
                            <option value="{{ $w->id }}" {{ $w->id == $worker ? 'selected' : '' }}>
                                {{ $w->name . ' ' . $w->family_name1 }}</option>
                        @endforeach
                    </select>
                    <x-jet-input-error for='worker' />
                </div>
            @endif

            <div class="flex flex-nowrap">
                <div>
                    <x-jet-label value="{{ __('From date') }}" />
                    <x-datepicker class="form-control h-8 pt-2" wire:model='fromdate' />
                    <x-jet-input-error for='fromdate' />
                </div>

                <div class="ml-2">
                    <x-jet-label value="{{ __('To date') }}" />
                    <x-datepicker class="form-control h-8 pt-2" wire:model='todate' name="todate" id="todate" />
                    <x-jet-input-error class="whitespace-pre-wrap" for='todate' />
                </div>
            </div>

            <div>
                <x-jet-label value="{{ __('Event Type') }}" />
                <select class="form-control pt-1 h-8 whitespace-nowrap" wire:model='event_type_id'>
                    <option value="All">{{ __('All') }}</option>
                    @foreach ($eventTypes as $eventType)
                        <option value="{{ $eventType->id }}">
                            {{ $eventType->name }}
                        </option>
                    @endforeach
                </select>
                <x-jet-input-error for='event_type_id' />
            </div>

            <div>
                <x-jet-label value="{{ __('Group By') }}" />
                <select class="form-control pt-1 h-8 whitespace-nowrap" wire:model.live='groupBy'>
                    <option value="none">{{ __('None') }}</option>
                    <option value="date">{{ __('Date') }}</option>
                    <option value="user">{{ __('Worker') }}</option>
                </select>
                <x-jet-input-error for='groupBy' />
            </div>

            <div>
                <x-jet-label value="{{ __('Order By') }}" />
                <select class="form-control pt-1 h-8 whitespace-nowrap" wire:model.live='orderBy'>
                    <option value="start">{{ __('Date') }}</option>
                    <option value="user_name">{{ __('Worker') }}</option>
                </select>
                <x-jet-input-error for='orderBy' />
            </div>

            <div>
                <x-jet-label value="{{ __('Type') }}" />
                <select class="form-control pt-1 h-8 whitespace-nowrap" wire:model='rtype'>
                    @foreach ($rtypes as $rtype_key => $rtype_val)
                        <option value="{{ $rtype_key }}">
                            {{ $rtype_key }}</option>
                    @endforeach
                </select>
                <x-jet-input-error for='rtype' />
            </div>

            <div class="h-8 pt-1 flex gap-2 ml-auto">
                <x-jet-button class="h-8 mt-4 bg-indigo-500 hover:bg-indigo-600 justify-center"
                    wire:click='generatePreview'
                    wire:loading.attr="disabled"
                    onclick="showReportLoading()">
                    {{ __('Generate Report') }}
                </x-jet-button>

                @php
                    if ($rtype === 'PDF') {
                        $downloadUrl = route('reports.preview', [
                            'worker' => $worker,
                            'fromdate' => $fromdate,
                            'todate' => $todate,
                            'event_type_id' => $event_type_id,
                            'report_source' => $report_source,
                            'groupBy' => $groupBy,
                            'orderBy' => $orderBy,
                            'download' => 1,
                        ]);
                    } else {
                        $downloadUrl = route('reports.export', [
                            'worker' => $worker,
                            'fromdate' => $fromdate,
                            'todate' => $todate,
                            'event_type_id' => $event_type_id,
                            'report_source' => $report_source,
                            'rtype' => $rtype,
                        ]);
                    }
                @endphp
                <button 
                   onclick="handleDownload('{{ $downloadUrl }}')"
                   class="inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-700 focus:outline-none focus:border-green-700 focus:ring focus:ring-green-200 disabled:opacity-25 transition h-8 mt-4 justify-center">
                    {{ __('Download') }}
                </button>
            </div>
        </div>
    </div>

    {{-- PDF Preview --}}
    @if($pdfUrl)
        <div class="w-full min-h-screen mt-6 bg-gray-100 rounded-lg shadow-lg overflow-hidden">
            <iframe 
                src="{{ $pdfUrl }}" 
                class="w-full min-h-screen"
                frameborder="0"
                onload="hideReportLoading()"
            ></iframe>
        </div>
    @endif
    
    {{-- Hidden trigger for closing alert --}}
    <div wire:loading.class.remove="hidden" wire:target="generatePreview,export" class="hidden" id="loadingTrigger"></div>
</div>

@push('scripts')
<script>
    let reportLoadingAlert = null;
    let isGeneratingPreview = false;
    let loadingStartTime = null;

    // Handle download button click
    function handleDownload(url) {
        // Make GET request and check response
        fetch(url)
            .then(response => {
                // Check if response indicates async generation (202 status)
                if (response.status === 202) {
                    // Show SweetAlert in current tab
                    Swal.fire({
                        icon: 'info',
                        title: '{{ __("Please wait, the report may take a few minutes...") }}',
                        html: '{{ __("This report will be generated asynchronously and sent to your inbox.") }}<br><br>' +
                              '<small>{{ __("You will receive a notification in your inbox with a download link.") }}</small><br><br>' +
                              '<small style="color: #f59e0b;"><strong>{{ __("Chrome users") }}:</strong> {{ __("If you cannot download reports, make sure popup blocker is disabled.") }}</small>',
                        confirmButtonText: '{{ __("OK") }}',
                        confirmButtonColor: '#667eea',
                        allowOutsideClick: false,
                        allowEscapeKey: true
                    });
                    // Job already dispatched by the GET request above
                } else {
                    // Check if response is JSON (error) or Blob (file)
                    const contentType = response.headers.get('content-type');
                    
                    if (contentType && contentType.indexOf('application/json') !== -1) {
                        return response.json().then(data => {
                            throw new Error(data.message || data.error || 'Server error');
                        });
                    }

                    // Normal download - use anchor tag with download attribute
                    return response.blob().then(blob => {
                        // Check if blob is actually a JSON error (sometimes headers are lost)
                        if (blob.type === 'application/json') {
                             return blob.text().then(text => {
                                 const data = JSON.parse(text);
                                 throw new Error(data.message || data.error || 'Server error');
                             });
                        }

                        const blobUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = blobUrl;
                        a.download = 'cth_informe_' + new Date().getTime() + '.pdf';
                        document.body.appendChild(a);
                        
                        try {
                            a.click();
                            window.URL.revokeObjectURL(blobUrl);
                            
                            // Warning about popup blockers
                            if ('{{ $isChrome }}') {
                                setTimeout(() => {
                                     Swal.fire({
                                        icon: 'warning',
                                        title: '{{ __("Download didn\'t start?") }}',
                                        text: '{{ __("If the download failed, you may be using a popup blocker. Please generate the report first to view it.") }}',
                                        confirmButtonText: '{{ __("OK") }}'
                                     });
                                }, 2000);
                            }
                        } catch (e) {
                            console.error('Download click failed:', e);
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Download error:', error);
                
                let errorMsg = '{{ __("There was an error generating your report. Please try again or contact support.") }}';
                // Try to extract readable error message
                if (error.message && error.message !== 'Failed to fetch') {
                    errorMsg += '<br><br><small class="text-red-500">' + error.message + '</small>';
                }

                Swal.fire({
                    icon: 'error',
                    title: '{{ __("Error") }}',
                    html: errorMsg,
                    confirmButtonText: '{{ __("OK") }}'
                });
            })
            .finally(() => {
                 // Close loading
                 if(reportLoadingAlert) {
                      reportLoadingAlert.close();
                 }
            });
    }

    // Show message for Chrome popup blocker
    function showChromePopupBlockerMessage() {
        Swal.fire({
            icon: 'warning',
            title: '{{ __("Popup Blocker Detected") }}',
            html: '{{ __("Your browser is blocking the download.") }}<br><br>' +
                  '<strong>{{ __("Solution") }}:</strong><br>' +
                  '1. {{ __("Click on \'Generate Report\' first to preview") }}<br>' +
                  '2. {{ __("Or allow popups for this site in your browser settings") }}',
            confirmButtonText: '{{ __("OK") }}',
            confirmButtonColor: '#667eea'
        });
    }

    function showReportLoading() {
        if (!reportLoadingAlert) {
            isGeneratingPreview = true;
            loadingStartTime = Date.now();
            reportLoadingAlert = Swal.fire({
                title: '{{ __("Generating...") }}',
                html: '{{ __("Please wait while the report is being generated...") }}',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        
        // Set a timeout to close if it takes too long or something goes wrong
        setTimeout(() => {
            if (isGeneratingPreview) {
                hideReportLoading();
            }
        }, 60000); // 60 seconds max
    }

    function hideReportLoading() {
        // Ensure minimum display time of 2 seconds to avoid flashing
        const minDisplayTime = 2000; // 2 seconds
        const elapsedTime = loadingStartTime ? Date.now() - loadingStartTime : minDisplayTime;
        const remainingTime = Math.max(0, minDisplayTime - elapsedTime);
        
        setTimeout(() => {
            isGeneratingPreview = false;
            if (reportLoadingAlert) {
                Swal.close();
                reportLoadingAlert = null;
                loadingStartTime = null;
            }
        }, remainingTime);
    }

    // Listen for when Livewire finishes updating
    document.addEventListener('livewire:load', function () {
        // NO cerramos el mensaje automáticamente cuando Livewire termina
        // Solo lo cerramos cuando el iframe se carga (onload) o en caso de export directo
        Livewire.hook('message.processed', (message, component) => {
            // Si no estamos generando preview (es decir, es un export directo), cerramos después de un delay
            setTimeout(() => {
                // Solo cerrar si no hay preview activo (el iframe no se está cargando)
                if (!document.querySelector('iframe[src*="reports.preview"]')) {
                    hideReportLoading();
                }
            }, 1000); // Increased from 500ms to 1000ms
        });

        Livewire.on('download-report', function(data) {
            // First, check if this will be an async report by making a HEAD request
            fetch(data.url, { method: 'HEAD' })
                .then(response => {
                    // Check if response indicates async generation (202 status)
                    if (response.status === 202) {
                        // Show SweetAlert in current tab
                        Swal.fire({
                            icon: 'info',
                            title: '{{ __("Please wait, the report may take a few minutes...") }}',
                            html: '{{ __("This report will be generated asynchronously and sent to your inbox.") }}<br><br><small>{{ __("You will receive a notification in your inbox with a download link.") }}</small>',
                            confirmButtonText: '{{ __("OK") }}',
                            confirmButtonColor: '#667eea',
                            allowOutsideClick: false,
                            allowEscapeKey: true
                        });
                        
                        // Trigger the actual async job by making GET request
                        fetch(data.url).catch(err => console.log('Async job dispatched'));
                    } else {
                        // Normal download - use hidden iframe to avoid popup blocker
                        let iframe = document.getElementById('download-iframe');
                        if (!iframe) {
                            iframe = document.createElement('iframe');
                            iframe.id = 'download-iframe';
                            iframe.style.display = 'none';
                            document.body.appendChild(iframe);
                        }
                        iframe.src = data.url;
                    }
                })
                .catch(err => {
                    // Fallback: use hidden iframe
                    let iframe = document.getElementById('download-iframe');
                    if (!iframe) {
                        iframe = document.createElement('iframe');
                        iframe.id = 'download-iframe';
                        iframe.style.display = 'none';
                        document.body.appendChild(iframe);
                    }
                    iframe.src = data.url;
                });
        });

        Livewire.on('async-report-started', function(data) {
            hideReportLoading(); // Ensure loading spinner is closed
            Swal.fire({
                icon: 'info',
                title: data.title,
                text: data.text,
                timer: 20000, // 20 seconds timeout (increased from 10s)
                timerProgressBar: true,
                showConfirmButton: true,
                confirmButtonText: '{{ __("sweetalert.ok_button") }}',
                allowOutsideClick: true,
                allowEscapeKey: true
            });
        });
    });
</script>
@endpush
