<?php
$user = App\Models\User::where('email', 'pbenav@gmail.com')->first();
if ($user) {
    echo "Email: " . $user->email . PHP_EOL;
    echo "Is Admin (Column): " . ($user->is_admin ? 'Yes' : 'No') . PHP_EOL;
    echo "Has Admin Role: " . ($user->hasRole('admin') ? 'Yes' : 'No') . PHP_EOL;
} else {
    echo "User not found" . PHP_EOL;
}
