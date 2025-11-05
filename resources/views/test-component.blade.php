<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test SmartClockButton Component</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .debug-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            font-family: monospace;
            white-space: pre-wrap;
        }
    </style>
    @livewireStyles
</head>
<body>
    <div class="container">
        <h1>Test SmartClockButton Component</h1>
        
        <div class="debug-info">
Usuario: {{ auth()->user()->name }}
Equipo: {{ auth()->user()->currentTeam ? auth()->user()->currentTeam->name : 'Sin equipo' }}
Timestamp: {{ now()->format('Y-m-d H:i:s') }}
        </div>
        
        <h2>Componente SmartClockButton:</h2>
        
        @livewire('smart-clock-button')
        
        <div style="margin-top: 30px;">
            <a href="/inicio" style="color: #007bff; text-decoration: none;">← Volver a Inicio</a>
        </div>
    </div>
    
    @livewireScripts
</body>
</html>