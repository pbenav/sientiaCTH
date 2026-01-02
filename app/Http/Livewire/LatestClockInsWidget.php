<?php

namespace App\Http\Livewire;

use Livewire\Component;

class LatestClockInsWidget extends Component
{
    public function render()
    {
        $events = auth()->user()->events()
            ->select([
                'events.*',  // Select all event fields including JSON columns
            ])
            ->with('eventType')
            ->where('is_open', false)
            ->latest('start')
            ->take(5)
            ->get();

        return view('livewire.latest-clock-ins-widget', [
            'events' => $events
        ]);
    }
}
