#!/bin/bash
# Script de configuración SSL/TLS con Let's Encrypt
# Proyecto: Fortaleza Digital - Rustikan

echo "=== CONFIGURACIÓN HTTPS CON LET'S ENCRYPT ==="

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

# Solicitar información al usuario
read -p "Ingresa tu dominio (ej: rustikan.com): " DOMAIN
read -p "Ingresa tu email para Let's Encrypt: " EMAIL

if [ -z "$DOMAIN" ] || [ -z "$EMAIL" ]; then
    echo -e "${RED}Error: Debes proporcionar dominio y email${NC}"
    exit 1
fi

echo -e "\n${YELLOW}1. INSTALANDO CERTBOT${NC}"
sudo apt-get update > /dev/null 2>&1
sudo apt-get install -y certbot python3-certbot-apache > /dev/null 2>&1
check_status "Certbot instalado"

echo -e "\n${YELLOW}2. OBTENIENDO CERTIFICADO SSL${NC}"
# Para testing usa --staging, para producción quítalo
sudo certbot --apache -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos --email $EMAIL --redirect
check_status "Certificado SSL obtenido"

echo -e "\n${YELLOW}3. CONFIGURANDO RENOVACIÓN AUTOMÁTICA${NC}"
# Crear un script de renovación
sudo tee /etc/cron.d/certbot-renew > /dev/null << 'EOF'
# Renovar certificados SSL automáticamente
0 3 * * * root certbot renew --quiet --post-hook "systemctl reload apache2"
EOF
check_status "Renovación automática configurada"

# Probar renovación
sudo certbot renew --dry-run
check_status "Test de renovación exitoso"

echo -e "\n${YELLOW}4. CONFIGURANDO SSL AVANZADO${NC}"
# Crear archivo de configuración SSL fuerte
sudo tee /etc/apache2/conf-available/ssl-params.conf > /dev/null << 'EOF'
# Configuración SSL moderna y segura
SSLProtocol             all -SSLv3 -TLSv1 -TLSv1.1
SSLCipherSuite          ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384
SSLHonorCipherOrder     off
SSLSessionTickets       off

# HSTS (HTTP Strict Transport Security)
Header always set Strict-Transport-Security "max-age=63072000"

# OCSP Stapling
SSLUseStapling On
SSLStaplingCache "shmcb:logs/ssl_stapling(32768)"
EOF

sudo a2enconf ssl-params > /dev/null 2>&1
check_status "Configuración SSL avanzada aplicada"

# Reiniciar Apache
sudo systemctl restart apache2
check_status "Apache reiniciado con SSL"

echo -e "\n${GREEN}=== HTTPS CONFIGURADO EXITOSAMENTE ===${NC}"
echo -e "\n${YELLOW}Prueba tu sitio:${NC}"
echo "  https://$DOMAIN"
echo "  https://www.$DOMAIN"

echo -e "\n${YELLOW}Verifica tu SSL en:${NC}"
echo "  https://www.ssllabs.com/ssltest/analyze.html?d=$DOMAIN"

echo -e "\n${YELLOW}Renovación automática:${NC}"
echo "  Los certificados se renovarán automáticamente cada día a las 3 AM"
echo "  Puedes probar manualmente con: sudo certbot renew --dry-run"
