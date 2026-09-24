<?php

namespace Database\Seeders;

use App\Models\Zona;
use Illuminate\Database\Seeder;

class ZonaSeeder extends Seeder
{
    public function run(): void
    {
        $zonas = [
            ['codigo_zona' => 'Norte', 'descripcion' => 'Habitaciones en el ala norte del hotel'],
            ['codigo_zona' => 'Sur',   'descripcion' => 'Habitaciones en el ala sur del hotel'],
            ['codigo_zona' => 'VIP',   'descripcion' => 'Suites y habitaciones premium'],
        ];

        foreach ($zonas as $zona) {
            Zona::firstOrCreate(['codigo_zona' => $zona['codigo_zona']], $zona);
        }
    }
}
