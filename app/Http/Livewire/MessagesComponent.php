<?php

namespace App\Http\Livewire;

use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

/**
 * A Livewire component for handling the internal messaging system.
 *
 * This component provides a full-featured messaging interface, including an
 * inbox, sent items, trash, and notifications.
 */
class MessagesComponent extends Component
{
    public string $view = 'inbox';
    public bool $showComposeForm = false;
    public array $recipients = [];
    public string $subject = '';
    public string $body = '';
    public $users;
    public array $selectedMessages = [];
    public string $bulkAction = '';
    public array $selectedNotifications = [];
    public string $bulkAlertAction = '';
    public bool $selectAll = false;

    protected $queryString = ['view', 'message'];

    public function mount()
    {
        // Set view based on query parameter or default to inbox
        if (!in_array($this->view, ['inbox', 'sent', 'trash', 'alerts'])) {
            $this->view = 'inbox';
        }

        $team = Auth::user()->currentTeam;
        if ($team) {
            $this->users = $team->allUsers()->where('id', '!=', Auth::id());
        } else {
            $this->users = collect();
        }
    }

    /**
     * Handle the update of the selectAll property.
     *
     * @param bool $value
     * @return void
     */
    public function updatedSelectAll(bool $value): void
    {
        if ($this->view === 'alerts') {
            if ($value) {
                $this->selectedNotifications = Auth::user()->notifications
                    ->filter(function ($notification) {
                        return $notification->type !== 'App\Notifications\NewMessage';
                    })
                    ->pluck('id')->toArray();
            } else {
                $this->selectedNotifications = [];
            }
        } else {
            if ($value) {
                if ($this->view === 'inbox') {
                    $this->selectedMessages = Auth::user()->receivedMessages()->whereNull('message_user.deleted_at')->pluck('messages.id')->toArray();
                } elseif ($this->view === 'sent') {
                    $this->selectedMessages = Auth::user()->messages()->whereNull('sender_deleted_at')->whereNull('sender_purged_at')->pluck('id')->toArray();
                }
            } else {
                $this->selectedMessages = [];
            }
        }
    }

    /**
     * Toggle the compose form.
     *
     * @return void
     */
    public function toggleComposeForm(): void
    {
        $this->showComposeForm = !$this->showComposeForm;
    }

    /**
     * Select all team members as recipients.
     *
     * @return void
     */
    public function selectAllTeam(): void
    {
        if ($this->users) {
            $this->recipients = $this->users->pluck('id')->toArray();
        }
    }

    /**
     * Compose a message to all team members.
     *
     * @return void
     */
    public function composeToAll(): void
    {
        $this->showComposeForm = true;
        $this->selectAllTeam();
    }

    /**
     * Send a new message.
     *
     * @return void
     */
    public function sendMessage(): void
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

    /**
     * Show the inbox view.
     *
     * @return void
     */
    public function showInbox(): void
    {
        $this->view = 'inbox';
    }

    /**
     * Show the sent items view.
     *
     * @return void
     */
    public function showSent(): void
    {
        $this->view = 'sent';
    }

    /**
     * Show the trash view.
     *
     * @return void
     */
    public function showTrash(): void
    {
        $this->view = 'trash';
    }

    /**
     * Delete a message.
     *
     * @param int $messageId
     * @return void
     */
    public function deleteMessage(int $messageId): void
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
        $this->emitTo('notification-icon', 'refreshCount');
    }

    /**
     * Restore a message from the trash.
     *
     * @param int $messageId
     * @return void
     */
    public function restoreMessage(int $messageId): void
    {
        $message = Message::find($messageId);

        if ($message->sender_id === Auth::id()) {
            $message->sender_deleted_at = null;
            $message->save();
        } else {
            Auth::user()->receivedMessages()->updateExistingPivot($messageId, ['deleted_at' => null]);
        }

        $this->showTrash();
        $this->emitTo('notification-icon', 'refreshCount');
    }

    /**
     * Permanently delete a message.
     *
     * @param int $messageId
     * @return void
     */
    public function forceDeleteMessage(int $messageId): void
    {
        $message = Message::find($messageId);

        if ($message->sender_id === Auth::id()) {
            $message->sender_purged_at = now();
            $message->save();
        } else {
            Auth::user()->receivedMessages()->detach($messageId);
        }

        $this->showTrash();
        $this->emitTo('notification-icon', 'refreshCount');
    }

    /**
     * Empty the trash.
     *
     * @return void
     */
    public function emptyTrash(): void
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
        $this->emitTo('notification-icon', 'refreshCount');
    }

    /**
     * Mark a message as read.
     *
     * @param int $messageId
     * @return void
     */
    public function markAsRead(int $messageId): void
    {
        Auth::user()->receivedMessages()->updateExistingPivot($messageId, ['read_at' => now()]);
        $this->emitTo('notification-icon', 'refreshCount');
        $this->showInbox();
    }

    /**
     * Reply to a message.
     *
     * @param int $messageId
     * @return void
     */
    public function replyTo(int $messageId): void
    {
        $message = Message::find($messageId);

        $this->markAsRead($messageId);

        $this->showComposeForm = true;
        $this->recipients = [$message->sender_id];
        $this->subject = 'Re: ' . $message->subject;
        $this->body = "\n\n\n> " . $message->body;
    }

    /**
     * Apply a bulk action to the selected messages.
     *
     * @return void
     */
    public function applyBulkAction(): void
    {
        if (empty($this->bulkAction)) {
            return;
        }

        if ($this->view === 'inbox') {
            if ($this->bulkAction === 'markAsRead') {
                Auth::user()->receivedMessages()->updateExistingPivot($this->selectedMessages, ['read_at' => now()]);
            } elseif ($this->bulkAction === 'delete') {
                Auth::user()->receivedMessages()->updateExistingPivot($this->selectedMessages, ['deleted_at' => now()]);
            }
            $this->showInbox();
        } elseif ($this->view === 'sent') {
            if ($this->bulkAction === 'delete') {
                Message::where('sender_id', Auth::id())
                    ->whereIn('id', $this->selectedMessages)
                    ->update(['sender_deleted_at' => now()]);
            }
            $this->showSent();
        }

        $this->bulkAction = '';
        $this->emitTo('notification-icon', 'refreshCount');
    }

    /**
     * Delete a notification.
     *
     * @param string $notificationId
     * @return void
     */
    public function deleteNotification(string $notificationId): void
    {
        Auth::user()->notifications()->find($notificationId)->delete();
        $this->emitTo('notification-icon', 'refreshCount');
    }

    /**
     * Apply a bulk action to the selected notifications.
     *
     * @return void
     */
    public function applyBulkAlertAction(): void
    {
        if ($this->bulkAlertAction === 'delete') {
            Auth::user()->notifications()->whereIn('id', $this->selectedNotifications)->delete();
            $this->bulkAlertAction = '';
        }
        $this->emitTo('notification-icon', 'refreshCount');
    }

    /**
     * Show the alerts view.
     *
     * @return void
     */
    public function showAlerts(): void
    {
        $this->view = 'alerts';
        Auth::user()->unreadNotifications->where('type', '!=', 'App\Notifications\NewMessage')->markAsRead();
        $this->emitTo('notification-icon', 'refreshCount');
    }

    public function render()
    {
        $messageList = collect();
        if ($this->view === 'inbox') {
            $messageList = Auth::user()->receivedMessages()->whereNull('message_user.deleted_at')->get();
        } elseif ($this->view === 'sent') {
            $messageList = Auth::user()->messages()->with('recipients')->whereNull('sender_deleted_at')->whereNull('sender_purged_at')->get();
        } elseif ($this->view === 'trash') {
            $received = Auth::user()->receivedMessages()->whereNotNull('message_user.deleted_at')->get();
            $sent = Auth::user()->messages()->with('recipients')->whereNotNull('sender_deleted_at')->whereNull('sender_purged_at')->get();
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
