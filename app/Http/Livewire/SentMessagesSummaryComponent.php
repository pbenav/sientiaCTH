<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class SentMessagesSummaryComponent extends Component
{
    public function render()
    {
        // Get last 5 sent messages
        $messages = Message::where('sender_id', Auth::id())
            ->whereNull('sender_deleted_at')
            ->whereNull('sender_purged_at')
            ->with(['recipients'])
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.sent-messages-summary-component', [
            'messages' => $messages
        ]);
    }
}
