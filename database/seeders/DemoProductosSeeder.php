<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Tienda;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * ~70 productos representativos de Lanzarote, repartidos en las 12 tiendas
 * demo. Cada producto con su imagen Unsplash, precio realista, stock variado,
 * y algunos con oferta activa o destacado.
 *
 * Idempotente: firstOrCreate por slug.
 */
class DemoProductosSeeder extends Seeder
{
    public function run(): void
    {
        $tiendas = Tienda::pluck('id', 'slug');
        $cats    = Categoria::pluck('id', 'slug');

        $productos = [
            // ═══════ Finca El Nido (frutas-y-verduras) ═══════
            ['tienda'=>'finca-el-nido','cat'=>'frutas-y-verduras','nombre'=>'Tomate canario','descripcion'=>'Tomate de invernadero del sur de Lanzarote, recolectado en su punto óptimo de maduración.','precio'=>3.50,'unidad'=>'kg','stock'=>60,'destacado'=>true,'imagen'=>'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=600&h=600&fit=crop'],
            ['tienda'=>'finca-el-nido','cat'=>'frutas-y-verduras','nombre'=>'Papa negra del país','descripcion'=>'Variedad autóctona cultivada en suelo volcánico. Ideal para papas arrugadas con mojo.','precio'=>4.20,'precio_oferta'=>3.50,'oferta_activa'=>true,'unidad'=>'kg','stock'=>80,'destacado'=>true,'imagen'=>'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=600&h=600&fit=crop'],
            ['tienda'=>'finca-el-nido','cat'=>'frutas-y-verduras','nombre'=>'Batata bonita','descripcion'=>'Batata de carne anaranjada y sabor dulce, perfecta para repostería tradicional.','precio'=>2.80,'unidad'=>'kg','stock'=>50,'imagen'=>'https://images.unsplash.com/photo-1596097635121-14b8cf8f99c1?w=600&h=600&fit=crop'],
            ['tienda'=>'finca-el-nido','cat'=>'frutas-y-verduras','nombre'=>'Plátano canario','descripcion'=>'Plátano de Canarias con denominación, dulce y de piel fina.','precio'=>2.60,'unidad'=>'kg','stock'=>40,'imagen'=>'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=600&h=600&fit=crop'],
            ['tienda'=>'finca-el-nido','cat'=>'frutas-y-verduras','nombre'=>'Calabaza canaria','descripcion'=>'Calabaza de pulpa firme, ideal para potajes y rancho canario.','precio'=>1.80,'unidad'=>'kg','stock'=>35,'imagen'=>'https://images.unsplash.com/photo-1570586437263-ab629fccc818?w=600&h=600&fit=crop'],
            ['tienda'=>'finca-el-nido','cat'=>'frutas-y-verduras','nombre'=>'Mojo verde casero','descripcion'=>'Mojo verde elaborado con cilantro, comino, ajo y aceite de oliva canario.','precio'=>3.90,'unidad'=>'bote','stock'=>25,'imagen'=>'https://images.unsplash.com/photo-1599909533730-3a36037d4d44?w=600&h=600&fit=crop'],

            // ═══════ Huerta Los Jameos ═══════
            ['tienda'=>'huerta-los-jameos','cat'=>'frutas-y-verduras','nombre'=>'Cebolla morada de Haría','descripcion'=>'Cebolla morada de cultivo tradicional, dulce y de gran tamaño.','precio'=>1.90,'unidad'=>'kg','stock'=>70,'imagen'=>'https://images.unsplash.com/photo-1599362243196-f0f1abf3e09f?w=600&h=600&fit=crop'],
            ['tienda'=>'huerta-los-jameos','cat'=>'frutas-y-verduras','nombre'=>'Pimientos palmeros','descripcion'=>'Pimientos pequeños de las Islas Canarias, muy aromáticos.','precio'=>4.50,'unidad'=>'kg','stock'=>30,'destacado'=>true,'imagen'=>'https://images.unsplash.com/photo-1583286020-b7c5f8e51b87?w=600&h=600&fit=crop'],
            ['tienda'=>'huerta-los-jameos','cat'=>'frutas-y-verduras','nombre'=>'Aloe vera fresco','descripcion'=>'Pencas de aloe vera ecológico, cosechadas a mano en el Valle de Haría.','precio'=>5.50,'precio_oferta'=>4.50,'oferta_activa'=>true,'unidad'=>'kg','stock'=>20,'imagen'=>'https://images.unsplash.com/photo-1611075384133-c1ba5380bff8?w=600&h=600&fit=crop'],
            ['tienda'=>'huerta-los-jameos','cat'=>'frutas-y-verduras','nombre'=>'Lechuga canaria','descripcion'=>'Lechuga de hoja rizada, recogida el mismo día del envío.','precio'=>1.60,'unidad'=>'ud','stock'=>45,'imagen'=>'https://images.unsplash.com/photo-1622206151226-18ca2c9ab4a1?w=600&h=600&fit=crop'],
            ['tienda'=>'huerta-los-jameos','cat'=>'frutas-y-verduras','nombre'=>'Naranja de Haría','descripcion'=>'Naranja dulce del Valle de Haría, ideal para zumo.','precio'=>2.30,'unidad'=>'kg','stock'=>55,'imagen'=>'https://images.unsplash.com/photo-1547514701-42782101795e?w=600&h=600&fit=crop'],

            // ═══════ Bodega La Geria (vinoteca) ═══════
            ['tienda'=>'bodega-la-geria','cat'=>'vinoteca','nombre'=>'Malvasía Volcánica Seco','descripcion'=>'Vino blanco D.O. Lanzarote, fermentado en hoyos volcánicos. Notas minerales y cítricas.','precio'=>14.50,'unidad'=>'botella 75 cl','stock'=>120,'destacado'=>true,'imagen'=>'https://images.unsplash.com/photo-1584916201218-f4242ceb4809?w=600&h=600&fit=crop'],
            ['tienda'=>'bodega-la-geria','cat'=>'vinoteca','nombre'=>'Malvasía Semidulce','descripcion'=>'Equilibrio entre frescura y dulzor. Perfecto con quesos y postres canarios.','precio'=>13.90,'precio_oferta'=>11.90,'oferta_activa'=>true,'unidad'=>'botella 75 cl','stock'=>90,'imagen'=>'https://images.unsplash.com/photo-1553361371-9b22f78e8b1d?w=600&h=600&fit=crop'],
            ['tienda'=>'bodega-la-geria','cat'=>'vinoteca','nombre'=>'Moscatel Diego','descripcion'=>'Vino dulce de uva moscatel cultivada en el Parque Natural de La Geria.','precio'=>18.00,'unidad'=>'botella 50 cl','stock'=>40,'imagen'=>'https://images.unsplash.com/photo-1547595628-c61a29f496f0?w=600&h=600&fit=crop'],
            ['tienda'=>'bodega-la-geria','cat'=>'vinoteca','nombre'=>'Tinto Listán Negro','descripcion'=>'Vino tinto canario joven, con cuerpo medio y final largo.','precio'=>12.50,'unidad'=>'botella 75 cl','stock'=>60,'imagen'=>'https://images.unsplash.com/photo-1543007630-9710e4a00a20?w=600&h=600&fit=crop'],

            // ═══════ Bodega Stratvs ═══════
            ['tienda'=>'bodega-stratvs','cat'=>'vinoteca','nombre'=>'Malvasía Seca Stratvs','descripcion'=>'Edición limitada de Malvasía Seca, crianza sobre lías. Solo 3000 botellas anuales.','precio'=>22.00,'unidad'=>'botella 75 cl','stock'=>50,'destacado'=>true,'imagen'=>'https://images.unsplash.com/photo-1474722883778-792e7990302f?w=600&h=600&fit=crop'],
            ['tienda'=>'bodega-stratvs','cat'=>'vinoteca','nombre'=>'Moscatel Stratvs','descripcion'=>'Moscatel naturalmente dulce, vendimia tardía. Aromas a miel y flores blancas.','precio'=>26.00,'unidad'=>'botella 50 cl','stock'=>35,'imagen'=>'https://images.unsplash.com/photo-1506377247377-2a5b3b417ebb?w=600&h=600&fit=crop'],
            ['tienda'=>'bodega-stratvs','cat'=>'vinoteca','nombre'=>'Espumoso Lanzarote','descripcion'=>'Vino espumoso elaborado con uva malvasía. Burbuja fina y notas tostadas.','precio'=>28.00,'precio_oferta'=>23.90,'oferta_activa'=>true,'unidad'=>'botella 75 cl','stock'=>25,'imagen'=>'https://images.unsplash.com/photo-1546158097-c2bd4d0aa53d?w=600&h=600&fit=crop'],

            // ═══════ Quesos Doña Carmen (lácteos-y-quesos) ═══════
            ['tienda'=>'quesos-dona-carmen','cat'=>'lacteos-y-quesos','nombre'=>'Queso majorero curado D.O.P.','descripcion'=>'Queso de cabra majorera curado durante 60 días. Sabor intenso, textura firme.','precio'=>14.90,'unidad'=>'kg','stock'=>40,'destacado'=>true,'imagen'=>'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=600&h=600&fit=crop'],
            ['tienda'=>'quesos-dona-carmen','cat'=>'lacteos-y-quesos','nombre'=>'Queso semicurado al pimentón','descripcion'=>'Queso de cabra semicurado con pimentón de la Vera. Toque ahumado característico.','precio'=>12.50,'unidad'=>'kg','stock'=>35,'imagen'=>'https://images.unsplash.com/photo-1452195100486-9cc805987862?w=600&h=600&fit=crop'],
            ['tienda'=>'quesos-dona-carmen','cat'=>'lacteos-y-quesos','nombre'=>'Queso tierno fresco','descripcion'=>'Queso de cabra tierno, sin curación. Ideal para ensaladas o desayunos.','precio'=>9.90,'precio_oferta'=>8.50,'oferta_activa'=>true,'unidad'=>'kg','stock'=>30,'imagen'=>'https://images.unsplash.com/photo-1559561853-08451507cbe7?w=600&h=600&fit=crop'],
            ['tienda'=>'quesos-dona-carmen','cat'=>'lacteos-y-quesos','nombre'=>'Cuajada de cabra','descripcion'=>'Cuajada tradicional preparada con leche cruda de cabra majorera.','precio'=>3.50,'unidad'=>'ud 200 g','stock'=>50,'imagen'=>'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&h=600&fit=crop'],

            // ═══════ Lácteos Conejera ═══════
            ['tienda'=>'lacteos-conejera','cat'=>'lacteos-y-quesos','nombre'=>'Yogur natural artesano','descripcion'=>'Yogur de cabra natural, sin azúcares añadidos. Textura cremosa.','precio'=>2.20,'unidad'=>'ud 250 g','stock'=>80,'imagen'=>'https://images.unsplash.com/photo-1571212515416-fef01fc43637?w=600&h=600&fit=crop'],
            ['tienda'=>'lacteos-conejera','cat'=>'lacteos-y-quesos','nombre'=>'Leche fresca de cabra','descripcion'=>'Leche cruda pasteurizada en botella de vidrio retornable.','precio'=>2.80,'unidad'=>'litro','stock'=>60,'destacado'=>true,'imagen'=>'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=600&h=600&fit=crop'],
            ['tienda'=>'lacteos-conejera','cat'=>'lacteos-y-quesos','nombre'=>'Requesón fresco','descripcion'=>'Requesón cremoso ideal para postres o tostadas con miel de palma.','precio'=>4.50,'unidad'=>'ud 400 g','stock'=>40,'imagen'=>'https://images.unsplash.com/photo-1559561853-08451507cbe7?w=600&h=600&fit=crop'],

            // ═══════ Pescados El Charco ═══════
            ['tienda'=>'pescados-el-charco','cat'=>'pescados-y-mariscos','nombre'=>'Vieja canaria fresca','descripcion'=>'Pescado emblemático de Canarias, capturado al amanecer. Carne blanca y delicada.','precio'=>22.00,'unidad'=>'kg','stock'=>20,'destacado'=>true,'imagen'=>'https://images.unsplash.com/photo-1535397842105-faa8e88e6e8e?w=600&h=600&fit=crop'],
            ['tienda'=>'pescados-el-charco','cat'=>'pescados-y-mariscos','nombre'=>'Sama dorada','descripcion'=>'Sama fresca del banco canario-sahariano. Perfecta para la sal o el horno.','precio'=>19.50,'precio_oferta'=>16.90,'oferta_activa'=>true,'unidad'=>'kg','stock'=>18,'imagen'=>'https://images.unsplash.com/photo-1559737558-2f5a35f4523b?w=600&h=600&fit=crop'],
            ['tienda'=>'pescados-el-charco','cat'=>'pescados-y-mariscos','nombre'=>'Cherne pequeño','descripcion'=>'Cherne entero, ideal para sancocho canario.','precio'=>18.00,'unidad'=>'kg','stock'=>15,'imagen'=>'https://images.unsplash.com/photo-1535398089889-dd807df1dfaa?w=600&h=600&fit=crop'],
            ['tienda'=>'pescados-el-charco','cat'=>'pescados-y-mariscos','nombre'=>'Atún rojo en taco','descripcion'=>'Taco de atún rojo del Atlántico. Calidad sushi.','precio'=>32.00,'unidad'=>'kg','stock'=>10,'imagen'=>'https://images.unsplash.com/photo-1582450871972-ab5ca641643d?w=600&h=600&fit=crop'],
            ['tienda'=>'pescados-el-charco','cat'=>'pescados-y-mariscos','nombre'=>'Calamares de la zona','descripcion'=>'Calamares pequeños limpios, listos para freír o a la plancha.','precio'=>16.00,'unidad'=>'kg','stock'=>25,'imagen'=>'https://images.unsplash.com/photo-1565680018434-b513d5e5fd47?w=600&h=600&fit=crop'],

            // ═══════ Mariscos Punta Mujeres ═══════
            ['tienda'=>'mariscos-punta-mujeres','cat'=>'pescados-y-mariscos','nombre'=>'Pulpo canario','descripcion'=>'Pulpo recogido en la costa norte. Limpio y listo para cocer.','precio'=>24.00,'unidad'=>'kg','stock'=>12,'destacado'=>true,'imagen'=>'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&h=600&fit=crop'],
            ['tienda'=>'mariscos-punta-mujeres','cat'=>'pescados-y-mariscos','nombre'=>'Lapas frescas','descripcion'=>'Lapas recién recolectadas en la costa norte. Para preparar a la plancha con mojo.','precio'=>18.00,'unidad'=>'kg','stock'=>15,'imagen'=>'https://images.unsplash.com/photo-1473093226795-af9932fe5856?w=600&h=600&fit=crop'],
            ['tienda'=>'mariscos-punta-mujeres','cat'=>'pescados-y-mariscos','nombre'=>'Bocinegro entero','descripcion'=>'Bocinegro de pesca artesanal. Pescado fino, ideal al horno.','precio'=>20.00,'precio_oferta'=>17.50,'oferta_activa'=>true,'unidad'=>'kg','stock'=>10,'imagen'=>'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=600&h=600&fit=crop'],
            ['tienda'=>'mariscos-punta-mujeres','cat'=>'pescados-y-mariscos','nombre'=>'Camarones canarios','descripcion'=>'Camarones pequeños frescos para cocer con sal marina.','precio'=>26.00,'unidad'=>'kg','stock'=>8,'imagen'=>'https://images.unsplash.com/photo-1565280654386-466a262d4bcb?w=600&h=600&fit=crop'],

            // ═══════ Panadería del Norte ═══════
            ['tienda'=>'panaderia-del-norte','cat'=>'panaderia','nombre'=>'Pan de millo','descripcion'=>'Pan tradicional canario de harina de maíz, miga densa y dulce.','precio'=>3.20,'unidad'=>'pieza 500 g','stock'=>30,'destacado'=>true,'imagen'=>'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=600&h=600&fit=crop'],
            ['tienda'=>'panaderia-del-norte','cat'=>'panaderia','nombre'=>'Pan de papas','descripcion'=>'Pan elaborado con masa madre y papa canaria. Receta familiar.','precio'=>3.00,'unidad'=>'pieza','stock'=>25,'imagen'=>'https://images.unsplash.com/photo-1568254183919-78a4f43a2877?w=600&h=600&fit=crop'],
            ['tienda'=>'panaderia-del-norte','cat'=>'panaderia','nombre'=>'Rosquetes anisados','descripcion'=>'Rosquetes tradicionales con anís verde, horneados a diario.','precio'=>6.50,'unidad'=>'bolsa 500 g','stock'=>35,'imagen'=>'https://images.unsplash.com/photo-1486427944299-d1955d23e34d?w=600&h=600&fit=crop'],
            ['tienda'=>'panaderia-del-norte','cat'=>'panaderia','nombre'=>'Bizcocho lanzaroteño','descripcion'=>'Bizcocho típico con almendra y miel de palma. Receta de la abuela.','precio'=>9.50,'precio_oferta'=>7.90,'oferta_activa'=>true,'unidad'=>'ud 600 g','stock'=>20,'imagen'=>'https://images.unsplash.com/photo-1571115177098-24ec42ed204d?w=600&h=600&fit=crop'],

            // ═══════ Horno La Geria ═══════
            ['tienda'=>'horno-la-geria','cat'=>'panaderia','nombre'=>'Truchas de batata','descripcion'=>'Empanadillas dulces rellenas de batata, típicas de Navidad. Pack de 6.','precio'=>8.00,'unidad'=>'pack 6 ud','stock'=>25,'destacado'=>true,'imagen'=>'https://images.unsplash.com/photo-1486427944299-d1955d23e34d?w=600&h=600&fit=crop'],
            ['tienda'=>'horno-la-geria','cat'=>'panaderia','nombre'=>'Queque marmolado','descripcion'=>'Queque clásico canario de vainilla y chocolate.','precio'=>7.50,'unidad'=>'ud 500 g','stock'=>20,'imagen'=>'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=600&h=600&fit=crop'],
            ['tienda'=>'horno-la-geria','cat'=>'panaderia','nombre'=>'Suspiros de mojo','descripcion'=>'Pequeños suspiros de merengue con ralladura de limón. Pack de 12.','precio'=>5.00,'unidad'=>'pack 12 ud','stock'=>30,'imagen'=>'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=600&h=600&fit=crop'],

            // ═══════ Artesanos de Teguise ═══════
            ['tienda'=>'artesanos-de-teguise','cat'=>'artesania','nombre'=>'Timple lanzaroteño','descripcion'=>'Timple artesano de cinco cuerdas, instrumento tradicional canario.','precio'=>185.00,'unidad'=>'ud','stock'=>5,'destacado'=>true,'imagen'=>'https://images.unsplash.com/photo-1525201548942-d8732f6617a0?w=600&h=600&fit=crop'],
            ['tienda'=>'artesanos-de-teguise','cat'=>'artesania','nombre'=>'Jarrón cerámica volcánica','descripcion'=>'Jarrón de barro negro inspirado en la cerámica aborigen canaria.','precio'=>45.00,'precio_oferta'=>38.00,'oferta_activa'=>true,'unidad'=>'ud','stock'=>15,'imagen'=>'https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?w=600&h=600&fit=crop'],
            ['tienda'=>'artesanos-de-teguise','cat'=>'artesania','nombre'=>'Mortero de piedra volcánica','descripcion'=>'Mortero tallado en piedra volcánica de Timanfaya. Para mojos y especias.','precio'=>32.00,'unidad'=>'ud','stock'=>12,'imagen'=>'https://images.unsplash.com/photo-1602006822373-8c66083fcdf2?w=600&h=600&fit=crop'],
            ['tienda'=>'artesanos-de-teguise','cat'=>'artesania','nombre'=>'Cuadro lava + roseta','descripcion'=>'Cuadro decorativo con lava esmaltada y roseta canaria tradicional.','precio'=>65.00,'unidad'=>'ud','stock'=>8,'imagen'=>'https://images.unsplash.com/photo-1582494906108-7fe49f47b9d4?w=600&h=600&fit=crop'],

            // ═══════ Cestería Mancha Blanca ═══════
            ['tienda'=>'cesteria-mancha-blanca','cat'=>'artesania','nombre'=>'Cesta de palma trenzada','descripcion'=>'Cesta tradicional trenzada a mano con palma canaria. Distintos tamaños.','precio'=>28.00,'unidad'=>'ud mediana','stock'=>20,'destacado'=>true,'imagen'=>'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600&h=600&fit=crop'],
            ['tienda'=>'cesteria-mancha-blanca','cat'=>'artesania','nombre'=>'Sombrero canario','descripcion'=>'Sombrero clásico de palma, idóneo para días de sol fuerte.','precio'=>22.00,'precio_oferta'=>18.00,'oferta_activa'=>true,'unidad'=>'ud','stock'=>18,'imagen'=>'https://images.unsplash.com/photo-1521369909029-2afed882baee?w=600&h=600&fit=crop'],
            ['tienda'=>'cesteria-mancha-blanca','cat'=>'artesania','nombre'=>'Estera de palma','descripcion'=>'Estera de palma para mesa o decoración. 40×60 cm.','precio'=>16.00,'unidad'=>'ud','stock'=>30,'imagen'=>'https://images.unsplash.com/photo-1605883705077-8d3d3cebe78c?w=600&h=600&fit=crop'],
        ];

        $creados = 0;
        foreach ($productos as $p) {
            $tiendaId = $tiendas[$p['tienda']] ?? null;
            $catId    = $cats[$p['cat']] ?? null;
            if (!$tiendaId || !$catId) continue;

            $slug = Str::slug($p['nombre']) . '-' . substr(md5($p['tienda'].$p['nombre']), 0, 5);

            Producto::firstOrCreate(
                ['slug' => $slug],
                [
                    'tienda_id'     => $tiendaId,
                    'categoria_id'  => $catId,
                    'nombre'        => $p['nombre'],
                    'descripcion'   => $p['descripcion'],
                    'precio'        => $p['precio'],
                    'precio_oferta' => $p['precio_oferta'] ?? null,
                    'oferta_activa' => $p['oferta_activa'] ?? false,
                    'unidad'        => $p['unidad'],
                    'imagen'        => $p['imagen'],
                    'stock'         => $p['stock'],
                    'stock_minimo'  => max(3, (int) round($p['stock'] * 0.15)),
                    'disponible'    => true,
                    'destacado'     => $p['destacado'] ?? false,
                ]
            );
            $creados++;
        }

        $this->command?->info("DemoProductosSeeder: {$creados} productos creados.");
    }
}
