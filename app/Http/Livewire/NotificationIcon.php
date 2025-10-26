<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * A Livewire component for displaying a notification icon with an unread count.
 *
 * This component is responsible for calculating and displaying the number of
 * unread notifications for the current user.
 */
class NotificationIcon extends Component
{
    /**
     * The number of unread notifications.
     *
     * @var int
     */
    public int $unreadCount;

    /**
     * The event listeners for the component.
     *
     * @var array
     */
    protected $listeners = ['refreshCount'];

    /**
     * Initialize the component.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->refreshCount();
    }

    /**
     * Refresh the unread notification count.
     *
     * @return void
     */
    public function refreshCount(): void
    {
        if (Auth::check()) {
            $unreadMessages = Auth::user()->receivedMessages()->whereNull('message_user.read_at')->whereNull('message_user.deleted_at')->count();
            $unreadEventNotifications = Auth::user()->unreadNotifications
                ->where('type', '!=', 'App\Notifications\NewMessage')
                ->count();
            $this->unreadCount = $unreadMessages + $unreadEventNotifications;
        } else {
            $this->unreadCount = 0;
        }
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.notification-icon');
    }
}
