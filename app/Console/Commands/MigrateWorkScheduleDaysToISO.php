<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserMeta;

class MigrateWorkScheduleDaysToISO extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:migrate-to-iso';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra los días del horario laboral de abreviaturas españolas (L,M,X,J,V,S,D) a números ISO (1-7)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando migración de horarios laborales a formato ISO...');
        $this->info('Este comando es idempotente y puede ejecutarse múltiples veces de forma segura.');
        $this->newLine();

        // Mapeo de abreviaturas españolas a números ISO
        $dayMap = [
            'L' => 1, // Lunes
            'M' => 2, // Martes
            'X' => 3, // Miércoles
            'J' => 4, // Jueves
            'V' => 5, // Viernes
            'S' => 6, // Sábado
            'D' => 7  // Domingo
        ];

        $schedules = UserMeta::where('meta_key', 'work_schedule')->get();
        
        if ($schedules->isEmpty()) {
            $this->warn('No se encontraron horarios laborales para migrar.');
            return Command::SUCCESS;
        }

        $this->info("Encontrados {$schedules->count()} horarios para procesar...");
        $this->newLine();

        $migratedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        $progressBar = $this->output->createProgressBar($schedules->count());
        $progressBar->start();

        foreach ($schedules as $scheduleMeta) {
            try {
                $schedule = json_decode($scheduleMeta->meta_value, true);
                
                // Validar que el JSON se decodificó correctamente
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->newLine();
                    $this->error("Usuario {$scheduleMeta->user_id}: JSON inválido - " . json_last_error_msg());
                    $errorCount++;
                    $progressBar->advance();
                    continue;
                }

                // Validar que es un array
                if (!is_array($schedule)) {
                    $this->newLine();
                    $this->warn("Usuario {$scheduleMeta->user_id}: formato de horario inválido (no es array), saltando...");
                    $skippedCount++;
                    $progressBar->advance();
                    continue;
                }

                // Validar que no está vacío
                if (empty($schedule)) {
                    $this->newLine();
                    $this->warn("Usuario {$scheduleMeta->user_id}: horario vacío, saltando...");
                    $skippedCount++;
                    $progressBar->advance();
                    continue;
                }

                $modified = false;

                foreach ($schedule as &$slot) {
                    // Validar que cada slot es un array
                    if (!is_array($slot)) {
                        continue;
                    }

                    if (isset($slot['days']) && is_array($slot['days'])) {
                        $newDays = [];
                        $slotModified = false;
                        
                        foreach ($slot['days'] as $day) {
                            // Si ya es un número válido (1-7), mantenerlo
                            if (is_numeric($day)) {
                                $dayInt = (int)$day;
                                if ($dayInt >= 1 && $dayInt <= 7) {
                                    $newDays[] = $dayInt;
                                } else {
                                    // Número fuera de rango, intentar mapear si es letra
                                    if (is_string($day) && isset($dayMap[strtoupper($day)])) {
                                        $newDays[] = $dayMap[strtoupper($day)];
                                        $slotModified = true;
                                    }
                                }
                            }
                            // Si es una letra (string), convertirla
                            elseif (is_string($day)) {
                                $dayUpper = strtoupper(trim($day));
                                if (isset($dayMap[$dayUpper])) {
                                    $newDays[] = $dayMap[$dayUpper];
                                    $slotModified = true;
                                } else {
                                    // Letra no reconocida, mantenerla para no perder datos
                                    $newDays[] = $day;
                                }
                            }
                            // Tipo no reconocido, mantenerlo
                            else {
                                $newDays[] = $day;
                            }
                        }
                        
                        // Eliminar duplicados y ordenar
                        $newDays = array_unique($newDays);
                        sort($newDays);
                        
                        $slot['days'] = $newDays;
                        
                        if ($slotModified) {
                            $modified = true;
                        }
                    }
                }

                if ($modified) {
                    // Validar que el JSON se puede codificar correctamente
                    $newJson = json_encode($schedule);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $this->newLine();
                        $this->error("Usuario {$scheduleMeta->user_id}: error al codificar JSON - " . json_last_error_msg());
                        $errorCount++;
                        $progressBar->advance();
                        continue;
                    }

                    $scheduleMeta->meta_value = $newJson;
                    $scheduleMeta->save();
                    $migratedCount++;
                } else {
                    $skippedCount++;
                }

            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Usuario {$scheduleMeta->user_id}: error inesperado - {$e->getMessage()}");
                $errorCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Resumen final
        $this->info('=== Resumen de migración ===');
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Total procesados', $schedules->count()],
                ['✓ Migrados exitosamente', $migratedCount],
                ['○ Ya en formato ISO (saltados)', $skippedCount],
                ['✗ Errores', $errorCount],
            ]
        );

        if ($migratedCount > 0) {
            $this->info("\n✓ Migración completada exitosamente. {$migratedCount} horarios actualizados.");
        }

        if ($errorCount > 0) {
            $this->warn("\n⚠ Se encontraron {$errorCount} errores. Revisa los mensajes anteriores.");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
