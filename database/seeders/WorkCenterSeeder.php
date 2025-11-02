<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WorkCenterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $team = \App\Models\Team::first();

        if ($team) {
            $team->workCenters()->firstOrCreate(
                ['code' => 'OC-001'],
                [
                    'name' => 'Oficina Central',
                    'address' => 'Calle Principal 123',
                    'city' => 'Ciudad Principal',
                    'postal_code' => '12345',
                    'state' => 'Provincia Principal',
                    'country' => 'País Principal',
                ]
            );

            $team->workCenters()->firstOrCreate(
                ['code' => 'SS-002'],
                [
                    'name' => 'Sede Secundaria',
                    'address' => 'Avenida Secundaria 456',
                    'city' => 'Ciudad Secundaria',
                    'postal_code' => '67890',
                    'state' => 'Provincia Secundaria',
                    'country' => 'País Secundario',
                ]
            );
        }
    }
}
