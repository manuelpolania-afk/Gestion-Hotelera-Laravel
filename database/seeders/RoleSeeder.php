<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['descripcion' => 'Acceso total al sistema']
        );

        Role::firstOrCreate(
            ['nombre' => 'Cliente'],
            ['descripcion' => 'Usuario que realiza reservas']
        );
    }
}
