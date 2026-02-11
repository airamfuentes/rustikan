#!/bin/bash
# Script de configuración de Alta Disponibilidad con HAProxy
# Proyecto: Fortaleza Digital - Rustikan

echo "=== CONFIGURACIÓN DE ALTA DISPONIBILIDAD CON HAPROXY ==="

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

echo -e "\n${YELLOW}1. INSTALANDO HAPROXY${NC}"
sudo apt-get update > /dev/null 2>&1
sudo apt-get install -y haproxy keepalived > /dev/null 2>&1
check_status "HAProxy y Keepalived instalados"

echo -e "\n${YELLOW}2. CONFIGURANDO SERVIDORES BACKEND${NC}"
# Configurar Apache para escuchar en puertos adicionales
sudo tee -a /etc/apache2/ports.conf > /dev/null << 'EOF'

# Puertos adicionales para backend servers
Listen 8080
Listen 8081
EOF
check_status "Puertos backend configurados"

# Crear virtual hosts para los backend servers
sudo tee /etc/apache2/sites-available/rustikan-backend1.conf > /dev/null << 'EOF'
<VirtualHost *:8080>
    ServerName rustikan
    ServerAlias www.rustikan
    
    DocumentRoot /var/www/html/rustikan/public
    
    <Directory /var/www/html/rustikan/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Header para identificar el servidor
    Header add X-Served-By "Backend-1"
    
    ErrorLog ${APACHE_LOG_DIR}/rustikan-backend1-error.log
    CustomLog ${APACHE_LOG_DIR}/rustikan-backend1-access.log combined
</VirtualHost>
EOF

sudo tee /etc/apache2/sites-available/rustikan-backend2.conf > /dev/null << 'EOF'
<VirtualHost *:8081>
    ServerName rustikan
    ServerAlias www.rustikan
    
    DocumentRoot /var/www/html/rustikan/public
    
    <Directory /var/www/html/rustikan/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Header para identificar el servidor
    Header add X-Served-By "Backend-2"
    
    ErrorLog ${APACHE_LOG_DIR}/rustikan-backend2-error.log
    CustomLog ${APACHE_LOG_DIR}/rustikan-backend2-access.log combined
</VirtualHost>
EOF

# Habilitar los sitios backend
sudo a2ensite rustikan-backend1.conf rustikan-backend2.conf > /dev/null 2>&1
check_status "Virtual hosts backend creados"

# Reiniciar Apache
sudo systemctl restart apache2
check_status "Apache reiniciado con backends"

echo -e "\n${YELLOW}3. CONFIGURANDO HAPROXY${NC}"
# Backup de la configuración original
sudo cp /etc/haproxy/haproxy.cfg /etc/haproxy/haproxy.cfg.backup.$(date +%F)

# Crear configuración de HAProxy
sudo tee /etc/haproxy/haproxy.cfg > /dev/null << 'EOF'
global
    log /dev/log    local0
    log /dev/log    local1 notice
    chroot /var/lib/haproxy
    stats socket /run/haproxy/admin.sock mode 660 level admin
    stats timeout 30s
    user haproxy
    group haproxy
    daemon

    # Configuraciones de seguridad
    tune.ssl.default-dh-param 2048
    ssl-default-bind-ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384
    ssl-default-bind-options ssl-min-ver TLSv1.2 no-tls-tickets

defaults
    log     global
    mode    http
    option  httplog
    option  dontlognull
    option  http-server-close
    option  forwardfor except 127.0.0.0/8
    option  redispatch
    retries 3
    timeout connect 5000
    timeout client  50000
    timeout server  50000
    errorfile 400 /etc/haproxy/errors/400.http
    errorfile 403 /etc/haproxy/errors/403.http
    errorfile 408 /etc/haproxy/errors/408.http
    errorfile 500 /etc/haproxy/errors/500.http
    errorfile 502 /etc/haproxy/errors/502.http
    errorfile 503 /etc/haproxy/errors/503.http
    errorfile 504 /etc/haproxy/errors/504.http

# Frontend - Recibe las peticiones en el puerto 80
frontend http_front
    bind *:80
    # Redirigir HTTP a HTTPS (descomenta para producción)
    # redirect scheme https code 301 if !{ ssl_fc }
    
    # ACLs para seguridad
    acl is_post method POST
    acl is_suspicious path_beg -i /admin /wp-admin /phpmyadmin
    
    # Bloquear accesos sospechosos
    http-request deny if is_suspicious
    
    # Headers de seguridad
    http-response set-header X-Frame-Options SAMEORIGIN
    http-response set-header X-Content-Type-Options nosniff
    http-response set-header X-XSS-Protection "1; mode=block"
    
    default_backend rustikan_backend

# Frontend HTTPS (para cuando tengas SSL configurado)
# frontend https_front
#     bind *:443 ssl crt /etc/letsencrypt/live/YOUR_DOMAIN/fullchain.pem
#     default_backend rustikan_backend

# Backend - Pool de servidores
backend rustikan_backend
    balance roundrobin
    option httpchk GET /
    
    # Health check
    http-check expect status 200
    
    # Servidores backend
    server backend1 127.0.0.1:8080 check inter 2000 rise 2 fall 3 maxconn 1000
    server backend2 127.0.0.1:8081 check inter 2000 rise 2 fall 3 maxconn 1000
    
    # Cookie de sesión para sticky sessions
    cookie SERVERID insert indirect nocache
    server backend1 127.0.0.1:8080 cookie backend1
    server backend2 127.0.0.1:8081 cookie backend2

# Estadísticas de HAProxy
listen stats
    bind *:8404
    stats enable
    stats uri /stats
    stats realm Haproxy\ Statistics
    stats auth admin:rustikan2026
    stats refresh 30s
EOF
check_status "HAProxy configurado"

# Habilitar HAProxy
sudo systemctl enable haproxy
sudo systemctl restart haproxy
check_status "HAProxy iniciado"

echo -e "\n${YELLOW}4. CONFIGURANDO KEEPALIVED (FAILOVER)${NC}"
# Obtener la IP principal
MAIN_IP=$(hostname -I | awk '{print $1}')

sudo tee /etc/keepalived/keepalived.conf > /dev/null << EOF
vrrp_script chk_haproxy {
    script "killall -0 haproxy"
    interval 2
    weight 2
}

vrrp_instance VI_1 {
    state MASTER
    interface $(ip route | grep default | awk '{print $5}')
    virtual_router_id 51
    priority 101
    advert_int 1
    
    authentication {
        auth_type PASS
        auth_pass rustikan123
    }
    
    virtual_ipaddress {
        ${MAIN_IP}/24
    }
    
    track_script {
        chk_haproxy
    }
}
EOF
check_status "Keepalived configurado"

sudo systemctl enable keepalived
sudo systemctl restart keepalived
check_status "Keepalived iniciado"

echo -e "\n${YELLOW}5. ACTUALIZANDO FIREWALL${NC}"
# Abrir puertos necesarios
sudo ufw allow 8080/tcp
sudo ufw allow 8081/tcp
sudo ufw allow 8404/tcp
sudo ufw allow 112/tcp  # VRRP para keepalived
check_status "Puertos de HAProxy permitidos en firewall"

echo -e "\n${YELLOW}6. MODIFICANDO CONFIGURACIÓN ORIGINAL${NC}"
# Deshabilitar el virtual host original en puerto 80 para que HAProxy lo maneje
sudo a2dissite rustikan.conf > /dev/null 2>&1
sudo systemctl reload apache2
check_status "Virtual host original deshabilitado"

echo -e "\n${GREEN}=== ALTA DISPONIBILIDAD CONFIGURADA ===${NC}"
echo -e "\n${YELLOW}Configuración completada:${NC}"
echo "✓ HAProxy balanceando en puerto 80"
echo "✓ Backend Server 1 en puerto 8080"
echo "✓ Backend Server 2 en puerto 8081"
echo "✓ Keepalived monitoreando HAProxy"
echo "✓ Health checks cada 2 segundos"
echo "✓ Sticky sessions habilitadas"

echo -e "\n${YELLOW}Acceso a estadísticas:${NC}"
echo "  http://localhost:8404/stats"
echo "  Usuario: admin"
echo "  Contraseña: rustikan2026"

echo -e "\n${YELLOW}Verificar estado:${NC}"
echo "  sudo systemctl status haproxy"
echo "  sudo systemctl status keepalived"

echo -e "\n${YELLOW}Probar balanceo:${NC}"
echo "  curl -H 'Host: rustikan' http://localhost"
echo "  (Revisa el header X-Served-By)"

echo -e "\n${YELLOW}Logs:${NC}"
echo "  /var/log/haproxy.log"
echo "  /var/log/apache2/rustikan-backend*.log"
