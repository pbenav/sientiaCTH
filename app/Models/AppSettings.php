<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSettings extends Model
{
    /**
     * Get a setting value by key.
     * Currently returns the default value as there is no app_settings table yet.
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        // For now, we return the default value or check config if available
        // This is a placeholder to avoid the "Class not found" error
        return config('app.' . $key, $default);
    }
}
