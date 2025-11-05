<?php

namespace App\Http\Livewire;

use App\Models\Event;
use Livewire\Component;

/**
 * A Livewire component for displaying event information in a modal.
 */
class EventInfoModal extends Component
{
    /**
     * Whether to show the modal.
     *
     * @var bool
     */
    public $showModal = false;

    /**
     * The event data to display.
     *
     * @var array|null
     */
    public $eventData = null;

    /**
     * The event listeners for the component.
     *
     * @var array
     */
    protected $listeners = [
        'showEventInfoModal' => 'showEvent',
    ];

    /**
     * Show the event information modal.
     *
     * @param array $eventData
     * @return void
     */
    public function showEvent(array $eventData): void
    {
        $this->eventData = $eventData;
        $this->showModal = true;
    }

    /**
     * Close the modal.
     *
     * @return void
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->eventData = null;
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.events.event-info-modal-simple');
    }
}