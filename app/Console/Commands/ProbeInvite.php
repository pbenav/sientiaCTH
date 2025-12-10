<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProbeInvite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'probe:invite {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Provoca una invitación de equipo hacia el email dado para pruebas de logging';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->argument('email');

        try {
            $owner = \App\Models\User::whereHas('ownedTeams')->first();

            if (! $owner) {
                $this->error('No se ha encontrado ningún usuario con equipos propios.');
                return 1;
            }

            $team = $owner->ownedTeams()->first();

            if (! $team) {
                $this->error('El usuario propietario no tiene equipos.');
                return 1;
            }

            // Determinar role si Jetstream tiene roles configuradas
            $role = null;
            $jetRoles = config('jetstream.roles');
            if (is_array($jetRoles) && count($jetRoles) > 0) {
                $keys = array_keys($jetRoles);
                $role = $keys[0];
            } else {
                // Usar role por defecto común en este proyecto
                $role = 'user';
            }

            // Ejecutar la acción de invitación
            app(\App\Actions\Jetstream\InviteTeamMember::class)->invite($owner, $team, $email, $role);

            $this->info("Invitación creada/intento enviado desde user_id={$owner->id} a team_id={$team->id} hacia {$email}");

            return 0;
        } catch (\Exception $e) {
            $this->error('Excepción durante la invitación: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
}
