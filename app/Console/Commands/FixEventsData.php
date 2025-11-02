<?php

namespace App\Console\Commands;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixEventsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:fix-data 
                            {--dry-run : Solo analizar sin aplicar cambios}
                            {--user= : ID de usuario específico para analizar}
                            {--from= : Fecha desde (Y-m-d)}
                            {--to= : Fecha hasta (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analiza y corrige eventos con problemas: sin end, sin event_type_id, start > end';

    protected $issues = [
        'missing_end' => [],
        'missing_type' => [],
        'invalid_dates' => [],
    ];

    protected $fixed = [
        'missing_end' => 0,
        'missing_type' => 0,
        'invalid_dates' => 0,
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $userId = $this->option('user');
        $from = $this->option('from');
        $to = $this->option('to');

        $this->info('=== Analizando eventos con problemas ===');
        if ($isDryRun) {
            $this->warn('MODO DRY-RUN: No se aplicarán cambios');
        }

        // Construir query base
        $query = Event::query();

        if ($userId) {
            $query->where('user_id', $userId);
            $this->info("Filtrando por usuario ID: {$userId}");
        }

        if ($from) {
            $query->where('start', '>=', $from);
            $this->info("Filtrando desde: {$from}");
        }

        if ($to) {
            $query->where('start', '<=', $to);
            $this->info("Filtrando hasta: {$to}");
        }

        $events = $query->get();
        $this->info("Total de eventos a analizar: {$events->count()}");
        $this->newLine();

        // Analizar eventos
        $bar = $this->output->createProgressBar($events->count());
        $bar->start();

        foreach ($events as $event) {
            $this->analyzeEvent($event, $isDryRun);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Mostrar resultados
        $this->displayResults($isDryRun);

        return 0;
    }

    protected function analyzeEvent(Event $event, bool $isDryRun)
    {
        $hasIssues = false;

        // 1. Eventos sin end
        if (empty($event->end)) {
            $this->issues['missing_end'][] = [
                'id' => $event->id,
                'user_id' => $event->user_id,
                'start' => $event->start,
                'type' => $event->eventType->description ?? 'Sin tipo',
            ];

            if (!$isDryRun) {
                $this->fixMissingEnd($event);
            }
            $hasIssues = true;
        }

        // 2. Eventos sin event_type_id
        if (empty($event->event_type_id)) {
            $this->issues['missing_type'][] = [
                'id' => $event->id,
                'user_id' => $event->user_id,
                'start' => $event->start,
                'end' => $event->end,
            ];

            if (!$isDryRun) {
                $this->fixMissingType($event);
            }
            $hasIssues = true;
        }

        // 3. Eventos con start > end
        if (!empty($event->start) && !empty($event->end)) {
            $start = Carbon::parse($event->start);
            $end = Carbon::parse($event->end);

            if ($start->gt($end)) {
                $this->issues['invalid_dates'][] = [
                    'id' => $event->id,
                    'user_id' => $event->user_id,
                    'start' => $event->start,
                    'end' => $event->end,
                    'diff_hours' => $start->diffInHours($end),
                ];

                if (!$isDryRun) {
                    $this->fixInvalidDates($event);
                }
                $hasIssues = true;
            }
        }
    }

    protected function fixMissingEnd(Event $event)
    {
        try {
            DB::beginTransaction();

            // Establecer end = start + 8 horas (jornada típica)
            $start = Carbon::parse($event->start);
            $end = $start->copy()->addHours(8);

            $oldEnd = $event->end;
            $event->end = $end->format('Y-m-d H:i:s');
            $event->save();

            $this->fixed['missing_end']++;

            Log::info('Evento sin end corregido', [
                'event_id' => $event->id,
                'user_id' => $event->user_id,
                'start' => $event->start,
                'old_end' => $oldEnd,
                'new_end' => $event->end,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al corregir evento sin end', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function fixMissingType(Event $event)
    {
        try {
            DB::beginTransaction();

            // Buscar el tipo por defecto (normalmente id=1 es jornada laboral)
            // O el primer tipo que tenga is_workday_type=true
            $defaultType = DB::table('event_types')
                ->where('is_workday_type', true)
                ->orderBy('id')
                ->first();

            if (!$defaultType) {
                $defaultType = DB::table('event_types')->orderBy('id')->first();
            }

            if ($defaultType) {
                $oldTypeId = $event->event_type_id;
                $event->event_type_id = $defaultType->id;
                $event->save();

                $this->fixed['missing_type']++;

                Log::info('Evento sin tipo corregido', [
                    'event_id' => $event->id,
                    'user_id' => $event->user_id,
                    'old_type_id' => $oldTypeId,
                    'new_type_id' => $event->event_type_id,
                    'type_description' => $defaultType->description,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al corregir evento sin tipo', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function fixInvalidDates(Event $event)
    {
        try {
            DB::beginTransaction();

            $start = Carbon::parse($event->start);
            $end = Carbon::parse($event->end);

            // Intercambiar start y end
            $oldStart = $event->start;
            $oldEnd = $event->end;

            $event->start = $end->format('Y-m-d H:i:s');
            $event->end = $start->format('Y-m-d H:i:s');
            $event->save();

            $this->fixed['invalid_dates']++;

            Log::info('Evento con fechas inválidas corregido', [
                'event_id' => $event->id,
                'user_id' => $event->user_id,
                'old_start' => $oldStart,
                'old_end' => $oldEnd,
                'new_start' => $event->start,
                'new_end' => $event->end,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al corregir evento con fechas inválidas', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function displayResults(bool $isDryRun)
    {
        $this->info('=== RESULTADOS DEL ANÁLISIS ===');
        $this->newLine();

        // Eventos sin end
        $count = count($this->issues['missing_end']);
        if ($count > 0) {
            $this->warn("❌ Eventos SIN END encontrados: {$count}");
            if ($isDryRun) {
                $this->table(
                    ['ID', 'Usuario', 'Start', 'Tipo'],
                    array_slice($this->issues['missing_end'], 0, 10)
                );
                if ($count > 10) {
                    $this->line("... y " . ($count - 10) . " más");
                }
            } else {
                $this->info("✓ Corregidos: {$this->fixed['missing_end']}");
            }
            $this->newLine();
        } else {
            $this->info("✓ No se encontraron eventos sin END");
        }

        // Eventos sin tipo
        $count = count($this->issues['missing_type']);
        if ($count > 0) {
            $this->warn("❌ Eventos SIN EVENT_TYPE_ID encontrados: {$count}");
            if ($isDryRun) {
                $this->table(
                    ['ID', 'Usuario', 'Start', 'End'],
                    array_slice($this->issues['missing_type'], 0, 10)
                );
                if ($count > 10) {
                    $this->line("... y " . ($count - 10) . " más");
                }
            } else {
                $this->info("✓ Corregidos: {$this->fixed['missing_type']}");
            }
            $this->newLine();
        } else {
            $this->info("✓ No se encontraron eventos sin tipo");
        }

        // Eventos con fechas inválidas
        $count = count($this->issues['invalid_dates']);
        if ($count > 0) {
            $this->warn("❌ Eventos con START > END encontrados: {$count}");
            if ($isDryRun) {
                $this->table(
                    ['ID', 'Usuario', 'Start', 'End', 'Diff (h)'],
                    array_slice($this->issues['invalid_dates'], 0, 10)
                );
                if ($count > 10) {
                    $this->line("... y " . ($count - 10) . " más");
                }
            } else {
                $this->info("✓ Corregidos: {$this->fixed['invalid_dates']}");
            }
            $this->newLine();
        } else {
            $this->info("✓ No se encontraron eventos con fechas inválidas");
        }

        // Resumen
        $totalIssues = count($this->issues['missing_end']) + 
                       count($this->issues['missing_type']) + 
                       count($this->issues['invalid_dates']);

        $totalFixed = $this->fixed['missing_end'] + 
                      $this->fixed['missing_type'] + 
                      $this->fixed['invalid_dates'];

        $this->newLine();
        if ($isDryRun) {
            $this->warn("Total de problemas encontrados: {$totalIssues}");
            $this->info("Ejecuta el comando sin --dry-run para aplicar las correcciones.");
        } else {
            $this->info("Total de problemas corregidos: {$totalFixed}");
            $this->info("Los cambios se han registrado en el log de Laravel.");
        }
    }
}
