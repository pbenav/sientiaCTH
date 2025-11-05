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

    public function mount()
    {
        $this->refreshClockData();
    }

    /**
     * Get fresh instance of SmartClockInService
     */
    private function getSmartClockService(): SmartClockInService
    {
        return app(SmartClockInService::class);
    }

    public function refreshClockData()
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        try {
            $this->clockData = $this->getSmartClockService()->getClockAction($user);
            
            $this->canClock = $this->clockData['can_clock'];
            $this->clockAction = $this->clockData['action'] ?? '';
            $this->errorMessage = '';
            $this->statusMessage = $this->clockData['message'] ?? '';
            
        } catch (\Exception $e) {
            $this->errorMessage = __('Error loading clock data');
            $this->canClock = false;
            $this->clockData = [];
        }
    }

    public function getUserInfo()
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        // Get default work center
        $defaultWorkCenter = null;
        if ($user->currentTeam) {
            $defaultWorkCenterMeta = $user->meta->where('meta_key', 'default_work_center_id')->first();
            if ($defaultWorkCenterMeta) {
                $defaultWorkCenter = $user->currentTeam->workCenters()->find($defaultWorkCenterMeta->meta_value);
            }
        }

        return [
            'name' => $user->name,
            'family_name_1' => $user->family_name1,
            'family_name_2' => $user->family_name2,
            'full_name' => trim($user->name . ' ' . $user->family_name1 . ' ' . $user->family_name2),
            'team' => $user->currentTeam ? $user->currentTeam->name : __('No team'),
            'user_code' => $user->user_code,
            'work_center' => $defaultWorkCenter ? $defaultWorkCenter->name : null,
            'work_center_code' => $defaultWorkCenter ? $defaultWorkCenter->code : null,
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
        if (!$this->canClock || empty($this->clockData)) {
            $this->message = $this->clockData['message'] ?? __('Cannot clock in/out at this time');
            $this->messageType = 'error';
            return;
        }

        $user = Auth::user();
        
        if ($this->clockData['action'] === 'clock_in') {
            $overtime = $this->clockData['overtime'] ?? false;
            $eventTypeId = $this->clockData['event_type_id'] ?? null;
            
            if (!$eventTypeId) {
                $this->message = __('No event type configured');
                $this->messageType = 'error';
                return;
            }
            
            $result = $this->getSmartClockService()->clockIn($user, $eventTypeId, $overtime);
        } elseif ($this->clockData['action'] === 'clock_out') {
            $openEventId = $this->clockData['open_event_id'] ?? null;
            
            if (!$openEventId) {
                $this->message = __('No open event found');
                $this->messageType = 'error';
                return;
            }
            
            $result = $this->getSmartClockService()->clockOut($user, $openEventId);
        } elseif ($this->clockData['action'] === 'redirect_to_events') {
            // Redirect to events when outside grace period
            session()->flash('alertFail', $this->clockData['message']);
            return $this->redirect(route('events'));
        } elseif ($this->clockData['action'] === 'redirect_to_profile') {
            // Redirect to profile to configure schedule
            session()->flash('message', $this->clockData['message']);
            return $this->redirect($this->clockData['redirect_url']);
        } elseif ($this->clockData['action'] === 'confirm_exceptional_clock_in') {
            // Show confirmation dialog for exceptional clock-in
            $this->showConfirmation = true;
            return;
        } else {
            $this->message = __('Unknown action');
            $this->messageType = 'error';
            return;
        }

        $this->message = $result['message'];
        $this->messageType = $result['success'] ? 'success' : 'error';
        
        // Close confirmation dialog
        $this->showConfirmation = false;

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

    public function confirmExceptionalClockIn()
    {
        $user = Auth::user();
        $eventTypeId = $this->clockData['event_type_id'] ?? null;

        if (!$eventTypeId) {
            $this->message = 'No se pudo obtener el tipo de evento';
            $this->messageType = 'error';
            $this->showConfirmation = false;
            return;
        }

        $result = $this->getSmartClockService()->requestExceptionalClockIn($user, $eventTypeId);
        
        $this->showConfirmation = false;

        if ($result['success']) {
            // Redirect to exceptional clock-in process
            session()->flash('info', $result['message']);
            return $this->redirect($result['redirect_url']);
        } else {
            $this->message = $result['message'];
            $this->messageType = 'error';
        }
    }

    public function render()
    {
        $userInfo = $this->getUserInfo();
        
        return view('livewire.smart-clock-button', [
            'team' => $userInfo['team'] ?? null,
            'workCenter' => $userInfo['work_center'] ?? null,
        ]);
    }
}
