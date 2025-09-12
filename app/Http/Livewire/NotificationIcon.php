<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationIcon extends Component
{
    public $unreadCount;

    protected $listeners = ['NotificationCountChanged' => 'refreshCount'];

    public function mount()
    {
        $this->refreshCount();
    }

    public function refreshCount()
    {
        $unreadMessages = Auth::user()->receivedMessages()->whereNull('message_user.read_at')->count();
        $unreadEventNotifications = Auth::user()->unreadNotifications->count();
        $newCount = $unreadMessages + $unreadEventNotifications;

        if ($newCount > $this->unreadCount) {
            $this->dispatchBrowserEvent('new-notification');
        }

        $this->unreadCount = $newCount;
    }

    public function render()
    {
        return view('livewire.notification-icon');
    }
}
