#!/bin/bash
# Script para configurar HTTPS local con certificado autofirmado
# Para desarrollo en localhost/dominios locales

echo "=== CONFIGURACIÓN HTTPS LOCAL (Certificado Autofirmado) ==="

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

DOMAIN="rustikan"
SSL_DIR="/etc/ssl/rustikan"
DAYS=365

echo -e "\n${YELLOW}1. CREANDO DIRECTORIO PARA CERTIFICADOS${NC}"
sudo mkdir -p $SSL_DIR
check_status "Directorio creado"

echo -e "\n${YELLOW}2. GENERANDO CERTIFICADO AUTOFIRMADO${NC}"
sudo openssl req -x509 -nodes -days $DAYS -newkey rsa:2048 \
    -keyout $SSL_DIR/rustikan.key \
    -out $SSL_DIR/rustikan.crt \
    -subj "/C=ES/ST=Spain/L=City/O=Rustikan/OU=Development/CN=$DOMAIN" \
    -addext "subjectAltName=DNS:$DOMAIN,DNS:www.$DOMAIN,DNS:localhost,IP:127.0.0.1"
check_status "Certificado generado"

echo -e "\n${YELLOW}3. CONFIGURANDO PERMISOS${NC}"
sudo chmod 600 $SSL_DIR/rustikan.key
sudo chmod 644 $SSL_DIR/rustikan.crt
check_status "Permisos configurados"

echo -e "\n${YELLOW}4. HABILITANDO MÓDULO SSL EN APACHE${NC}"
sudo a2enmod ssl > /dev/null 2>&1
sudo a2enmod headers > /dev/null 2>&1
check_status "Módulo SSL habilitado"

echo -e "\n${YELLOW}5. CREANDO VIRTUAL HOST HTTPS${NC}"
sudo tee /etc/apache2/sites-available/rustikan-ssl.conf > /dev/null << EOF
<VirtualHost *:443>
    ServerName $DOMAIN
    ServerAlias www.$DOMAIN
    
    DocumentRoot /var/www/html/rustikan/public
    
    <Directory /var/www/html/rustikan/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile $SSL_DIR/rustikan.crt
    SSLCertificateKeyFile $SSL_DIR/rustikan.key
    
    # SSL Security
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384
    SSLHonorCipherOrder off
    
    # Security Headers
    Header always set Strict-Transport-Security "max-age=31536000"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    ErrorLog \${APACHE_LOG_DIR}/rustikan-ssl-error.log
    CustomLog \${APACHE_LOG_DIR}/rustikan-ssl-access.log combined
</VirtualHost>
EOF
check_status "Virtual host HTTPS creado"

echo -e "\n${YELLOW}6. CONFIGURANDO REDIRECCIÓN HTTP → HTTPS${NC}"
sudo tee /etc/apache2/sites-available/rustikan-redirect.conf > /dev/null << EOF
<VirtualHost *:80>
    ServerName $DOMAIN
    ServerAlias www.$DOMAIN
    
    # Redirigir todo a HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}\$1 [R=301,L]
    
    ErrorLog \${APACHE_LOG_DIR}/rustikan-redirect-error.log
    CustomLog \${APACHE_LOG_DIR}/rustikan-redirect-access.log combined
</VirtualHost>
EOF
check_status "Redirección HTTP→HTTPS configurada"

echo -e "\n${YELLOW}7. HABILITANDO SITIOS${NC}"
sudo a2dissite rustikan.conf > /dev/null 2>&1
sudo a2ensite rustikan-ssl.conf > /dev/null 2>&1
sudo a2ensite rustikan-redirect.conf > /dev/null 2>&1
check_status "Sitios habilitados"

echo -e "\n${YELLOW}8. VERIFICANDO CONFIGURACIÓN${NC}"
sudo apache2ctl configtest
if [ $? -eq 0 ] || sudo apache2ctl configtest 2>&1 | grep -q "Syntax OK"; then
    echo -e "${GREEN}✓ Configuración válida${NC}"
else
    echo -e "${RED}✗ Error en configuración${NC}"
    exit 1
fi

echo -e "\n${YELLOW}9. REINICIANDO APACHE${NC}"
sudo systemctl restart apache2
check_status "Apache reiniciado"

echo -e "\n${YELLOW}10. ACTUALIZANDO .env DE LARAVEL${NC}"
if [ -f /var/www/html/rustikan/.env ]; then
    sudo sed -i 's|APP_URL=http://rustikan|APP_URL=https://rustikan|g' /var/www/html/rustikan/.env
    cd /var/www/html/rustikan
    php artisan config:cache > /dev/null 2>&1
    echo -e "${GREEN}✓ APP_URL actualizada a HTTPS${NC}"
fi

echo -e "\n${GREEN}=== HTTPS LOCAL CONFIGURADO ===${NC}"

echo -e "\n${YELLOW}Acceso:${NC}"
echo "  https://rustikan"
echo "  https://www.rustikan"

echo -e "\n${YELLOW}⚠️  ADVERTENCIA IMPORTANTE:${NC}"
echo "Este es un certificado AUTOFIRMADO para desarrollo."
echo "Tu navegador mostrará una advertencia de seguridad."

echo -e "\n${YELLOW}Para aceptar el certificado en tu navegador:${NC}"
echo ""
echo "  Chrome/Edge:"
echo "    1. Ve a https://rustikan"
echo "    2. Clic en 'Avanzado'"
echo "    3. Clic en 'Ir a rustikan (no seguro)'"
echo ""
echo "  Firefox:"
echo "    1. Ve a https://rustikan"
echo "    2. Clic en 'Avanzado'"
echo "    3. Clic en 'Aceptar el riesgo y continuar'"
echo ""
echo "  Alternativa - Agregar certificado al sistema:"
echo "    sudo cp $SSL_DIR/rustikan.crt /usr/local/share/ca-certificates/"
echo "    sudo update-ca-certificates"

echo -e "\n${YELLOW}Verificar:${NC}"
echo "  curl -k https://rustikan"
echo "  openssl s_client -connect localhost:443 -servername rustikan"

echo -e "\n${YELLOW}Información del certificado:${NC}"
openssl x509 -in $SSL_DIR/rustikan.crt -text -noout | grep -A 2 "Subject:"
openssl x509 -in $SSL_DIR/rustikan.crt -text -noout | grep -A 1 "Not After"

echo -e "\n${YELLOW}Para PRODUCCIÓN con dominio real:${NC}"
echo "  Usa ./setup-ssl.sh (Let's Encrypt)"
