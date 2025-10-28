<?php

require '/var/www/cth/vendor/autoload.php';
$app = require_once '/var/www/cth/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Event;

$events = Event::with('eventType')->get();

foreach ($events as $event) {
    if ($event->eventType) {
        $event->update(['team_id' => $event->eventType->team_id]);
    }
}
