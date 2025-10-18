<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationIcon extends Component
{
    public $unreadCount;

    protected $listeners = ['refreshCount'];

    public function mount()
    {
        $this->refreshCount();
    }

    public function refreshCount()
    {
        if (Auth::check()) {
            $unreadMessages = Auth::user()->receivedMessages()->whereNull('message_user.read_at')->count();
            $unreadEventNotifications = Auth::user()->unreadNotifications->count();
            $this->unreadCount = $unreadMessages + $unreadEventNotifications;
        } else {
            $this->unreadCount = 0;
        }
    }

    public function render()
    {
        return view('livewire.notification-icon');
    }
}
