<?php

namespace App\Http\Livewire\Events;

use App\Models\Event;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RequestEventReopening extends Component
{
    public $eventId;
    public $event;
    public $reason = '';
    public $showModal = false;

    protected $listeners = ['openReopeningModal' => 'openModal'];

    protected $rules = [
        'reason' => 'required|string|min:10|max:500',
    ];

    protected function messages()
    {
        return [
            'reason.required' => __('Please provide a reason for the reopening request.'),
            'reason.min' => __('The reason must be at least 10 characters.'),
            'reason.max' => __('The reason cannot exceed 500 characters.'),
        ];
    }

    public function mount($eventId = null)
    {
        if ($eventId) {
            $this->eventId = $eventId;
            $this->loadEvent();
        }
    }

    public function loadEvent()
    {
        $this->event = Event::with(['user', 'eventType'])->findOrFail($this->eventId);
    }

    public function openModal($eventId)
    {
        $this->eventId = $eventId;
        $this->loadEvent();
        
        // Check if user can request reopening
        if (!$this->canRequestReopening()) {
            session()->flash('error', __('You cannot request reopening for this event.'));
            return;
        }
        
        $this->reason = '';
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reason = '';
        $this->resetValidation();
    }

    public function canRequestReopening()
    {
        if (!$this->event) {
            return false;
        }

        // Event must be closed
        if ($this->event->is_open) {
            return false;
        }

        // User must own the event
        if ($this->event->user_id !== Auth::id()) {
            return false;
        }

        return true;
    }

    public function requestReopening()
    {
        $this->validate();

        if (!$this->canRequestReopening()) {
            session()->flash('error', __('You cannot request reopening for this event.'));
            $this->closeModal();
            return;
        }

        // Get all team administrators
        $team = Auth::user()->currentTeam;
        $administrators = $team->allUsers()->filter(function ($user) use ($team) {
            return $user->hasTeamRole($team, 'admin') || $user->ownsTeam($team);
        });
        
        // Create message for each administrator
        $subject = __('Reopening Request for Event #:id', ['id' => $this->event->id]);
        
        $messageBody = ucfirst(__('has requested to reopen the following event')) . ":\n\n";
        $messageBody .= "**" . __('Event') . " #" . $this->event->id . "**\n";
        $messageBody .= __('Date') . ": " . \Carbon\Carbon::parse($this->event->start)->format('d/m/Y H:i') . "\n";
        $messageBody .= __('Event Type') . ": " . ($this->event->eventType->name ?? __('Unknown')) . "\n";
        $messageBody .= __('User') . ": " . $this->event->user->name . "\n\n";
        $messageBody .= "**" . __('Reason provided') . ":**\n";
        $messageBody .= $this->reason . "\n\n";
        $messageBody .= "[" . __('View Event') . "](" . route('events', ['event_id' => $this->event->id]) . ")";

        // Create message
        $message = Message::create([
            'sender_id' => Auth::id(),
            'subject' => $subject,
            'body' => $messageBody,
        ]);

        // Attach all administrators as recipients (excluding sender)
        $adminIds = $administrators
            ->filter(fn($admin) => $admin->id !== Auth::id())
            ->pluck('id')
            ->toArray();
        
        if (count($adminIds) > 0) {
            $message->recipients()->attach($adminIds);
        }

        session()->flash('message', __('Reopening request sent successfully'));
        $this->closeModal();
        $this->emit('reopeningRequestSent');
    }

    public function render()
    {
        return view('livewire.events.request-event-reopening');
    }
}
