<div align="center">

# 🌿 Rustikan

**Marketplace de productos locales de Lanzarote**

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=flat-square&logo=vue.js&logoColor=white)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-2-9553E9?style=flat-square)](https://inertiajs.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)

</div>

---

## ¿Qué es Rustikan?

Rustikan es una plataforma web para descubrir, explorar y comprar productos artesanales y locales de Lanzarote. Conecta a productores locales con compradores a través de un marketplace moderno con mapa interactivo, tiendas por categorías y gestión de pedidos.

---

## ✨ Funcionalidades principales

### 🛒 Tienda pública
- **Inicio** con buscador en tiempo real y mapa Leaflet interactivo de tiendas
- **Catálogo por categorías** con vistas en cuadrícula y lista
- **Detalle de tienda** con portada, productos, contacto y filtros
- **Carrito de compra** persistente (localStorage)

### 🛡️ Panel de administración
- Gestión completa de **tiendas**, **productos**, **pedidos**, **usuarios** y **categorías**
- Subida de imágenes con **recorte interactivo** (logo circular · portada panorámica)
- Dashboard con estadísticas e ingresos
- Registro de actividad auditada

### 🔐 Seguridad
- Rate limiting en registro, login y recuperación de contraseña
- Control de acceso por roles (`admin` / `user`)
- Validación estricta en todos los formularios
- Imágenes servidas desde disco `public` con symlink seguro

---

## 🏗️ Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.3 · Laravel 12 |
| Frontend | Vue 3 (Composition API) · Inertia.js 2 |
| Estilos | Tailwind CSS 3 · paleta personalizada `tierra` |
| Bundler | Vite 7 |
| Base de datos | MySQL |
| Mapa | Leaflet.js + MarkerCluster |
| Imágenes | Cropperjs v1 |
| Auth | Laravel Breeze |

---

## 🚀 Instalación local

### Requisitos previos
- PHP 8.3+
- Composer
- Node.js 20+
- MySQL

### 1. Clonar el repositorio
```bash
git clone https://github.com/airamfuentes/rustikan.git
cd rustikan
```

### 2. Instalar dependencias
```bash
composer install
npm install
```

### 3. Configurar entorno
```bash
cp .env.example .env
php artisan key:generate
```

Edita `.env` con tus credenciales de base de datos:
```env
DB_DATABASE=rustikan
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Base de datos y datos de prueba
```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
```

### 5. Compilar assets y arrancar
```bash
npm run dev
php artisan serve
```

Abre [http://localhost:8000](http://localhost:8000)

---

## 👤 Credenciales de prueba

| Rol | Email | Contraseña |
|---|---|---|
| Admin | `admin@rustikan.com` | `password` |
| Usuario | `juan@example.com` | `password` |
| Usuario | `maria@example.com` | `password` |

---

## 📁 Estructura del proyecto

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # CRUD de tiendas, productos, pedidos…
│   │   └── Auth/           # Autenticación (Breeze)
│   └── Middleware/         # EnsureUserIsAdmin
├── Models/                 # Tienda, Producto, Pedido, Categoria…
resources/
├── js/
│   ├── Components/         # TiendaCard, MapaTiendas, ImageCropper…
│   └── Pages/
│       ├── Admin/          # Panel, Tiendas, Productos, Pedidos…
│       ├── Categorias/
│       └── Auth/
├── css/
database/
├── migrations/
└── seeders/                # 25 tiendas · 80+ productos
routes/
├── web.php                 # Rutas públicas y admin
└── auth.php                # Rutas de autenticación
```

---

## 🔒 Seguridad implementada

- `throttle:5,1` en registro y reset de contraseña
- `throttle:3,1` en solicitud de recuperación
- `throttle:60,1` en todas las rutas admin
- Rate limiting nativo en login (`LoginRequest`)
- `.env` excluido del repositorio
- Contraseñas hasheadas con `bcrypt`

---

## 📄 Licencia

Este proyecto es de uso privado. Todos los derechos reservados © 2026 Rustikan.
