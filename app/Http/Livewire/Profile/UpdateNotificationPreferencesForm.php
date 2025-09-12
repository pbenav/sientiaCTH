<?php

namespace App\Http\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UpdateNotificationPreferencesForm extends Component
{
    public $notifyNewMessages;

    public function mount()
    {
        $this->notifyNewMessages = Auth::user()->notify_new_messages;
    }

    public function update()
    {
        $user = Auth::user();
        $user->notify_new_messages = $this->notifyNewMessages;
        $user->save();

        $this->emit('saved');
    }

    public function render()
    {
        return view('livewire.profile.update-notification-preferences-form');
    }
}
