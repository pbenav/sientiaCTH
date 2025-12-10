<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Handle SMTP connection errors for admins
        $this->renderable(function (\Swift_TransportException $e, $request) {
            if (auth()->check() && auth()->user()->is_admin) {
                $errorMessage = $e->getMessage();
                
                // Check if it's a connection error
                if (str_contains($errorMessage, 'Connection could not be established') ||
                    str_contains($errorMessage, 'stream_socket_client') ||
                    str_contains($errorMessage, 'Unable to connect')) {
                    
                    session()->flash('alertFail', __('Mail server connection error. Please check your SMTP configuration.'));
                    
                    return redirect()->route('admin.mail-settings')->with([
                        'smtp_error' => $errorMessage,
                        'message' => __('Cannot connect to mail server. Please verify your SMTP settings below.'),
                        'messageType' => 'error'
                    ]);
                }
            }
        });

        // Handle Symfony Mailer exceptions (Laravel 9+)
        $this->renderable(function (\Symfony\Component\Mailer\Exception\TransportException $e, $request) {
            if (auth()->check() && auth()->user()->is_admin) {
                $errorMessage = $e->getMessage();
                
                // Check if it's a connection error
                if (str_contains($errorMessage, 'Connection could not be established') ||
                    str_contains($errorMessage, 'stream_socket_client') ||
                    str_contains($errorMessage, 'Unable to connect')) {
                    
                    session()->flash('alertFail', __('Mail server connection error. Please check your SMTP configuration.'));
                    
                    return redirect()->route('admin.mail-settings')->with([
                        'smtp_error' => $errorMessage,
                        'message' => __('Cannot connect to mail server. Please verify your SMTP settings below.'),
                        'messageType' => 'error'
                    ]);
                }
            }
        });
    }
}
