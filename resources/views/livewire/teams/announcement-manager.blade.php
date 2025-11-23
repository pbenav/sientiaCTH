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
                                    <div class="flex-1">
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
                                        <div class="mt-2 prose prose-sm max-w-none text-gray-700">
                                            {!! $announcement->content !!}
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
                    <x-jet-input id="title" type="text" class="mt-1 block w-full" wire:model="title" />
                    <x-jet-input-error for="title" class="mt-2" />
                </div>

                <div>
                    <div class="flex justify-between items-center">
                        <x-jet-label for="content" value="{{ __('Content') }}" />
                        <button type="button" onclick="toggleHtmlEditor()" 
                                class="text-xs px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded">
                            <i class="fas fa-code mr-1"></i>
                            <span id="editor-mode-text">Modo HTML</span>
                        </button>
                    </div>
                    <div wire:ignore>
                        <div id="quill-editor" style="height: 300px; background: white;"></div>
                        <textarea id="html-editor" 
                                  style="display:none; height: 300px; font-family: monospace; background: #f5f5f5; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                                  class="w-full"></textarea>
                        <textarea id="announcement-content" style="display:none;">{{ $content }}</textarea>
                    </div>
                    <x-jet-input-error for="content" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-jet-label for="start_date" value="{{ __('Start Date') }} ({{ __('Optional') }})" />
                        <x-jet-input id="start_date" type="date" class="mt-1 block w-full" wire:model="start_date" />
                        <x-jet-input-error for="start_date" class="mt-2" />
                    </div>

                    <div>
                        <x-jet-label for="end_date" value="{{ __('End Date') }} ({{ __('Optional') }})" />
                        <x-jet-input id="end_date" type="date" class="mt-1 block w-full" wire:model="end_date" />
                        <x-jet-input-error for="end_date" class="mt-2" />
                    </div>
                </div>

                <div class="flex items-center">
                    <input id="is_active" 
                           type="checkbox" 
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                           wire:model="is_active">
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

            <x-jet-button class="ml-3" wire:click="save">
                {{ __('Save') }}
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>

@push('scripts')
<!-- Quill.js CSS -->
<link href="{{ asset('css/quill.snow.css') }}" rel="stylesheet">
<!-- Quill.js JavaScript -->
<script src="{{ asset('js/quill.js') }}"></script>

<script>
    let quill = null;
    let isHtmlMode = false;
    
    function toggleHtmlEditor() {
        const quillEditor = document.getElementById('quill-editor');
        const htmlEditor = document.getElementById('html-editor');
        const modeText = document.getElementById('editor-mode-text');
        
        if (!isHtmlMode) {
            // Cambiar a modo HTML
            const html = quill.root.innerHTML;
            htmlEditor.value = html;
            quillEditor.style.display = 'none';
            htmlEditor.style.display = 'block';
            modeText.textContent = 'Modo Visual';
            isHtmlMode = true;
            
            // Sincronizar cambios del textarea HTML con Livewire
            htmlEditor.addEventListener('input', function() {
                @this.set('content', htmlEditor.value);
            });
        } else {
            // Cambiar a modo visual
            const html = htmlEditor.value;
            
            // Actualizar Quill con el HTML editado usando clipboard API
            quill.clipboard.dangerouslyPasteHTML(0, html);
            
            // IMPORTANTE: Sincronizar con Livewire
            @this.set('content', html);
            
            htmlEditor.style.display = 'none';
            quillEditor.style.display = 'block';
            modeText.textContent = 'Modo HTML';
            isHtmlMode = false;
        }
    }
    
    document.addEventListener('livewire:load', function () {
        initQuill();
        
        Livewire.on('closeModal', () => {
            if (quill) {
                quill.setContents([]);
                @this.set('content', '');
                isHtmlMode = false;
                document.getElementById('quill-editor').style.display = 'block';
                document.getElementById('html-editor').style.display = 'none';
                document.getElementById('html-editor').value = '';
                document.getElementById('editor-mode-text').textContent = 'Modo HTML';
            }
        });
        
        // Escuchar evento cuando se carga un anuncio para editar
        Livewire.on('loadAnnouncementContent', () => {
            // Esperar un momento para que Livewire sincronice el contenido
            setTimeout(() => {
                if (!quill || !document.querySelector('.ql-editor')) {
                    initQuill();
                }
                
                // Leer el contenido directamente del textarea de Livewire
                const content = @this.get('content');
                
                // Cargar el contenido en ambos editores
                if (quill && content) {
                    // Usar clipboard API de Quill para insertar HTML complejo correctamente
                    quill.clipboard.dangerouslyPasteHTML(0, content);
                    document.getElementById('html-editor').value = content;
                    document.getElementById('announcement-content').value = content;
                }
                
                // Ensure it's in visual mode
                isHtmlMode = false;
                document.getElementById('quill-editor').style.display = 'block';
                document.getElementById('html-editor').style.display = 'none';
                document.getElementById('editor-mode-text').textContent = 'Modo HTML';
            }, 150);
        });
        
        Livewire.hook('message.processed', (message, component) => {
            // Only reinitialize if modal is visible and editor doesn't exist
            const modal = document.querySelector('[role="dialog"]');
            const editorContainer = document.querySelector('#quill-editor');
            
            if (modal && editorContainer) {
                if (!quill || !document.querySelector('.ql-editor')) {
                    initQuill();
                }
            }
        });
    });
    
    function initQuill() {
        const editorContainer = document.getElementById('quill-editor');
        if (!editorContainer) {
            return;
        }
        
        // Eliminar instancia anterior si existe
        if (quill) {
            const qlContainer = document.querySelector('.ql-container');
            if (qlContainer) {
                qlContainer.remove();
            }
            const qlToolbar = document.querySelector('.ql-toolbar');
            if (qlToolbar) {
                qlToolbar.remove();
            }
        }
        
        // Crear nueva instancia de Quill
        quill = new Quill('#quill-editor', {
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
        
        // Cargar contenido inicial del textarea usando clipboard API
        const textarea = document.getElementById('announcement-content');
        if (textarea && textarea.value) {
            quill.clipboard.dangerouslyPasteHTML(0, textarea.value);
        }
        
        // Sincronizar con Livewire en cada cambio
        quill.on('text-change', function() {
            const html = quill.root.innerHTML;
            @this.set('content', html);
        });
    }
</script>
@endpush
