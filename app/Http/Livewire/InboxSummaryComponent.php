<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class InboxSummaryComponent extends Component
{
    public function render()
    {
        // Get last 5 unread messages for current user
        $messages = Message::whereHas('recipients', function ($query) {
                $query->where('user_id', Auth::id())
                      ->whereNull('read_at');
            })
            ->with(['sender'])
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.inbox-summary-component', [
            'messages' => $messages
        ]);
    }
}
