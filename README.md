# 🚀 Rustikan

Proyecto desarrollado con Laravel + Inertia.js + Vue 3

## 📦 Stack Tecnológico

- **Backend**: Laravel 12
- **Frontend**: Vue.js 3 + Inertia.js
- **Estilos**: Tailwind CSS v4
- **Base de datos**: MySQL
- **Autenticación**: Laravel Breeze
- **Build**: Vite 7

## ⚙️ Requisitos

- PHP 8.4+
- Composer
- Node.js 20+
- MySQL 8.0+

## 🛠️ Instalación

### 1. Instalar dependencias

```bash
composer install
npm install --legacy-peer-deps
```

### 2. Configurar entorno

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configurar base de datos

Edita `.env`:
```env
DB_DATABASE=rustikan
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

Crear base de datos:
```sql
CREATE DATABASE rustikan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Migrar base de datos

```bash
php artisan migrate
```

### 5. Iniciar desarrollo

```bash
# Terminal 1: Vite (hot reload)
npm run dev

# Terminal 2: Laravel (opcional, Apache ya sirve en http://rustikan)
php artisan serve
```

## 🌐 URLs

- **Desarrollo**: http://rustikan o http://localhost:8000
- **Vite**: http://localhost:5173

## 📁 Estructura

```
resources/js/
├── Components/       # Componentes Vue reutilizables
├── Layouts/          # Layouts (Authenticated, Guest)
├── Pages/            # Páginas de la aplicación
│   ├── Auth/        # Login, Register, etc.
│   ├── Profile/     # Gestión de perfil
│   └── Home.vue     # Página principal
├── app.js           # Entry point
└── bootstrap.js     # Config Axios

routes/
└── web.php          # Rutas de la aplicación
```

## 🚀 Comandos útiles

```bash
# Desarrollo
npm run dev              # Vite con hot reload
npm run build            # Compilar para producción

# Base de datos
php artisan migrate      # Ejecutar migraciones
php artisan migrate:fresh # Reiniciar BD
php artisan db:seed      # Seeders

# Cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Otros
php artisan tinker       # Consola interactiva
```

## 👤 Autor

**Airam Fuentes**
- GitHub: [@airamfuentes](https://github.com/airamfuentes)
- Email: airamfuentes2020@gmail.com

