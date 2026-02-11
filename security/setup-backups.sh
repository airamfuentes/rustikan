#!/bin/bash
# Script de Backup Automatizado y Remoto
# Proyecto: Fortaleza Digital - Rustikan

echo "=== CONFIGURACIÓN DE SISTEMA DE BACKUPS ==="

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

check_status() {
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ $1${NC}"
    else
        echo -e "${RED}✗ Error: $1${NC}"
        exit 1
    fi
}

# Configuración
PROJECT_PATH="/var/www/html/rustikan"
BACKUP_DIR="/var/backups/rustikan"
REMOTE_BACKUP_DIR="/mnt/backup-remoto/rustikan"  # Cambiar según tu configuración
DB_NAME="rustikan"
DB_USER="airamfuentes"
DB_PASS="1977"
RETENTION_DAYS=30

echo -e "\n${YELLOW}1. CREANDO ESTRUCTURA DE DIRECTORIOS${NC}"
# Crear directorios de backup
sudo mkdir -p $BACKUP_DIR/{daily,weekly,monthly}
sudo mkdir -p $BACKUP_DIR/logs
sudo chown -R $(whoami):$(whoami) $BACKUP_DIR
check_status "Directorios de backup creados"

echo -e "\n${YELLOW}2. CREANDO SCRIPT DE BACKUP${NC}"
# Script principal de backup
sudo tee /usr/local/bin/rustikan-backup.sh > /dev/null << 'EOFMAIN'
#!/bin/bash
# Script de Backup para Rustikan
# Ejecutado automáticamente por cron

# Configuración
PROJECT_PATH="/var/www/html/rustikan"
BACKUP_DIR="/var/backups/rustikan"
DB_NAME="rustikan"
DB_USER="airamfuentes"
DB_PASS="1977"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

# Logs
LOG_FILE="$BACKUP_DIR/logs/backup_$DATE.log"
exec > >(tee -a "$LOG_FILE") 2>&1

echo "=== Inicio de Backup: $(date) ==="

# Determinar tipo de backup según el día
DAY_OF_MONTH=$(date +%d)
DAY_OF_WEEK=$(date +%u)

if [ "$DAY_OF_MONTH" == "01" ]; then
    BACKUP_TYPE="monthly"
elif [ "$DAY_OF_WEEK" == "7" ]; then
    BACKUP_TYPE="weekly"
else
    BACKUP_TYPE="daily"
fi

BACKUP_PATH="$BACKUP_DIR/$BACKUP_TYPE"
BACKUP_NAME="rustikan_${BACKUP_TYPE}_${DATE}"

echo "Tipo de backup: $BACKUP_TYPE"
echo "Destino: $BACKUP_PATH/$BACKUP_NAME.tar.gz"

# 1. Backup de la base de datos
echo "1. Respaldando base de datos..."
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > /tmp/${BACKUP_NAME}_db.sql
if [ $? -eq 0 ]; then
    echo "✓ Base de datos respaldada"
    gzip /tmp/${BACKUP_NAME}_db.sql
else
    echo "✗ Error al respaldar base de datos"
    exit 1
fi

# 2. Backup de archivos de la aplicación
echo "2. Respaldando archivos de aplicación..."
cd $PROJECT_PATH
tar -czf /tmp/${BACKUP_NAME}_files.tar.gz \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='public/build' \
    --exclude='public/hot' \
    . 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✓ Archivos respaldados"
else
    echo "✗ Error al respaldar archivos"
    exit 1
fi

# 3. Backup de configuración del sistema
echo "3. Respaldando configuraciones del sistema..."
tar -czf /tmp/${BACKUP_NAME}_config.tar.gz \
    /etc/apache2/sites-available/rustikan*.conf \
    /etc/php/*/apache2/php.ini \
    /etc/mysql/mysql.conf.d/mysqld.cnf \
    /etc/ssh/sshd_config \
    /etc/fail2ban/jail.local \
    /etc/haproxy/haproxy.cfg \
    2>/dev/null

if [ $? -eq 0 ]; then
    echo "✓ Configuraciones respaldadas"
else
    echo "✗ Error al respaldar configuraciones"
fi

# 4. Crear archivo final combinado
echo "4. Creando archivo de backup final..."
mkdir -p /tmp/${BACKUP_NAME}
mv /tmp/${BACKUP_NAME}_*.gz /tmp/${BACKUP_NAME}/
tar -czf ${BACKUP_PATH}/${BACKUP_NAME}.tar.gz -C /tmp ${BACKUP_NAME}
rm -rf /tmp/${BACKUP_NAME}*

if [ $? -eq 0 ]; then
    echo "✓ Backup completado: ${BACKUP_PATH}/${BACKUP_NAME}.tar.gz"
    BACKUP_SIZE=$(du -h ${BACKUP_PATH}/${BACKUP_NAME}.tar.gz | cut -f1)
    echo "  Tamaño: $BACKUP_SIZE"
else
    echo "✗ Error al crear backup final"
    exit 1
fi

# 5. Limpiar backups antiguos
echo "5. Limpiando backups antiguos (más de $RETENTION_DAYS días)..."
if [ "$BACKUP_TYPE" == "daily" ]; then
    find $BACKUP_DIR/daily -name "rustikan_daily_*.tar.gz" -mtime +$RETENTION_DAYS -delete
    DELETED=$(find $BACKUP_DIR/daily -name "rustikan_daily_*.tar.gz" -mtime +$RETENTION_DAYS | wc -l)
    echo "✓ $DELETED backups diarios eliminados"
fi

# 6. Sincronizar con ubicación remota (si está configurado)
REMOTE_BACKUP_DIR="/mnt/backup-remoto/rustikan"
if [ -d "$REMOTE_BACKUP_DIR" ]; then
    echo "6. Sincronizando con backup remoto..."
    rsync -avz --delete $BACKUP_DIR/ $REMOTE_BACKUP_DIR/
    if [ $? -eq 0 ]; then
        echo "✓ Sincronización remota completada"
    else
        echo "✗ Error en sincronización remota"
    fi
else
    echo "6. Ubicación remota no configurada - omitiendo"
fi

# 7. Verificar integridad
echo "7. Verificando integridad del backup..."
tar -tzf ${BACKUP_PATH}/${BACKUP_NAME}.tar.gz > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✓ Integridad verificada"
else
    echo "✗ Error de integridad en el backup"
    exit 1
fi

# Resumen
echo ""
echo "=== Resumen del Backup ==="
echo "Tipo: $BACKUP_TYPE"
echo "Archivo: ${BACKUP_NAME}.tar.gz"
echo "Tamaño: $BACKUP_SIZE"
echo "Ubicación: $BACKUP_PATH"
echo "Finalizado: $(date)"
echo "=========================="

# Enviar notificación (opcional - requiere mailutils)
# echo "Backup completado: $BACKUP_NAME" | mail -s "Backup Rustikan OK" admin@rustikan.local

exit 0
EOFMAIN

sudo chmod +x /usr/local/bin/rustikan-backup.sh
check_status "Script de backup creado"

echo -e "\n${YELLOW}3. CONFIGURANDO TAREAS CRON${NC}"
# Agregar tarea cron
(sudo crontab -l 2>/dev/null; echo "# Backup diario de Rustikan a las 2 AM") | sudo crontab -
(sudo crontab -l 2>/dev/null; echo "0 2 * * * /usr/local/bin/rustikan-backup.sh >> $BACKUP_DIR/logs/cron.log 2>&1") | sudo crontab -
check_status "Tarea cron configurada"

echo -e "\n${YELLOW}4. CREANDO SCRIPT DE RESTAURACIÓN${NC}"
sudo tee /usr/local/bin/rustikan-restore.sh > /dev/null << 'EOFRESTORE'
#!/bin/bash
# Script de Restauración para Rustikan

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

if [ "$#" -ne 1 ]; then
    echo "Uso: $0 <archivo_backup.tar.gz>"
    echo ""
    echo "Backups disponibles:"
    ls -lh /var/backups/rustikan/{daily,weekly,monthly}/*.tar.gz 2>/dev/null
    exit 1
fi

BACKUP_FILE=$1
PROJECT_PATH="/var/www/html/rustikan"
TEMP_DIR="/tmp/rustikan_restore_$$"
DB_NAME="rustikan"
DB_USER="airamfuentes"
DB_PASS="1977"

if [ ! -f "$BACKUP_FILE" ]; then
    echo -e "${RED}Error: Archivo de backup no encontrado${NC}"
    exit 1
fi

echo -e "${YELLOW}=== RESTAURACIÓN DE RUSTIKAN ===${NC}"
echo "Backup: $BACKUP_FILE"
echo ""
read -p "¿Continuar con la restauración? (esto sobrescribirá datos actuales) [S/n]: " CONFIRM

if [[ ! $CONFIRM =~ ^[Ss]$ ]]; then
    echo "Restauración cancelada"
    exit 0
fi

echo -e "\n${YELLOW}1. Creando backup de seguridad actual...${NC}"
/usr/local/bin/rustikan-backup.sh
echo -e "${GREEN}✓ Backup de seguridad creado${NC}"

echo -e "\n${YELLOW}2. Extrayendo archivo de backup...${NC}"
mkdir -p $TEMP_DIR
tar -xzf $BACKUP_FILE -C $TEMP_DIR
BACKUP_NAME=$(ls $TEMP_DIR)
echo -e "${GREEN}✓ Backup extraído${NC}"

echo -e "\n${YELLOW}3. Restaurando base de datos...${NC}"
cd $TEMP_DIR/$BACKUP_NAME
gunzip ${BACKUP_NAME}_db.sql.gz
mysql -u $DB_USER -p$DB_PASS $DB_NAME < ${BACKUP_NAME}_db.sql
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Base de datos restaurada${NC}"
else
    echo -e "${RED}✗ Error al restaurar base de datos${NC}"
    exit 1
fi

echo -e "\n${YELLOW}4. Restaurando archivos de aplicación...${NC}"
tar -xzf ${BACKUP_NAME}_files.tar.gz -C $PROJECT_PATH
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Archivos restaurados${NC}"
else
    echo -e "${RED}✗ Error al restaurar archivos${NC}"
    exit 1
fi

echo -e "\n${YELLOW}5. Restaurando permisos...${NC}"
sudo chown -R www-data:www-data $PROJECT_PATH
sudo chmod -R 755 $PROJECT_PATH
sudo chmod -R 775 $PROJECT_PATH/storage
sudo chmod -R 775 $PROJECT_PATH/bootstrap/cache
echo -e "${GREEN}✓ Permisos restaurados${NC}"

echo -e "\n${YELLOW}6. Limpiando caché...${NC}"
cd $PROJECT_PATH
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo -e "${GREEN}✓ Caché limpiada${NC}"

echo -e "\n${YELLOW}7. Reiniciando servicios...${NC}"
sudo systemctl restart apache2
sudo systemctl restart mysql
echo -e "${GREEN}✓ Servicios reiniciados${NC}"

echo -e "\n${YELLOW}8. Limpiando archivos temporales...${NC}"
rm -rf $TEMP_DIR
echo -e "${GREEN}✓ Limpieza completada${NC}"

echo -e "\n${GREEN}=== RESTAURACIÓN COMPLETADA ===${NC}"
echo "Verifica que el sitio funcione correctamente en: http://rustikan"
EOFRESTORE

sudo chmod +x /usr/local/bin/rustikan-restore.sh
check_status "Script de restauración creado"

echo -e "\n${YELLOW}5. CONFIGURANDO MONITOREO DE BACKUPS${NC}"
# Script de verificación de backups
sudo tee /usr/local/bin/rustikan-check-backups.sh > /dev/null << 'EOFCHECK'
#!/bin/bash
# Verificar que los backups se estén realizando correctamente

BACKUP_DIR="/var/backups/rustikan"
ALERT_EMAIL="admin@rustikan.local"
MAX_AGE_HOURS=25  # Alertar si no hay backup en las últimas 25 horas

# Buscar el backup más reciente
LATEST_BACKUP=$(find $BACKUP_DIR/daily -name "rustikan_daily_*.tar.gz" -type f -printf '%T@ %p\n' | sort -n | tail -1 | cut -f2- -d" ")

if [ -z "$LATEST_BACKUP" ]; then
    echo "ALERTA: No se encontraron backups"
    # echo "No hay backups disponibles" | mail -s "ALERTA: Backup Rustikan" $ALERT_EMAIL
    exit 1
fi

# Verificar antigüedad
BACKUP_AGE_HOURS=$(( ($(date +%s) - $(stat -c %Y "$LATEST_BACKUP")) / 3600 ))

if [ $BACKUP_AGE_HOURS -gt $MAX_AGE_HOURS ]; then
    echo "ALERTA: El último backup tiene $BACKUP_AGE_HOURS horas"
    # echo "Último backup con $BACKUP_AGE_HOURS horas" | mail -s "ALERTA: Backup Rustikan" $ALERT_EMAIL
    exit 1
else
    echo "OK: Último backup hace $BACKUP_AGE_HOURS horas"
    echo "Archivo: $LATEST_BACKUP"
    echo "Tamaño: $(du -h $LATEST_BACKUP | cut -f1)"
fi
EOFCHECK

sudo chmod +x /usr/local/bin/rustikan-check-backups.sh
check_status "Script de verificación creado"

# Agregar verificación diaria
(sudo crontab -l 2>/dev/null; echo "0 12 * * * /usr/local/bin/rustikan-check-backups.sh") | sudo crontab -
check_status "Verificación diaria programada"

echo -e "\n${YELLOW}6. EJECUTANDO BACKUP INICIAL${NC}"
sudo /usr/local/bin/rustikan-backup.sh
check_status "Backup inicial completado"

echo -e "\n${GREEN}=== SISTEMA DE BACKUPS CONFIGURADO ===${NC}"
echo -e "\n${YELLOW}Comandos útiles:${NC}"
echo "  Backup manual:     sudo /usr/local/bin/rustikan-backup.sh"
echo "  Restaurar:         sudo /usr/local/bin/rustikan-restore.sh <archivo.tar.gz>"
echo "  Verificar backups: sudo /usr/local/bin/rustikan-check-backups.sh"
echo "  Listar backups:    ls -lh /var/backups/rustikan/{daily,weekly,monthly}/"

echo -e "\n${YELLOW}Programación de backups:${NC}"
echo "  Diarios:   Todos los días a las 2:00 AM"
echo "  Semanales: Domingos (se guardan como weekly)"
echo "  Mensuales: Día 1 de cada mes (se guardan como monthly)"
echo "  Retención: $RETENTION_DAYS días para backups diarios"

echo -e "\n${YELLOW}Ubicaciones:${NC}"
echo "  Backups:  $BACKUP_DIR"
echo "  Logs:     $BACKUP_DIR/logs"
echo "  Remoto:   $REMOTE_BACKUP_DIR (configurar manualmente)"

echo -e "\n${YELLOW}Próximos pasos:${NC}"
echo "  1. Configurar un servidor remoto o servicio cloud (AWS S3, Google Drive, etc.)"
echo "  2. Montar el directorio remoto en $REMOTE_BACKUP_DIR"
echo "  3. Probar la restauración con: sudo /usr/local/bin/rustikan-restore.sh"
echo "  4. Configurar notificaciones por email (instalar mailutils)"
