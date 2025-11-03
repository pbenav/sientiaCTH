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
    public Team $team;
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $title = '';
    public string $content = '';
    public bool $is_active = true;
    public ?string $start_date = null;
    public ?string $end_date = null;

    protected $rules = [
        'title' => 'required|string|max:255',
        'content' => 'required|string',
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
    }

    /**
     * Open modal to edit an existing announcement.
     */
    public function edit(int $id)
    {
        $announcement = TeamAnnouncement::findOrFail($id);
        
        // Verificar autorización
        if (!Gate::allows('update', $announcement)) {
            abort(403, __('Unauthorized action'));
        }
        
        // Verificar que el anuncio pertenece al equipo actual (protección IDOR)
        if ($announcement->team_id !== $this->team->id) {
            abort(403, __('Unauthorized action'));
        }
        
        $this->editingId = $announcement->id;
        $this->title = $announcement->title;
        $this->content = $announcement->content;
        $this->is_active = $announcement->is_active;
        $this->start_date = $announcement->start_date?->format('Y-m-d');
        $this->end_date = $announcement->end_date?->format('Y-m-d');
        $this->showModal = true;
        
        // Emitir evento para que JavaScript recargue el editor
        $this->emit('loadAnnouncementContent');
    }

    /**
     * Save the announcement (create or update).
     */
    public function save()
    {
        $this->validate();

        // Sanitizar el contenido HTML para prevenir XSS
        $sanitizer = new HtmlSanitizerService();
        $sanitizedContent = $sanitizer->sanitize($this->content);

        if ($this->editingId) {
            $announcement = TeamAnnouncement::findOrFail($this->editingId);
            
            // Verificar autorización
            if (!Gate::allows('update', $announcement)) {
                abort(403, __('Unauthorized action'));
            }
            
            // Verificar que el anuncio pertenece al equipo actual
            if ($announcement->team_id !== $this->team->id) {
                abort(403, __('Unauthorized action'));
            }
            
            // Actualizar sin incluir team_id ni created_by (protección mass assignment)
            $announcement->update([
                'title' => $this->title,
                'content' => $sanitizedContent,
                'is_active' => $this->is_active,
                'start_date' => $this->start_date ?: null,
                'end_date' => $this->end_date ?: null,
            ]);
            
            session()->flash('message', __('Announcement updated successfully.'));
        } else {
            // Verificar autorización para crear
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
                'is_active' => $this->is_active,
                'start_date' => $this->start_date ?: null,
                'end_date' => $this->end_date ?: null,
            ])->save();
            
            session()->flash('message', __('Announcement created successfully.'));
        }

        $this->closeModal();
    }

    /**
     * Delete an announcement.
     */
    public function delete(int $id)
    {
        $announcement = TeamAnnouncement::findOrFail($id);
        
        // Verificar autorización
        if (!Gate::allows('delete', $announcement)) {
            abort(403, __('Unauthorized action'));
        }
        
        // Verificar que el anuncio pertenece al equipo actual
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
        
        // Verificar autorización
        if (!Gate::allows('update', $announcement)) {
            abort(403, __('Unauthorized action'));
        }
        
        // Verificar que el anuncio pertenece al equipo actual
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
        $this->emit('closeModal');
    }

    /**
     * Reset the form fields.
     */
    private function resetForm()
    {
        $this->editingId = null;
        $this->title = '';
        $this->content = '';
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
