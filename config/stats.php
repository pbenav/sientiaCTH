<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Confidence thresholds (porcentajes)
    |--------------------------------------------------------------------------
    | Define los umbrales en porcentaje para clasificar la confianza.
    | Ajusta estos valores sin tocar el código.
    |
    */
    'confidence_thresholds' => [
        'very_high' => 90, // >= 90% -> Muy alta
        'high'      => 75, // 75-89 -> Alta
        'moderate'  => 50, // 50-74 -> Moderada
        'low'       => 20, // 20-49 -> Baja
        // < 20 -> Very low (no necesita clave aquí)
    ],
];