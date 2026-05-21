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
        // ── OWNERS (20) ──────────────────────────────────────────────────
        $firstNames = ['Domingo','María','Antonio','Carmen','Sergio','Lucía','Javier','Ana','Pablo','Isabel','Miguel','Laura','Carlos','Raquel','Rubén','Nerea','Diego','Marta','Fernando','Sara'];
        $lastNames1 = ['Perdomo','Hernández','Betancor','Reyes','Acuña','Pérez','Morales','González','Rodríguez','Santana'];
        $lastNames2 = ['Cabrera','Curbelo','Suárez','Padilla','Toledo','Rivero','Rivera','Martín','Marrero','Brito'];

        foreach (range(0, 19) as $i) {
            $name = $firstNames[$i % count($firstNames)];
            $ap1  = $lastNames1[$i % count($lastNames1)];
            $ap2  = $lastNames2[($i + 3) % count($lastNames2)];
            $apellidos = $ap1 . ' ' . $ap2;
            $email = Str::slug($name . '.' . explode(' ', $apellidos)[0]) . ".owner{$i}@rustikan-demo.es";
            $telefono = '6' . str_pad((string)(28000000 + $i), 8, '0', STR_PAD_LEFT);
            $direccion = 'C/ Demo ' . (10 + $i) . ', Lanzarote';

            $seed   = urlencode($name . ' ' . $apellidos);
            $avatar = "https://api.dicebear.com/7.x/adventurer/svg?seed={$seed}";

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name'              => $name,
                    'apellidos'         => $apellidos,
                    'password'          => Hash::make('password'),
                    'role'              => 'owner',
                    'telefono'          => $telefono,
                    'direccion'         => $direccion,
                    'fecha_nacimiento'  => Carbon::now()->subYears(30 + ($i % 20))->subMonths(rand(0, 11))->toDateString(),
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
