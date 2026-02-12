# Rustikan

Proyecto Laravel + Inertia.js + Vue 3

## Stack

- Laravel 12
- Vue.js 3 + Inertia.js
- Tailwind CSS v4
- MySQL
- Vite 7

## Instalación

```bash
# Dependencias
composer install
npm install --legacy-peer-deps

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Base de datos (edita .env primero)
php artisan migrate

# Iniciar desarrollo
npm run dev
```

## Desarrollo

```bash
npm run dev              # Vite con hot reload
php artisan serve        # Servidor Laravel
php artisan migrate      # Ejecutar migraciones
php artisan tinker       # Consola interactiva
```

## Estructura

```
resources/js/
├── Components/     # Componentes reutilizables
├── Layouts/        # Layouts (Authenticated, Guest)
└── Pages/          # Páginas de la aplicación
    ├── Auth/       # Autenticación
    └── Profile/    # Perfil de usuario
```

