<?php

namespace App\Http\Livewire;

use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class MessagesComponent extends Component
{
    public $view = 'inbox';
    public $showComposeForm = false;
    public $recipients = [];
    public $subject = '';
    public $body = '';
    public $users;
    public $selectedMessages = [];
    public $bulkAction = '';
    public $selectedNotifications = [];
    public $bulkAlertAction = '';

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
    }

    public function showSent()
    {
        $this->view = 'sent';
    }

    public function showTrash()
    {
        $this->view = 'trash';
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

    public function deleteNotification($notificationId)
    {
        Auth::user()->notifications()->find($notificationId)->delete();
        $this->emit('NotificationCountChanged');
    }

    public function applyBulkAlertAction()
    {
        if ($this->bulkAlertAction === 'delete') {
            Auth::user()->notifications()->whereIn('id', $this->selectedNotifications)->delete();
            $this->selectedNotifications = [];
            $this->bulkAlertAction = '';
        }
        $this->emit('NotificationCountChanged');
    }

    public function showAlerts()
    {
        $this->view = 'alerts';
        Auth::user()->unreadNotifications->where('type', '!=', 'App\Notifications\NewMessage')->markAsRead();
        $this->emit('NotificationCountChanged');
    }

    public function render()
    {
        $messageList = collect();
        if ($this->view === 'inbox') {
            $messageList = Auth::user()->receivedMessages()->whereNull('message_user.deleted_at')->get();
        } elseif ($this->view === 'sent') {
            $messageList = Auth::user()->messages()->whereNull('sender_deleted_at')->whereNull('sender_purged_at')->get();
        } elseif ($this->view === 'trash') {
            $received = Auth::user()->receivedMessages()->whereNotNull('message_user.deleted_at')->get();
            $sent = Auth::user()->messages()->whereNotNull('sender_deleted_at')->whereNull('sender_purged_at')->get();
            $messageList = $received->merge($sent);
        } elseif ($this->view === 'alerts') {
            $messageList = Auth::user()->notifications->filter(function ($notification) {
                return $notification->type !== 'App\Notifications\NewMessage';
            });
        }

        return view('livewire.messages-component', [
            'messageList' => $messageList
        ]);
    }
}
