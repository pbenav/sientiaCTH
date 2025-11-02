<?php
 
namespace App\Http\Middleware;
 
use Closure;
use Illuminate\Session\Store;
use Auth;
use Session;
 
class SessionExpired {
    protected $session;
    protected $timeout;
     
    public function __construct(Store $session){
        $this->session = $session;
        // SESSION_LIFETIME is in minutes, convert to seconds
        $this->timeout = env('SESSION_LIFETIME', 120) * 60;
    }
    
    public function handle($request, Closure $next){
        // Only check session timeout for authenticated users
        if (!Auth::check()) {
            return $next($request);
        }
        
        $currentTime = time();
        $lastActivity = session('lastActivityTime');
        
        // If this is the first activity, set the time
        if (!$lastActivity) {
            $this->session->put('lastActivityTime', $currentTime);
        } 
        // Check if session has expired
        elseif ($currentTime - $lastActivity > $this->timeout) {
            $this->session->forget('lastActivityTime');
            $this->session->flush();
            Auth::guard('web')->logout();
            
            return redirect()->route('login')->with('message', 'Your session has expired. Please login again.');
        }
        
        // Update last activity time for authenticated users
        $this->session->put('lastActivityTime', $currentTime);
        
        return $next($request);
    }
}
