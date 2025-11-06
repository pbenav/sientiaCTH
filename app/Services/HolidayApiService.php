<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HolidayApiService
{
    /**
     * Fetch holidays from Spanish public API
     * 
     * @param int $year
     * @param string|null $municipality
     * @return array
     */
    public function fetchHolidays(int $year, ?string $municipality = null): array
    {
        try {
            // Spanish public holidays API - you can use different APIs here
            // For now, let's use a mock implementation that returns common Spanish holidays
            return $this->getSpanishHolidays($year, $municipality);
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch holidays from API', [
                'year' => $year,
                'municipality' => $municipality,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Get Spanish holidays for a given year
     * This is a basic implementation - you can replace with actual API calls
     */
    private function getSpanishHolidays(int $year, ?string $municipality = null): array
    {
        $holidays = [];
        
        // National holidays (these are fixed for Spain)
        $nationalHolidays = [
            ['name' => 'Año Nuevo', 'date' => "$year-01-01", 'type' => 'Nacional'],
            ['name' => 'Día de Reyes', 'date' => "$year-01-06", 'type' => 'Nacional'],
            ['name' => 'Día del Trabajador', 'date' => "$year-05-01", 'type' => 'Nacional'],
            ['name' => 'Asunción de la Virgen', 'date' => "$year-08-15", 'type' => 'Nacional'],
            ['name' => 'Día de la Hispanidad', 'date' => "$year-10-12", 'type' => 'Nacional'],
            ['name' => 'Día de Todos los Santos', 'date' => "$year-11-01", 'type' => 'Nacional'],
            ['name' => 'Día de la Constitución', 'date' => "$year-12-06", 'type' => 'Nacional'],
            ['name' => 'Inmaculada Concepción', 'date' => "$year-12-08", 'type' => 'Nacional'],
            ['name' => 'Navidad', 'date' => "$year-12-25", 'type' => 'Nacional'],
        ];

        $holidays = array_merge($holidays, $nationalHolidays);

        // Calculate Easter-based holidays (Semana Santa)
        $easter = $this->calculateEaster($year);
        $easterHolidays = [
            ['name' => 'Viernes Santo', 'date' => $easter->copy()->subDays(2)->format('Y-m-d'), 'type' => 'Nacional'],
            ['name' => 'Lunes de Pascua', 'date' => $easter->copy()->addDay()->format('Y-m-d'), 'type' => 'Regional'],
        ];

        $holidays = array_merge($holidays, $easterHolidays);

        // Add some regional/local holidays based on municipality
        if ($municipality) {
            $localHolidays = $this->getLocalHolidays($year, $municipality);
            $holidays = array_merge($holidays, $localHolidays);
        }

        return $holidays;
    }

    /**
     * Calculate Easter date for a given year
     */
    private function calculateEaster(int $year): Carbon
    {
        $easter = easter_date($year);
        return Carbon::createFromTimestamp($easter);
    }

    /**
     * Get local holidays based on municipality
     * This is a basic implementation - you can expand this
     */
    private function getLocalHolidays(int $year, string $municipality): array
    {
        $municipality = strtolower(trim($municipality));
        $localHolidays = [];

        // Add some common local holidays based on municipality
        if (str_contains($municipality, 'madrid')) {
            $localHolidays[] = ['name' => 'San Isidro Labrador', 'date' => "$year-05-15", 'type' => 'Local'];
            $localHolidays[] = ['name' => 'Virgen de la Almudena', 'date' => "$year-11-09", 'type' => 'Local'];
        } elseif (str_contains($municipality, 'barcelona')) {
            $localHolidays[] = ['name' => 'Sant Jordi', 'date' => "$year-04-23", 'type' => 'Local'];
            $localHolidays[] = ['name' => 'La Mercè', 'date' => "$year-09-24", 'type' => 'Local'];
        } elseif (str_contains($municipality, 'valencia')) {
            $localHolidays[] = ['name' => 'San José', 'date' => "$year-03-19", 'type' => 'Local'];
            $localHolidays[] = ['name' => 'Día de la Comunidad Valenciana', 'date' => "$year-10-09", 'type' => 'Regional'];
        }

        return $localHolidays;
    }

    /**
     * Try to fetch holidays from external API (placeholder for future implementation)
     */
    private function fetchFromExternalApi(int $year, ?string $municipality = null): array
    {
        try {
            // This is where you could integrate with actual holiday APIs
            // Example: Spanish government API, or other holiday services
            
            // For now, return empty array as fallback
            return [];
            
        } catch (\Exception $e) {
            Log::warning('External holiday API failed', ['error' => $e->getMessage()]);
            return [];
        }
    }
}