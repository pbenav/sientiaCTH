<?php

namespace App\Http\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UpdateCalendarPreferencesForm extends Component
{
    public $state = [];

    protected $rules = [
        'state.week_starts_on' => 'required|integer|in:0,1',
    ];

    public function mount()
    {
        $this->state = [
            'week_starts_on' => Auth::user()->week_starts_on ?? 1,
        ];
    }

    public function updateCalendarPreferences()
    {
        $this->validate();

        Auth::user()->forceFill([
            'week_starts_on' => $this->state['week_starts_on'],
        ])->save();

        $this->emit('saved');

        session()->flash('status', 'calendar-preferences-updated');
    }

    public function render()
    {
        return view('livewire.profile.update-calendar-preferences-form');
    }
}
