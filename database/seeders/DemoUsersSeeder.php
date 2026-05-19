<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        // ── OWNERS ──────────────────────────────────────────────────────
        $owners = [
            ['name' => 'Domingo', 'apellidos' => 'Perdomo Cabrera',    'email' => 'domingo@finca-el-nido.es',      'telefono' => '628100201', 'direccion' => 'C/ Volcán 12, Tinajo, Lanzarote'],
            ['name' => 'María',   'apellidos' => 'Hernández Curbelo',  'email' => 'maria@huerta-jameos.es',        'telefono' => '628100202', 'direccion' => 'Camino de Los Jameos 3, Haría, Lanzarote'],
            ['name' => 'Antonio', 'apellidos' => 'Betancor Suárez',    'email' => 'antonio@bodega-geria.es',       'telefono' => '628100203', 'direccion' => 'La Geria s/n, Yaiza, Lanzarote'],
            ['name' => 'Carmen',  'apellidos' => 'Reyes Padilla',      'email' => 'carmen@quesos-majorero.es',     'telefono' => '628100204', 'direccion' => 'C/ Real 45, Teguise, Lanzarote'],
            ['name' => 'Sergio',  'apellidos' => 'Acuña Toledo',       'email' => 'sergio@pescados-arrecife.es',   'telefono' => '628100205', 'direccion' => 'Muelle Comercial, Arrecife, Lanzarote'],
            ['name' => 'Lucía',   'apellidos' => 'Pérez Cabrera',      'email' => 'lucia@panaderia-norte.es',      'telefono' => '628100206', 'direccion' => 'C/ Iglesia 8, Haría, Lanzarote'],
            ['name' => 'Javier',  'apellidos' => 'Morales Rivero',     'email' => 'javier@artesanos-lanzarote.es', 'telefono' => '628100207', 'direccion' => 'C/ César Manrique 22, Teguise, Lanzarote'],
        ];

        foreach ($owners as $i => $o) {
            $seed   = urlencode($o['name'] . ' ' . $o['apellidos']);
            $avatar = "https://api.dicebear.com/7.x/adventurer/svg?seed={$seed}";
            User::updateOrCreate(
                ['email' => $o['email']],
                [
                    'name'              => $o['name'],
                    'apellidos'         => $o['apellidos'],
                    'password'          => Hash::make('password'),
                    'role'              => 'owner',
                    'telefono'          => $o['telefono'],
                    'direccion'         => $o['direccion'],
                    'fecha_nacimiento'  => Carbon::now()->subYears(35 + $i)->subMonths(rand(0, 11))->toDateString(),
                    'avatar'            => $avatar,
                    'rusticoin_balance' => 0,
                    'email_verified_at' => now(),
                ]
            );
        }

        // ── SUPPLIER ─────────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'almacen@rustikan.com'],
            [
                'name'              => 'Almacén Central',
                'apellidos'         => 'Rustikan',
                'password'          => Hash::make('password'),
                'role'              => 'supplier',
                'telefono'          => '928800100',
                'direccion'         => 'Polígono Industrial Argana Alta, Arrecife, Lanzarote',
                'avatar'            => 'https://api.dicebear.com/7.x/shapes/svg?seed=rustikan-almacen',
                'rusticoin_balance' => 0,
                'email_verified_at' => now(),
            ]
        );

        // ── 50 CLIENTES ───────────────────────────────────────────────────
        $nombres = [
            'Aitana', 'Adrián', 'Alba', 'Alejandro', 'Andrea', 'Ángel', 'Antonio', 'Aroa',
            'Beatriz', 'Borja', 'Carlos', 'Carla', 'Cristina', 'Daniel', 'David', 'Diego',
            'Elena', 'Emma', 'Estela', 'Fátima', 'Fernando', 'Gabriel', 'Gloria', 'Héctor',
            'Iván', 'Inés', 'Isabel', 'Jaime', 'Jorge', 'Julia', 'Laura', 'Lorena',
            'Lucas', 'Manuel', 'Marcos', 'Marina', 'Martín', 'Miguel', 'Nerea', 'Noelia',
            'Óscar', 'Pablo', 'Patricia', 'Paula', 'Raquel', 'Rubén', 'Sara', 'Sergio',
            'Sofía', 'Tania',
        ];

        $apellidos1 = ['Cabrera', 'Hernández', 'Pérez', 'Curbelo', 'Betancor', 'Reyes', 'Acuña',
                       'Padilla', 'Toledo', 'Rivero', 'Morales', 'Suárez', 'Perdomo', 'González',
                       'Rodríguez', 'Martín', 'Santana', 'Marrero', 'Bonilla', 'Brito'];
        $apellidos2 = ['Cabrera', 'Hernández', 'Pérez', 'Curbelo', 'Betancor', 'Reyes', 'Acuña',
                       'Padilla', 'Toledo', 'Rivero', 'Morales', 'Suárez', 'Perdomo', 'González',
                       'Rodríguez', 'Martín', 'Santana', 'Marrero', 'Bonilla', 'Brito'];

        $municipios = ['Arrecife', 'Teguise', 'San Bartolomé', 'Tías', 'Yaiza', 'Tinajo', 'Haría'];

        $calles = [
            'C/ La Marina %d', 'C/ César Manrique %d', 'Av. Marítima %d', 'C/ Real %d',
            'C/ El Charco %d', 'C/ La Iglesia %d', 'C/ El Jable %d', 'C/ Volcán %d',
            'C/ La Geria %d', 'C/ Punta Mujeres %d',
        ];

        foreach ($nombres as $idx => $nombre) {
            $apellidos = $apellidos1[$idx % count($apellidos1)] . ' ' . $apellidos2[($idx + 5) % count($apellidos2)];
            $emailSlug = Str::slug($nombre . '.' . explode(' ', $apellidos)[0]);
            $email     = "{$emailSlug}.{$idx}@rustikan-demo.es";
            $municipio = $municipios[$idx % count($municipios)];
            $calle     = sprintf($calles[$idx % count($calles)], 5 + ($idx * 7) % 90);
            $tel       = '6' . str_pad((string)(28000000 + $idx * 13), 8, '0', STR_PAD_LEFT);
            $balance   = [0, 0, 0, 5.50, 12.00, 25.00, 50.00, 100.00][$idx % 8];

            $seed   = urlencode($nombre . ' ' . $apellidos);
            $style  = ['adventurer', 'micah', 'personas', 'avataaars', 'lorelei'][$idx % 5];
            $avatar = "https://api.dicebear.com/7.x/{$style}/svg?seed={$seed}";

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name'              => $nombre,
                    'apellidos'         => $apellidos,
                    'password'          => Hash::make('password'),
                    'role'              => 'user',
                    'telefono'          => $tel,
                    'direccion'         => "{$calle}, {$municipio}, Lanzarote",
                    'fecha_nacimiento'  => Carbon::now()->subYears(20 + ($idx % 35))->subMonths(($idx * 3) % 12)->toDateString(),
                    'avatar'            => $avatar,
                    'rusticoin_balance' => $balance,
                    'email_verified_at' => now(),
                ]
            );
        }

        $this->command?->info('DemoUsersSeeder: 50 clientes + 7 owners + 1 supplier listos. Contraseña: "password".');
    }
}
