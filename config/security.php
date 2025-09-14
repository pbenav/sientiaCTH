<?php

return [
    'login_delay' => [
        'base' => 5, // The base delay in seconds
        'factor' => 2, // The exponential factor
        'max_attempts' => 5, // The number of attempts before showing in the manager
        'max_attempts_before_hard_lock' => 3, // After this many attempts, a hard lock is applied
        'hard_lock_duration_in_hours' => 24, // The duration of the hard lock
    ],
];
