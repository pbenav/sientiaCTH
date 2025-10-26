<?php

namespace App\Http\Livewire\Security;

use Livewire\Component;
use App\Models\FailedLoginAttempt;

/**
 * A Livewire component for managing blocked IP addresses.
 *
 * This component provides a list of currently blocked IP addresses and allows
 * administrators to unblock them.
 */
class BlockedIpManager extends Component
{
    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.security.blocked-ip-manager', [
            'blockedIps' => FailedLoginAttempt::where('lockout_time', '>', 0)
                ->where('created_at', '>', now()->subSeconds(config('security.login_delay.base') * config('security.login_delay.factor') * config('security.login_delay.max_attempts')))
                ->latest()
                ->paginate(10),
        ]);
    }

    /**
     * Unblock an IP address.
     *
     * @param int $id
     * @return void
     */
    public function unblock(int $id): void
    {
        $attempt = FailedLoginAttempt::findOrFail($id);
        $attempt->delete();
        session()->flash('message', __('sweetalert.unblocked_ip_message'));
    }
}
