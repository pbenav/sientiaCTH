<?php

namespace App\Traits\Stats;

use App\Models\Event;
use App\Models\User;
use App\Traits\HandlesTimezoneConversion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

trait CalculatesDashboardData
{
    use HandlesTimezoneConversion;
    /**
     * Get the data for the dashboard.
     *
     * @param float $scheduledHours
     * @param int $scheduledDays
     * @return array
     */
    private function getDashboardData(float $scheduledHours, int $scheduledDays): array
    {
        $user = User::find($this->browsedUser);
        $teamTimezone = $this->getUserTimezone($user);
        $workdayEventType = $user->currentTeam->eventTypes()->where('is_workday_type', true)->first();
        if (!$workdayEventType) {
            return [];
        }

        $allEvents = Event::query()
            ->where('user_id', $this->browsedUser)
            ->whereMonth('start', $this->selectedMonth)
            ->whereYear('start', $this->selectedYear)
            ->orderBy('start', 'asc')
            ->get();

        // Normalizar: trabajar solo con eventos cerrados (tienen end)
        $closedEvents = $allEvents->filter(function ($event) {
            return ! empty($event->end);
        })->values();

        // Collection of workday type events
        $workdayEvents = $closedEvents->where('event_type_id', $workdayEventType->id);

        // Calculate authorizable events for selected year (full year, not just current month)
        // IMPORTANTE: Usar selectedYear en lugar de now()->year para respetar el año que el usuario está viendo
        $currentYear = $this->selectedYear;
        
        // Get team holidays for the selected year (and next year for events that might cross)
        $yearStart = Carbon::create($currentYear, 1, 1, 0, 0, 0, $teamTimezone);
        $yearEnd = Carbon::create($currentYear, 12, 31, 23, 59, 59, $teamTimezone);
        $nextYearStart = Carbon::create($currentYear + 1, 1, 1, 0, 0, 0, $teamTimezone);
        $nextYearEnd = Carbon::create($currentYear + 1, 12, 31, 23, 59, 59, $teamTimezone);
        
        $holidaysCurrentYear = $user->currentTeam->holidays()
            ->whereBetween('date', [$yearStart, $yearEnd])
            ->pluck('date')
            ->map(fn ($date) => $date->format('Y-m-d'))
            ->toArray();
            
        $holidaysNextYear = $user->currentTeam->holidays()
            ->whereBetween('date', [$nextYearStart, $nextYearEnd])
            ->pluck('date')
            ->map(fn ($date) => $date->format('Y-m-d'))
            ->toArray();
            
        $allHolidays = array_merge($holidaysCurrentYear, $holidaysNextYear);
        
        $authorizableEventsByType = Event::with('eventType')
            ->where('user_id', $user->id)
            ->whereYear('start', $currentYear)
            ->whereHas('eventType', function ($query) {
                $query->where('is_authorizable', true);
            })
            ->get()
            ->groupBy('event_type_id')
            ->map(function ($events, $typeId) use ($teamTimezone, $user, $allHolidays, $currentYear) {
                $eventType = $events->first()->eventType;
                
                // Calculate the sum of days for all events of this type
                $totalDays = $events->sum(function ($event) use ($teamTimezone, $user, $allHolidays, $currentYear) {
                    if (empty($event->start) || empty($event->end)) {
                        return 0;
                    }
                    
                    // Parse as UTC and convert to team timezone to avoid timezone shift issues
                    $startLocal = $this->utcToTeamTimezone($event->start, $teamTimezone);
                    $endLocal = $this->utcToTeamTimezone($event->end, $teamTimezone);
                    
                    // Get date strings in local timezone
                    $startDate = $startLocal->toDateString();
                    $endDate = $endLocal->toDateString();
                    
                    // For all-day events ending at 00:00:00, don't count the end day
                    // This handles events stored as "2025-01-15 00:00:00 to 2025-01-16 00:00:00"
                    if ($endLocal->format('H:i:s') === '00:00:00') {
                        $endLocal = $endLocal->subSecond();
                        $endDate = $endLocal->toDateString();
                    }
                    
                    // If same day in local timezone, count as 1
                    if ($startDate === $endDate) {
                        return 1;
                    }
                    
                    // Calculate days based on user preference
                    if ($user->vacation_calculation_type === 'working') {
                        // Count working days (excluding weekends and holidays)
                        // Only count days within the current year
                        return $this->calculateWorkingDaysInYear($startLocal, $endLocal, $allHolidays, $currentYear);
                    }
                    
                    // Natural days: count the difference in days + 1
                    // Only count days within the current year
                    $yearStart = Carbon::create($currentYear, 1, 1, 0, 0, 0, $startLocal->timezone);
                    $yearEnd = Carbon::create($currentYear, 12, 31, 23, 59, 59, $startLocal->timezone);
                    
                    $effectiveStart = $startLocal->copy()->max($yearStart);
                    $effectiveEnd = $endLocal->copy()->min($yearEnd);
                    
                    if ($effectiveStart->gt($effectiveEnd)) {
                        return 0; // Event is entirely outside the current year
                    }
                    
                    return $effectiveStart->copy()->startOfDay()->diffInDays($effectiveEnd->copy()->startOfDay()) + 1;
                });
                
                return [
                    'description' => $eventType->name ?? 'Sin nombre',
                    'days' => $totalDays,
                    'color' => $eventType->color ?? '#9333ea', // purple-600 por defecto
                ];
            })
            ->values()
            ->toArray();


        // Calculate hours per day to avoid mismatches: only count workday hours
        // and compute non-workday hours only on scheduled days.
        
        // Use team's timezone for consistent date/time operations (already defined above)
        $startDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1, 0, 0, 0, $teamTimezone);
        
        // If the request is for the current month, limit calculation to today; if past month, use whole month
        $today = Carbon::today($teamTimezone);
        if ($this->selectedYear === (int) $today->year && $this->selectedMonth === (int) $today->month) {
            $endDate = $today;
        } else {
            $endDate = $startDate->copy()->endOfMonth();
        }

        $scheduleMeta = $user->meta->where('meta_key', 'work_schedule')->first();
        $schedule = $scheduleMeta ? json_decode($scheduleMeta->meta_value, true) : [];

                $registeredHours = 0.0; // total workday hours (all days, including non-scheduled)
    $registeredWithinScheduledSeconds = 0; // seconds of workday events that fall within scheduled slots
    $extraSeconds = 0; // seconds of workday events outside scheduled slots (only on days with work)
    $nonWorkdayHours = 0.0;
    $dailyWorked = [];
    $scheduledSeconds = 0;

    // Para puntualidad: acumuladores por franja
    $entryDeviationsSeconds = []; // abs(event.start - slot.start)
    $exitDeviationsSeconds = []; // abs(event.end - slot.end)
    $entryBackdateSeconds = []; // abs(event.start - event.created_at)
    $exitBackdateSeconds = []; // abs(event.end - event.updated_at)
    $entryPctList = []; // percent punctuality per slot (0..1)
    $exitPctList = [];
    $entryRealPctList = []; // Real punctuality (created_at vs slot)
    $exitRealPctList = [];
    $breakdownLines = [];

        // PRIMER PASO: contar TODAS las horas registradas del tipo principal (incluyendo días sin schedule)
        foreach ($workdayEvents as $ev) {
            $evStart = $this->utcToTeamTimezone($ev->start, $teamTimezone);
            $evEnd = $this->utcToTeamTimezone($ev->end, $teamTimezone);
            $hours = $evStart->diffInSeconds($evEnd) / 3600;
            $registeredHours += $hours;
        }

        // SEGUNDO PASO: Iterar por cada día del mes para calcular horas programadas, puntualidad, etc.
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayKey = $date->format('Y-m-d');

            // comprobar si es día programado: puede haber varias franjas (slots) en el mismo día
            // Usar número ISO directamente (1-7)
            $dayNumber = (int) $date->format('N');
            $daySchedules = collect($schedule)->filter(function ($slot) use ($dayNumber) {
                return in_array($dayNumber, $slot['days']);
            })->values()->all();

            if (empty($daySchedules)) {
                continue; // no es día laboral programado, pero ya contamos sus horas arriba
            }

            // Duración programada del día: sumar todas las franjas (slots) del día
            foreach ($daySchedules as $daySchedule) {
                if (! empty($daySchedule['start']) && ! empty($daySchedule['end'])) {
                    $scheduledStartTime = Carbon::parse($date->format('Y-m-d') . ' ' . $daySchedule['start'], $teamTimezone);
                    $scheduledEndTime = Carbon::parse($date->format('Y-m-d') . ' ' . $daySchedule['end'], $teamTimezone);
                    $scheduledSeconds += max(0, $scheduledEndTime->diffInSeconds($scheduledStartTime));
                }
            }

            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();

            // eventos que intersectan este día
                        $eventsOfDay = $closedEvents->filter(function ($ev) use ($dayStart, $dayEnd, $teamTimezone) {
                $evStart = $this->utcToTeamTimezone($ev->start, $teamTimezone);
                $evEnd = $this->utcToTeamTimezone($ev->end, $teamTimezone);
                return $evStart->lte($dayEnd) && $evEnd->gte($dayStart);
            });

            $dayWorkSeconds = 0;
            $dayNonWorkSeconds = 0;

            foreach ($eventsOfDay as $ev) {
                $evStart = $this->utcToTeamTimezone($ev->start, $teamTimezone)->max($dayStart);
                $evEnd = $this->utcToTeamTimezone($ev->end, $teamTimezone)->min($dayEnd);
                $seconds = max(0, $evEnd->diffInSeconds($evStart));

                if ($ev->event_type_id == $workdayEventType->id) {
                    $dayWorkSeconds += $seconds;
                } else {
                    $dayNonWorkSeconds += $seconds;
                }
            }

            // Marcar día como trabajado si tuvo horas de jornada
            if ($dayWorkSeconds > 0) {
                $dailyWorked[] = $date->format('Y-m-d');
            }

            // Calcular cuánto de dayWorkSeconds cae dentro de las franjas programadas para ese día
            $dayWithinScheduledSeconds = 0;
            foreach ($daySchedules as $daySchedule) {
                if (empty($daySchedule['start']) || empty($daySchedule['end'])) continue;

                $slotStart = Carbon::parse($date->format('Y-m-d') . ' ' . $daySchedule['start'], $teamTimezone);
                $slotEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $daySchedule['end'], $teamTimezone);

                // Para detectar la mejor intersección por franja (si hay varios eventos que tocan la franja),
                // guardamos el evento con mayor tiempo de intersección y calculamos desviaciones a partir de él.
                $bestIntersectionSeconds = 0;
                $bestEventForSlot = null;

                foreach ($eventsOfDay as $ev2) {
                    if ($ev2->event_type_id != $workdayEventType->id) continue;
                    $evStart2 = $this->utcToTeamTimezone($ev2->start, $teamTimezone)->max($dayStart);
                    $evEnd2 = $this->utcToTeamTimezone($ev2->end, $teamTimezone)->min($dayEnd);
                    // intersección con el slot
                    $intStart = $evStart2->max($slotStart);
                    $intEnd = $evEnd2->min($slotEnd);
                    $intSeconds = max(0, $intEnd->diffInSeconds($intStart));

                    if ($intSeconds > $bestIntersectionSeconds) {
                        $bestIntersectionSeconds = $intSeconds;
                        $bestEventForSlot = $ev2;
                    }
                }

                // Solo acumular la mejor intersección por slot para evitar doble conteo
                $dayWithinScheduledSeconds += $bestIntersectionSeconds;

                // Si encontramos un evento principal para esta franja, calcular desviaciones
                if ($bestEventForSlot) {
                    try {
                        $eventStart = $this->utcToTeamTimezone($bestEventForSlot->start, $teamTimezone);
                        $eventEnd = $this->utcToTeamTimezone($bestEventForSlot->end, $teamTimezone);
                        $createdAt = $this->utcToTeamTimezone($bestEventForSlot->created_at, $teamTimezone);
                        $updatedAt = $this->utcToTeamTimezone($bestEventForSlot->updated_at, $teamTimezone);

                        $entryDeviationsSeconds[] = abs($eventStart->diffInSeconds($slotStart));
                        $exitDeviationsSeconds[] = abs($eventEnd->diffInSeconds($slotEnd));
                        $entryBackdateSeconds[] = abs($eventStart->diffInSeconds($createdAt));
                        $exitBackdateSeconds[] = abs($eventEnd->diffInSeconds($updatedAt));

                        // Calcular porcentaje de puntualidad relativo a la duración de la franja
                        $slotDuration = max(1, $slotEnd->diffInSeconds($slotStart));
                        $entryPct = max(0, min(1, 1 - (abs($eventStart->diffInSeconds($slotStart)) / $slotDuration)));
                        $exitPct = max(0, min(1, 1 - (abs($eventEnd->diffInSeconds($slotEnd)) / $slotDuration)));
                        $entryRealPct = max(0, min(1, 1 - (abs($createdAt->diffInSeconds($slotStart)) / $slotDuration)));
                        $exitRealPct = max(0, min(1, 1 - (abs($updatedAt->diffInSeconds($slotEnd)) / $slotDuration)));
                        
                        $entryPctList[] = $entryPct;
                        $exitPctList[] = $exitPct;
                        $entryRealPctList[] = $entryRealPct;
                        $exitRealPctList[] = $exitRealPct;

                        // Añadir línea al desglose: fecha, franja y porcentajes
                        $breakdownLines[] = $date->format('Y-m-d') . ' ' . $slotStart->format('H:i') . '-' . $slotEnd->format('H:i') . ': entrada ' . round($entryPct * 100, 2) . '%, salida ' . round($exitPct * 100, 2) . '%';
                    } catch (\Exception $e) {
                        // En caso de datos malformados, ignorar esa franja
                    }
                }
                else {
                    // No hay evento para la franja: indicar sin registro
                    $breakdownLines[] = $date->format('Y-m-d') . ' ' . $slotStart->format('H:i') . '-' . $slotEnd->format('H:i') . ': sin registro';
                }
            }

            $dayOutsideSeconds = max(0, $dayWorkSeconds - $dayWithinScheduledSeconds);

            // registeredHours ya se calculó antes del loop (total del mes)
            $registeredWithinScheduledSeconds += $dayWithinScheduledSeconds;
            
            // Solo acumular horas extra si realmente hay tiempo fuera del horario programado
            // y si el día tuvo actividad laboral
            if ($dayWorkSeconds > 0) {
                $extraSeconds += $dayOutsideSeconds;
            }
            $nonWorkdayHours += $dayNonWorkSeconds / 3600;
        }

        // Horas programadas calculadas a partir del schedule (en horas)
        $scheduledHoursCalculated = $scheduledSeconds / 3600;

        // Horas registradas dentro del horario (en horas)
        $registeredWithinHours = $registeredWithinScheduledSeconds / 3600;

        // Porcentaje de cumplimiento: horas registradas del tipo jornada / horas programadas (capado a 100%)
        if ($scheduledHoursCalculated > 0) {
            $rawPct = ($registeredHours / $scheduledHoursCalculated) * 100;
            $percentage_completion = round(min(100, $rawPct), 2);
        } else {
            $percentage_completion = 0;
        }

        // Calculate extra hours as the difference between registered and scheduled hours
        // If registered hours exceed scheduled hours, the difference is overtime
        $extra_hours = max(0, round($registeredHours - $scheduledHoursCalculated, 2));

        $scheduleMeta = $user->meta->where('meta_key', 'work_schedule')->first();
        $schedule = $scheduleMeta ? json_decode($scheduleMeta->meta_value, true) : [];
        $punctualDays = 0;
        $realPunctualDays = 0;
        $absentDays = 0;
        // Obtener el margen de cortesía (gracia) del equipo, por defecto 5 minutos
        $courtesyMargin = $user->currentTeam->clock_in_grace_period_minutes ?? 5;
        // Los días trabajados se calculan a partir de la iteración diaria anterior
        $workedDays = collect($dailyWorked);

        $startDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1, 0, 0, 0, $teamTimezone);
        // Reutilizar la misma lógica: si es mes en curso, considerar hasta hoy
        $today = Carbon::today($teamTimezone);
        if ($this->selectedYear === (int) $today->year && $this->selectedMonth === (int) $today->month) {
            $endDate = $today;
        } else {
            $endDate = $startDate->copy()->endOfMonth();
        }

        $holidays = $this->actualUser->currentTeam->holidays()
            ->whereBetween('date', [$startDate, $endDate])
            ->pluck('date')
            ->map(fn ($date) => $date->format('Y-m-d'));

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if ($holidays->contains($date->format('Y-m-d'))) {
                continue;
            }

            // Usar número ISO directamente (1-7)
            $dayNumber = (int) $date->format('N');
            $daySchedule = collect($schedule)->first(function ($slot) use ($dayNumber) {
                return in_array($dayNumber, $slot['days']);
            });

            if ($daySchedule) {
                $isWorked = $workedDays->contains($date->format('Y-m-d'));

                if (!$isWorked) {
                    $absentDays++;
                } else {
                    // Buscar el primer evento del día entre los eventos de jornada laboral
                    $firstEvent = $workdayEvents->first(function ($event) use ($date, $teamTimezone) {
                        return $this->utcToTeamTimezone($event->start, $teamTimezone)->isSameDay($date);
                    });

                    if ($firstEvent) {
                        $scheduledStartTime = Carbon::parse($date->format('Y-m-d') . ' ' . $daySchedule['start'], $teamTimezone);
                        $actualStartTime = $this->utcToTeamTimezone($firstEvent->start, $teamTimezone);
                        
                        // Aplicar margen de cortesía
                        if ($actualStartTime <= $scheduledStartTime->copy()->addMinutes($courtesyMargin)) {
                            $punctualDays++;
                        }
                        
                        $realStartTime = $this->utcToTeamTimezone($firstEvent->created_at, $teamTimezone);
                        // Aplicar margen de cortesía también a la comprobación real
                        if ($realStartTime <= $scheduledStartTime->copy()->addMinutes($courtesyMargin)) {
                            $realPunctualDays++;
                        }
                    }
                }
            }
        }

        $workedDaysCount = $scheduledDays - $absentDays;
        $punctuality = ($workedDaysCount > 0) ? round(($punctualDays / $workedDaysCount) * 100, 2) : 0;
        $realPunctuality = ($workedDaysCount > 0) ? round(($realPunctualDays / $workedDaysCount) * 100, 2) : 0;

        $confidenceScores = [];
        // Calcular confianza SOLO sobre los eventos de jornada laboral (excluyendo pausas, vacaciones, etc)
        foreach ($workdayEvents as $event) {
            // Parse as UTC since events are stored in UTC
            $start = Carbon::parse($event->start, 'UTC');
            $end = Carbon::parse($event->end, 'UTC');
            $createdAt = Carbon::parse($event->created_at, 'UTC');
            $updatedAt = Carbon::parse($event->updated_at, 'UTC');

            $diffStart = abs($start->diffInSeconds($createdAt));
            $diffEnd = abs($end->diffInSeconds($updatedAt));
            $duration = abs($start->diffInSeconds($end));

            if ($duration > 0) {
                $totalDiff = $diffStart + $diffEnd;
                $confidence = max(0, (1 - ($totalDiff / $duration)) * 100);
                $confidenceScores[] = $confidence;
            }
        }

    $avgConfidence = !empty($confidenceScores) ? round(array_sum($confidenceScores) / count($confidenceScores), 2) : 0;
        $minConfidence = !empty($confidenceScores) ? round(min($confidenceScores), 2) : 0;
        $maxConfidence = !empty($confidenceScores) ? round(max($confidenceScores), 2) : 0;

        // Clasificar según umbrales configurables
        $thresholds = Config::get('stats.confidence_thresholds', [
            'very_high' => 90,
            'high' => 75,
            'moderate' => 50,
            'low' => 20,
        ]);

    // --- Cálculo de puntualidad (entrada/salida) como porcentaje relativo a la duración de cada franja ---
    // Filter outliers (deviations > 12 hours) to avoid distorting the average
    $outlierThreshold = 12 * 3600; // 12 hours
    
    $filteredEntryBackdate = array_filter($entryBackdateSeconds, fn($s) => $s <= $outlierThreshold);
    $filteredExitBackdate = array_filter($exitBackdateSeconds, fn($s) => $s <= $outlierThreshold);

    // Calcular promedio sin outliers
    $avgEntryDeviationSec = !empty($entryDeviationsSeconds) ? array_sum($entryDeviationsSeconds) / count($entryDeviationsSeconds) : 0;
    $avgExitDeviationSec = !empty($exitDeviationsSeconds) ? array_sum($exitDeviationsSeconds) / count($exitDeviationsSeconds) : 0;
    $avgEntryBackdateSec = !empty($filteredEntryBackdate) ? array_sum($filteredEntryBackdate) / count($filteredEntryBackdate) : 0;
    $avgExitBackdateSec = !empty($filteredExitBackdate) ? array_sum($filteredExitBackdate) / count($filteredExitBackdate) : 0;

    // Convertir a formato Xm Ys para presentación
    $formatDuration = function($seconds) {
        $m = floor($seconds / 60);
        $s = round($seconds % 60);
        return sprintf('%dm %ds', $m, $s);
    };

    // Helper para formatear horas decimales a Xh Ym
    $formatHours = function($hoursFloat) {
        $h = floor($hoursFloat);
        $m = round(($hoursFloat - $h) * 60);
        return sprintf('%dh %02dm', $h, $m);
    };

    $avgEntryDeviationMin = $formatDuration($avgEntryDeviationSec);
    $avgExitDeviationMin = $formatDuration($avgExitDeviationSec);
    $avgEntryBackdateMin = $formatDuration($avgEntryBackdateSec);
    $avgExitBackdateMin = $formatDuration($avgExitBackdateSec);

        // Calcular promedio de porcentajes por franja (no mediana para obtener valores más precisos con pocos registros)
        $avgEntryPct = !empty($entryPctList) ? round((array_sum($entryPctList) / count($entryPctList)) * 100, 2) : 0;
        $avgExitPct = !empty($exitPctList) ? round((array_sum($exitPctList) / count($exitPctList)) * 100, 2) : 0;
        $avgEntryRealPct = !empty($entryRealPctList) ? round((array_sum($entryRealPctList) / count($entryRealPctList)) * 100, 2) : 0;
        $avgExitRealPct = !empty($exitRealPctList) ? round((array_sum($exitRealPctList) / count($exitRealPctList)) * 100, 2) : 0;

        // Combinado: mediana/porcentaje promedio simple de entrada y salida
        $punctualityEntryWeighted = $avgEntryPct;
        $punctualityExitWeighted = $avgExitPct;
        $punctualityCombinedPct = round((($punctualityEntryWeighted + $punctualityExitWeighted) / 2), 2);

        $classify = function (float $value) use ($thresholds): string {
            if ($value >= $thresholds['very_high']) return 'very_high';
            if ($value >= $thresholds['high']) return 'high';
            if ($value >= $thresholds['moderate']) return 'moderate';
            if ($value >= $thresholds['low']) return 'low';
            return 'very_low';
        };

    $avgConfidenceCategory = $classify($avgConfidence);

        $exceptionalEventsCount = Event::where('user_id', $this->browsedUser)
            ->where('is_exceptional', true)
            ->whereYear('start', $this->selectedYear)
            ->whereMonth('start', $this->selectedMonth)
            ->count();

        $automaticallyClosedCount = Event::where('user_id', $this->browsedUser)
            ->where('is_closed_automatically', true)
            ->whereYear('updated_at', $this->selectedYear)
            ->whereMonth('updated_at', $this->selectedMonth)
            ->count();

        // Preparar líneas de desglose para tooltips
        $punctualityBreakdownLines = $breakdownLines;

        return [
            'exceptional_events_count' => $exceptionalEventsCount,
            'automatically_closed_count' => $automaticallyClosedCount,
            'percentage_completion' => $percentage_completion,
            'extra_hours' => $extra_hours,
            'extra_hours_fmt' => $formatHours($extra_hours),
            'daily_overtime' => round($extraSeconds / 3600, 2),
            'daily_overtime_fmt' => $formatHours($extraSeconds / 3600),
            'punctuality' => $punctuality,
            'real_punctuality' => $realPunctuality,
            'absenteeism' => $absentDays,
            'registered_hours' => round($registeredHours, 2),
            'registered_hours_fmt' => $formatHours($registeredHours),
            'registered_within_hours' => round($registeredWithinHours, 2),
            'registered_within_hours_fmt' => $formatHours($registeredWithinHours),
            'effective_scheduled_hours' => round($scheduledHoursCalculated, 2),
            'effective_scheduled_hours_fmt' => $formatHours($scheduledHoursCalculated),
            'avg_confidence' => $avgConfidence,
            'min_confidence' => $minConfidence,
            'max_confidence' => $maxConfidence,
            // Añadimos categoría media y umbrales usados
            'avg_confidence_category' => $avgConfidenceCategory,
            'confidence_thresholds' => $thresholds,
            // Puntualidad: entrada/salida en minutos y porcentaje (combinado)
            'punctuality_entry_minutes' => $avgEntryDeviationMin,
            'punctuality_exit_minutes' => $avgExitDeviationMin,
            'punctuality_entry_backdate_minutes' => $avgEntryBackdateMin,
            'punctuality_exit_backdate_minutes' => $avgExitBackdateMin,
            'punctuality_entry_pct' => $punctualityEntryWeighted,
            'punctuality_exit_pct' => $punctualityExitWeighted,
            'punctuality_entry_real_pct' => $avgEntryRealPct,
            'punctuality_exit_real_pct' => $avgExitRealPct,
            'punctuality_combined_pct' => $punctualityCombinedPct,
            'punctuality_breakdown_lines' => $punctualityBreakdownLines,
            // Eventos autorizables del año en curso
            'authorizable_events' => $authorizableEventsByType,
        ];
    }


    /**
     * Calculate working days between two dates within a specific year, excluding weekends and holidays.
     *
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @param array $holidays Array of holiday dates in 'Y-m-d' format
     * @param int $year The year to count days within
     * @return int
     */
    private function calculateWorkingDaysInYear(Carbon $startDate, Carbon $endDate, array $holidays, int $year): int
    {
        $workingDays = 0;
        
        // Constrain dates to the specified year
        $yearStart = Carbon::create($year, 1, 1, 0, 0, 0, $startDate->timezone);
        $yearEnd = Carbon::create($year, 12, 31, 23, 59, 59, $startDate->timezone);
        
        $effectiveStart = $startDate->copy()->max($yearStart)->startOfDay();
        $effectiveEnd = $endDate->copy()->min($yearEnd)->startOfDay();
        
        // If the event is entirely outside the year, return 0
        if ($effectiveStart->gt($effectiveEnd)) {
            return 0;
        }
        
        $current = $effectiveStart->copy();

        while ($current->lte($effectiveEnd)) {
            $dayOfWeek = (int) $current->format('N'); // 1 (Monday) to 7 (Sunday)
            $dateString = $current->format('Y-m-d');

            // Count if it's not a weekend (Saturday=6, Sunday=7) and not a holiday
            if ($dayOfWeek < 6 && !in_array($dateString, $holidays)) {
                $workingDays++;
            }

            $current->addDay();
        }

        return $workingDays;
    }
}
