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

        // Colección de eventos del tipo jornada laboral
        $workdayEvents = $closedEvents->where('event_type_id', $workdayEventType->id);

        // Calcular eventos autorizables del año en curso (año completo, no solo el mes actual)
        $currentYear = now()->year;
        $authorizableEventsByType = Event::with('eventType')
            ->where('user_id', $user->id)
            ->whereYear('start', $currentYear)
            ->whereHas('eventType', function ($query) {
                $query->where('is_authorizable', true);
            })
            ->get()
            ->groupBy('event_type_id')
            ->map(function ($events, $typeId) {
                $eventType = $events->first()->eventType;
                
                // Calcular la suma de días de todos los eventos de este tipo
                $totalDays = $events->sum(function ($event) {
                    if (empty($event->start) || empty($event->end)) {
                        return 0;
                    }
                    $start = Carbon::parse($event->start);
                    $end = Carbon::parse($event->end);
                    // Si el evento es del mismo día, cuenta como 1 día
                    // Si es de varios días, cuenta los días completos
                    return max(1, $start->startOfDay()->diffInDays($end->startOfDay()) + 1);
                });
                
                return [
                    'description' => $eventType->name ?? 'Sin nombre',
                    'days' => $totalDays,
                    'color' => $eventType->color ?? '#9333ea', // purple-600 por defecto
                ];
            })
            ->values()
            ->toArray();

        // Calcular horas por día para evitar desajustes: solo contar horas de jornada laboral
        // y computar horas no-jornada solo en los días programados.
        $startDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1);
        $endDate = $startDate->copy()->endOfMonth();

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
            $evStart = Carbon::parse($ev->start);
            $evEnd = Carbon::parse($ev->end);
            $hours = $evStart->diffInSeconds($evEnd) / 3600;
            $registeredHours += $hours;
        }

        // SEGUNDO PASO: Iterar por cada día del mes para calcular horas programadas, puntualidad, etc.
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayKey = $date->format('Y-m-d');

            // comprobar si es día programado: puede haber varias franjas (slots) en el mismo día
            $dayInitial = $this->getDayInitial($date->format('N'));
            $daySchedules = collect($schedule)->filter(function ($slot) use ($dayInitial) {
                return in_array($dayInitial, $slot['days']);
            })->values()->all();

            if (empty($daySchedules)) {
                continue; // no es día laboral programado, pero ya contamos sus horas arriba
            }

            // Duración programada del día: sumar todas las franjas (slots) del día
            foreach ($daySchedules as $daySchedule) {
                if (! empty($daySchedule['start']) && ! empty($daySchedule['end'])) {
                    $scheduledStartTime = Carbon::parse($date->format('Y-m-d') . ' ' . $daySchedule['start']);
                    $scheduledEndTime = Carbon::parse($date->format('Y-m-d') . ' ' . $daySchedule['end']);
                    $scheduledSeconds += max(0, $scheduledEndTime->diffInSeconds($scheduledStartTime));
                }
            }

            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();

            // eventos que intersectan este día
            $eventsOfDay = $closedEvents->filter(function ($ev) use ($dayStart, $dayEnd) {
                $evStart = Carbon::parse($ev->start);
                $evEnd = Carbon::parse($ev->end);
                return $evStart->lte($dayEnd) && $evEnd->gte($dayStart);
            });

            $dayWorkSeconds = 0;
            $dayNonWorkSeconds = 0;

            foreach ($eventsOfDay as $ev) {
                $evStart = Carbon::parse($ev->start)->max($dayStart);
                $evEnd = Carbon::parse($ev->end)->min($dayEnd);
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

                $slotStart = Carbon::parse($date->format('Y-m-d') . ' ' . $daySchedule['start']);
                $slotEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $daySchedule['end']);

                // Para detectar la mejor intersección por franja (si hay varios eventos que tocan la franja),
                // guardamos el evento con mayor tiempo de intersección y calculamos desviaciones a partir de él.
                $bestIntersectionSeconds = 0;
                $bestEventForSlot = null;

                foreach ($eventsOfDay as $ev2) {
                    if ($ev2->event_type_id != $workdayEventType->id) continue;
                    $evStart2 = Carbon::parse($ev2->start)->max($dayStart);
                    $evEnd2 = Carbon::parse($ev2->end)->min($dayEnd);
                    // intersección con el slot
                    $intStart = $evStart2->max($slotStart);
                    $intEnd = $evEnd2->min($slotEnd);
                    $intSeconds = max(0, $intEnd->diffInSeconds($intStart));
                    $dayWithinScheduledSeconds += $intSeconds;

                    if ($intSeconds > $bestIntersectionSeconds) {
                        $bestIntersectionSeconds = $intSeconds;
                        $bestEventForSlot = $ev2;
                    }
                }

                // Si encontramos un evento principal para esta franja, calcular desviaciones
                if ($bestEventForSlot) {
                    try {
                        $eventStart = Carbon::parse($bestEventForSlot->start);
                        $eventEnd = Carbon::parse($bestEventForSlot->end);
                        $createdAt = Carbon::parse($bestEventForSlot->created_at);
                        $updatedAt = Carbon::parse($bestEventForSlot->updated_at);

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

            if ($dayWorkSeconds > 0) {
                $dailyWorked[] = $date->format('Y-m-d');
            }

            // registeredHours ya se calculó antes del loop (total del mes)
            $registeredWithinScheduledSeconds += $dayWithinScheduledSeconds;
            $extraSeconds += $dayOutsideSeconds;
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

        // Horas extra: todas las horas de jornada que están fuera del slot en días con registros
        $extra_hours = round($extraSeconds / 3600, 2);

        $scheduleMeta = $user->meta->where('meta_key', 'work_schedule')->first();
        $schedule = $scheduleMeta ? json_decode($scheduleMeta->meta_value, true) : [];
        $punctualDays = 0;
        $absentDays = 0;
        // Los días trabajados se calculan a partir de la iteración diaria anterior
        $workedDays = collect($dailyWorked);

        $startDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $holidays = $this->actualUser->currentTeam->holidays()
            ->whereBetween('date', [$startDate, $endDate])
            ->pluck('date')
            ->map(fn ($date) => $date->format('Y-m-d'));

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if ($holidays->contains($date->format('Y-m-d'))) {
                continue;
            }

            $dayInitial = $this->getDayInitial($date->format('N'));
            $daySchedule = collect($schedule)->first(function ($slot) use ($dayInitial) {
                return in_array($dayInitial, $slot['days']);
            });

            if ($daySchedule) {
                $isWorked = $workedDays->contains($date->format('Y-m-d'));

                if (!$isWorked) {
                    $absentDays++;
                } else {
                    // Buscar el primer evento del día entre los eventos de jornada laboral
                    $firstEvent = $workdayEvents->first(function ($event) use ($date) {
                        return Carbon::parse($event->start)->isSameDay($date);
                    });

                    if ($firstEvent) {
                        $scheduledStartTime = Carbon::parse($date->format('Y-m-d') . ' ' . $daySchedule['start']);
                        $actualStartTime = Carbon::parse($firstEvent->start);
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
            $start = Carbon::parse($event->start);
            $end = Carbon::parse($event->end);
            $createdAt = Carbon::parse($event->created_at);
            $updatedAt = Carbon::parse($event->updated_at);

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

        // Calcular mediana de porcentajes por franja para reducir impacto de outliers
        $median = function (array $a) {
            if (empty($a)) return 0;
            sort($a);
            $count = count($a);
            $mid = intdiv($count, 2);
            if ($count % 2 === 1) {
                return $a[$mid];
            }
            return ($a[$mid - 1] + $a[$mid]) / 2.0;
        };

        $avgEntryPct = !empty($entryPctList) ? round($median($entryPctList) * 100, 2) : 0;
        $avgExitPct = !empty($exitPctList) ? round($median($exitPctList) * 100, 2) : 0;

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
