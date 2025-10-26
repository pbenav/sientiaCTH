<?php

namespace App\Http\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * A Livewire component for updating user notification preferences.
 *
 * This component provides a form for users to manage their notification
 * settings, such as enabling or disabling email and internal notifications.
 */
class UpdateNotificationPreferencesForm extends Component
{
    /**
     * The component's state.
     *
     * @var array
     */
    public array $state = [];

    /**
     * Mount the component.
     *
     * @return void
     */
    public function mount(): void
    {
        $user = Auth::user();
        $notifyByEmail = $user->meta->where('meta_key', 'notify_by_email')->first();
        $this->state['notify_by_email'] = $notifyByEmail ? (bool)$notifyByEmail->meta_value : false;

        $notifyByInternal = $user->meta->where('meta_key', 'notify_by_internal_message')->first();
        $this->state['notify_by_internal_message'] = $notifyByInternal ? (bool)$notifyByInternal->meta_value : true;
    }

    /**
     * Update the user's notification preferences.
     *
     * @return void
     */
    public function updateNotificationPreferences(): void
    {
        $user = Auth::user();

        $user->meta()->updateOrCreate(
            ['meta_key' => 'notify_by_email'],
            ['meta_value' => $this->state['notify_by_email'] ? '1' : '0']
        );

        $user->meta()->updateOrCreate(
            ['meta_key' => 'notify_by_internal_message'],
            ['meta_value' => $this->state['notify_by_internal_message'] ? '1' : '0']
        );

        $this->emit('saved');
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.profile.update-notification-preferences-form');
    }
}
