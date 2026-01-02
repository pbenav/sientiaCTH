<?php

namespace App\Traits;

use Carbon\Carbon;

/**
 * Trait para manejar conversiones de zona horaria de forma consistente.
 * 
 * REGLA FUNDAMENTAL: Las fechas en BD están SIEMPRE en UTC.
 * Antes de formatear o calcular, SIEMPRE convertir a zona horaria local.
 */
trait HandlesTimezoneConversion
{
    /**
     * Obtener la zona horaria del equipo del evento.
     * 
     * @param \App\Models\Event $event
     * @return string
     */
    public function getEventTimezone($event): string
    {
        return $event->team->timezone ?? config('app.timezone');
    }
    
    /**
     * Convertir una fecha UTC a la zona horaria del equipo.
     * 
     * @param string $utcDate Fecha en UTC (formato BD)
     * @param string $timezone Zona horaria destino
     * @return \Carbon\Carbon
     */
    public function utcToTeamTimezone(string $utcDate, string $timezone): Carbon
    {
        return Carbon::parse($utcDate, 'UTC')->setTimezone($timezone);
    }
    
    /**
     * Convertir una fecha de zona horaria local a UTC.
     * 
     * @param string $localDate Fecha en zona horaria local
     * @param string $timezone Zona horaria origen
     * @return \Carbon\Carbon
     */
    public function teamTimezoneToUtc(string $localDate, string $timezone): Carbon
    {
        return Carbon::parse($localDate, $timezone)->setTimezone('UTC');
    }
    
    /**
     * Calcular duración de un evento de día completo en días.
     * IMPORTANTE: Convierte de UTC a zona horaria local antes de calcular.
     * NOTA: Siempre devuelve días naturales (para listado de eventos).
     * 
     * @param string $startUtc Fecha inicio en UTC
     * @param string $endUtc Fecha fin en UTC
     * @param string $timezone Zona horaria del equipo
     * @return int Número de días (mínimo 1)
     */
    public function calculateAllDayEventDays(string $startUtc, string $endUtc, string $timezone): int
    {
        // Convertir de UTC a zona horaria local
        $start = $this->utcToTeamTimezone($startUtc, $timezone);
        $end = $this->utcToTeamTimezone($endUtc, $timezone);
        
        // Para eventos de día completo, contar días del calendario
        // Normalizar a inicio del día para contar correctamente
        $startDay = $start->copy()->startOfDay();
        $endDay = $end->copy()->startOfDay();
        
        // Si el evento termina a las 23:59:59, cuenta ese día completo
        // Si termina a cualquier hora después de 00:00:00, cuenta ese día
        if ($end->format('H:i:s') !== '00:00:00') {
            $endDay->addDay();
        }
        
        // Siempre devolver días naturales (el listado de eventos muestra días calendario)
        $days = $startDay->diffInDays($endDay);
        return $days < 1 ? 1 : $days;
    }
    
    // ========================================================================
    // MÉTODOS DE OBTENCIÓN DE TIMEZONE
    // ========================================================================
    
    /**
     * Obtener la zona horaria del equipo actual del usuario autenticado.
     * 
     * @return string
     */
    public function getCurrentTeamTimezone(): string
    {
        $user = \Auth::user();
        if (!$user || !$user->currentTeam) {
            return config('app.timezone');
        }
        return $user->currentTeam->timezone ?? config('app.timezone');
    }
    
    /**
     * Obtener la zona horaria de un usuario específico.
     * 
     * @param \App\Models\User $user
     * @return string
     */
    public function getUserTimezone($user): string
    {
        if (!$user || !$user->currentTeam) {
            return config('app.timezone');
        }
        return $user->currentTeam->timezone ?? config('app.timezone');
    }
    
    /**
     * Obtener la zona horaria de un equipo específico.
     * 
     * @param \App\Models\Team $team
     * @return string
     */
    public function getTeamTimezone($team): string
    {
        if (!$team) {
            return config('app.timezone');
        }
        return $team->timezone ?? config('app.timezone');
    }
    
    // ========================================================================
    // MÉTODOS DE CONVERSIÓN CON CONTEXTO
    // ========================================================================
    
    /**
     * Convertir una fecha UTC a la zona horaria del equipo actual.
     * 
     * @param string $utcDate Fecha en UTC (formato BD)
     * @return \Carbon\Carbon
     */
    public function utcToCurrentTeam(string $utcDate): Carbon
    {
        $timezone = $this->getCurrentTeamTimezone();
        return $this->utcToTeamTimezone($utcDate, $timezone);
    }
    
    /**
     * Convertir una fecha UTC a la zona horaria de un usuario.
     * 
     * @param string $utcDate Fecha en UTC (formato BD)
     * @param \App\Models\User $user
     * @return \Carbon\Carbon
     */
    public function utcToUserTimezone(string $utcDate, $user): Carbon
    {
        $timezone = $this->getUserTimezone($user);
        return $this->utcToTeamTimezone($utcDate, $timezone);
    }
    
    /**
     * Convertir una fecha de la zona horaria del equipo actual a UTC.
     * 
     * @param string $localDate Fecha en zona horaria local
     * @return \Carbon\Carbon
     */
    public function currentTeamToUtc(string $localDate): Carbon
    {
        $timezone = $this->getCurrentTeamTimezone();
        return $this->teamTimezoneToUtc($localDate, $timezone);
    }
    
    // ========================================================================
    // MÉTODOS HELPER PARA CASOS COMUNES
    // ========================================================================
    
    /**
     * Parsear una fecha de evento (siempre en UTC) a la zona horaria del equipo.
     * 
     * @param \App\Models\Event $event
     * @param string $dateField Campo de fecha ('start' o 'end')
     * @return \Carbon\Carbon|null
     */
    public function parseEventDate($event, string $dateField = 'start'): ?Carbon
    {
        if (!$event->$dateField) {
            return null;
        }
        
        $timezone = $this->getEventTimezone($event);
        return $this->utcToTeamTimezone($event->$dateField, $timezone);
    }
    
    /**
     * Crear un Carbon con la zona horaria del equipo actual o especificado.
     * 
     * @param \App\Models\Team|null $team
     * @return \Carbon\Carbon
     */
    public function nowInTeamTimezone($team = null): Carbon
    {
        $timezone = $team ? $this->getTeamTimezone($team) : $this->getCurrentTeamTimezone();
        return Carbon::now($timezone);
    }
    
    /**
     * Crear un Carbon para una fecha específica en la zona horaria del equipo.
     * 
     * @param string $date Fecha a parsear
     * @param \App\Models\Team|null $team
     * @return \Carbon\Carbon
     */
    public function createInTeamTimezone(string $date, $team = null): Carbon
    {
        $timezone = $team ? $this->getTeamTimezone($team) : $this->getCurrentTeamTimezone();
        return Carbon::parse($date, $timezone);
    }
    
    /**
     * Convertir múltiples fechas UTC a la zona horaria especificada.
     * Útil para procesar lotes de eventos.
     * 
     * @param array $utcDates Array de fechas en UTC
     * @param string $timezone Zona horaria destino
     * @return array Array de objetos Carbon convertidos
     */
    public function batchUtcToTeamTimezone(array $utcDates, string $timezone): array
    {
        return array_map(function($date) use ($timezone) {
            return $this->utcToTeamTimezone($date, $timezone);
        }, $utcDates);
    }
}

