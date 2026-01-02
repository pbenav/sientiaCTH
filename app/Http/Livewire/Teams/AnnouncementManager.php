<?php

namespace App\Http\Livewire\Teams;

use App\Models\Team;
use App\Models\TeamAnnouncement;
use App\Services\HtmlSanitizerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

/**
 * A Livewire component for managing team announcements.
 */
class AnnouncementManager extends Component
{
    public ?Team $team;
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $title = '';
    public string $content = '';
    public ?string $format = null;
    public bool $is_active = true;
    public ?string $start_date = null;
    public ?string $end_date = null;

    protected $rules = [
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'format' => 'required|in:markdown,html',
        'is_active' => 'boolean',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
    ];

    /**
     * Mount the component.
     */
    public function mount(Team $team)
    {
        // Verificar que el usuario pertenece al equipo (puede ver anuncios)
        if (!Gate::allows('viewAny', [TeamAnnouncement::class, $team])) {
            abort(403, __('You do not have permission to view team announcements.'));
        }
        
        $this->team = $team;
    }

    /**
     * Open modal to create a new announcement.
     */
    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
        // Clear the editor content on the frontend
        $this->dispatchBrowserEvent('load-announcement-content', ['content' => '', 'format' => null]);
    }

    public function setFormat($format)
    {
        if (in_array($format, ['markdown', 'html'])) {
            $this->format = $format;
        }
    }

    /**
     * Open modal to edit an existing announcement.
     */
    public function edit(int $id)
    {
        $announcement = TeamAnnouncement::findOrFail($id);
        
        // Verify authorization
        if (!Gate::allows('update', $announcement)) {
            abort(403, __('Unauthorized action'));
        }
        
        // Verify the announcement belongs to the current team (IDOR protection)
        if ($announcement->team_id !== $this->team->id) {
            abort(403, __('Unauthorized action'));
        }
        
        $this->editingId = $announcement->id;
        $this->title = $announcement->title;
        $this->content = $announcement->content;
        $this->format = $announcement->format ?? 'html'; // Default to html for existing records
        $this->is_active = $announcement->is_active;
        $this->start_date = $announcement->start_date?->format('Y-m-d');
        $this->end_date = $announcement->end_date?->format('Y-m-d');
        $this->showModal = true;
        
        // Emitir evento con el contenido directamente para que JavaScript lo cargue
        // Emitir evento de navegador para Alpine.js
        $this->dispatchBrowserEvent('load-announcement-content', [
            'content' => $announcement->content,
            'format' => $this->format
        ]);
    }

    /**
     * Save the announcement (create or update).
     */
    public function save()
    {
        // Log para debugging
        \Log::info('AnnouncementManager::save', [
            'title' => $this->title,
            'content_length' => strlen($this->content ?? ''),
            'format' => $this->format,
            'content_preview' => substr($this->content ?? '', 0, 100),
        ]);

        $this->validate();

        // Normalizar el contenido antes de sanitizar
        $content = $this->content;
        
        if ($this->format === 'html') {
            // Si es HTML, eliminar saltos de línea innecesarios entre etiquetas
            $content = preg_replace('/>\s+</', '><', $content);
            // Eliminar espacios en blanco al inicio y final
            $content = trim($content);
            
            // Sanitizar el contenido HTML para prevenir XSS
            $sanitizer = new HtmlSanitizerService();
            $sanitizedContent = $sanitizer->sanitize($content);
        } else {
            // Si es Markdown, solo normalizar espacios en blanco y trim
            $content = trim($content);
            // Normalizar múltiples saltos de línea consecutivos (máximo 2)
            $content = preg_replace('/\n{3,}/', "\n\n", $content);
            $sanitizedContent = $content;
        }

        if ($this->editingId) {
            $announcement = TeamAnnouncement::findOrFail($this->editingId);
            
            // Verify authorization
            if (!Gate::allows('update', $announcement)) {
                abort(403, __('Unauthorized action'));
            }
            
            // Verify the announcement belongs to the current team
            if ($announcement->team_id !== $this->team->id) {
                abort(403, __('Unauthorized action'));
            }
            
            // Update without including team_id or created_by (mass assignment protection)
            $announcement->update([
                'title' => $this->title,
                'content' => $sanitizedContent,
                'format' => $this->format,
                'is_active' => $this->is_active,
                'start_date' => $this->start_date ?: null,
                'end_date' => $this->end_date ?: null,
            ]);
            
            session()->flash('message', __('Announcement updated successfully.'));
        } else {
            // Verify authorization to create
            if (!Gate::allows('create', [TeamAnnouncement::class, $this->team])) {
                abort(403, __('Unauthorized action'));
            }
            
            // Crear nuevo anuncio con forceFill para campos guarded
            $announcement = new TeamAnnouncement();
            $announcement->forceFill([
                'team_id' => $this->team->id,
                'created_by' => Auth::id(),
            ])->fill([
                'title' => $this->title,
                'content' => $sanitizedContent,
                'format' => $this->format,
                'is_active' => $this->is_active,
                'start_date' => $this->start_date ?: null,
                'end_date' => $this->end_date ?: null,
            ])->save();
            
            session()->flash('message', __('Announcement created successfully.'));
        }

        $this->closeModal();
        $this->emit('saved');
    }

    /**
     * Delete an announcement.
     */
    public function delete(int $id)
    {
        $announcement = TeamAnnouncement::findOrFail($id);
        
        // Verify authorization
        if (!Gate::allows('delete', $announcement)) {
            abort(403, __('Unauthorized action'));
        }
        
        // Verify the announcement belongs to the current team
        if ($announcement->team_id !== $this->team->id) {
            abort(403, __('Unauthorized action'));
        }
        
        $announcement->delete();
        
        session()->flash('message', __('Announcement deleted successfully.'));
    }

    /**
     * Toggle the active status of an announcement.
     */
    public function toggleActive(int $id)
    {
        $announcement = TeamAnnouncement::findOrFail($id);
        
        // Verify authorization
        if (!Gate::allows('update', $announcement)) {
            abort(403, __('Unauthorized action'));
        }
        
        // Verify the announcement belongs to the current team
        if ($announcement->team_id !== $this->team->id) {
            abort(403, __('Unauthorized action'));
        }
        
        $announcement->update(['is_active' => !$announcement->is_active]);
    }

    /**
     * Close the modal and reset the form.
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        
        // Disparar evento de navegador para limpiar el editor Alpine
        $this->dispatchBrowserEvent('close-announcement-modal');
    }

    /**
     * Reset the form fields.
     */
    private function resetForm()
    {
        $this->editingId = null;
        $this->title = '';
        $this->content = '';
        $this->format = null;
        $this->is_active = true;
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->format('Y-m-d');
        $this->resetValidation();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $announcements = $this->team->announcements()
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.teams.announcement-manager', [
            'announcements' => $announcements
        ]);
    }
}
