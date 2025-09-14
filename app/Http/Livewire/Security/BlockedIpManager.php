<?php

namespace App\Http\Livewire\Security;

use Livewire\Component;
use App\Models\FailedLoginAttempt;

class BlockedIpManager extends Component
{
    public function render()
    {
        return view('livewire.security.blocked-ip-manager', [
            'blockedIps' => FailedLoginAttempt::where('lockout_time', '>', 0)
                ->where('created_at', '>', now()->subSeconds(config('security.login_delay.base') * config('security.login_delay.factor') * config('security.login_delay.max_attempts')))
                ->latest()
                ->paginate(10),
        ]);
    }

    public function unblock($id)
    {
        $attempt = FailedLoginAttempt::findOrFail($id);
        $attempt->delete();
        session()->flash('message', __('sweetalert.unblocked_ip_message'));
    }
}
