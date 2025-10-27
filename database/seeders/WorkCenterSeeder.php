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

        $team->workCenters()->create([
            'name' => 'Oficina Central',
            'code' => 'OC-001',
            'address' => 'Calle Principal 123',
            'city' => 'Ciudad Principal',
            'postal_code' => '12345',
            'state' => 'Provincia Principal',
            'country' => 'País Principal',
        ]);

        $team->workCenters()->create([
            'name' => 'Sede Secundaria',
            'code' => 'SS-002',
            'address' => 'Avenida Secundaria 456',
            'city' => 'Ciudad Secundaria',
            'postal_code' => '67890',
            'state' => 'Provincia Secundaria',
            'country' => 'País Secundario',
        ]);
    }
}
