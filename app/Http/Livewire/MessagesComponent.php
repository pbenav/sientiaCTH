<?php

namespace App\Http\Livewire;

use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MessagesComponent extends Component
{
    public $view = 'inbox';
    public $messageList;
    public $showComposeForm = false;
    public $recipients = [];
    public $subject = '';
    public $body = '';
    public $users;

    public function mount()
    {
        $this->showInbox();
        $this->users = \App\Models\User::where('id', '!=', Auth::id())->get();
    }

    public function toggleComposeForm()
    {
        $this->showComposeForm = !$this->showComposeForm;
    }

    public function sendMessage()
    {
        $this->validate([
            'recipients' => 'required|array|min:1',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'subject' => $this->subject,
            'body' => $this->body,
        ]);

        $message->recipients()->attach($this->recipients);

        $this->emit('NewMessage');

        $this->recipients = [];
        $this->subject = '';
        $this->body = '';

        $this->showComposeForm = false;
        $this->showSent();
    }

    public function showInbox()
    {
        $this->view = 'inbox';
        $this->messageList = Auth::user()->receivedMessages()->whereNull('message_user.deleted_at')->get();
    }

    public function showSent()
    {
        $this->view = 'sent';
        $this->messageList = Auth::user()->messages()->get();
    }

    public function showTrash()
    {
        $this->view = 'trash';
        $this->messageList = Auth::user()->receivedMessages()->whereNotNull('message_user.deleted_at')->get();
    }

    public function deleteMessage($messageId)
    {
        Auth::user()->receivedMessages()->updateExistingPivot($messageId, ['deleted_at' => now()]);
        $this->showInbox();
    }

    public function restoreMessage($messageId)
    {
        Auth::user()->receivedMessages()->updateExistingPivot($messageId, ['deleted_at' => null]);
        $this->showTrash();
    }

    public function render()
    {
        return view('livewire.messages-component');
    }
}
