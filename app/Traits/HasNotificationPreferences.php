<?php

namespace App\Traits;

trait HasNotificationPreferences
{
    /**
     * Check if the user wants to receive email notifications.
     *
     * @return bool
     */
    public function wantsEmailNotifications(): bool
    {
        $preference = $this->meta->where('meta_key', 'notify_by_email')->first();

        return $preference && $preference->meta_value == '1';
    }

    /**
     * Check if the user wants to receive internal notifications.
     *
     * @return bool
     */
    public function wantsInternalNotifications(): bool
    {
        $preference = $this->meta->where('meta_key', 'notify_by_internal_message')->first();

        // Default to true if not set
        return $preference ? (bool)$preference->meta_value : true;
    }
}
