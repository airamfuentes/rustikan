<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Renombrar "Vinos y Bebidas" → "Vinoteca"
        DB::table('categorias')
            ->where('slug', 'vinos-y-bebidas')
            ->update([
                'nombre' => 'Vinoteca',
                'slug'   => 'vinoteca',
                'icono'  => 'vinoteca',
            ]);

        // Eliminar "Conservas y Mermeladas"
        DB::table('categorias')
            ->where('slug', 'conservas-y-mermeladas')
            ->delete();
    }

    public function down(): void
    {
        // Restaurar "Vinoteca" → "Vinos y Bebidas"
        DB::table('categorias')
            ->where('slug', 'vinoteca')
            ->update([
                'nombre' => 'Vinos y Bebidas',
                'slug'   => 'vinos-y-bebidas',
                'icono'  => 'vinoteca',
            ]);

        // Restaurar "Conservas y Mermeladas"
        DB::table('categorias')->insertOrIgnore([
            'nombre'      => 'Conservas y Mermeladas',
            'slug'        => 'conservas-y-mermeladas',
            'icono'       => 'conservas',
            'descripcion' => 'Conservas y mermeladas caseras',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }
};
