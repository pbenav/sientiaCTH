<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationIcon extends Component
{
    public $unreadCount;

    protected $listeners = ['NewMessage' => 'refreshCount'];

    public function mount()
    {
        $this->refreshCount();
    }

    public function refreshCount()
    {
        $this->unreadCount = Auth::user()->receivedMessages()->whereNull('read_at')->count();
    }

    public function render()
    {
        return view('livewire.notification-icon');
    }
}
