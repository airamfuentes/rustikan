<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@rustikan.com'],
            [
                'name'              => 'Admin Rustikan',
                'password'          => bcrypt('password'),
                'role'              => 'admin',
                'telefono'          => '928123456',
                'direccion'         => 'Arrecife, Lanzarote',
                'email_verified_at' => now(),
            ]
        );

        // Create some regular users
        \App\Models\User::firstOrCreate(
            ['email' => 'juan@example.com'],
            [
                'name'              => 'Juan García',
                'password'          => bcrypt('password'),
                'role'              => 'user',
                'telefono'          => '928234567',
                'direccion'         => 'Teguise, Lanzarote',
                'email_verified_at' => now(),
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'maria@example.com'],
            [
                'name'              => 'María López',
                'password'          => bcrypt('password'),
                'role'              => 'user',
                'telefono'          => '928345678',
                'direccion'         => 'Puerto del Carmen, Lanzarote',
                'email_verified_at' => now(),
            ]
        );

        // Call other seeders
        $this->call([
            CategoriaSeeder::class,
            TiendaSeeder::class,
            ProductoSeeder::class,
            DemoUsersSeeder::class,
            DemoTiendasSeeder::class,
            DemoProductosSeeder::class,
            DemoResenasSeeder::class,
        ]);
    }
}
