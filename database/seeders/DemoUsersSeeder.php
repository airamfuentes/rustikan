<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Carga 50 usuarios "cliente final" + 7 propietarios + 1 supplier de prueba.
 * Datos pensados para Lanzarote: nombres españoles/canarios, teléfonos +34,
 * direcciones en los 7 municipios de la isla. Avatares vía UI Avatars (no se
 * descargan a storage; se almacena la URL completa, que el front pinta tal cual).
 *
 * Idempotente: usa firstOrCreate por email.
 *
 * Contraseña por defecto para TODOS: "password"
 */
class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        // ───────────────────────────────────────────────────────────────
        // OWNERS (propietarios de tienda)
        // ───────────────────────────────────────────────────────────────
        $owners = [
            ['name' => 'Domingo', 'apellidos' => 'Perdomo Cabrera',    'email' => 'domingo@finca-el-nido.es',     'telefono' => '628100201', 'direccion' => 'C/ Volcán 12, Tinajo, Lanzarote'],
            ['name' => 'María',   'apellidos' => 'Hernández Curbelo',  'email' => 'maria@huerta-jameos.es',       'telefono' => '628100202', 'direccion' => 'Camino de Los Jameos 3, Haría, Lanzarote'],
            ['name' => 'Antonio', 'apellidos' => 'Betancor Suárez',    'email' => 'antonio@bodega-geria.es',      'telefono' => '628100203', 'direccion' => 'La Geria s/n, Yaiza, Lanzarote'],
            ['name' => 'Carmen',  'apellidos' => 'Reyes Padilla',      'email' => 'carmen@quesos-majorero.es',    'telefono' => '628100204', 'direccion' => 'C/ Real 45, Teguise, Lanzarote'],
            ['name' => 'Sergio',  'apellidos' => 'Acuña Toledo',       'email' => 'sergio@pescados-arrecife.es',  'telefono' => '628100205', 'direccion' => 'Muelle Comercial, Arrecife, Lanzarote'],
            ['name' => 'Lucía',   'apellidos' => 'Pérez Cabrera',      'email' => 'lucia@panaderia-norte.es',     'telefono' => '628100206', 'direccion' => 'C/ Iglesia 8, Haría, Lanzarote'],
            ['name' => 'Javier',  'apellidos' => 'Morales Rivero',     'email' => 'javier@artesanos-lanzarote.es','telefono' => '628100207', 'direccion' => 'C/ César Manrique 22, Teguise, Lanzarote'],
        ];

        foreach ($owners as $i => $o) {
            User::firstOrCreate(
                ['email' => $o['email']],
                [
                    'name'              => $o['name'],
                    'apellidos'         => $o['apellidos'],
                    'password'          => Hash::make('password'),
                    'role'              => 'owner',
                    'telefono'          => $o['telefono'],
                    'direccion'         => $o['direccion'],
                    'fecha_nacimiento'  => Carbon::now()->subYears(35 + $i)->subMonths(rand(0, 11))->toDateString(),
                    'avatar'            => $this->avatarUrl($o['name'].' '.$o['apellidos'], 'ea580c'),
                    'rusticoin_balance' => 0,
                    'email_verified_at' => now(),
                ]
            );
        }

        // ───────────────────────────────────────────────────────────────
        // SUPPLIER (almacén)
        // ───────────────────────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'almacen@rustikan.com'],
            [
                'name'              => 'Almacén Central',
                'apellidos'         => 'Rustikan',
                'password'          => Hash::make('password'),
                'role'              => 'supplier',
                'telefono'          => '928800100',
                'direccion'         => 'Polígono Industrial Argana Alta, Arrecife, Lanzarote',
                'avatar'            => $this->avatarUrl('Almacén Central', '0ea5e9'),
                'rusticoin_balance' => 0,
                'email_verified_at' => now(),
            ]
        );

        // ───────────────────────────────────────────────────────────────
        // 50 CLIENTES (rol user)
        // ───────────────────────────────────────────────────────────────
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

        $municipios = [
            'Arrecife', 'Teguise', 'San Bartolomé', 'Tías', 'Yaiza', 'Tinajo', 'Haría',
        ];

        $callesPlantilla = [
            'C/ La Marina %d', 'C/ César Manrique %d', 'Av. Marítima %d', 'C/ Real %d',
            'C/ El Charco %d', 'C/ La Iglesia %d', 'C/ El Jable %d', 'C/ Volcán %d',
            'C/ La Geria %d', 'C/ Punta Mujeres %d',
        ];

        // Avatares: usamos UI Avatars (color de fondo por hash del nombre).
        $colores = ['f97316', 'ea580c', '0ea5e9', '10b981', '6366f1', 'a855f7', 'ef4444', 'f59e0b'];

        foreach ($nombres as $idx => $nombre) {
            $apellidos = $apellidos1[$idx % count($apellidos1)] . ' ' . $apellidos2[($idx + 5) % count($apellidos2)];
            $emailSlug = Str::slug($nombre . '.' . explode(' ', $apellidos)[0]);
            $email     = "{$emailSlug}.{$idx}@rustikan-demo.es";

            $municipio = $municipios[$idx % count($municipios)];
            $calle     = sprintf($callesPlantilla[$idx % count($callesPlantilla)], 5 + ($idx * 7) % 90);
            $tel       = '6' . str_pad((string)(28000000 + $idx * 13), 8, '0', STR_PAD_LEFT);

            // Saldo aleatorio para que el monedero RustiCoin tenga vida.
            $balance = [0, 0, 0, 5.50, 12.00, 25.00, 50.00, 100.00][$idx % 8];

            User::firstOrCreate(
                ['email' => $email],
                [
                    'name'              => $nombre,
                    'apellidos'         => $apellidos,
                    'password'          => Hash::make('password'),
                    'role'              => 'user',
                    'telefono'          => $tel,
                    'direccion'         => "{$calle}, {$municipio}, Lanzarote",
                    'fecha_nacimiento'  => Carbon::now()->subYears(20 + ($idx % 35))->subMonths(($idx * 3) % 12)->toDateString(),
                    'avatar'            => $this->avatarUrl($nombre . ' ' . $apellidos, $colores[$idx % count($colores)]),
                    'rusticoin_balance' => $balance,
                    'email_verified_at' => now(),
                ]
            );
        }

        $this->command?->info('DemoUsersSeeder: 50 clientes + 7 owners + 1 supplier listos. Contraseña para todos: "password".');
    }

    /**
     * Genera URL de DiceBear Avataaars (avatares tipo bitmoji, sin descarga).
     * Seed determinista por nombre → mismo nombre = mismo avatar siempre.
     */
    private function avatarUrl(string $name, string $bg = 'ea580c'): string
    {
        $seed = urlencode(str_replace(' ', '', $name));
        return "https://api.dicebear.com/7.x/avataaars/svg?seed={$seed}&backgroundColor={$bg}&radius=50";
    }
}
