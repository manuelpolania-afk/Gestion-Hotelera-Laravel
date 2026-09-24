<?php

namespace Database\Seeders;

use App\Models\Habitacion;
use App\Models\Zona;
use Illuminate\Database\Seeder;

class HabitacionSeeder extends Seeder
{
    public function run(): void
    {
        $norte = Zona::where('codigo_zona', 'Norte')->first();
        $sur   = Zona::where('codigo_zona', 'Sur')->first();
        $vip   = Zona::where('codigo_zona', 'VIP')->first();

        $habitaciones = [
            [
                'zona_id'          => $norte->id,
                'nombre_habitacion' => 'Habitación 101',
                'capacidad'        => 2,
                'valor'            => 80000,
                'estado'           => 'Disponible',
            ],
            [
                'zona_id'          => $norte->id,
                'nombre_habitacion' => 'Habitación 102',
                'capacidad'        => 3,
                'valor'            => 110000,
                'estado'           => 'Disponible',
            ],
            [
                'zona_id'          => $sur->id,
                'nombre_habitacion' => 'Habitación 201',
                'capacidad'        => 2,
                'valor'            => 90000,
                'estado'           => 'Disponible',
            ],
            [
                'zona_id'          => $vip->id,
                'nombre_habitacion' => 'Suite Presidencial',
                'capacidad'        => 4,
                'valor'            => 350000,
                'estado'           => 'Disponible',
            ],
        ];

        foreach ($habitaciones as $data) {
            Habitacion::firstOrCreate(
                ['nombre_habitacion' => $data['nombre_habitacion']],
                $data
            );
        }
    }
}
