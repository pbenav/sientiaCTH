<?php

namespace App\Http\Livewire\Teams;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use DateTimeZone;

/**
 * A Livewire component for managing the timezone for a team.
 *
 * This component provides a form for team administrators to select a timezone
 * for their team.
 */
class TimezoneManager extends Component
{
    public $team;
    public array $state = [];
    public array $timezones;

    /**
     * Mount the component.
     *
     * @param mixed $team
     * @return void
     */
    public function mount($team): void
    {
        $this->team = $team;
        $this->state['timezone'] = $this->team->timezone;
        $this->timezones = $this->getTimezoneList();
    }

    /**
     * Update the timezone.
     *
     * @return void
     */
    public function updateTimezone(): void
    {
        $this->resetErrorBag();
        Gate::forUser(auth()->user())->authorize('update', $this->team);

        $this->team->forceFill([
            'timezone' => $this->state['timezone'],
        ])->save();

        $this->emit('saved');
    }

    /**
     * Get a list of timezones with 'Europe/Madrid' as the first option.
     *
     * @return array
     */
    protected function getTimezoneList(): array
    {
        $timezones = [];
        $identifiers = DateTimeZone::listIdentifiers();
        $madridTimezone = 'Europe/Madrid';

        foreach ($identifiers as $identifier) {
            $dateTime = new \DateTime('now', new DateTimeZone($identifier));
            $offset = $dateTime->getOffset() / 3600;
            $offsetFormatted = 'UTC' . ($offset >= 0 ? '+' : '') . $offset;
            
            $timezones[$identifier] = "($offsetFormatted) $identifier";
        }

        if (isset($timezones[$madridTimezone])) {
            $madridEntry = $timezones[$madridTimezone];
            unset($timezones[$madridTimezone]);
        } else {
            $madridEntry = null; 
        }
        
        $sortedTimezones = [];
        if ($madridEntry) {
            $sortedTimezones[$madridTimezone] = $madridEntry;
        }
        $sortedTimezones = array_merge($sortedTimezones, $timezones);

        return $sortedTimezones;
    }
    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.teams.timezone-manager');
    }
}
