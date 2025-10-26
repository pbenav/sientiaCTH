<?php

namespace App\Models;

use Laravel\Jetstream\Membership as JetstreamMembership;

/**
 * Represents the membership of a user in a team.
 *
 * This model extends the base Jetstream membership model to allow for
 * customization of the team membership functionality.
 */
class Membership extends JetstreamMembership
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
}
