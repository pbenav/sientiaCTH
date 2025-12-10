<?php

namespace App\Traits\Stats;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

trait CalculatesDashboardData
{
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
        $teamTimezone = $user->currentTeam->timezone ?? config('app.timezone');
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

        // Calculate authorizable events for current year (full year, not just current month)
        $currentYear = now()->year;
        $authorizableEventsByType = Event::with('eventType')
            ->where('user_id', $user->id)
            ->whereYear('start', $currentYear)
            ->whereHas('eventType', function ($query) {
                $query->where('is_authorizable', true);
            })
            ->get()
            ->groupBy('event_type_id')
            ->map(function ($events, $typeId) use ($teamTimezone) {
                $eventType = $events->first()->eventType;
                
                // Calculate the sum of days for all events of this type
                $totalDays = $events->sum(function ($event) use ($teamTimezone) {
                    if (empty($event->start) || empty($event->end)) {
                        return 0;
                    }
                    // Parse as UTC (how Laravel stores timestamps)
                    $startUTC = Carbon::parse($event->start, 'UTC');
                    $endUTC = Carbon::parse($event->end, 'UTC');
                    
                    // Calculate days based on UTC dates to avoid timezone conversion issues
                    // For all-day events stored as 00:00:00 to 23:59:59, this will correctly count as 1 day
                    $startDate = $startUTC->toDateString();
                    $endDate = $endUTC->toDateString();
                    
                    // If same day in UTC, count as 1; otherwise count the difference + 1
                    if ($startDate === $endDate) {
                        return 1;
                    }
                    
                    return $startUTC->copy()->startOfDay()->diffInDays($endUTC->copy()->startOfDay()) + 1;
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
    $breakdownLines = [];

        // PRIMER PASO: contar TODAS las horas registradas del tipo principal (incluyendo días sin schedule)
        foreach ($workdayEvents as $ev) {
            $evStart = Carbon::parse($ev->start, 'UTC')->setTimezone($teamTimezone);
            $evEnd = Carbon::parse($ev->end, 'UTC')->setTimezone($teamTimezone);
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
                $evStart = Carbon::parse($ev->start, 'UTC')->setTimezone($teamTimezone);
                $evEnd = Carbon::parse($ev->end, 'UTC')->setTimezone($teamTimezone);
                return $evStart->lte($dayEnd) && $evEnd->gte($dayStart);
            });

            $dayWorkSeconds = 0;
            $dayNonWorkSeconds = 0;

            foreach ($eventsOfDay as $ev) {
                $evStart = Carbon::parse($ev->start, 'UTC')->setTimezone($teamTimezone)->max($dayStart);
                $evEnd = Carbon::parse($ev->end, 'UTC')->setTimezone($teamTimezone)->min($dayEnd);
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
                    $evStart2 = Carbon::parse($ev2->start, 'UTC')->setTimezone($teamTimezone)->max($dayStart);
                    $evEnd2 = Carbon::parse($ev2->end, 'UTC')->setTimezone($teamTimezone)->min($dayEnd);
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
                        $eventStart = Carbon::parse($bestEventForSlot->start, 'UTC')->setTimezone($teamTimezone);
                        $eventEnd = Carbon::parse($bestEventForSlot->end, 'UTC')->setTimezone($teamTimezone);
                        $createdAt = Carbon::parse($bestEventForSlot->created_at, 'UTC')->setTimezone($teamTimezone);
                        $updatedAt = Carbon::parse($bestEventForSlot->updated_at, 'UTC')->setTimezone($teamTimezone);

                        $entryDeviationsSeconds[] = abs($eventStart->diffInSeconds($slotStart));
                        $exitDeviationsSeconds[] = abs($eventEnd->diffInSeconds($slotEnd));
                        $entryBackdateSeconds[] = abs($eventStart->diffInSeconds($createdAt));
                        $exitBackdateSeconds[] = abs($eventEnd->diffInSeconds($updatedAt));

                        // Calcular porcentaje de puntualidad relativo a la duración de la franja
                        $slotDuration = max(1, $slotEnd->diffInSeconds($slotStart));
                        $entryPct = max(0, min(1, 1 - (abs($eventStart->diffInSeconds($slotStart)) / $slotDuration)));
                        $exitPct = max(0, min(1, 1 - (abs($eventEnd->diffInSeconds($slotEnd)) / $slotDuration)));
                        $entryPctList[] = $entryPct;
                        $exitPctList[] = $exitPct;

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
        $absentDays = 0;
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
                        return Carbon::parse($event->start, 'UTC')->setTimezone($teamTimezone)->isSameDay($date);
                    });

                    if ($firstEvent) {
                        $scheduledStartTime = Carbon::parse($date->format('Y-m-d') . ' ' . $daySchedule['start'], $teamTimezone);
                        $actualStartTime = Carbon::parse($firstEvent->start, 'UTC')->setTimezone($teamTimezone);
                        if ($actualStartTime <= $scheduledStartTime) {
                            $punctualDays++;
                        }
                    }
                }
            }
        }

        $workedDaysCount = $scheduledDays - $absentDays;
        $punctuality = ($workedDaysCount > 0) ? round(($punctualDays / $workedDaysCount) * 100, 2) : 0;

        $confidenceScores = [];
        // Calcular confianza sobre los eventos cerrados (ya filtrados en $closedEvents)
        foreach ($closedEvents as $event) {
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
    $avgEntryDeviationSec = !empty($entryDeviationsSeconds) ? array_sum($entryDeviationsSeconds) / count($entryDeviationsSeconds) : 0;
    $avgExitDeviationSec = !empty($exitDeviationsSeconds) ? array_sum($exitDeviationsSeconds) / count($exitDeviationsSeconds) : 0;
    $avgEntryBackdateSec = !empty($entryBackdateSeconds) ? array_sum($entryBackdateSeconds) / count($entryBackdateSeconds) : 0;
    $avgExitBackdateSec = !empty($exitBackdateSeconds) ? array_sum($exitBackdateSeconds) / count($exitBackdateSeconds) : 0;

    // Convertir a minutos para presentación
    $avgEntryDeviationMin = round($avgEntryDeviationSec / 60, 2);
    $avgExitDeviationMin = round($avgExitDeviationSec / 60, 2);
    $avgEntryBackdateMin = round($avgEntryBackdateSec / 60, 2);
    $avgExitBackdateMin = round($avgExitBackdateSec / 60, 2);

        // Calcular promedio de porcentajes por franja (no mediana para obtener valores más precisos con pocos registros)
        $avgEntryPct = !empty($entryPctList) ? round((array_sum($entryPctList) / count($entryPctList)) * 100, 2) : 0;
        $avgExitPct = !empty($exitPctList) ? round((array_sum($exitPctList) / count($exitPctList)) * 100, 2) : 0;

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
            'punctuality' => $punctuality,
            'absenteeism' => $absentDays,
            'registered_hours' => round($registeredHours, 2),
            'registered_within_hours' => round($registeredWithinHours, 2),
            'effective_scheduled_hours' => round($scheduledHoursCalculated, 2),
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
            'punctuality_combined_pct' => $punctualityCombinedPct,
            'punctuality_breakdown_lines' => $punctualityBreakdownLines,
            // Eventos autorizables del año en curso
            'authorizable_events' => $authorizableEventsByType,
        ];
    }
}
