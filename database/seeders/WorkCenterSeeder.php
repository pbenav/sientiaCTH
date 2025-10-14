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
        ]);

        $team->workCenters()->create([
            'name' => 'Sede Secundaria',
            'code' => 'SS-002',
            'address' => 'Avenida Secundaria 456',
        ]);
    }
}
