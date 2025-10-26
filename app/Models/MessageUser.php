<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents the pivot table between messages and users.
 *
 * This model is used to associate messages with their recipients.
 */
class MessageUser extends Model
{
    use HasFactory;
}
