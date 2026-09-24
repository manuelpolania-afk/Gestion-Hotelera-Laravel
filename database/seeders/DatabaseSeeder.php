<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\HabitacionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ZonaSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ZonaSeeder::class,
            HabitacionSeeder::class,
        ]);

        // User::factory(10)->create();

        User::factory()->create([
            'name'      => 'Test User',
            'email'     => 'test@example.com',
            'documento' => '0000000000',
            'telefono'  => '0000000000',
            'role_id'   => Role::where('nombre', 'Administrador')->first()->id,
            'estado'    => 'activo',
        ]);
    }
}
