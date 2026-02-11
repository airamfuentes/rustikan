#!/bin/bash
# Script de Hardenización del Servidor
# Proyecto: Fortaleza Digital - Rustikan

echo "=== INICIANDO HARDENIZACIÓN DEL SERVIDOR ==="

# Colores para output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Función para verificar si el comando fue exitoso
check_status() {
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ $1${NC}"
    else
        echo -e "${RED}✗ Error: $1${NC}"
        exit 1
    fi
}

echo -e "\n${YELLOW}1. CONFIGURANDO UFW (FIREWALL)${NC}"
# Instalar UFW si no está instalado
sudo apt-get update > /dev/null 2>&1
sudo apt-get install -y ufw > /dev/null 2>&1
check_status "UFW instalado"

# Configurar reglas básicas
sudo ufw --force reset > /dev/null 2>&1
sudo ufw default deny incoming
check_status "Denegar todo el tráfico entrante por defecto"

sudo ufw default allow outgoing
check_status "Permitir todo el tráfico saliente por defecto"

# Permitir SSH (puerto 22)
sudo ufw allow 22/tcp
check_status "Puerto SSH (22) permitido"

# Permitir HTTP (puerto 80)
sudo ufw allow 80/tcp
check_status "Puerto HTTP (80) permitido"

# Permitir HTTPS (puerto 443)
sudo ufw allow 443/tcp
check_status "Puerto HTTPS (443) permitido"

# Permitir MySQL (solo desde localhost)
sudo ufw allow from 127.0.0.1 to any port 3306
check_status "Puerto MySQL (3306) permitido solo desde localhost"

# Habilitar UFW
sudo ufw --force enable
check_status "UFW habilitado"

echo -e "\n${YELLOW}2. ASEGURANDO SSH${NC}"
# Backup de la configuración SSH original
sudo cp /etc/ssh/sshd_config /etc/ssh/sshd_config.backup.$(date +%F)
check_status "Backup de configuración SSH creado"

# Configurar SSH seguro
sudo sed -i 's/#PermitRootLogin prohibit-password/PermitRootLogin no/' /etc/ssh/sshd_config
sudo sed -i 's/PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config
check_status "Login root deshabilitado"

sudo sed -i 's/#PasswordAuthentication yes/PasswordAuthentication yes/' /etc/ssh/sshd_config
check_status "Autenticación por contraseña configurada"

sudo sed -i 's/#MaxAuthTries 6/MaxAuthTries 3/' /etc/ssh/sshd_config
check_status "Máximo de intentos de autenticación: 3"

# Agregar configuraciones adicionales de seguridad
echo "
# Configuraciones de seguridad adicionales
Protocol 2
X11Forwarding no
MaxSessions 2
ClientAliveInterval 300
ClientAliveCountMax 2
AllowUsers airamfuentes
" | sudo tee -a /etc/ssh/sshd_config > /dev/null
check_status "Configuraciones adicionales de SSH aplicadas"

# Reiniciar SSH
sudo systemctl restart sshd
check_status "Servicio SSH reiniciado"

echo -e "\n${YELLOW}3. DESHABILITANDO SERVICIOS INNECESARIOS${NC}"
# Lista de servicios a deshabilitar (si existen)
services_to_disable=("telnet" "ftp" "rsh" "rlogin" "cups" "avahi-daemon")

for service in "${services_to_disable[@]}"; do
    if systemctl is-active --quiet $service 2>/dev/null; then
        sudo systemctl stop $service
        sudo systemctl disable $service
        echo -e "${GREEN}✓ Servicio $service deshabilitado${NC}"
    fi
done

echo -e "\n${YELLOW}4. CONFIGURANDO FAIL2BAN${NC}"
# Instalar fail2ban
sudo apt-get install -y fail2ban > /dev/null 2>&1
check_status "Fail2ban instalado"

# Crear configuración personalizada
sudo tee /etc/fail2ban/jail.local > /dev/null << 'EOF'
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 3
destemail = admin@rustikan.local
sendername = Fail2Ban

[sshd]
enabled = true
port = ssh
logpath = %(sshd_log)s
maxretry = 3

[apache-auth]
enabled = true
port = http,https
logpath = %(apache_error_log)s
maxretry = 5

[apache-badbots]
enabled = true
port = http,https
logpath = %(apache_access_log)s
maxretry = 2

[apache-noscript]
enabled = true
port = http,https
logpath = %(apache_error_log)s

[apache-overflows]
enabled = true
port = http,https
logpath = %(apache_error_log)s
maxretry = 2
EOF
check_status "Fail2ban configurado"

# Iniciar fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
check_status "Fail2ban iniciado"

echo -e "\n${YELLOW}5. OPTIMIZANDO KERNEL PARA SEGURIDAD${NC}"
# Backup del archivo sysctl
sudo cp /etc/sysctl.conf /etc/sysctl.conf.backup.$(date +%F)

# Agregar configuraciones de seguridad al kernel
sudo tee -a /etc/sysctl.conf > /dev/null << 'EOF'

# Configuraciones de seguridad del kernel - Rustikan
# Protección contra IP spoofing
net.ipv4.conf.all.rp_filter = 1
net.ipv4.conf.default.rp_filter = 1

# Ignorar paquetes ICMP redirect
net.ipv4.conf.all.accept_redirects = 0
net.ipv4.conf.default.accept_redirects = 0
net.ipv4.conf.all.secure_redirects = 0
net.ipv4.conf.default.secure_redirects = 0

# No aceptar source routed packets
net.ipv4.conf.all.accept_source_route = 0
net.ipv4.conf.default.accept_source_route = 0

# Protección contra SYN flood
net.ipv4.tcp_syncookies = 1
net.ipv4.tcp_max_syn_backlog = 2048
net.ipv4.tcp_synack_retries = 2
net.ipv4.tcp_syn_retries = 5

# Log de paquetes con direcciones imposibles
net.ipv4.conf.all.log_martians = 1
net.ipv4.conf.default.log_martians = 1

# Ignorar pings (opcional)
# net.ipv4.icmp_echo_ignore_all = 1

# Protección contra tiempo de espera
net.ipv4.tcp_fin_timeout = 15
net.ipv4.tcp_keepalive_time = 300
net.ipv4.tcp_keepalive_probes = 5
net.ipv4.tcp_keepalive_intvl = 15
EOF
check_status "Configuraciones de kernel aplicadas"

# Aplicar cambios
sudo sysctl -p > /dev/null 2>&1
check_status "Cambios de kernel activados"

echo -e "\n${YELLOW}6. CONFIGURANDO AUDITORÍA DEL SISTEMA${NC}"
# Instalar auditd
sudo apt-get install -y auditd audispd-plugins > /dev/null 2>&1
check_status "Auditd instalado"

# Configurar reglas de auditoría
sudo tee -a /etc/audit/rules.d/audit.rules > /dev/null << 'EOF'
# Auditoría de modificaciones críticas del sistema
-w /etc/passwd -p wa -k passwd_changes
-w /etc/group -p wa -k group_changes
-w /etc/shadow -p wa -k shadow_changes
-w /etc/sudoers -p wa -k sudoers_changes
-w /etc/ssh/sshd_config -p wa -k sshd_config_changes

# Auditoría de logs del sistema
-w /var/log/auth.log -p wa -k auth_log_changes
-w /var/log/syslog -p wa -k syslog_changes

# Auditoría de archivos de Laravel
-w /var/www/html/rustikan/.env -p wa -k laravel_env_changes
-w /var/www/html/rustikan/config/ -p wa -k laravel_config_changes
EOF
check_status "Reglas de auditoría configuradas"

# Iniciar auditd
sudo systemctl enable auditd
sudo systemctl start auditd
check_status "Auditd iniciado"

echo -e "\n${YELLOW}7. ASEGURANDO APACHE${NC}"
# Deshabilitar listado de directorios
sudo sed -i 's/Options Indexes FollowSymLinks/Options -Indexes +FollowSymLinks/' /etc/apache2/apache2.conf 2>/dev/null || true

# Ocultar versión de Apache
echo "ServerTokens Prod" | sudo tee -a /etc/apache2/conf-available/security.conf > /dev/null
echo "ServerSignature Off" | sudo tee -a /etc/apache2/conf-available/security.conf > /dev/null
check_status "Apache asegurado"

# Habilitar módulos de seguridad
sudo a2enmod headers ssl rewrite > /dev/null 2>&1
check_status "Módulos de seguridad de Apache habilitados"

# Agregar headers de seguridad
sudo tee /etc/apache2/conf-available/security-headers.conf > /dev/null << 'EOF'
<IfModule mod_headers.c>
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
</IfModule>
EOF

sudo a2enconf security-headers > /dev/null 2>&1
check_status "Headers de seguridad configurados"

# Reiniciar Apache
sudo systemctl restart apache2
check_status "Apache reiniciado"

echo -e "\n${YELLOW}8. CONFIGURANDO LÍMITES DE RECURSOS${NC}"
# Configurar límites del sistema
sudo tee -a /etc/security/limits.conf > /dev/null << 'EOF'
# Límites de recursos - Rustikan
* soft nofile 4096
* hard nofile 65536
* soft nproc 4096
* hard nproc 8192
www-data soft nofile 8192
www-data hard nofile 16384
EOF
check_status "Límites de recursos configurados"

echo -e "\n${GREEN}=== HARDENIZACIÓN COMPLETADA ===${NC}"
echo -e "\n${YELLOW}Resumen de cambios:${NC}"
echo "✓ Firewall UFW configurado y activo"
echo "✓ SSH asegurado (root deshabilitado, intentos limitados)"
echo "✓ Servicios innecesarios deshabilitados"
echo "✓ Fail2ban configurado para protección contra ataques"
echo "✓ Kernel optimizado para seguridad"
echo "✓ Sistema de auditoría configurado"
echo "✓ Apache asegurado con headers de seguridad"
echo "✓ Límites de recursos configurados"

echo -e "\n${YELLOW}Estado del firewall:${NC}"
sudo ufw status verbose

echo -e "\n${YELLOW}Estado de Fail2ban:${NC}"
sudo fail2ban-client status

echo -e "\n${YELLOW}IMPORTANTE:${NC}"
echo "- Guarda los backups creados en /etc/ssh/ y /etc/"
echo "- Revisa los logs en /var/log/auth.log y /var/log/fail2ban.log"
echo "- Considera cambiar el puerto SSH desde el 22 a otro puerto"
echo "- Configura autenticación por clave SSH para mayor seguridad"
