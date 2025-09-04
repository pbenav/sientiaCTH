<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\EventType;
use App\Models\Team;

class ManageEventTypes extends Component
{
    public Team $team;
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $newEventType = ['name' => '', 'color' => '#ffffff'];
    public $editingEventType;
    public $deletingEventType;

    protected function rules()
    {
        return [
            'newEventType.name' => 'required|string|max:255',
            'newEventType.color' => 'required|string|max:7',
            'editingEventType.name' => 'required|string|max:255',
            'editingEventType.color' => 'required|string|max:7',
        ];
    }

    public function mount(Team $team)
    {
        $this->team = $team;
    }

    public function render()
    {
        return view('livewire.manage-event-types', [
            'eventTypes' => $this->team->eventTypes()->get(),
        ]);
    }

    public function create()
    {
        $this->resetErrorBag();
        $this->newEventType = ['name' => '', 'color' => '#ffffff'];
        $this->showCreateModal = true;
    }

    public function store()
    {
        $this->validate([
            'newEventType.name' => 'required|string|max:255',
            'newEventType.color' => 'required|string|max:7',
        ]);

        $this->team->eventTypes()->create($this->newEventType);

        $this->showCreateModal = false;
    }

    public function edit(EventType $eventType)
    {
        $this->resetErrorBag();
        $this->editingEventType = $eventType;
        $this->showEditModal = true;
    }

    public function update()
    {
        $this->validate([
            'editingEventType.name' => 'required|string|max:255',
            'editingEventType.color' => 'required|string|max:7',
        ]);

        $this->editingEventType->save();

        $this->showEditModal = false;
    }

    public function confirmDelete(EventType $eventType)
    {
        $this->deletingEventType = $eventType;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $this->deletingEventType->delete();
        $this->showDeleteModal = false;
    }
}
