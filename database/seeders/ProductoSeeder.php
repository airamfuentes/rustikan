<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Tienda;
use App\Models\Categoria;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $tiendas = Tienda::pluck('id', 'slug');
        $cats    = Categoria::pluck('id', 'slug');

        $productos = [

            // ═══════════════════════════════════════════════════════════════
            // FRUTAS Y VERDURAS
            // ═══════════════════════════════════════════════════════════════

            // ── Finca El Nido ────────────────────────────────────────────
            ['tienda' => 'finca-el-nido', 'cat' => 'frutas-y-verduras', 'nombre' => 'Tomates Cherry', 'slug' => 'tomates-cherry-finca', 'descripcion' => 'Tomates cherry ecológicos, dulces y jugosos', 'precio' => 3.50, 'unidad' => 'kg', 'imagen' => null, 'stock' => 50, 'stock_minimo' => 10, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'finca-el-nido', 'cat' => 'frutas-y-verduras', 'nombre' => 'Lechugas Mixtas', 'slug' => 'lechugas-mixtas-finca', 'descripcion' => 'Variedad de lechugas frescas del día', 'precio' => 2.00, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 30, 'stock_minimo' => 5, 'disponible' => true, 'destacado' => false],
            ['tienda' => 'finca-el-nido', 'cat' => 'frutas-y-verduras', 'nombre' => 'Zanahorias Ecológicas', 'slug' => 'zanahorias-finca', 'descripcion' => 'Zanahorias ecológicas recién cosechadas', 'precio' => 2.50, 'precio_oferta' => 1.99, 'unidad' => 'kg', 'imagen' => null, 'stock' => 40, 'stock_minimo' => 8, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'finca-el-nido', 'cat' => 'frutas-y-verduras', 'nombre' => 'Cebollas Moradas', 'slug' => 'cebollas-moradas-finca', 'descripcion' => 'Cebollas moradas de cultivo propio, sabor suave', 'precio' => 1.80, 'unidad' => 'kg', 'imagen' => null, 'stock' => 35, 'stock_minimo' => 8, 'disponible' => true, 'destacado' => false],

            // ── Huerta Los Jameos ────────────────────────────────────────
            ['tienda' => 'huerta-los-jameos', 'cat' => 'frutas-y-verduras', 'nombre' => 'Papas Negras', 'slug' => 'papas-negras-jameos', 'descripcion' => 'Papas negras canarias de cultivo volcánico', 'precio' => 4.20, 'unidad' => 'kg', 'imagen' => null, 'stock' => 60, 'stock_minimo' => 15, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'huerta-los-jameos', 'cat' => 'frutas-y-verduras', 'nombre' => 'Calabazas de Otoño', 'slug' => 'calabazas-jameos', 'descripcion' => 'Calabazas vieiras para potajes canarios', 'precio' => 2.80, 'unidad' => 'kg', 'imagen' => null, 'stock' => 25, 'stock_minimo' => 5, 'disponible' => true, 'destacado' => false],
            ['tienda' => 'huerta-los-jameos', 'cat' => 'frutas-y-verduras', 'nombre' => 'Pimientos Verdes', 'slug' => 'pimientos-verdes-jameos', 'descripcion' => 'Pimientos verdes crujientes, ideales para mojos', 'precio' => 3.00, 'unidad' => 'kg', 'imagen' => null, 'stock' => 30, 'stock_minimo' => 7, 'disponible' => true, 'destacado' => false],

            // ── El Malpaís Verde ─────────────────────────────────────────
            ['tienda' => 'el-malpais-verde', 'cat' => 'frutas-y-verduras', 'nombre' => 'Batatas de Lanzarote', 'slug' => 'batatas-malpais', 'descripcion' => 'Batatas dulces cultivadas en terreno volcánico', 'precio' => 3.20, 'unidad' => 'kg', 'imagen' => null, 'stock' => 40, 'stock_minimo' => 10, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'el-malpais-verde', 'cat' => 'frutas-y-verduras', 'nombre' => 'Acelgas Frescas', 'slug' => 'acelgas-malpais', 'descripcion' => 'Acelgas recién cortadas del malpaís', 'precio' => 1.90, 'unidad' => 'manojo', 'imagen' => null, 'stock' => 20, 'stock_minimo' => 5, 'disponible' => true, 'destacado' => false],
            ['tienda' => 'el-malpais-verde', 'cat' => 'frutas-y-verduras', 'nombre' => 'Higos Picos', 'slug' => 'higos-picos-malpais', 'descripcion' => 'Higos picos (tunos) típicos de Canarias', 'precio' => 4.50, 'unidad' => 'kg', 'imagen' => null, 'stock' => 15, 'stock_minimo' => 3, 'disponible' => true, 'destacado' => true],

            // ── La Cesta de Yaiza ────────────────────────────────────────
            ['tienda' => 'la-cesta-de-yaiza', 'cat' => 'frutas-y-verduras', 'nombre' => 'Cesta Semanal Pequeña', 'slug' => 'cesta-pequena-yaiza', 'descripcion' => 'Cesta variada con 5 verduras de temporada', 'precio' => 12.00, 'unidad' => 'cesta', 'imagen' => null, 'stock' => 20, 'stock_minimo' => 5, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'la-cesta-de-yaiza', 'cat' => 'frutas-y-verduras', 'nombre' => 'Cesta Semanal Grande', 'slug' => 'cesta-grande-yaiza', 'descripcion' => 'Cesta familiar con 10 verduras y frutas', 'precio' => 22.00, 'unidad' => 'cesta', 'imagen' => null, 'stock' => 15, 'stock_minimo' => 3, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'la-cesta-de-yaiza', 'cat' => 'frutas-y-verduras', 'nombre' => 'Plátanos Canarios', 'slug' => 'platanos-yaiza', 'descripcion' => 'Plátanos de Canarias, pequeños y sabrosos', 'precio' => 2.50, 'unidad' => 'kg', 'imagen' => null, 'stock' => 50, 'stock_minimo' => 12, 'disponible' => true, 'destacado' => false],

            // ═══════════════════════════════════════════════════════════════
            // CARNES
            // ═══════════════════════════════════════════════════════════════

            // ── Carnicería El Volcán ─────────────────────────────────────
            ['tienda' => 'carniceria-el-volcan', 'cat' => 'carnes', 'nombre' => 'Cabrito de Lanzarote', 'slug' => 'cabrito-volcan', 'descripcion' => 'Cabrito majorero criado en libertad, pieza entera', 'precio' => 14.90, 'unidad' => 'kg', 'imagen' => null, 'stock' => 12, 'stock_minimo' => 3, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'carniceria-el-volcan', 'cat' => 'carnes', 'nombre' => 'Costillas de Cerdo', 'slug' => 'costillas-volcan', 'descripcion' => 'Costillas de cerdo negro canario para adobar', 'precio' => 8.50, 'unidad' => 'kg', 'imagen' => null, 'stock' => 25, 'stock_minimo' => 5, 'disponible' => true, 'destacado' => false],
            ['tienda' => 'carniceria-el-volcan', 'cat' => 'carnes', 'nombre' => 'Carne de Cabra Guisada', 'slug' => 'cabra-guisada-volcan', 'descripcion' => 'Troceada y lista para guisar con papas', 'precio' => 11.50, 'unidad' => 'kg', 'imagen' => null, 'stock' => 18, 'stock_minimo' => 4, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'carniceria-el-volcan', 'cat' => 'carnes', 'nombre' => 'Chorizo Canario', 'slug' => 'chorizo-volcan', 'descripcion' => 'Chorizo artesanal con pimentón de la isla', 'precio' => 9.80, 'unidad' => 'kg', 'imagen' => null, 'stock' => 30, 'stock_minimo' => 8, 'disponible' => true, 'destacado' => false],

            // ── La Majada de Teguise ─────────────────────────────────────
            ['tienda' => 'la-majada-de-teguise', 'cat' => 'carnes', 'nombre' => 'Ternera Gallega', 'slug' => 'ternera-majada', 'descripcion' => 'Solomillo de ternera seleccionada, madurada 21 días', 'precio' => 24.90, 'unidad' => 'kg', 'imagen' => null, 'stock' => 10, 'stock_minimo' => 2, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'la-majada-de-teguise', 'cat' => 'carnes', 'nombre' => 'Cordero Lechal', 'slug' => 'cordero-majada', 'descripcion' => 'Medio cordero lechal de Lanzarote', 'precio' => 18.50, 'unidad' => 'kg', 'imagen' => null, 'stock' => 8, 'stock_minimo' => 2, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'la-majada-de-teguise', 'cat' => 'carnes', 'nombre' => 'Hamburguesas de Cabra', 'slug' => 'hamburguesas-majada', 'descripcion' => 'Pack de 4 hamburguesas de cabra majorera con especias', 'precio' => 6.80, 'unidad' => 'pack', 'imagen' => null, 'stock' => 20, 'stock_minimo' => 5, 'disponible' => true, 'destacado' => false],

            // ── Rancho Los Helechos ──────────────────────────────────────
            ['tienda' => 'rancho-los-helechos', 'cat' => 'carnes', 'nombre' => 'Pollo Campero Entero', 'slug' => 'pollo-helechos', 'descripcion' => 'Pollo campero criado al aire libre sin antibióticos', 'precio' => 9.90, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 15, 'stock_minimo' => 3, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'rancho-los-helechos', 'cat' => 'carnes', 'nombre' => 'Conejo de Granja', 'slug' => 'conejo-helechos', 'descripcion' => 'Conejo entero para salmorejo canario', 'precio' => 7.50, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 12, 'stock_minimo' => 3, 'disponible' => true, 'destacado' => false],
            ['tienda' => 'rancho-los-helechos', 'cat' => 'carnes', 'nombre' => 'Huevos Camperos', 'slug' => 'huevos-helechos', 'descripcion' => 'Docena de huevos de gallinas en libertad', 'precio' => 3.80, 'unidad' => 'docena', 'imagen' => null, 'stock' => 40, 'stock_minimo' => 10, 'disponible' => true, 'destacado' => true],

            // ═══════════════════════════════════════════════════════════════
            // PESCADOS Y MARISCOS
            // ═══════════════════════════════════════════════════════════════

            // ── Pescadería Mar Azul ──────────────────────────────────────
            ['tienda' => 'pescaderia-mar-azul', 'cat' => 'pescados-y-mariscos', 'nombre' => 'Vieja Fresca', 'slug' => 'vieja-mar-azul', 'descripcion' => 'Vieja del día, pescado típico canario', 'precio' => 18.50, 'unidad' => 'kg', 'imagen' => null, 'stock' => 15, 'stock_minimo' => 3, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'pescaderia-mar-azul', 'cat' => 'pescados-y-mariscos', 'nombre' => 'Calamares Frescos', 'slug' => 'calamares-mar-azul', 'descripcion' => 'Calamares frescos de Órzola', 'precio' => 12.00, 'unidad' => 'kg', 'imagen' => null, 'stock' => 20, 'stock_minimo' => 5, 'disponible' => true, 'destacado' => false],
            ['tienda' => 'pescaderia-mar-azul', 'cat' => 'pescados-y-mariscos', 'nombre' => 'Cherne del Día', 'slug' => 'cherne-mar-azul', 'descripcion' => 'Cherne capturado por pescadores locales', 'precio' => 22.00, 'unidad' => 'kg', 'imagen' => null, 'stock' => 8, 'stock_minimo' => 2, 'disponible' => true, 'destacado' => true],

            // ── La Lonja de Arrecife ─────────────────────────────────────
            ['tienda' => 'la-lonja-de-arrecife', 'cat' => 'pescados-y-mariscos', 'nombre' => 'Mero de Bajura', 'slug' => 'mero-lonja', 'descripcion' => 'Mero fresco de bajura del Atlántico', 'precio' => 25.00, 'unidad' => 'kg', 'imagen' => null, 'stock' => 6, 'stock_minimo' => 2, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'la-lonja-de-arrecife', 'cat' => 'pescados-y-mariscos', 'nombre' => 'Chopas al Peso', 'slug' => 'chopas-lonja', 'descripcion' => 'Chopas recién desembarcadas en el puerto', 'precio' => 14.00, 'unidad' => 'kg', 'imagen' => null, 'stock' => 18, 'stock_minimo' => 4, 'disponible' => true, 'destacado' => false],
            ['tienda' => 'la-lonja-de-arrecife', 'cat' => 'pescados-y-mariscos', 'nombre' => 'Langosta Canaria', 'slug' => 'langosta-lonja', 'descripcion' => 'Langosta viva del Atlántico, pieza selecta', 'precio' => 45.00, 'unidad' => 'kg', 'imagen' => null, 'stock' => 4, 'stock_minimo' => 1, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'la-lonja-de-arrecife', 'cat' => 'pescados-y-mariscos', 'nombre' => 'Sardinas Frescas', 'slug' => 'sardinas-lonja', 'descripcion' => 'Sardinas para asar o salazón, del día', 'precio' => 6.00, 'unidad' => 'kg', 'imagen' => null, 'stock' => 30, 'stock_minimo' => 8, 'disponible' => true, 'destacado' => false],

            // ── El Islote del Mar ────────────────────────────────────────
            ['tienda' => 'el-islote-del-mar', 'cat' => 'pescados-y-mariscos', 'nombre' => 'Langostinos del Atlántico', 'slug' => 'langostinos-islote', 'descripcion' => 'Langostinos tigre frescos, calibre grande', 'precio' => 16.00, 'unidad' => 'kg', 'imagen' => null, 'stock' => 15, 'stock_minimo' => 3, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'el-islote-del-mar', 'cat' => 'pescados-y-mariscos', 'nombre' => 'Almejas del Jable', 'slug' => 'almejas-islote', 'descripcion' => 'Almejas frescas para arroces y caldos', 'precio' => 12.50, 'unidad' => 'kg', 'imagen' => null, 'stock' => 10, 'stock_minimo' => 2, 'disponible' => true, 'destacado' => false],
            ['tienda' => 'el-islote-del-mar', 'cat' => 'pescados-y-mariscos', 'nombre' => 'Pulpo Fresco', 'slug' => 'pulpo-islote', 'descripcion' => 'Pulpo del Atlántico, limpio y listo para cocinar', 'precio' => 15.00, 'unidad' => 'kg', 'imagen' => null, 'stock' => 12, 'stock_minimo' => 3, 'disponible' => true, 'destacado' => true],

            // ═══════════════════════════════════════════════════════════════
            // PANADERÍA
            // ═══════════════════════════════════════════════════════════════

            // ── Panadería La Tahona ──────────────────────────────────────
            ['tienda' => 'panaderia-la-tahona', 'cat' => 'panaderia', 'nombre' => 'Pan de Millo', 'slug' => 'pan-millo-tahona', 'descripcion' => 'Pan tradicional canario de millo (maíz)', 'precio' => 2.80, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 40, 'stock_minimo' => 10, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'panaderia-la-tahona', 'cat' => 'panaderia', 'nombre' => 'Empanadas de Atún', 'slug' => 'empanadas-tahona', 'descripcion' => 'Empanadas caseras rellenas de atún', 'precio' => 1.50, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 25, 'stock_minimo' => 8, 'disponible' => true, 'destacado' => false],
            ['tienda' => 'panaderia-la-tahona', 'cat' => 'panaderia', 'nombre' => 'Hogaza de Masa Madre', 'slug' => 'hogaza-tahona', 'descripcion' => 'Hogaza artesanal con 24h de fermentación', 'precio' => 3.50, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 20, 'stock_minimo' => 5, 'disponible' => true, 'destacado' => true],

            // ── Obrador La Caleta ────────────────────────────────────────
            ['tienda' => 'obrador-la-caleta', 'cat' => 'panaderia', 'nombre' => 'Croissant de Mantequilla', 'slug' => 'croissant-caleta', 'descripcion' => 'Croissant hojaldrado con mantequilla francesa', 'precio' => 1.80, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 60, 'stock_minimo' => 15, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'obrador-la-caleta', 'cat' => 'panaderia', 'nombre' => 'Hogaza de Espelta', 'slug' => 'espelta-caleta', 'descripcion' => 'Pan de espelta integral con semillas', 'precio' => 4.00, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 25, 'stock_minimo' => 6, 'disponible' => true, 'destacado' => false],
            ['tienda' => 'obrador-la-caleta', 'cat' => 'panaderia', 'nombre' => 'Bollos de Anís', 'slug' => 'bollos-anis-caleta', 'descripcion' => 'Bollitos tradicionales con anís matalahúva', 'precio' => 0.90, 'precio_oferta' => 0.70, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 50, 'stock_minimo' => 12, 'disponible' => true, 'destacado' => false],
            ['tienda' => 'obrador-la-caleta', 'cat' => 'panaderia', 'nombre' => 'Tarta de Almendra', 'slug' => 'tarta-almendra-caleta', 'descripcion' => 'Tarta casera de almendras de La Gomera', 'precio' => 15.00, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 8, 'stock_minimo' => 2, 'disponible' => true, 'destacado' => true],

            // ── El Horno de Haría ────────────────────────────────────────
            ['tienda' => 'el-horno-de-haria', 'cat' => 'panaderia', 'nombre' => 'Bienmesabe', 'slug' => 'bienmesabe-haria', 'descripcion' => 'Postre canario de almendra, miel y huevo', 'precio' => 6.50, 'unidad' => 'tarro', 'imagen' => null, 'stock' => 20, 'stock_minimo' => 5, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'el-horno-de-haria', 'cat' => 'panaderia', 'nombre' => 'Polvorones Artesanales', 'slug' => 'polvorones-haria', 'descripcion' => 'Polvorones de manteca con almendra triturada', 'precio' => 5.00, 'unidad' => 'caja', 'imagen' => null, 'stock' => 30, 'stock_minimo' => 8, 'disponible' => true, 'destacado' => false],
            ['tienda' => 'el-horno-de-haria', 'cat' => 'panaderia', 'nombre' => 'Truchas de Batata', 'slug' => 'truchas-batata-haria', 'descripcion' => 'Empanadillas dulces rellenas de batata y almendra', 'precio' => 1.20, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 35, 'stock_minimo' => 10, 'disponible' => true, 'destacado' => true],

            // ═══════════════════════════════════════════════════════════════
            // LÁCTEOS Y QUESOS
            // ═══════════════════════════════════════════════════════════════

            // ── Quesería La Majorera ─────────────────────────────────────
            ['tienda' => 'queseria-la-majorera', 'cat' => 'lacteos-y-quesos', 'nombre' => 'Queso Curado', 'slug' => 'queso-curado-majorera', 'descripcion' => 'Queso majorero curado 6 meses, sabor intenso', 'precio' => 14.90, 'unidad' => 'kg', 'imagen' => null, 'stock' => 25, 'stock_minimo' => 5, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'queseria-la-majorera', 'cat' => 'lacteos-y-quesos', 'nombre' => 'Queso Semicurado', 'slug' => 'queso-semi-majorera', 'descripcion' => 'Queso semicurado de cabra, textura cremosa', 'precio' => 12.50, 'unidad' => 'kg', 'imagen' => null, 'stock' => 30, 'stock_minimo' => 8, 'disponible' => true, 'destacado' => false],
            ['tienda' => 'queseria-la-majorera', 'cat' => 'lacteos-y-quesos', 'nombre' => 'Queso en Aceite', 'slug' => 'queso-aceite-majorera', 'descripcion' => 'Tacos de queso curado en aceite de oliva virgen', 'precio' => 8.90, 'unidad' => 'tarro', 'imagen' => null, 'stock' => 20, 'stock_minimo' => 5, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'queseria-la-majorera', 'cat' => 'lacteos-y-quesos', 'nombre' => 'Queso Fresco', 'slug' => 'queso-fresco-majorera', 'descripcion' => 'Queso fresco de cabra, ideal para ensaladas', 'precio' => 7.50, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 35, 'stock_minimo' => 10, 'disponible' => true, 'destacado' => false],

            // ── El Caserío de Femés ──────────────────────────────────────
            ['tienda' => 'el-caserio-de-femes', 'cat' => 'lacteos-y-quesos', 'nombre' => 'Yogur de Oveja Natural', 'slug' => 'yogur-oveja-femes', 'descripcion' => 'Yogur artesanal de leche de oveja, sin azúcar', 'precio' => 2.80, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 40, 'stock_minimo' => 10, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'el-caserio-de-femes', 'cat' => 'lacteos-y-quesos', 'nombre' => 'Kéfir Artesanal', 'slug' => 'kefir-femes', 'descripcion' => 'Kéfir de leche de oveja con probióticos naturales', 'precio' => 3.50, 'unidad' => 'botella', 'imagen' => null, 'stock' => 25, 'stock_minimo' => 6, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'el-caserio-de-femes', 'cat' => 'lacteos-y-quesos', 'nombre' => 'Leche Fresca de Oveja', 'slug' => 'leche-femes', 'descripcion' => 'Leche entera pasteurizada, sabor auténtico', 'precio' => 2.20, 'unidad' => 'litro', 'imagen' => null, 'stock' => 30, 'stock_minimo' => 8, 'disponible' => true, 'destacado' => false],

            // ── Finca Cabra Feliz ────────────────────────────────────────
            ['tienda' => 'finca-cabra-feliz', 'cat' => 'lacteos-y-quesos', 'nombre' => 'Requesón Artesanal', 'slug' => 'requeson-cabra', 'descripcion' => 'Requesón fresco de cabra, textura ligera', 'precio' => 4.50, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 20, 'stock_minimo' => 5, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'finca-cabra-feliz', 'cat' => 'lacteos-y-quesos', 'nombre' => 'Mantequilla de Cabra', 'slug' => 'mantequilla-cabra', 'descripcion' => 'Mantequilla artesanal sin sal, 250g', 'precio' => 3.90, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 25, 'stock_minimo' => 6, 'disponible' => true, 'destacado' => false],
            ['tienda' => 'finca-cabra-feliz', 'cat' => 'lacteos-y-quesos', 'nombre' => 'Queso Tierno de Cabra', 'slug' => 'queso-tierno-cabra', 'descripcion' => 'Queso tierno de 15 días, sabor suave y cremoso', 'precio' => 9.50, 'unidad' => 'kg', 'imagen' => null, 'stock' => 15, 'stock_minimo' => 4, 'disponible' => true, 'destacado' => true],

            // ═══════════════════════════════════════════════════════════════
            // VINOTECA
            // ═══════════════════════════════════════════════════════════════

            // ── Bodega Los Volcanes ──────────────────────────────────────
            ['tienda' => 'bodega-los-volcanes', 'cat' => 'vinoteca', 'nombre' => 'Malvasía Volcánica', 'slug' => 'malvasia-volcanes', 'descripcion' => 'Vino blanco DO Lanzarote, uva Malvasía, notas tropicales', 'precio' => 12.50, 'unidad' => 'botella', 'imagen' => null, 'stock' => 60, 'stock_minimo' => 15, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'bodega-los-volcanes', 'cat' => 'vinoteca', 'nombre' => 'Listán Negro', 'slug' => 'listan-negro-volcanes', 'descripcion' => 'Vino tinto joven de La Geria, fresco y frutal', 'precio' => 10.00, 'unidad' => 'botella', 'imagen' => null, 'stock' => 45, 'stock_minimo' => 10, 'disponible' => true, 'destacado' => false],
            ['tienda' => 'bodega-los-volcanes', 'cat' => 'vinoteca', 'nombre' => 'Moscatel Dulce', 'slug' => 'moscatel-volcanes', 'descripcion' => 'Vino dulce natural de uva moscatel, para postres', 'precio' => 14.00, 'unidad' => 'botella', 'imagen' => null, 'stock' => 30, 'stock_minimo' => 8, 'disponible' => true, 'destacado' => true],

            // ── Viña La Caldera ──────────────────────────────────────────
            ['tienda' => 'vina-la-caldera', 'cat' => 'vinoteca', 'nombre' => 'Blanco Seco Reserva', 'slug' => 'blanco-caldera', 'descripcion' => 'Malvasía seca con 12 meses en barrica de roble', 'precio' => 18.50, 'unidad' => 'botella', 'imagen' => null, 'stock' => 25, 'stock_minimo' => 5, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'vina-la-caldera', 'cat' => 'vinoteca', 'nombre' => 'Rosado Volcánico', 'slug' => 'rosado-caldera', 'descripcion' => 'Rosado de listán negro, fresco y mineral', 'precio' => 11.00, 'unidad' => 'botella', 'imagen' => null, 'stock' => 35, 'stock_minimo' => 8, 'disponible' => true, 'destacado' => false],
            ['tienda' => 'vina-la-caldera', 'cat' => 'vinoteca', 'nombre' => 'Pack Cata 3 Vinos', 'slug' => 'pack-cata-caldera', 'descripcion' => 'Selección de 3 botellas: blanco, rosado y tinto', 'precio' => 35.00, 'precio_oferta' => 29.90, 'unidad' => 'pack', 'imagen' => null, 'stock' => 15, 'stock_minimo' => 3, 'disponible' => true, 'destacado' => true],

            // ── El Grifo – Tienda ────────────────────────────────────────
            ['tienda' => 'el-grifo-tienda', 'cat' => 'vinoteca', 'nombre' => 'El Grifo Malvasía Colección', 'slug' => 'malvasia-grifo', 'descripcion' => 'Malvasía colección premium, edición limitada', 'precio' => 22.00, 'unidad' => 'botella', 'imagen' => null, 'stock' => 20, 'stock_minimo' => 5, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'el-grifo-tienda', 'cat' => 'vinoteca', 'nombre' => 'Licor de Tuno Indio', 'slug' => 'licor-tuno-grifo', 'descripcion' => 'Licor artesanal de higo pico, dulce y refrescante', 'precio' => 9.50, 'unidad' => 'botella', 'imagen' => null, 'stock' => 40, 'stock_minimo' => 10, 'disponible' => true, 'destacado' => false],
            ['tienda' => 'el-grifo-tienda', 'cat' => 'vinoteca', 'nombre' => 'Entrada Museo del Vino', 'slug' => 'museo-grifo', 'descripcion' => 'Visita guiada al museo + cata de 3 vinos', 'precio' => 15.00, 'unidad' => 'persona', 'imagen' => null, 'stock' => 100, 'stock_minimo' => 10, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'el-grifo-tienda', 'cat' => 'vinoteca', 'nombre' => 'Brut Nature Espumoso', 'slug' => 'brut-grifo', 'descripcion' => 'Espumoso método tradicional con uva malvasía', 'precio' => 16.50, 'unidad' => 'botella', 'imagen' => null, 'stock' => 18, 'stock_minimo' => 4, 'disponible' => true, 'destacado' => false],

            // ── Mezclas del Atlántico ────────────────────────────────────
            ['tienda' => 'mezclas-del-atlantico', 'cat' => 'vinoteca', 'nombre' => 'IPA Volcánica', 'slug' => 'ipa-atlantico', 'descripcion' => 'Cerveza artesanal IPA con agua del Atlántico', 'precio' => 3.50, 'unidad' => 'lata', 'imagen' => null, 'stock' => 80, 'stock_minimo' => 20, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'mezclas-del-atlantico', 'cat' => 'vinoteca', 'nombre' => 'Stout de Gofio', 'slug' => 'stout-gofio-atlantico', 'descripcion' => 'Stout oscura con gofio canario, notas tostadas', 'precio' => 3.80, 'unidad' => 'lata', 'imagen' => null, 'stock' => 50, 'stock_minimo' => 12, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'mezclas-del-atlantico', 'cat' => 'vinoteca', 'nombre' => 'Pack Cata 6 Cervezas', 'slug' => 'pack-cata-atlantico', 'descripcion' => '6 cervezas artesanales variadas para descubrir', 'precio' => 18.00, 'precio_oferta' => 14.90, 'unidad' => 'pack', 'imagen' => null, 'stock' => 25, 'stock_minimo' => 5, 'disponible' => true, 'destacado' => false],

            // ═══════════════════════════════════════════════════════════════
            // ARTESANÍA
            // ═══════════════════════════════════════════════════════════════

            // ── Taller La Tinaja ─────────────────────────────────────────
            ['tienda' => 'taller-la-tinaja', 'cat' => 'artesania', 'nombre' => 'Jarrón Volcánico', 'slug' => 'jarron-tinaja', 'descripcion' => 'Jarrón decorativo de cerámica modelado a mano con arcilla volcánica', 'precio' => 35.00, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 10, 'stock_minimo' => 2, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'taller-la-tinaja', 'cat' => 'artesania', 'nombre' => 'Cuenco Rústico', 'slug' => 'cuenco-tinaja', 'descripcion' => 'Cuenco de barro canario para servir mojos', 'precio' => 12.00, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 20, 'stock_minimo' => 5, 'disponible' => true, 'destacado' => false],
            ['tienda' => 'taller-la-tinaja', 'cat' => 'artesania', 'nombre' => 'Set Tazas Lava', 'slug' => 'tazas-tinaja', 'descripcion' => 'Juego de 2 tazas con esmalte que imita la lava', 'precio' => 22.00, 'unidad' => 'set', 'imagen' => null, 'stock' => 15, 'stock_minimo' => 3, 'disponible' => true, 'destacado' => true],

            // ── Tejidos Volcánicos ───────────────────────────────────────
            ['tienda' => 'tejidos-volcanicos', 'cat' => 'artesania', 'nombre' => 'Bolso Tejido a Mano', 'slug' => 'bolso-tejidos', 'descripcion' => 'Bolso de hilo de algodón reciclado, tintes vegetales', 'precio' => 28.00, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 12, 'stock_minimo' => 3, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'tejidos-volcanicos', 'cat' => 'artesania', 'nombre' => 'Bufanda de Lana Merino', 'slug' => 'bufanda-tejidos', 'descripcion' => 'Bufanda tejida con lana merino y tinte de cochinilla', 'precio' => 32.00, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 8, 'stock_minimo' => 2, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'tejidos-volcanicos', 'cat' => 'artesania', 'nombre' => 'Cojín Diseño Canario', 'slug' => 'cojin-tejidos', 'descripcion' => 'Funda de cojín con patrones geométricos canarios', 'precio' => 18.00, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 15, 'stock_minimo' => 4, 'disponible' => true, 'destacado' => false],

            // ── El Estudio de César ──────────────────────────────────────
            ['tienda' => 'el-estudio-de-cesar', 'cat' => 'artesania', 'nombre' => 'Lámina Manrique 40x60', 'slug' => 'lamina-cesar', 'descripcion' => 'Reproducción artística inspirada en César Manrique', 'precio' => 25.00, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 30, 'stock_minimo' => 8, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'el-estudio-de-cesar', 'cat' => 'artesania', 'nombre' => 'Escultura Mini Volcán', 'slug' => 'escultura-cesar', 'descripcion' => 'Escultura decorativa en basalto tallado a mano', 'precio' => 45.00, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 6, 'stock_minimo' => 1, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'el-estudio-de-cesar', 'cat' => 'artesania', 'nombre' => 'Pendientes de Basalto', 'slug' => 'pendientes-cesar', 'descripcion' => 'Pendientes artesanales de piedra volcánica pulida', 'precio' => 15.00, 'unidad' => 'par', 'imagen' => null, 'stock' => 25, 'stock_minimo' => 5, 'disponible' => true, 'destacado' => false],
            ['tienda' => 'el-estudio-de-cesar', 'cat' => 'artesania', 'nombre' => 'Imán de Lanzarote', 'slug' => 'iman-cesar', 'descripcion' => 'Imán decorativo con ilustración de los Jameos del Agua', 'precio' => 5.00, 'unidad' => 'unidad', 'imagen' => null, 'stock' => 50, 'stock_minimo' => 12, 'disponible' => true, 'destacado' => false],

            // ── Sal de Janubio ───────────────────────────────────────────
            ['tienda' => 'sal-de-janubio', 'cat' => 'artesania', 'nombre' => 'Flor de Sal', 'slug' => 'flor-sal-janubio', 'descripcion' => 'Flor de sal recolectada a mano en las Salinas de Janubio', 'precio' => 6.50, 'unidad' => 'tarro', 'imagen' => null, 'stock' => 60, 'stock_minimo' => 15, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'sal-de-janubio', 'cat' => 'artesania', 'nombre' => 'Sal Negra Volcánica', 'slug' => 'sal-negra-janubio', 'descripcion' => 'Sal marina con carbón activo volcánico, ideal para carnes', 'precio' => 7.50, 'unidad' => 'tarro', 'imagen' => null, 'stock' => 40, 'stock_minimo' => 10, 'disponible' => true, 'destacado' => true],
            ['tienda' => 'sal-de-janubio', 'cat' => 'artesania', 'nombre' => 'Sal con Hierbas', 'slug' => 'sal-hierbas-janubio', 'descripcion' => 'Sal marina con mix de hierbas canarias: tomillo, orégano y cilantro', 'precio' => 5.80, 'unidad' => 'tarro', 'imagen' => null, 'stock' => 35, 'stock_minimo' => 8, 'disponible' => true, 'destacado' => false],
            ['tienda' => 'sal-de-janubio', 'cat' => 'artesania', 'nombre' => 'Pack Regalo 3 Sales', 'slug' => 'pack-sales-janubio', 'descripcion' => 'Estuche regalo con flor de sal, sal negra y sal con hierbas', 'precio' => 16.00, 'precio_oferta' => 13.50, 'unidad' => 'pack', 'imagen' => null, 'stock' => 20, 'stock_minimo' => 5, 'disponible' => true, 'destacado' => true],
        ];

        foreach ($productos as $data) {
            $tiendaId = $tiendas[$data['tienda']] ?? null;
            $catId    = $cats[$data['cat']] ?? null;
            if (!$tiendaId || !$catId) continue;

            Producto::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge(
                    collect($data)->except(['tienda', 'cat'])->toArray(),
                    ['tienda_id' => $tiendaId, 'categoria_id' => $catId]
                )
            );
        }
    }
}
