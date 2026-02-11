# Rustikan

Proyecto final de ciclo formativo - Aplicación web desarrollada con Laravel, Vue.js y Tailwind CSS.

## 🚀 Tecnologías

- **Backend**: Laravel 12
- **Frontend**: Vue.js 3 + Inertia.js
- **Estilos**: Tailwind CSS
- **Base de datos**: MySQL
- **Autenticación**: Laravel Breeze

## 📋 Requisitos

- PHP 8.4+
- Composer
- Node.js 18+
- NPM
- MySQL 8.0+

## 🛠️ Instalación

### En Linux (Desarrollo)

1. Clonar el repositorio:
```bash
git clone https://github.com/airamfuentes/rustikan.git
cd rustikan
```

2. Instalar dependencias de PHP:
```bash
composer install
```

3. Instalar dependencias de Node:
```bash
npm install --legacy-peer-deps
```

4. Copiar el archivo de configuración:
```bash
cp .env.example .env
```

5. Configurar la base de datos en `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rustikan
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

6. Generar la clave de la aplicación:
```bash
php artisan key:generate
```

7. Ejecutar las migraciones:
```bash
php artisan migrate
```

8. Compilar los assets:
```bash
npm run build
```

9. Iniciar el servidor de desarrollo:
```bash
php artisan serve
```

La aplicación estará disponible en `http://localhost:8000`

### En Windows

1. Clonar el repositorio:
```cmd
git clone https://github.com/airamfuentes/rustikan.git
cd rustikan
```

2. Instalar dependencias de PHP:
```cmd
composer install
```

3. Instalar dependencias de Node:
```cmd
npm install --legacy-peer-deps
```

4. Copiar el archivo de configuración:
```cmd
copy .env.example .env
```

5. Configurar la base de datos en `.env` (usar tu XAMPP/WAMP/Laragon):
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rustikan
DB_USERNAME=root
DB_PASSWORD=
```

6. Generar la clave de la aplicación:
```cmd
php artisan key:generate
```

7. Crear la base de datos en MySQL:
```sql
CREATE DATABASE rustikan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

8. Ejecutar las migraciones:
```cmd
php artisan migrate
```

9. Compilar los assets:
```cmd
npm run build
```

10. Iniciar el servidor de desarrollo:
```cmd
php artisan serve
```

## 🔧 Desarrollo

Para desarrollo con hot-reload del frontend:

```bash
npm run dev
```

En otra terminal:

```bash
php artisan serve
```

## 📝 Comandos útiles

- `php artisan migrate:fresh` - Reiniciar base de datos
- `php artisan db:seed` - Poblar base de datos
- `php artisan tinker` - Consola interactiva
- `npm run build` - Compilar assets para producción
- `php artisan test` - Ejecutar tests

## 👤 Autor

**Airam Fuentes**
- GitHub: [@airamfuentes](https://github.com/airamfuentes)
- Email: airamfuentes2020@gmail.com

## 📄 Licencia

Este proyecto es parte de un proyecto final de ciclo formativo.
