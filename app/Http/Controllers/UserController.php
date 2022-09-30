<?php
namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Event;
use Exception;
use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;

class UserController extends Controller
{  
    public Event $ev;

    public function index(Request $request)
    {
        $this->ev = new Event();
        $this->ev->start = '2022-03-15 09:00:00';
        $this->ev->end = '2022-03-15 14:00:00';
        /* $ip = $request->ip(); Dynamic IP address */
        $ip = $request->ip(); /* Static IP address */
        $ip = '2.139.248.211'; /* Static IP address */

        try {
            $currentUserInfo = Location::get($ip);
        } catch (Exception $e) {
            echo 'Excepción capturada: ',  $e->getMessage(), "\n";
        }
        $currentDate = Carbon::now();
        $period = $this->ev->get_period();

        return view('user', compact('currentUserInfo','currentDate', 'period'));
    }
}
