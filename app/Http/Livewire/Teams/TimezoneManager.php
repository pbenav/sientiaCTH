<?php

namespace App\Http\Livewire\Teams;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use DateTimeZone;

class TimezoneManager extends Component
{
    public $team;
    public $state = [];
    public $timezones;

    public function mount($team)
    {
        $this->team = $team;
        $this->state['timezone'] = $this->team->timezone;
        $this->timezones = $this->getTimezoneList();
    }

    public function updateTimezone()
    {
        $this->resetErrorBag();
        Gate::forUser(auth()->user())->authorize('update', $this->team);

        $this->team->forceFill([
            'timezone' => $this->state['timezone'],
        ])->save();

        $this->emit('saved');
    }

    protected function getTimezoneList()
    {
        $timezones = [];
        $identifiers = DateTimeZone::listIdentifiers();
        foreach ($identifiers as $identifier) {
            $dateTime = new \DateTime('now', new DateTimeZone($identifier));
            $offset = $dateTime->getOffset() / 3600;
            $offsetFormatted = 'UTC' . ($offset >= 0 ? '+' : '') . $offset;
            $timezones[$identifier] = "($offsetFormatted) $identifier";
        }
        return $timezones;
    }

    public function render()
    {
        return view('livewire.teams.timezone-manager');
    }
}
