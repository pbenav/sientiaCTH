<?php

namespace App\Http\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * A Livewire component for updating user geolocation preferences.
 *
 * This component provides a form for users to enable or disable GPS geolocation
 * tracking during clock-in and clock-out events.
 */
class UpdateGeolocationPreferencesForm extends Component
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
        $this->state['geolocation_enabled'] = $user->geolocation_enabled ?? false;
    }

    /**
     * Update the user's geolocation preferences.
     *
     * @return void
     */
    public function updateGeolocationPreferences(): void
    {
        $user = Auth::user();

        $user->update([
            'geolocation_enabled' => $this->state['geolocation_enabled']
        ]);

        $this->emit('saved');
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.profile.update-geolocation-preferences-form');
    }
}
