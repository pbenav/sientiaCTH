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
        'moderate'  => 50, // 50-74 -> Moderate
        'low'       => 20, // 20-49 -> Low
        // < 20 -> Very low (no key needed here)
    ],

    // Reference (in minutes) used to normalize punctuality deviations to percentages.
    // For example, 60 minutes means that an average deviation of 60min -> 0% punctuality.
    'punctuality_reference_minutes' => 60,
];