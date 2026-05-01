#!/bin/bash
###############################################################################
# Rustikan — Script de deploy para Laravel Forge
#
# Pega este contenido en Forge → Site → Deployment Script.
# Forge lo ejecutará automáticamente en cada `git push` al branch configurado.
#
# Pasos:
#   1. Pull del último código
#   2. Instalar dependencias PHP (sin paquetes de dev)
#   3. Instalar dependencias JS y compilar assets
#   4. Migrar base de datos
#   5. Limpiar y recachear configs/rutas/vistas
#   6. Recargar PHP-FPM y reiniciar queue worker
###############################################################################

cd $FORGE_SITE_PATH

# Activar modo mantenimiento (opcional, descomenta si quieres página de mantenimiento)
# $FORGE_PHP artisan down --render="errors::503" --secret="rustikan-bypass-XXXX" || true

# 1. Pull del repositorio
git pull origin $FORGE_SITE_BRANCH

# 2. Composer (solo dependencias de producción + autoloader optimizado)
$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 3. Frontend: instalar dependencias y compilar assets
npm ci
npm run build

# 4. Symlink de storage (idempotente — solo crea si no existe)
$FORGE_PHP artisan storage:link || true

# 5. Migraciones (--force evita el prompt en producción)
$FORGE_PHP artisan migrate --force

# 6. Limpiar cachés antiguos y recachear
$FORGE_PHP artisan config:clear
$FORGE_PHP artisan route:clear
$FORGE_PHP artisan view:clear
$FORGE_PHP artisan event:clear

$FORGE_PHP artisan config:cache
$FORGE_PHP artisan route:cache
$FORGE_PHP artisan view:cache
$FORGE_PHP artisan event:cache

# 7. Recargar PHP-FPM (aplica cambios de OPcache sin downtime)
( flock -w 10 9 || exit 1
    echo 'Recargando PHP-FPM...'; sudo -S service $FORGE_PHP_FPM reload ) 9>/tmp/fpmlock

# 8. Reiniciar queue worker (toma el código nuevo)
$FORGE_PHP artisan queue:restart

# Desactivar modo mantenimiento (si lo activaste arriba)
# $FORGE_PHP artisan up
