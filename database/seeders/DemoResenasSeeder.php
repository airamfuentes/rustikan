<?php

namespace Database\Seeders;

use App\Models\Resena;
use App\Models\Tienda;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea reseñas escritas y realistas para todas las tiendas demo.
 * Recalcula valoracion y total_resenas en cada tienda al terminar.
 * Idempotente: elimina reseñas previas de usuarios demo antes de insertar.
 */
class DemoResenasSeeder extends Seeder
{
    public function run(): void
    {
        // Mapa tiendaSlug → array de reseñas [ user_email, puntuacion, titulo, comentario ]
        $data = [

            // ── Finca El Nido ────────────────────────────────────────────────
            'finca-el-nido' => [
                ['aitana.cabrera.0@rustikan-demo.es',    5, '¡El mejor tomate de la isla!',       'Llevo meses pidiendo aquí y el tomate canario es espectacular, nada que ver con el de supermercado. La papa negra para las arrugadas quedó perfecta.'],
                ['adrian.hernandez.1@rustikan-demo.es',  4, 'Muy buena calidad, entrega puntual',  'Las verduras llegan siempre frescas y bien envasadas. El mojo verde casero es una joya, se lo recomiendo a todo el mundo.'],
                ['alba.perez.2@rustikan-demo.es',        5, 'Producto ecológico de verdad',        'Se nota que cultivan con cuidado. La batata tiene un sabor dulce que no había probado nunca. Volveré seguro.'],
                ['alejandro.curbelo.3@rustikan-demo.es', 4, 'Buena relación calidad-precio',      'Los plátanos canarios son de matrícula. Un poco caro el mojo pero vale la pena por la calidad artesanal.'],
                ['andrea.betancor.4@rustikan-demo.es',   5, 'Finca familiar con mucho mimo',      'Pedimos la cesta semanal y cada semana sorprende. Variedad enorme y todo recién cogido.'],
            ],

            // ── Huerta Los Jameos ────────────────────────────────────────────
            'huerta-los-jameos' => [
                ['angel.reyes.5@rustikan-demo.es',      5, 'La cebolla morada es única',          'Nunca había probado una cebolla así de dulce y crujiente. El aloe vera fresco también impresiona, lo uso para la piel y es increíble.'],
                ['antonio.acuna.6@rustikan-demo.es',    4, 'Tres generaciones de saber hacer',    'Se nota la experiencia familiar. Los pimientos palmeros son perfectos para sofritos. Entrega rápida al norte de la isla.'],
                ['aroa.padilla.7@rustikan-demo.es',     5, 'Producto fresco garantizado',         'La lechuga llega crujiente como si la acabaran de cortar. Muy recomendable para quienes buscan km0 real.'],
                ['beatriz.toledo.8@rustikan-demo.es',   3, 'Bien pero podría mejorar el embalaje','El producto es bueno pero la naranja llegó con algún golpe. El sabor estaba bien igual.'],
                ['borja.rivero.9@rustikan-demo.es',     5, 'Mi huerta favorita del norte',        'Compramos aquí cada semana. Haría tiene una luz especial y las verduras lo reflejan. Gracias por mantener esto vivo.'],
            ],

            // ── Bodega La Geria ──────────────────────────────────────────────
            'bodega-la-geria' => [
                ['carlos.morales.10@rustikan-demo.es',  5, 'El mejor blanco de Lanzarote',        'La Malvasía Volcánica Seca es de otro nivel. Esas notas minerales que te recuerdan al volcán en cada sorbo. Compré 6 botellas para llevar a la península.'],
                ['carla.suarez.11@rustikan-demo.es',    5, 'Vino único en el mundo',              'Estar en los hoyos volcánicos de La Geria y probar este vino es una experiencia que no olvidarás. El Moscatel Diego con postre canario: perfección.'],
                ['cristina.perdomo.12@rustikan-demo.es',4, 'Muy buena relación calidad-precio',   'El Semidulce en oferta estaba de lujo. Maridé con el queso majorero y fue un descubrimiento. Repetiré sin duda.'],
                ['daniel.gonzalez.13@rustikan-demo.es', 5, 'Auténtico vino volcánico',            'Imposible encontrar algo así fuera de Lanzarote. El Listán Negro joven es fresco y diferente. Envío rápido y bien protegido.'],
                ['david.rodriguez.14@rustikan-demo.es', 4, 'Gran bodega con historia',            'Visité la bodega y además pedí online. El trato es excelente y los vinos hablan por sí solos.'],
            ],

            // ── Bodega Stratvs (demo) ────────────────────────────────────────
            'bodega-stratvs-demo' => [
                ['diego.martin.15@rustikan-demo.es',    5, 'Edición limitada que vale cada euro', 'La Malvasía Seca Stratvs es mi vino favorito de toda Canarias. Solo 3000 botellas y lo entiendo: algo así no puede producirse en masa.'],
                ['elena.santana.16@rustikan-demo.es',   5, 'El espumoso me sorprendió',           'No esperaba que un espumoso de Lanzarote pudiera tener esta finura. Burbuja elegante y notas tostadas preciosas. Ideal para celebraciones.'],
                ['emma.marrero.17@rustikan-demo.es',    4, 'Boutique que se nota',                'El Moscatel de vendimia tardía es una delicia. Un pelín caro pero es una joya enológica. El packaging también cuida mucho los detalles.'],
            ],

            // ── Quesos Doña Carmen ───────────────────────────────────────────
            'quesos-dona-carmen' => [
                ['estela.bonilla.18@rustikan-demo.es',  5, 'El mejor queso que he probado',       'El majorero curado D.O.P. es espectacular. Textura firme, sabor profundo. Lo tomamos con el vino de La Geria y fue una velada memorable.'],
                ['fatima.brito.19@rustikan-demo.es',    5, 'Artesanía láctea de verdad',          'El semicurado al pimentón es diferente a todo. Ese toque ahumado con el queso de cabra... una maravilla. Gracias Doña Carmen por mantener esta tradición.'],
                ['fernando.cabrera.20@rustikan-demo.es',4, 'Calidad y autenticidad garantizada',  'El queso tierno fresco es suave y perfecto para ensaladas. La cuajada de cabra nos encantó de postre. Muy recomendable.'],
                ['gabriel.hernandez.21@rustikan-demo.es',5,'DOP que se merece cada distinción',   'Llevo años comprando aquí desde Madrid. Es el único queso majorero que puedo conseguir con D.O.P. auténtica. El envío llega perfectamente refrigerado.'],
                ['gloria.perez.22@rustikan-demo.es',    4, 'Imprescindible en Lanzarote',         'Si visitas la isla y no te traes un queso de Doña Carmen, te has perdido lo mejor. Pequeñas tandas que garantizan frescura.'],
            ],

            // ── Lácteos Conejera ─────────────────────────────────────────────
            'lacteos-conejera' => [
                ['hector.curbelo.23@rustikan-demo.es',  5, 'Yogur que sabe a yogur de verdad',    'El yogur natural de cabra no tiene comparación con los industriales. Cremoso, sin azúcar, perfecto con miel de palma. Me lo traigo siempre que bajo al norte.'],
                ['ivan.betancor.24@rustikan-demo.es',   4, 'Leche fresca excepcional',            'La leche de cabra en botella de vidrio retornable es un detalle sostenible que se agradece. Sabor limpio y auténtico.'],
                ['ines.reyes.25@rustikan-demo.es',      5, 'Requesón que no olvidaré',            'El requesón fresco con miel de palma es uno de los mejores desayunos que he tenido en mi vida. Cabras criadas en libertad, se nota en el sabor.'],
            ],

            // ── Pescados El Charco ───────────────────────────────────────────
            'pescados-el-charco' => [
                ['isabel.acuna.26@rustikan-demo.es',    5, 'Pescado fresco como ninguno',         'La vieja canaria llegó tan fresca que olía al mar. La preparé a la plancha con mojo rojo y fue el mejor plato del verano. Sergio sabe lo que hace.'],
                ['jaime.padilla.27@rustikan-demo.es',   5, 'Arrecife en tu mesa',                 'El cherne para el sancocho canario era de matrícula. Limpio, sin espinas malas, de un tamaño perfecto. Los calamares también estaban increíbles.'],
                ['jorge.toledo.28@rustikan-demo.es',    4, 'Atún rojo de calidad sushi',          'Pedí el taco de atún rojo y llegó en perfectas condiciones con el hielo. Calidad para sashimi sin salir de Lanzarote. Muy impresionante.'],
                ['julia.rivero.29@rustikan-demo.es',    5, 'El mejor pescado de la isla',         'Pesco desde pequeña y sé reconocer un pescado de calidad. La sama estaba en su punto exacto. Descarga diaria que se nota mucho.'],
                ['laura.morales.30@rustikan-demo.es',   4, 'Entrega puntual y bien envasado',     'Los calamares limpios y listos para freír son un lujo. Perfectos a la plancha con limón. Volveré cada semana.'],
            ],

            // ── Mariscos Punta Mujeres ───────────────────────────────────────
            'mariscos-punta-mujeres' => [
                ['lorena.suarez.31@rustikan-demo.es',   5, 'Las lapas más frescas de la isla',    'Las lapas a la plancha con mojo verde fueron el aperitivo perfecto. Recogidas esa misma mañana en la costa norte. Sabor a mar puro.'],
                ['lucas.perdomo.32@rustikan-demo.es',   5, 'Pulpo que se deshace en la boca',     'El pulpo canario limpio y listo para cocer es un regalo. Lo preparé con papas arrugadas y resultó el plato estrella de la reunión familiar.'],
                ['manuel.gonzalez.33@rustikan-demo.es', 4, 'Pesca artesanal que se aprecia',      'El bocinegro en oferta tenía una frescura brutal. Al horno con patatas se convirtió en la estrella de la cena. Repetiré sin duda.'],
                ['marcos.rodriguez.34@rustikan-demo.es',5, 'El norte de Lanzarote en tu plato',   'Los camarones canarios cocidos con sal marina fueron el mejor aperitivo del año. Pequeños pero con un sabor intensísimo.'],
            ],

            // ── Panadería del Norte ──────────────────────────────────────────
            'panaderia-del-norte' => [
                ['marina.martin.35@rustikan-demo.es',   5, 'Pan de millo que me recuerda a mi abuela', 'Llevaba años sin comer pan de millo como el de antes. Miga densa, corteza dorada y ese sabor dulce del maíz canario. Gracias por mantener esto vivo.'],
                ['martin.santana.36@rustikan-demo.es',  5, 'Horno que huele a tradición',         'Los rosquetes anisados con café con leche son el desayuno perfecto. Los bizcochos lanzaroteños son adictivos. Pedido recurrente cada semana.'],
                ['miguel.marrero.37@rustikan-demo.es',  4, 'Buena panadería artesanal',           'El pan de papas está muy logrado. La masa madre se nota. El bizcocho en oferta nos llegó en perfecto estado y duró tres días en casa... porque lo devoramos.'],
                ['nerea.bonilla.38@rustikan-demo.es',   5, 'La mejor repostería del norte',       'Las rosquetas están hechas con amor. No como los industriales. Lucia tiene un don especial para los dulces tradicionales. ¡No paramos de comer!'],
            ],

            // ── Horno La Geria ───────────────────────────────────────────────
            'horno-la-geria' => [
                ['noelia.brito.39@rustikan-demo.es',    5, 'Las truchas de batata son un sueño',  'Las truchas de batata son el dulce navideño que esperaba todo el año. Las encargué fuera de temporada y por suerte las hacen todo el año. Un regalo perfecto.'],
                ['oscar.cabrera.40@rustikan-demo.es',   4, 'Queque marmolado como el de siempre', 'El queque tiene esa textura húmeda que se consigue solo con buenos huevos y mantequilla real. Los suspiros de mojo son originales y deliciosos.'],
                ['pablo.hernandez.41@rustikan-demo.es', 5, 'Repostería canaria de primer nivel',  'Descubrí el Horno La Geria en un viaje y desde entonces pido online. Los precios son justos para la calidad que tienen. Muy satisfecho.'],
            ],

            // ── Artesanos de Teguise ─────────────────────────────────────────
            'artesanos-de-teguise' => [
                ['patricia.perez.42@rustikan-demo.es',  5, 'El timple más bonito que he visto',   'Compré el timple artesano como regalo para mi padre músico. La calidad de la madera y el acabado son impresionantes. Ya le saca canciones todos los días.'],
                ['paula.curbelo.43@rustikan-demo.es',   5, 'Cerámica que es arte',                'El jarrón de cerámica volcánica es la pieza más especial de mi casa. El efecto del barro negro canario es único. Javier explica la historia de cada pieza.'],
                ['raquel.betancor.44@rustikan-demo.es', 4, 'Mortero perfecto para mojos',         'El mortero de piedra volcánica es robusto y con mucho carácter. Perfecto para hacer mojos a mano. El granito volcánico le da un sabor especial.'],
                ['ruben.reyes.45@rustikan-demo.es',     5, 'Artesanía viva de Teguise',            'Vine al mercadillo de Teguise y luego compré online. El cuadro de lava esmaltada con roseta canaria es una obra de arte. Absolutamente recomendable.'],
            ],

            // ── Cestería Mancha Blanca ───────────────────────────────────────
            'cesteria-mancha-blanca' => [
                ['sara.acuna.46@rustikan-demo.es',      5, 'La cesta de palma es perfecta',       'La cesta trenzada a mano es resistente, preciosa y huele a palmera. La uso para ir al mercado cada semana. Mucho mejor que cualquier bolsa de tela.'],
                ['sergio.padilla.47@rustikan-demo.es',  4, 'Sombrero canario auténtico',          'El sombrero de palma es el complemento perfecto para los días de sol en la isla. Hecho a mano, se nota la diferencia con los turísticos de bazar.'],
                ['sofia.toledo.48@rustikan-demo.es',    5, 'Tradición que merece apoyo',          'Comprar en Cestería Mancha Blanca es apoyar a una familia artesana que lleva generaciones trenzando palma. La estera es preciosa en la mesa del comedor.'],
                ['tania.rivero.49@rustikan-demo.es',    5, 'Sombrero en oferta: acierto total',   'El sombrero canario estaba en oferta y dudé, pero lo pedí y es de una calidad excepcional. Te lo agradezco, Javier. Un regalo ideal para cualquier amigo del sol.'],
            ],

            // ── Tiendas del TiendaSeeder ─────────────────────────────────────

            'carniceria-el-volcan' => [
                ['aitana.cabrera.0@rustikan-demo.es',   5, 'Cabrito majorero insuperable',        'El cabrito criado en libertad cerca del volcán tiene un sabor que no existe en la península. Lo preparé asado y fue la estrella de la Navidad familiar.'],
                ['beatriz.toledo.8@rustikan-demo.es',   4, 'Embutido artesano de calidad',        'El chorizo canario tiene el punto exacto de pimentón. Las costillas de cerdo negro para adobar son una maravilla.'],
                ['carlos.morales.10@rustikan-demo.es',  5, 'La mejor carnicería de San Bartolomé','Compro aquí cada semana. La carne de cabra guisada se deshace sola. Muy recomendable para quien quiera cocinar recetas canarias auténticas.'],
            ],

            'la-majada-de-teguise' => [
                ['david.rodriguez.14@rustikan-demo.es', 5, 'Solomillo que no olvidaré',           'La ternera madurada 21 días es de otro nivel. Tierna, jugosa, con un sabor profundo. Carlos Díaz sabe elegir la mejor carne.'],
                ['elena.santana.16@rustikan-demo.es',   5, 'El cordero lechal más tierno',        'El medio cordero lechal de Lanzarote al horno fue el plato de la cena de aniversario. Perfecto en todos los sentidos.'],
                ['fatima.brito.19@rustikan-demo.es',    4, 'Hamburguesas de cabra sorprendentes', 'No esperaba que una hamburguesa de cabra majorera pudiera estar tan buena. Las voy a repetir seguro, ideales para barbacoa.'],
            ],

            'pescaderia-mar-azul' => [
                ['gabriel.hernandez.21@rustikan-demo.es',5,'La vieja más fresca de Órzola',       'Órzola es el sitio perfecto para el pescado del norte y Mar Azul lo sabe aprovechar. La vieja del día estaba en su punto ideal.'],
                ['hector.curbelo.23@rustikan-demo.es',  4, 'Cherne de bajura de primera',         'El cherne capturado por pescadores locales tiene una textura firmísima. Perfecto para el sancocho. Muy recomendable.'],
                ['ivan.betancor.24@rustikan-demo.es',   5, 'Calamares como nunca',                'Los calamares frescos de Órzola son pequeños y tiernos. A la plancha con ajo y limón... espectaculares.'],
            ],

            'la-lonja-de-arrecife' => [
                ['jaime.padilla.27@rustikan-demo.es',   5, 'La langosta más fresca de Canarias',  'Pedí la langosta para una ocasión especial y fue la mejor decisión. Llegó viva y el sabor fue impresionante. Los precios son justos para lo que ofrecen.'],
                ['julia.rivero.29@rustikan-demo.es',    5, 'El mero de bajura es espectacular',   'La lonja de Arrecife tiene lo mejor del puerto cada mañana. El mero a la sal fue un éxito rotundo en la reunión familiar.'],
                ['laura.morales.30@rustikan-demo.es',   4, 'Chopas frescas y bien de precio',     'Las chopas son perfectas para fritada canaria. Precio muy competitivo y siempre fresquísimas. Mi pescadería de referencia en Arrecife.'],
            ],

            'panaderia-la-tahona' => [
                ['lorena.suarez.31@rustikan-demo.es',   5, 'El pan de millo de mi infancia',      'Este pan me recuerda exactamente al de mi abuela en Teguise. La masa madre hace toda la diferencia. Llevo pediendo todas las semanas.'],
                ['lucas.perdomo.32@rustikan-demo.es',   4, 'Empanadas de atún perfectas',         'Las empanadas caseras son jugosas y con mucho relleno. Perfectas para el almuerzo. La hogaza de masa madre dura varios días en perfectas condiciones.'],
                ['manuel.gonzalez.33@rustikan-demo.es', 5, 'Artesanía panadera real',             'Pocas panaderías en la isla hacen pan de verdad. La Tahona es una de ellas. El olor cuando abres el paquete es irresistible.'],
            ],

            'obrador-la-caleta' => [
                ['marina.martin.35@rustikan-demo.es',   5, 'El mejor croissant de Lanzarote',     'El croissant de mantequilla es hojaldrado, crujiente y con ese interior tierno perfecto. Vine de vacaciones a Playa Blanca y ahora pido online. No hay excusa.'],
                ['nerea.bonilla.38@rustikan-demo.es',   5, 'La tarta de almendra es de otro mundo','La tarta de almendras con receta de La Gomera es probablemente el mejor postre que he comido en mi vida. Un regalo que no olvidaré.'],
                ['oscar.cabrera.40@rustikan-demo.es',   4, 'Bollería de calidad sin aceite de palma','Los bollos de anís en oferta son la merienda perfecta. Me alegra que no usen aceite de palma. La espelta integral también está muy lograda.'],
            ],

            'queseria-la-majorera' => [
                ['pablo.hernandez.41@rustikan-demo.es', 5, 'Queso en aceite que es un vicio',     'El tarro de queso curado en aceite de oliva es el mejor aperitivo del mundo. Con una copa de Malvasía seca... perfección absoluta.'],
                ['patricia.perez.42@rustikan-demo.es',  5, 'La quesería más valorada con razón',  'La mejor quesería que he visitado en Canarias. El curado de 6 meses tiene una complejidad de sabor enorme. Me llevo siempre cuatro piezas a Madrid.'],
                ['paula.curbelo.43@rustikan-demo.es',   4, 'Queso fresco para ensaladas insuperable','El queso fresco de cabra es suave y cremoso. Perfecto en ensalada con tomate canario y orégano. Precios muy razonables para la calidad.'],
            ],

            'bodega-los-volcanes' => [
                ['raquel.betancor.44@rustikan-demo.es', 5, 'La Malvasía que me conquistó',        'Llegué a Lanzarote sin saber nada de vinos volcánicos y ahora soy fan total de la Malvasía de Los Volcanes. Ese mineral único te cambia la percepción del vino.'],
                ['ruben.reyes.45@rustikan-demo.es',     5, 'El Moscatel Dulce con postre: 10/10', 'El Moscatel Dulce con bienmesabe canario es la combinación más perfecta que he probado jamás. Una experiencia gastronómica completa.'],
                ['sara.acuna.46@rustikan-demo.es',      4, 'DO Lanzarote que merece más fama',    'El Listán Negro joven es fresco y muy diferente a cualquier tinto peninsular. Precio justo para una botella con tanto carácter.'],
            ],

            'vina-la-caldera' => [
                ['sergio.padilla.47@rustikan-demo.es',  5, 'Terroir único en el mundo',           'El Blanco Seco Reserva con 12 meses en barrica es una obra maestra. Los viñedos centenarios en hoyos de picón dan al vino una mineralidad que no existe en ningún otro lugar.'],
                ['sofia.toledo.48@rustikan-demo.es',    4, 'Pack de cata: la mejor compra',       'El pack de cata de 3 vinos es perfecto para iniciarse en los vinos de La Geria. El rosado volcánico me sorprendió especialmente.'],
                ['tania.rivero.49@rustikan-demo.es',    5, 'Una visita obligatoria y una compra imprescindible','El viñedo junto a Timanfaya es de una belleza brutal. Y los vinos están a la altura del paisaje. El blanco reserva es de colección.'],
            ],

            'el-grifo-tienda' => [
                ['aitana.cabrera.0@rustikan-demo.es',   5, 'La bodega más antigua de Canarias',   'Visitar El Grifo y luego comprar la Malvasía Colección online es lo mejor que puedes hacer en Lanzarote. 1775 años de historia en cada botella.'],
                ['adrian.hernandez.1@rustikan-demo.es', 5, 'El licor de tuno indio es adictivo',  'No conocía el licor de higo pico y ahora es mi aperitivo favorito. Dulce, refrescante y con un sabor absolutamente único. Un hallazgo.'],
                ['alba.perez.2@rustikan-demo.es',       4, 'Entrada al museo: experiencia única',  'La visita guiada al museo más la cata de 3 vinos vale cada euro. Aprendes muchísimo sobre la viticultura volcánica y sales con las ideas muy claras.'],
                ['alejandro.curbelo.3@rustikan-demo.es',5, 'El Brut Nature que no esperaba',      'Un espumoso de malvasía con método tradicional en Lanzarote... no me lo imaginaba tan bueno. Burbuja finísima y postgusto largo. Para repetir.'],
            ],

            'taller-la-tinaja' => [
                ['andrea.betancor.4@rustikan-demo.es',  5, 'El jarrón más especial de mi casa',   'El jarrón volcánico modelado a mano es la pieza central de mi salón. Cada vez que lo miro recuerdo Lanzarote. Arte canario que hay que apoyar.'],
                ['angel.reyes.5@rustikan-demo.es',      4, 'Set de tazas que impresionan',         'Las tazas con esmalte que imita la lava son un regalo perfecto para alguien que haya visitado la isla. El detalle del diseño es muy cuidado.'],
                ['antonio.acuna.6@rustikan-demo.es',    5, 'Cerámica que es historia viva',        'El cuenco rústico de barro canario es perfecto para servir los mojos. Auténtico y funcional. Haría no tiene igual para este tipo de artesanía.'],
            ],

            'sal-de-janubio' => [
                ['aroa.padilla.7@rustikan-demo.es',     5, 'La flor de sal más pura que he probado','Las Salinas de Janubio son un espectáculo natural y su sal es excepcional. La flor de sal recogida a mano tiene una textura delicada y un sabor limpio perfecto.'],
                ['beatriz.toledo.8@rustikan-demo.es',   5, 'Sal negra: descubrimiento del año',    'La sal negra volcánica con carbón activo cambió completamente cómo preparo las carnes. Un toque visual y gustativo que impresiona a cualquier invitado.'],
                ['borja.rivero.9@rustikan-demo.es',     4, 'Pack regalo muy bien presentado',      'Regalé el pack de 3 sales a mis padres y fue un éxito. La caja está bien presentada y es un regalo original con mucho carácter canario.'],
            ],
        ];

        $tiendas  = Tienda::pluck('id', 'slug');
        $usuarios = User::pluck('id', 'email');

        $totalCreadas   = 0;
        $missingTiendas = [];
        $missingUsers   = [];

        if ($usuarios->isEmpty()) {
            $this->command?->warn('⚠  No se encontraron usuarios en la BD. Ejecuta primero DemoUsersSeeder.');
        }

        foreach ($data as $tiendaSlug => $resenas) {
            $tiendaId = $tiendas[$tiendaSlug] ?? null;
            if (!$tiendaId) {
                $missingTiendas[] = $tiendaSlug;
                continue;
            }

            foreach ($resenas as [$email, $puntuacion, $titulo, $comentario]) {
                $userId = $usuarios[$email] ?? null;
                if (!$userId) {
                    $missingUsers[] = $email;
                    continue;
                }

                // Eliminar reseña previa del mismo usuario en la misma tienda
                Resena::where('user_id', $userId)->where('tienda_id', $tiendaId)->delete();

                Resena::create([
                    'user_id'     => $userId,
                    'tienda_id'   => $tiendaId,
                    'puntuacion'  => $puntuacion,
                    'titulo'      => $titulo,
                    'comentario'  => $comentario,
                    'created_at'  => now()->subDays(rand(1, 180))->subHours(rand(0, 23)),
                    'updated_at'  => now()->subDays(rand(0, 5)),
                ]);
                $totalCreadas++;
            }

            // Recalcular valoración y total_resenas de la tienda
            $stats = Resena::where('tienda_id', $tiendaId)
                ->selectRaw('COUNT(*) as total, AVG(puntuacion) as media')
                ->first();

            Tienda::where('id', $tiendaId)->update([
                'total_resenas' => $stats->total ?? 0,
                'valoracion'    => round($stats->media ?? 0, 2),
            ]);
        }

        // Recalcular TODAS las tiendas para que total_resenas y valoracion
        // reflejen exactamente las reseñas reales en BD.
        foreach (Tienda::all() as $tienda) {
            $stats = Resena::where('tienda_id', $tienda->id)
                ->selectRaw('COUNT(*) as total, AVG(puntuacion) as media')
                ->first();
            $tienda->update([
                'total_resenas' => $stats->total ?? 0,
                'valoracion'    => round($stats->media ?? 0, 2),
            ]);
        }

        $this->command?->info("DemoResenasSeeder: {$totalCreadas} reseñas creadas y valoraciones recalculadas.");

        if ($missingTiendas) {
            $this->command?->warn('Tiendas no encontradas (' . count($missingTiendas) . '): ' . implode(', ', $missingTiendas));
        }
        if ($missingUsers) {
            $this->command?->warn('Usuarios no encontrados (' . count(array_unique($missingUsers)) . '): ' . implode(', ', array_unique($missingUsers)));
        }
    }
}
