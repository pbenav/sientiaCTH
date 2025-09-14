<?php

namespace App\Actions\Fortify;

use Illuminate\Http\Request;
use App\Services\LoginSecurityService;

class EnsureLoginIsNotThrottledWithExponentialBackoff
{
    /**
     * The login security service instance.
     *
     * @var \App\Services\LoginSecurityService
     */
    protected $loginSecurityService;

    /**
     * Create a new action instance.
     *
     * @param  \App\Services\LoginSecurityService  $loginSecurityService
     * @return void
     */
    public function __construct(LoginSecurityService $loginSecurityService)
    {
        $this->loginSecurityService = $loginSecurityService;
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  callable  $next
     * @return mixed
     */
    public function __invoke(Request $request, $next)
    {
        $this->loginSecurityService->check($request);

        return $next($request);
    }
}
