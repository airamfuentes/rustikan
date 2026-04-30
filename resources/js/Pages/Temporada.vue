<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import FooterPublico from '@/Components/FooterPublico.vue';
import { useDarkMode } from '@/Composables/useDarkMode';
import { Sprout, CheckCircle2, CalendarDays, Leaf } from 'lucide-vue-next';
useDarkMode();

const mesActual = new Date().getMonth();

const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

const temporadaPorMes = [
    [
        { nombre: 'Papas nuevas',     desc: 'Las primeras cosechas del año, tiernas y con piel fina.' },
        { nombre: 'Tomates de palo',  desc: 'Tomates cultivados en invernadero, dulces y carnosos.' },
        { nombre: 'Cebollas',         desc: 'Ideales para guisos y sopas de invierno.' },
        { nombre: 'Naranjas',         desc: 'Cítricos en su punto óptimo de madurez.' },
    ],
    [
        { nombre: 'Fresas de Lanzarote', desc: 'Pequeñas y muy aromáticas, cultivadas sin pesticidas.' },
        { nombre: 'Pimientos',           desc: 'Pimientos de padrón y morrones de temporada.' },
        { nombre: 'Lechugas',            desc: 'Cogollos frescos para ensaladas.' },
        { nombre: 'Limones',             desc: 'Perfectos para aderezos y postres.' },
    ],
    [
        { nombre: 'Calabacín',  desc: 'Versátil en la cocina, bajo en calorías.' },
        { nombre: 'Espinacas',  desc: 'Ricas en hierro y perfectas para salteados.' },
        { nombre: 'Remolacha',  desc: 'Dulce y nutritiva, recién cosechada.' },
        { nombre: 'Aguacates',  desc: 'Cultivados en microclimas húmedos de la isla.' },
    ],
    [
        { nombre: 'Guisantes',  desc: 'Tiernos y dulces, ideales en arroces y estofados.' },
        { nombre: 'Alcachofas', desc: 'Temporada corta pero deliciosa.' },
        { nombre: 'Habas',      desc: 'Habas frescas para guisos tradicionales canarios.' },
        { nombre: 'Papas',      desc: 'Variedades locales con denominación de origen.' },
    ],
    [
        { nombre: 'Mangos',  desc: 'Primeros mangos tropicales de la temporada.' },
        { nombre: 'Pepinos', desc: 'Refrescantes y crujientes.' },
        { nombre: 'Tomates', desc: 'Los mejores tomates del año, llenos de sabor.' },
        { nombre: 'Cerezas', desc: 'Importadas de productores de confianza en la península.' },
    ],
    [
        { nombre: 'Sandías',    desc: 'Sandías canarias, dulces y refrescantes.' },
        { nombre: 'Melones',    desc: 'Melones de piel de sapo cultivados localmente.' },
        { nombre: 'Berenjenas', desc: 'Perfectas para el pisto canario.' },
        { nombre: 'Maíz',       desc: 'Mazorcas tiernas para asar o cocer.' },
    ],
    [
        { nombre: 'Papas de verano', desc: 'Segunda cosecha de papas, perfectas en mojo.' },
        { nombre: 'Higos',           desc: 'Higos blancos y negros, muy dulces.' },
        { nombre: 'Pimientos rojos', desc: 'Pimientos maduros para asar y conservar.' },
        { nombre: 'Albahaca',        desc: 'Hierba fresca para salsas y pizzas.' },
    ],
    [
        { nombre: 'Uvas',           desc: 'Uvas de viñedos lanzaroteños bajo arena volcánica.' },
        { nombre: 'Tomates cherry', desc: 'Pequeños y dulces, perfectos para aperitivos.' },
        { nombre: 'Plátanos',       desc: 'Plátanos canarios, más dulces y aromáticos.' },
        { nombre: 'Calabaza',       desc: 'Primeras calabazas de la temporada.' },
    ],
    [
        { nombre: 'Papas nuevas',   desc: 'Tercera cosecha del año, textura perfecta.' },
        { nombre: 'Granadas',       desc: 'Granadas frescas, ricas en antioxidantes.' },
        { nombre: 'Peras',          desc: 'Peras de temporada, jugosas y maduras.' },
        { nombre: 'Queso de cabra', desc: 'Producción láctea de otoño, más concentrada.' },
    ],
    [
        { nombre: 'Boniatos',          desc: 'Boniatos canarios, ideales asados o en puré.' },
        { nombre: 'Castañas',          desc: 'Castañas importadas de productores de confianza.' },
        { nombre: 'Miel de Lanzarote', desc: 'Miel de abeja negra, única en el mundo.' },
        { nombre: 'Cebolla morada',    desc: 'Variedades locales de sabor más suave.' },
    ],
    [
        { nombre: 'Naranjas tempranas', desc: 'Primeras naranjas de la temporada.' },
        { nombre: 'Nueces',             desc: 'Nueces frescas recién descascaradas.' },
        { nombre: 'Acelgas',            desc: 'Acelgas de huerta, perfectas para potajes.' },
        { nombre: 'Berros',             desc: 'Berros frescos para el famoso potaje canario.' },
    ],
    [
        { nombre: 'Naranjas',      desc: 'En su mejor momento, ideales para navidades.' },
        { nombre: 'Aguacates',     desc: 'Segunda temporada de aguacates locales.' },
        { nombre: 'Papas',         desc: 'Las papas de invierno, perfectas para el puchero.' },
        { nombre: 'Miel de palma', desc: 'Guarapo de palma, dulce artesanal canario.' },
    ],
];

const productosTemporada = computed(() => temporadaPorMes[mesActual]);
const mesNombre = computed(() => meses[mesActual]);

const siguienteMes = computed(() => meses[(mesActual + 1) % 12]);
const productosSiguienteMes = computed(() => temporadaPorMes[(mesActual + 1) % 12]);
</script>

<template>
    <Head title="Productos de temporada – Rustikan" />

    <div class="min-h-screen bg-white dark:bg-gray-900">
        <NavbarPublico />

        <!-- Hero -->
        <section class="bg-gradient-to-br from-green-600 via-emerald-600 to-lime-700 pb-20 pt-32 text-white">
            <div class="mx-auto max-w-4xl px-4 text-center">
                <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-white/10 backdrop-blur-sm ring-1 ring-white/20">
                    <Sprout class="h-8 w-8" />
                </div>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">De temporada en {{ mesNombre }}</h1>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-green-100">
                    Productos frescos y locales disponibles ahora mismo en las tiendas de Rustikan. Compra lo que la tierra de Lanzarote ofrece hoy.
                </p>
            </div>
        </section>

        <!-- Contenido -->
        <section class="py-16">
            <div class="mx-auto max-w-4xl px-4 sm:px-6">

                <!-- Productos actuales -->
                <div class="mb-16">
                    <div class="mb-8 flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400">
                            <CheckCircle2 class="h-5 w-5" />
                        </span>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Disponible ahora · {{ mesNombre }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Busca estas variedades en las tiendas locales</p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div
                            v-for="producto in productosTemporada"
                            :key="producto.nombre"
                            class="flex items-start gap-4 rounded-2xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-5 transition-shadow hover:shadow-md"
                        >
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                                <Leaf class="h-5 w-5" />
                            </span>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ producto.nombre }}</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ producto.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Próximo mes -->
                <div class="mb-16">
                    <div class="mb-8 flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400">
                            <CalendarDays class="h-5 w-5" />
                        </span>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Próximamente · {{ siguienteMes }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Anticipa tus compras del mes que viene</p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div
                            v-for="producto in productosSiguienteMes"
                            :key="producto.nombre"
                            class="flex items-start gap-4 rounded-2xl border border-amber-100 dark:border-amber-900/30 bg-amber-50/50 dark:bg-amber-900/10 p-5 opacity-80"
                        >
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                                <Leaf class="h-5 w-5" />
                            </span>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ producto.nombre }}</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ producto.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <FooterPublico />
    </div>
</template>
