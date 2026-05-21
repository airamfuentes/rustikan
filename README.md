<div align="center">

# Rustikan

**Marketplace local de productores de Lanzarote**

Conecta a productores, artesanos y pequeños negocios de Lanzarote con consumidores. Compra directa, sostenible y fomentando la economía circular.

---

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue-3-42b883?style=flat-square&logo=vue.js&logoColor=white)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-2-9553E9?style=flat-square)](https://inertiajs.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3-38bdf8?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Stripe](https://img.shields.io/badge/Stripe-Payments-6772E5?style=flat-square&logo=stripe&logoColor=white)](https://stripe.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)

</div>

---

## ¿Qué es Rustikan?

Rustikan es una plataforma e-commerce de proximidad diseñada específicamente para el ecosistema productor de Lanzarote. Permite a agricultores, bodegueros, artesanos, pescaderos y otros productores locales crear su tienda digital y llegar directamente al consumidor final, sin comisiones de alta y con control total de su negocio.

La plataforma está pensada para funcionar en producción real, con gestión de pedidos, pagos online, logística centralizada y un sistema de fidelización propio.

---

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | Laravel 12 (PHP 8.2) |
| Frontend | Vue 3 (Composition API) |
| Routing/SPA | Inertia.js 2 |
| Estilos | Tailwind CSS 3 |
| Base de datos | MySQL |
| Pagos | Stripe (Checkout Sessions + Webhooks) |
| Email | Resend |
| Mapas | Leaflet + MarkerCluster |
| Iconos | Lucide Vue Next |
| Build | Vite 5 |
| Avatares | DiceBear API |
| Imágenes | Unsplash API |

---

## Funcionalidades principales

### Para el consumidor
- Catálogo de tiendas y productos por categoría con buscador en tiempo real
- Filtros por categoría, precio, valoración y disponibilidad
- Carrito de compra con checkout multi-paso (dirección, método de pago)
- Pago con tarjeta vía Stripe o con Rusticoin (moneda interna)
- Historial de pedidos con seguimiento de estado
- Reseñas verificadas (solo usuarios que hayan completado un pedido)
- Tiendas favoritas con sincronización en tiempo real
- Mapa interactivo de productores en Lanzarote
- Chat en tiempo real con el equipo de soporte
- Sistema de notificaciones en campana
- Alertas automáticas por email cuando un producto sin stock vuelve a estar disponible

### Para el productor (Owner)
- Panel de gestión de tienda: descripción, imágenes, horarios, contacto
- Gestión completa de catálogo: productos, precios, stock, ofertas, destacados
- Solicitudes de cambio pendientes de aprobación por admin
- Dashboard con métricas de ventas, valoraciones e ingresos
- Historial de pedidos recibidos
- Solicitud de creación de tienda desde la plataforma

### Para el almacén (Supplier)
- Panel de pedidos en tiempo real con polling automático (5 segundos)
- Búsqueda y filtrado de pedidos con debounce instantáneo
- Gestión de estados: pendiente → en preparación → enviado → entregado
- Salida de pedidos: checklist masiva para marcar múltiples pedidos como enviados de una vez
- Modal de incidencias con motivo obligatorio (visible en admin)
- Inventario global de stock con alertas de bajo stock y sin stock
- Búsqueda de tiendas en la vista de inventario
- **Entrada de mercancía**: registro multi-producto bajo un mismo albarán, con numeración automática correlativa (ALB-YYYY-XXXX), búsqueda de tienda por nombre, auto-relleno del proveedor y ajuste de stock en tiempo real
- Historial completo de pedidos gestionados con exportación a PDF
- Chat directo con administración

### Para el administrador
- Dashboard con KPIs: ventas, usuarios, pedidos, ingresos
- Gestión completa de usuarios, tiendas, productos y categorías
- **Albaranes por tienda**: historial completo de entradas de mercancía con búsqueda, filtros de fecha y estadísticas (total de entradas, unidades, entradas de hoy)
- Aprobación/rechazo de solicitudes de cambio de tiendas
- Gestión de solicitudes de creación de nueva tienda
- Gestión de solicitudes de empleo (TrabajaConNosotros)
- Panel de incidencias con motivos del supplier visibles
- Exportación de facturas e historial en PDF
- Gestión de reseñas
- Panel de ingresos con gráficos por período
- Activar/desactivar tiendas (recepción de pedidos) y visibilidad pública desde tabla con toggle switches

---

## Arquitectura

```
┌─────────────────────────────────────────────────────┐
│                    Cliente (Vue 3)                   │
│         Inertia.js — sin API REST, sin JSON manual  │
└────────────────────┬────────────────────────────────┘
                     │  HTTP / Inertia responses
┌────────────────────▼────────────────────────────────┐
│                 Laravel 12 (Backend)                 │
│                                                     │
│  Controllers/          Models/                      │
│  ├── Admin/            ├── User                     │
│  ├── Owner/            ├── Tienda                   │
│  ├── Supplier/         ├── Producto                 │
│  └── Auth/             ├── Pedido / PedidoItem      │
│                        ├── EntradaMercancia         │
│  Middleware/           ├── StockAlert               │
│  ├── RoleCheck         ├── Notificacion             │
│  └── VerifyEmail       ├── Resena                   │
│                        ├── RusticoinTransaction     │
│  Observers/            ├── MensajeChat              │
│  └── ProductoObserver  └── ActivityLog              │
└────────────────────┬────────────────────────────────┘
                     │
         ┌───────────┼───────────┐
         ▼           ▼           ▼
       MySQL       Stripe      Resend
```

### Roles del sistema

| Rol | Descripción |
|---|---|
| `user` | Consumidor registrado |
| `owner` | Propietario de una o varias tiendas |
| `supplier` | Almacén central que gestiona y despacha pedidos |
| `admin` | Administración completa de la plataforma |

---

## Módulos destacados

### Rusticoin
Moneda interna de fidelización. Los usuarios acumulan Rusticoins con sus compras y pueden usarlos como método de pago alternativo a Stripe. El saldo se gestiona con transacciones auditadas en base de datos.

### Entrada de mercancía (Supplier)
El supplier registra la llegada de stock al almacén. Cada entrada agrupa múltiples productos bajo un mismo albarán, con numeración automática correlativa (ALB-YYYY-XXXX). Al seleccionar una tienda se auto-rellena el proveedor y se cargan sus productos disponibles. El stock de cada producto se actualiza automáticamente al guardar.

### Alertas de stock (StockAlert)
Los usuarios pueden suscribirse a un aviso cuando un producto sin stock vuelva a estar disponible. El `ProductoObserver` detecta la transición de stock 0 → positivo y envía automáticamente el email `StockDisponible` a todos los suscriptores del producto.

### Emails automáticos
- **PedidoConfirmado**: se envía al usuario cuando el supplier mueve el pedido a `en_preparacion`
- **PedidoEnviado**: se envía al usuario cuando el pedido pasa a `enviado`
- **StockDisponible**: se envía a los usuarios suscritos cuando un producto con stock 0 es repuesto
- **RecargaMonedero**: confirmación de recarga de Rusticoins

### Sistema de notificaciones
Notificaciones en campana con badge contador. Las notificaciones se cargan al abrir el panel y se eliminan del servidor al cerrarlo. Soporte para iconos y colores por tipo de evento.

### Chat Admin ↔ Supplier
Canal de mensajería interna entre administración y el equipo de almacén con polling en tiempo real.

### Solicitudes de cambio
Los owners no pueden modificar directamente ciertos datos de su tienda (nombre, categoría, datos sensibles). Envían una solicitud de cambio que el admin aprueba o rechaza con comentario.

### Incidencias
Cuando el supplier no puede procesar un pedido, abre un modal de incidencia que obliga a escribir el motivo. Ese motivo queda registrado en el pedido y es visible en el panel de administración.

### Exportación PDF
Facturas de pedidos y exportaciones del historial disponibles para admin y supplier.

---

## Categorías de producto

- Frutas y verduras
- Vinoteca
- Lácteos y quesos
- Pescados y mariscos
- Panadería y repostería
- Artesanía

---

## Internacionalización

La interfaz pública está disponible en **5 idiomas**: Español, English, Français, Deutsch, Italiano. El cambio de idioma es persistente y se aplica a todos los textos de la UI.

---

## Estructura de carpetas relevante

```
app/
├── Http/Controllers/
│   ├── Admin/          # Panel de administración
│   ├── Owner/          # Panel del productor
│   ├── Supplier/       # Panel del almacén
│   └── Auth/           # Registro, login, verificación
├── Mail/               # Mailables (PedidoConfirmado, PedidoEnviado, StockDisponible…)
├── Models/             # Eloquent models
├── Observers/          # ProductoObserver (alertas de stock)
└── Http/Middleware/

resources/js/
├── Pages/
│   ├── Admin/          # Vistas del panel admin
│   ├── Owner/          # Vistas del panel productor
│   ├── Supplier/       # Vistas del panel almacén
│   ├── Auth/           # Login, registro
│   ├── Info/           # Páginas estáticas (misión, contacto…)
│   └── Profile/        # Perfil de usuario
├── Components/         # Componentes reutilizables
├── Layouts/            # Layouts por rol
└── i18n/               # Traducciones (es/en/fr/de/it)

database/
├── migrations/
└── seeders/            # Demo data: tiendas, productos, usuarios
```

---

## Requisitos

- PHP 8.2+
- Node.js 18+
- MySQL 8+
- Composer
- Cuenta Stripe (para pagos)
- Cuenta Resend (para emails)

---

<div align="center">

Hecho con cariño en Lanzarote

</div>
