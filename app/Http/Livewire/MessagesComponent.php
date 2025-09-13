<?php

namespace App\Http\Livewire;

use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
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
    public $selectedMessages = [];
    public $bulkAction = '';

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
        $this->messageList = Auth::user()->messages()->whereNull('sender_deleted_at')->whereNull('sender_purged_at')->get();
    }

    public function showTrash()
    {
        $this->view = 'trash';
        $received = Auth::user()->receivedMessages()->whereNotNull('message_user.deleted_at')->get();
        $sent = Auth::user()->messages()->whereNotNull('sender_deleted_at')->whereNull('sender_purged_at')->get();
        $this->messageList = $received->merge($sent);
    }

    public function deleteMessage($messageId)
    {
        if ($this->view === 'sent') {
            $message = Auth::user()->messages()->find($messageId);
            if ($message) {
                $message->sender_deleted_at = now();
                $message->save();
            }
            $this->showSent();
        } else {
            Auth::user()->receivedMessages()->updateExistingPivot($messageId, ['deleted_at' => now()]);
            $this->showInbox();
        }
        $this->emit('NotificationCountChanged');
    }

    public function restoreMessage($messageId)
    {
        $message = Message::find($messageId);

        if ($message->sender_id === Auth::id()) {
            $message->sender_deleted_at = null;
            $message->save();
        } else {
            Auth::user()->receivedMessages()->updateExistingPivot($messageId, ['deleted_at' => null]);
        }

        $this->showTrash();
        $this->emit('NotificationCountChanged');
    }

    public function forceDeleteMessage($messageId)
    {
        $message = Message::find($messageId);

        if ($message->sender_id === Auth::id()) {
            $message->sender_purged_at = now();
            $message->save();
        } else {
            Auth::user()->receivedMessages()->detach($messageId);
        }

        $this->showTrash();
        $this->emit('NotificationCountChanged');
    }

    public function emptyTrash()
    {
        // Permanently delete received messages
        $receivedTrashItems = Auth::user()->receivedMessages()->whereNotNull('message_user.deleted_at')->get();
        Auth::user()->receivedMessages()->detach($receivedTrashItems->pluck('id'));

        // Mark sent messages as purged
        $sentTrashItems = Auth::user()->messages()->whereNotNull('sender_deleted_at')->whereNull('sender_purged_at')->get();
        foreach ($sentTrashItems as $item) {
            $item->sender_purged_at = now();
            $item->save();
        }

        $this->showTrash();
        $this->emit('NotificationCountChanged');
    }

    public function markAsRead($messageId)
    {
        Auth::user()->receivedMessages()->updateExistingPivot($messageId, ['read_at' => now()]);
        $this->emit('NotificationCountChanged');
        $this->showInbox();
    }

    public function replyTo($messageId)
    {
        $message = Message::find($messageId);

        $this->markAsRead($messageId);

        $this->showComposeForm = true;
        $this->recipients = [$message->sender_id];
        $this->subject = 'Re: ' . $message->subject;
        $this->body = "\n\n\n> " . $message->body;
    }

    public function applyBulkAction()
    {
        if (empty($this->bulkAction)) {
            return;
        }

        if ($this->bulkAction === 'markAsRead') {
            Auth::user()->receivedMessages()->updateExistingPivot($this->selectedMessages, ['read_at' => now()]);
        } elseif ($this->bulkAction === 'delete') {
            Auth::user()->receivedMessages()->updateExistingPivot($this->selectedMessages, ['deleted_at' => now()]);
        }

        $this->selectedMessages = [];
        $this->bulkAction = '';
        $this->emit('NotificationCountChanged');
        $this->showInbox();
    }

    public function showAlerts()
    {
        $this->view = 'alerts';
        $this->messageList = Auth::user()->notifications->filter(function ($notification) {
            return $notification->type !== 'App\Notifications\NewMessage';
        });

        Auth::user()->unreadNotifications->where('type', '!=', 'App\Notifications\NewMessage')->markAsRead();
        $this->emit('NotificationCountChanged');
    }

    public function render()
    {
        return view('livewire.messages-component');
    }
}
