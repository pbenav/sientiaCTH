<div>
    <x-jet-action-section>
        <x-slot name="title">
            {{ __('Team Announcements') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Manage announcements that will be displayed to all team members in the events page.') }}
        </x-slot>

        <x-slot name="content">
            @if (session('message'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('message') }}
                </div>
            @endif

            <div class="space-y-4">
                @can('create', [App\Models\TeamAnnouncement::class, $team])
                    <div class="flex justify-end">
                        <x-jet-button wire:click="create">
                            {{ __('Create Announcement') }}
                        </x-jet-button>
                    </div>
                @endcan

                @if ($announcements->isEmpty())
                    <p class="text-gray-500">{{ __('No announcements yet.') }}</p>
                @else
                    <div class="space-y-4">
                        @foreach ($announcements as $announcement)
                            <div class="border rounded-lg p-4 {{ $announcement->isCurrentlyValid() ? 'bg-white' : 'bg-gray-50' }}">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ $announcement->title }}
                                            @if (!$announcement->is_active)
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ __('Inactive') }}
                                                </span>
                                            @elseif (!$announcement->isCurrentlyValid())
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    {{ __('Out of date range') }}
                                                </span>
                                            @else
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                    {{ __('Active') }}
                                                </span>
                                            @endif
                                        </h3>
                                        <div class="mt-2 prose prose-sm max-w-none text-gray-700 break-words">
                                            @if($announcement->format === 'markdown')
                                                {!! Illuminate\Support\Str::markdown($announcement->content) !!}
                                            @else
                                                {!! $announcement->content !!}
                                            @endif
                                        </div>
                                        <div class="mt-2 text-sm text-gray-500">
                                            @if ($announcement->start_date || $announcement->end_date)
                                                <div>
                                                    <i class="far fa-calendar"></i>
                                                    @if ($announcement->start_date)
                                                        {{ __('From') }}: {{ $announcement->start_date->format('d/m/Y') }}
                                                    @endif
                                                    @if ($announcement->end_date)
                                                        {{ __('To') }}: {{ $announcement->end_date->format('d/m/Y') }}
                                                    @endif
                                                </div>
                                            @endif
                                            <div class="mt-1">
                                                {{ __('Created by') }}: {{ $announcement->creator->name }} {{ $announcement->creator->family_name1 }}
                                                <span class="mx-1">•</span>
                                                {{ $announcement->created_at->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                    @can('update', $announcement)
                                        <div class="ml-4 flex space-x-2">
                                            <button wire:click="toggleActive({{ $announcement->id }})" 
                                                    class="text-gray-400 hover:text-gray-600"
                                                    title="{{ $announcement->is_active ? __('Deactivate') : __('Activate') }}">
                                                <i class="fas fa-power-off"></i>
                                            </button>
                                            <button wire:click="edit({{ $announcement->id }})" 
                                                    class="text-gray-600 hover:text-gray-800"
                                                    title="{{ __('Edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button wire:click="delete({{ $announcement->id }})" 
                                                    onclick="return confirm('{{ __('Are you sure you want to delete this announcement?') }}')"
                                                    class="text-red-400 hover:text-red-600"
                                                    title="{{ __('Delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-slot>
    </x-jet-action-section>

    <!-- Modal -->
    <x-jet-dialog-modal wire:model="showModal">
        <x-slot name="title">
            {{ $editingId ? __('Edit Announcement') : __('Create Announcement') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-jet-label for="title" value="{{ __('Title') }}" />
                    <x-jet-input id="title" type="text" class="mt-1 block w-full" wire:model.defer="title" />
                    <x-jet-input-error for="title" class="mt-2" />
                </div>

                <div x-data="quillEditor()"
                     x-on:load-announcement-content.window="loadContent($event.detail)"
                     x-on:close-announcement-modal.window="resetEditor()"
                     x-on:sync-content-before-save.window="$wire.set('content', content)"
                     x-init="$watch('$wire.showModal', value => { if (!value) resetEditor(); })"
                     class="w-full">
                    
                    <!-- Format Selection -->
                    <div x-show="!format" class="grid grid-cols-2 gap-4 mb-4">
                        <button type="button" 
                                @click="setFormat('markdown')"
                                class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-300 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition cursor-pointer group">
                            <i class="fab fa-markdown text-4xl text-gray-400 group-hover:text-indigo-600 mb-2"></i>
                            <span class="text-sm font-medium text-gray-900">Markdown</span>
                            <span class="text-xs text-gray-500 text-center mt-1">{{ __('Simple text formatting') }}</span>
                        </button>
                        
                        <button type="button" 
                                @click="setFormat('html')"
                                class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-300 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition cursor-pointer group">
                            <i class="fas fa-code text-4xl text-gray-400 group-hover:text-indigo-600 mb-2"></i>
                            <span class="text-sm font-medium text-gray-900">HTML / Rich Text</span>
                            <span class="text-xs text-gray-500 text-center mt-1">{{ __('Visual editor with rich features') }}</span>
                        </button>
                    </div>
                    
                    <!-- Errores de formato -->
                    <div x-show="!format">
                        <x-jet-input-error for="format" class="mt-2" />
                    </div>

                    <!-- Editor Container -->
                    <div x-show="format">
                        <div class="flex justify-between items-center mb-2">
                            <x-jet-label for="content" value="{{ __('Content') }}" />
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium px-2 py-1 bg-gray-100 rounded text-gray-600 uppercase" x-text="format"></span>
                                <!-- Toggle View Mode Button -->
                                <button type="button" 
                                        @click="toggleViewMode()"
                                        class="flex items-center gap-1 px-2 py-1 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition"
                                        title="{{ __('Toggle view mode') }}">
                                    <i class="fas fa-eye" x-show="viewMode === 'code'"></i>
                                    <i class="fas fa-code" x-show="viewMode === 'visual'"></i>
                                    <span x-text="viewMode === 'code' ? '{{ __('Preview') }}' : '{{ __('Code') }}'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- HTML Format -->
                        <div x-show="format === 'html'">
                            <!-- Quill Editor (Visual Mode) -->
                            <div x-show="viewMode === 'visual'" wire:ignore>
                                <div x-ref="quillEditor" class="bg-white border border-gray-300 rounded-md" style="min-height: 300px;"></div>
                            </div>
                            
                            <!-- HTML Code Editor (Code Mode) -->
                            <div x-show="viewMode === 'code'">
                                <textarea x-ref="htmlCodeEditor"
                                          @input="content = $event.target.value; $wire.set('content', $event.target.value)"
                                          style="height: 300px; font-family: monospace; font-size: 12px;"
                                          class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                          placeholder="{{ __('HTML code...') }}"></textarea>
                            </div>
                        </div>                        <!-- Markdown Format -->
                        <div x-show="format === 'markdown'">
                            <!-- Markdown Code Editor (Code Mode) -->
                            <div x-show="viewMode === 'code'">
                                <textarea x-ref="markdownCodeEditor"
                                          @input="content = $event.target.value; $wire.set('content', $event.target.value)"
                                          style="height: 300px; font-family: monospace;"
                                          class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                          placeholder="{{ __('Write your announcement in Markdown...') }}"></textarea>
                                <div class="mt-1 text-xs text-gray-500 text-right">
                                    <a href="https://www.markdownguide.org/basic-syntax/" target="_blank" class="text-indigo-600 hover:text-indigo-800">{{ __('Markdown Guide') }}</a>
                                </div>
                            </div>
                            
                            <!-- Markdown Preview (Visual Mode) -->
                            <div x-show="viewMode === 'visual'">
                                <div x-ref="markdownPreview"
                                     class="w-full border-gray-300 rounded-md shadow-sm p-4 bg-white prose prose-sm max-w-none"
                                     style="min-height: 300px; max-height: 400px; overflow-y: auto;"
                                     x-html="renderMarkdown(content)"></div>
                                <div class="mt-1 text-xs text-gray-500 text-right">
                                    {{ __('Preview mode - switch to code to edit') }}
                                </div>
                            </div>
                        </div>
                        
                        <x-jet-input-error for="content" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-jet-label for="start_date" value="{{ __('Start Date') }} ({{ __('Optional') }})" />
                        <x-jet-input id="start_date" type="date" class="mt-1 block w-full" wire:model.defer="start_date" />
                        <x-jet-input-error for="start_date" class="mt-2" />
                    </div>

                    <div>
                        <x-jet-label for="end_date" value="{{ __('End Date') }} ({{ __('Optional') }})" />
                        <x-jet-input id="end_date" type="date" class="mt-1 block w-full" wire:model.defer="end_date" />
                        <x-jet-input-error for="end_date" class="mt-2" />
                    </div>
                </div>

                <div class="flex items-center">
                    <input id="is_active" 
                           type="checkbox" 
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                           wire:model.defer="is_active">
                    <label for="is_active" class="ml-2 text-sm text-gray-600">
                        {{ __('Active') }}
                    </label>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="closeModal">
                {{ __('Cancel') }}
            </x-jet-secondary-button>

            <x-jet-button class="ml-3 bg-indigo-600 hover:bg-indigo-700" 
                          @click="window.dispatchEvent(new CustomEvent('sync-content-before-save')); setTimeout(() => $wire.call('save'), 100)">
                {{ __('Save') }}
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>

@push('styles')
<!-- Quill.js CSS -->
<link href="{{ asset('css/quill.snow.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<!-- Quill.js JavaScript -->
<script src="{{ asset('js/quill.js') }}"></script>
<!-- Marked.js for Markdown parsing -->
<script src="https://cdn.jsdelivr.net/npm/marked@4.3.0/marked.min.js"></script>
<!-- Turndown for HTML to Markdown conversion -->
<script src="https://unpkg.com/turndown/dist/turndown.js"></script>
<!-- Turndown GFM Plugin -->
<script src="https://unpkg.com/turndown-plugin-gfm/dist/turndown-plugin-gfm.js"></script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('quillEditor', () => ({
            content: '',
            format: null,
            quill: null,
            viewMode: 'visual',

            init() {
                // Inicialización vacía - esperamos eventos
            },

            setFormat(format) {
                this.format = format;
                this.content = '';
                
                this.$wire.set('format', format);
                this.$wire.set('content', '');
                
                if (format === 'html') {
                    this.viewMode = 'visual';
                    // Esperar dos ticks: uno para que x-show actualice, otro para el DOM
                    this.$nextTick(() => {
                        setTimeout(() => {
                            this.initQuill();
                        }, 100);
                    });
                } else if (format === 'markdown') {
                    this.viewMode = 'code'; // Markdown siempre inicia en modo código
                }
            },
            
            toggleViewMode() {
                if (this.format === 'markdown') {
                    // Toggle para Markdown: código ↔ preview
                    this.viewMode = this.viewMode === 'code' ? 'visual' : 'code';
                    return;
                }
                
                if (this.format !== 'html') return;
                
                if (this.viewMode === 'visual') {
                    // Cambiar de Visual a Código
                    // Obtener HTML actual de Quill
                    if (this.quill) {
                        this.content = this.quill.root.innerHTML;
                        if (this.content === '<p><br></p>') this.content = '';
                    }
                    
                    this.viewMode = 'code';
                    
                    // Actualizar textarea con el contenido
                    this.$nextTick(() => {
                        if (this.$refs.htmlCodeEditor) {
                            this.$refs.htmlCodeEditor.value = this.content;
                        }
                    });
                } else {
                    // Cambiar de Código a Visual
                    // El contenido ya está en this.content gracias al @input del textarea
                    this.viewMode = 'visual';
                    
                    // Cargar en Quill
                    this.$nextTick(() => {
                        if (this.quill && this.content) {
                            this.quill.root.innerHTML = this.content;
                        }
                    });
                }
            },
            
            renderMarkdown(markdown) {
                if (!markdown) return '<p class="text-gray-400">{{ __("No content yet...") }}</p>';
                
                try {
                    marked.setOptions({
                        breaks: true,
                        gfm: true,
                        headerIds: false,
                        mangle: false
                    });
                    return marked.parse(markdown);
                } catch (e) {
                    console.error('Markdown render error:', e);
                    return '<p class="text-red-500">Error rendering markdown</p>';
                }
            },

            initQuill() {
                // Destruir instancia previa si existe
                this.destroyQuill();

                this.$nextTick(() => {
                    if (!this.$refs.quillEditor) {
                        console.error('Quill editor ref not found');
                        return;
                    }

                    // Verificar que el elemento sea visible
                    const isVisible = this.$refs.quillEditor.offsetParent !== null;
                    if (!isVisible) {
                        console.error('Quill editor is not visible');
                        return;
                    }

                    try {
                        console.log('Initializing Quill editor...');
                        
                        // Crear nueva instancia de Quill
                        this.quill = new Quill(this.$refs.quillEditor, {
                            theme: 'snow',
                            modules: {
                                toolbar: [
                                    [{ 'header': [1, 2, 3, false] }],
                                    ['bold', 'italic', 'underline', 'strike'],
                                    [{ 'color': [] }, { 'background': [] }],
                                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                    [{ 'align': [] }],
                                    ['link', 'image'],
                                    ['clean']
                                ]
                            },
                            placeholder: 'Escribe el contenido del anuncio...'
                        });

                        console.log('Quill initialized successfully');

                        // Cargar contenido inicial si existe
                        if (this.content) {
                            this.quill.root.innerHTML = this.content;
                        }

                        // Sincronizar cambios solo con Alpine (no con Livewire en cada tecla)
                        this.quill.on('text-change', () => {
                            let html = this.quill.root.innerHTML;
                            if (html === '<p><br></p>') html = '';
                            this.content = html;
                            // No sincronizar con Livewire aquí para evitar re-renders
                        });
                    } catch (error) {
                        console.error('Error initializing Quill:', error);
                    }
                });
            },
            
            destroyQuill() {
                if (this.quill) {
                    // Guardar referencia al contenedor antes de destruir
                    const container = this.$refs.quillEditor;
                    
                    // Destruir la instancia de Quill
                    this.quill = null;
                    
                    // Limpiar completamente el contenedor
                    if (container) {
                        // Quill crea elementos hermanos (toolbar), buscarlos y eliminarlos
                        const parent = container.parentElement;
                        if (parent) {
                            // Eliminar cualquier toolbar de Quill que pueda existir
                            const toolbars = parent.querySelectorAll('.ql-toolbar');
                            toolbars.forEach(toolbar => toolbar.remove());
                        }
                        
                        // Limpiar el contenedor del editor
                        container.innerHTML = '';
                        // Eliminar clases de Quill
                        container.className = 'bg-white border border-gray-300 rounded-md';
                    }
                }
            },

            loadContent(data) {
                let newContent = (typeof data === 'object' && data !== null && 'content' in data) ? data.content : data;
                let newFormat = (typeof data === 'object' && data !== null && 'format' in data) ? data.format : null;
                
                if (!newContent) newContent = '';
                
                this.content = newContent;
                this.format = newFormat;

                this.$wire.set('content', newContent);
                this.$wire.set('format', newFormat);

                if (newFormat === 'html') {
                    this.viewMode = 'visual';
                    // Esperar a que el DOM esté listo y visible
                    this.$nextTick(() => {
                        setTimeout(() => {
                            this.initQuill();
                            if (this.quill && newContent) {
                                this.quill.root.innerHTML = newContent;
                            }
                        }, 100);
                    });
                } else if (newFormat === 'markdown') {
                    this.viewMode = 'code'; // Markdown siempre carga en modo código
                    this.$nextTick(() => {
                        if (this.$refs.markdownCodeEditor) {
                            this.$refs.markdownCodeEditor.value = newContent;
                        }
                    });
                }
            },
            
            resetEditor() {
                this.destroyQuill();
                
                this.content = '';
                this.format = null;
                this.viewMode = 'visual';
                
                if (this.$refs.htmlCodeEditor) {
                    this.$refs.htmlCodeEditor.value = '';
                }
                if (this.$refs.markdownCodeEditor) {
                    this.$refs.markdownCodeEditor.value = '';
                }
            }
        }))
    });

    // SweetAlert toast notification
    document.addEventListener('livewire:load', function () {
        Livewire.on('saved', function () {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '{{ __("Cambios guardados correctamente") }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        });
    });
</script>
@endpush
