<?php

namespace App\Http\Livewire;

use App\Services\SmartClockInService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SmartClockButton extends Component
{
    public $clockData = [];
    public $showConfirmation = false;
    public $message = '';
    public $messageType = 'info'; // success, error, info
    public $canClock = false;
    public $clockAction = '';
    public $currentEvent = null;
    public $errorMessage = '';
    public $statusMessage = '';

    protected $smartClockService;

    public function mount()
    {
        $this->smartClockService = app(SmartClockInService::class);
        $this->refreshClockData();
    }

    public function refreshClockData()
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        try {
            $clockData = $this->smartClockService->getClockAction($user);
            
            $this->canClock = $clockData['can_clock'];
            $this->clockAction = $clockData['action'];
            $this->currentEvent = $clockData['current_event'] ?? null;
            $this->errorMessage = $clockData['error'] ?? null;
            $this->statusMessage = $clockData['message'] ?? '';
            
        } catch (\Exception $e) {
            $this->errorMessage = __('Error loading clock data');
            $this->canClock = false;
        }
    }

    public function getUserInfo()
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        return [
            'name' => $user->name,
            'family_name_1' => $user->family_name_1,
            'family_name_2' => $user->family_name_2,
            'full_name' => trim($user->name . ' ' . $user->family_name_1 . ' ' . $user->family_name_2),
            'team' => $user->currentTeam ? $user->currentTeam->name : __('No team'),
            'user_code' => $user->user_code,
        ];
    }

    public function getCurrentDateTime()
    {
        $user = Auth::user();
        $teamTimezone = $user && $user->currentTeam 
            ? ($user->currentTeam->timezone ?? config('app.timezone'))
            : config('app.timezone');
            
        return now($teamTimezone)->locale('es');
    }

    public function handleClockAction()
    {
        if (!$this->clockData['can_clock']) {
            $this->message = $this->clockData['message'] ?? __('Cannot clock in/out at this time');
            $this->messageType = 'error';
            return;
        }

        $user = Auth::user();
        
        if ($this->clockData['action'] === 'clock_in') {
            $overtime = $this->clockData['overtime'] ?? false;
            $result = $this->smartClockService->clockIn($user, $this->clockData['event_type_id'], $overtime);
        } elseif ($this->clockData['action'] === 'clock_out') {
            $result = $this->smartClockService->clockOut($user, $this->clockData['open_event_id']);
        } else {
            $this->message = __('Unknown action');
            $this->messageType = 'error';
            return;
        }

        $this->message = $result['message'];
        $this->messageType = $result['success'] ? 'success' : 'error';

        if ($result['success']) {
            // Refresh the clock data to update the button state
            $this->refreshClockData();
            
            // Emit event to refresh other components if needed
            $this->emit('eventCreated');
        }
    }

    public function confirmAction()
    {
        $this->showConfirmation = true;
    }

    public function cancelAction()
    {
        $this->showConfirmation = false;
    }

    public function render()
    {
        return view('livewire.smart-clock-button');
    }
}
