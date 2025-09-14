<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;
use App\Services\LoginSecurityService;

class LogFailedLoginAttempt
{
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    public $request;

    /**
     * The login security service instance.
     *
     * @var \App\Services\LoginSecurityService
     */
    protected $loginSecurityService;

    /**
     * Create the event listener.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\LoginSecurityService  $loginSecurityService
     * @return void
     */
    public function __construct(Request $request, LoginSecurityService $loginSecurityService)
    {
        $this->request = $request;
        $this->loginSecurityService = $loginSecurityService;
    }

    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Failed  $event
     * @return void
     */
    public function handle(Failed $event)
    {
        $this->loginSecurityService->logFailedAttempt($this->request);
    }
}
