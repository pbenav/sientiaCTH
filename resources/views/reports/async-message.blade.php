<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 500px;
            text-align: center;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h1 {
            color: #333;
            font-size: 20px;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        <h1>{{ __('Processing...') }}</h1>
    </div>
    
    <script>
        // Try to communicate with parent window
        function showAlertInParent() {
            try {
                // Check if we have access to window.opener (opened via window.open)
                if (window.opener && !window.opener.closed) {
                    // Show SweetAlert in parent window
                    window.opener.Swal.fire({
                        icon: 'info',
                        title: '{{ $title }}',
                        html: '{{ $message }}<br><br><small>{{ __("You will receive a notification in your inbox with a download link.") }}</small>',
                        confirmButtonText: '{{ __("OK") }}',
                        confirmButtonColor: '#667eea',
                        allowOutsideClick: false,
                        allowEscapeKey: true
                    });
                    
                    // Close this window
                    window.close();
                    return true;
                }
            } catch (e) {
                console.log('Cannot access parent window:', e);
            }
            return false;
        }

        // Try parent communication first
        if (!showAlertInParent()) {
            // Fallback: Show SweetAlert in current window
            Swal.fire({
                icon: 'info',
                title: '{{ $title }}',
                html: '{{ $message }}<br><br><small>{{ __("You will receive a notification in your inbox with a download link.") }}</small>',
                confirmButtonText: '{{ __("OK") }}',
                confirmButtonColor: '#667eea',
                allowOutsideClick: false,
                allowEscapeKey: true
            }).then((result) => {
                // Try to close window, or go back
                window.close();
                
                // If window.close() doesn't work, go back
                setTimeout(function() {
                    if (document.referrer) {
                        window.location.href = document.referrer;
                    } else {
                        window.location.href = '{{ url("/dashboard") }}';
                    }
                }, 100);
            });
        }
    </script>
</body>
</html>
