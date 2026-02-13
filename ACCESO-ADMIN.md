# 🛡️ Acceso al Panel de Administración

## Credenciales de Administrador

Para acceder al panel de administración utiliza estas credenciales:

- **Email:** `admin@rustikan.com`
- **Password:** `password`

## Rutas de Acceso

### Opción 1: Desde la URL
Una vez autenticado como admin, accede directamente a:
```
http://localhost/admin/dashboard
```
o
```
http://tusitio.com/admin/dashboard
```

### Opción 2: Desde la Interfaz

1. **Página de Inicio (Home):**
   - Si no estás autenticado: Haz clic en **"Acceder"** → Login
   - Una vez logueado como admin, verás el botón **"🛡️ Admin"** en la esquina superior derecha

2. **Dashboard de Usuario:**
   - Si ya estás autenticado, verás el enlace **"🛡️ Panel Admin"** en el menú de navegación
   - También hay un badge **"ADMIN"** visible junto a tu nombre
   - En dispositivos móviles, aparece en el menú hamburguesa

## Rutas del Panel Admin

Una vez dentro del panel admin, tienes acceso a:

- **Dashboard:** `/admin/dashboard` - Vista general con estadísticas
- **Tiendas:** `/admin/tiendas` - Gestión completa de tiendas
- **Productos:** `/admin/productos` - Gestión de productos y stock
- **Pedidos:** `/admin/pedidos` - Administración de pedidos
- **Usuarios:** `/admin/usuarios` - Gestión de usuarios
- **Categorías:** `/admin/categorias` - Gestión de categorías

## Características del Dashboard

### 📊 Estadísticas Principales
- Total de tiendas (activas/visibles)
- Total de productos (disponibles/sin stock)
- Valor total del inventario
- Pedidos (pendientes/completados)

### 🚨 Alertas de Stock
- **Productos SIN STOCK:** Alerta roja con productos agotados
- **Productos BAJO STOCK:** Alerta naranja con productos cerca del mínimo
- Enlaces directos para editar cada producto

### 📈 Visualizaciones
- Gráfico de productos por categoría
- Top 5 productos destacados
- Top 5 tiendas con más productos

### ⚡ Accesos Rápidos
- Enlaces directos a todas las secciones de gestión
- Filtros y búsqueda en cada tabla
- Acciones inline (toggle visible/activa, editar stock, etc.)

## Usuarios de Prueba

Además del admin, hay usuarios de prueba:

- **Usuario 1:** `juan@example.com` / `password` (role: user)
- **Usuario 2:** `maria@example.com` / `password` (role: user)

## Regenerar Base de Datos

Si necesitas resetear la base de datos con datos de prueba:

```bash
php artisan migrate:fresh --seed
```

Esto recreará todas las tablas y volverá a poblarlas con:
- 1 usuario admin
- 2 usuarios de prueba
- 8 categorías
- 4 tiendas
- 9 productos con stock
- 0 pedidos (de momento)

## Protección del Panel

El panel admin está protegido por dos middlewares:
1. **auth:** El usuario debe estar autenticado
2. **admin:** El usuario debe tener `role = 'admin'` en la base de datos

Si un usuario normal intenta acceder a `/admin/*`, será redirigido automáticamente.
