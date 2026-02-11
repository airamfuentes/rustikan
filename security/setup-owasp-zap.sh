#!/bin/bash
# Script de Análisis de Vulnerabilidades con OWASP ZAP
# Proyecto: Fortaleza Digital - Rustikan

echo "=== ANÁLISIS DE VULNERABILIDADES CON OWASP ZAP ==="

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

SECURITY_DIR="/var/www/html/rustikan/security"
REPORTS_DIR="$SECURITY_DIR/reports"

echo -e "\n${YELLOW}1. INSTALANDO DEPENDENCIAS${NC}"
sudo apt-get update > /dev/null 2>&1
sudo apt-get install -y wget default-jre > /dev/null 2>&1
check_status "Java instalado"

echo -e "\n${YELLOW}2. DESCARGANDO OWASP ZAP${NC}"
mkdir -p $SECURITY_DIR/tools
cd $SECURITY_DIR/tools

if [ ! -f "ZAP_2.14.0_Linux.tar.gz" ]; then
    wget -q https://github.com/zaproxy/zaproxy/releases/download/v2.14.0/ZAP_2.14.0_Linux.tar.gz
    check_status "OWASP ZAP descargado"
else
    echo -e "${GREEN}✓ OWASP ZAP ya descargado${NC}"
fi

echo -e "\n${YELLOW}3. EXTRAYENDO OWASP ZAP${NC}"
if [ ! -d "ZAP_2.14.0" ]; then
    tar -xzf ZAP_2.14.0_Linux.tar.gz
    check_status "OWASP ZAP extraído"
else
    echo -e "${GREEN}✓ OWASP ZAP ya extraído${NC}"
fi

# Crear directorio de reportes
mkdir -p $REPORTS_DIR
check_status "Directorio de reportes creado"

echo -e "\n${YELLOW}4. CREANDO SCRIPT DE ESCANEO BÁSICO${NC}"
tee $SECURITY_DIR/zap-scan-basic.sh > /dev/null << 'EOFBASIC'
#!/bin/bash
# Escaneo básico de vulnerabilidades

ZAP_PATH="/var/www/html/rustikan/security/tools/ZAP_2.14.0"
TARGET_URL="http://rustikan"
REPORT_DIR="/var/www/html/rustikan/security/reports"
DATE=$(date +%Y%m%d_%H%M%S)
REPORT_NAME="rustikan_basic_scan_$DATE"

echo "=== Escaneo Básico de Vulnerabilidades ==="
echo "Objetivo: $TARGET_URL"
echo "Iniciando: $(date)"

$ZAP_PATH/zap.sh -cmd \
    -quickurl $TARGET_URL \
    -quickout $REPORT_DIR/${REPORT_NAME}.html \
    -quickprogress

echo ""
echo "Escaneo completado: $(date)"
echo "Reporte: $REPORT_DIR/${REPORT_NAME}.html"
echo ""
echo "Abre el reporte con:"
echo "  firefox $REPORT_DIR/${REPORT_NAME}.html"
EOFBASIC

chmod +x $SECURITY_DIR/zap-scan-basic.sh
check_status "Script de escaneo básico creado"

echo -e "\n${YELLOW}5. CREANDO SCRIPT DE ESCANEO COMPLETO${NC}"
tee $SECURITY_DIR/zap-scan-full.sh > /dev/null << 'EOFFULL'
#!/bin/bash
# Escaneo completo de vulnerabilidades

ZAP_PATH="/var/www/html/rustikan/security/tools/ZAP_2.14.0"
TARGET_URL="http://rustikan"
REPORT_DIR="/var/www/html/rustikan/security/reports"
DATE=$(date +%Y%m%d_%H%M%S)
REPORT_NAME="rustikan_full_scan_$DATE"

echo "=== Escaneo Completo de Vulnerabilidades ==="
echo "Objetivo: $TARGET_URL"
echo "Iniciando: $(date)"
echo "ADVERTENCIA: Este escaneo puede tardar varios minutos"

$ZAP_PATH/zap.sh -cmd \
    -quickurl $TARGET_URL \
    -quickout $REPORT_DIR/${REPORT_NAME}.html \
    -quickprogress \
    -newsession \
    -config spider.maxDepth=5 \
    -config spider.maxChildren=50

echo ""
echo "Generando reportes adicionales..."

# Generar reporte JSON
$ZAP_PATH/zap.sh -cmd \
    -session $REPORT_DIR/${REPORT_NAME}.session \
    -exportreport $REPORT_DIR/${REPORT_NAME}.json \
    -format json

# Generar reporte XML
$ZAP_PATH/zap.sh -cmd \
    -session $REPORT_DIR/${REPORT_NAME}.session \
    -exportreport $REPORT_DIR/${REPORT_NAME}.xml \
    -format xml

echo ""
echo "Escaneo completado: $(date)"
echo "Reportes generados:"
echo "  HTML: $REPORT_DIR/${REPORT_NAME}.html"
echo "  JSON: $REPORT_DIR/${REPORT_NAME}.json"
echo "  XML:  $REPORT_DIR/${REPORT_NAME}.xml"
echo ""
echo "Abre el reporte HTML con:"
echo "  firefox $REPORT_DIR/${REPORT_NAME}.html"
EOFFULL

chmod +x $SECURITY_DIR/zap-scan-full.sh
check_status "Script de escaneo completo creado"

echo -e "\n${YELLOW}6. CREANDO SCRIPT DE PARCHES AUTOMÁTICOS${NC}"
tee $SECURITY_DIR/apply-security-patches.sh > /dev/null << 'EOFPATCH'
#!/bin/bash
# Aplicar parches de seguridad comunes en Laravel

PROJECT_PATH="/var/www/html/rustikan"
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo "=== Aplicando Parches de Seguridad ==="

cd $PROJECT_PATH

echo -e "\n${YELLOW}1. Actualizando dependencias de Composer${NC}"
composer update --no-dev --optimize-autoloader
echo -e "${GREEN}✓ Composer actualizado${NC}"

echo -e "\n${YELLOW}2. Actualizando dependencias de NPM${NC}"
npm audit fix
echo -e "${GREEN}✓ NPM actualizado${NC}"

echo -e "\n${YELLOW}3. Configurando headers de seguridad en Laravel${NC}"

# Crear middleware de seguridad
cat > app/Http/Middleware/SecurityHeaders.php << 'EOFMIDDLEWARE'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Headers de seguridad
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        // Content Security Policy
        $response->headers->set('Content-Security-Policy', 
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.bunny.net; " .
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net; " .
            "font-src 'self' https://fonts.bunny.net; " .
            "img-src 'self' data: https:; " .
            "connect-src 'self' ws: wss:;"
        );

        return $response;
    }
}
EOFMIDDLEWARE

echo -e "${GREEN}✓ Middleware de seguridad creado${NC}"

echo -e "\n${YELLOW}4. Registrando middleware${NC}"
# Agregar middleware al Kernel (esto debe hacerse manualmente)
echo "  Agrega esto a app/Http/Kernel.php en el array \$middleware:"
echo "    \\App\\Http\\Middleware\\SecurityHeaders::class,"

echo -e "\n${YELLOW}5. Configurando CORS seguro${NC}"
cat > config/cors.php << 'EOFCORS'
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [env('APP_URL', 'http://localhost')],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
EOFCORS

echo -e "${GREEN}✓ CORS configurado${NC}"

echo -e "\n${YELLOW}6. Asegurando archivos sensibles${NC}"
# Verificar permisos de archivos
chmod 600 .env
chmod 644 composer.json
chmod 644 package.json
find storage -type f -exec chmod 644 {} \;
find storage -type d -exec chmod 755 {} \;
find bootstrap/cache -type f -exec chmod 644 {} \;
find bootstrap/cache -type d -exec chmod 755 {} \;
echo -e "${GREEN}✓ Permisos de archivos configurados${NC}"

echo -e "\n${YELLOW}7. Deshabilitando debug en producción${NC}"
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/g' .env 2>/dev/null || true
echo -e "${GREEN}✓ Debug deshabilitado${NC}"

echo -e "\n${YELLOW}8. Regenerando caché${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✓ Caché regenerada${NC}"

echo -e "\n${GREEN}=== Parches Aplicados ===${NC}"
echo "Recuerda:"
echo "1. Agregar SecurityHeaders middleware manualmente"
echo "2. Probar todas las funcionalidades"
echo "3. Revisar logs de errores"
EOFPATCH

chmod +x $SECURITY_DIR/apply-security-patches.sh
check_status "Script de parches creado"

echo -e "\n${YELLOW}7. CREANDO GUÍA DE USO${NC}"
tee $SECURITY_DIR/README.md > /dev/null << 'EOFREADME'
# Guía de Análisis de Seguridad - Rustikan

## OWASP ZAP - Análisis de Vulnerabilidades

### 1. Escaneo Básico (Rápido - 2-5 minutos)
```bash
./security/zap-scan-basic.sh
```

Este escaneo detecta:
- Inyección SQL
- Cross-Site Scripting (XSS)
- Configuraciones incorrectas de seguridad
- Headers de seguridad faltantes

### 2. Escaneo Completo (Lento - 15-30 minutos)
```bash
./security/zap-scan-full.sh
```

Incluye:
- Todo lo del escaneo básico
- Spider profundo de la aplicación
- Pruebas de autenticación
- Análisis de sesiones
- Reportes en múltiples formatos

### 3. Ver Reportes
```bash
# Listar reportes
ls -lh security/reports/

# Abrir último reporte HTML
firefox security/reports/$(ls -t security/reports/*.html | head -1)
```

## Interpretación de Resultados

### Niveles de Riesgo
- **Alto (Rojo)**: Requiere acción inmediata
- **Medio (Naranja)**: Debe corregirse pronto
- **Bajo (Amarillo)**: Corregir cuando sea posible
- **Informativo (Azul)**: Solo información

### Vulnerabilidades Comunes y Soluciones

#### 1. Headers de Seguridad Faltantes
**Problema**: X-Frame-Options, X-XSS-Protection no configurados
**Solución**: Ejecutar `./security/apply-security-patches.sh`

#### 2. Inyección SQL
**Problema**: Consultas SQL sin validación
**Solución**: Usar Eloquent ORM y prepared statements

#### 3. Cross-Site Scripting (XSS)
**Problema**: Salida sin sanitizar
**Solución**: Usar `{{ $variable }}` en Blade, nunca `{!! $variable !!}`

#### 4. Exposición de Información Sensible
**Problema**: APP_DEBUG=true en producción
**Solución**: Configurar `APP_DEBUG=false` en .env

#### 5. CSRF Token Faltante
**Problema**: Formularios sin @csrf
**Solución**: Agregar `@csrf` en todos los formularios

## Aplicar Parches Automáticos
```bash
./security/apply-security-patches.sh
```

Esto actualizará:
- Dependencias de Composer y NPM
- Headers de seguridad
- Configuración de CORS
- Permisos de archivos
- Caché de Laravel

## Checklist de Seguridad Manual

### Laravel
- [ ] `APP_DEBUG=false` en producción
- [ ] `APP_KEY` generada y única
- [ ] CSRF protection habilitado
- [ ] Validación en todos los inputs
- [ ] Sanitización de salidas
- [ ] Rate limiting en API
- [ ] Autenticación de 2 factores

### Servidor
- [ ] Firewall configurado (UFW)
- [ ] SSH asegurado
- [ ] Fail2ban activo
- [ ] SSL/TLS configurado
- [ ] Headers de seguridad
- [ ] Logs monitoreados

### Base de Datos
- [ ] Usuario sin privilegios de root
- [ ] Conexión solo desde localhost
- [ ] Backups automáticos
- [ ] Contraseñas fuertes

## Comandos Útiles

```bash
# Ver estado de fail2ban
sudo fail2ban-client status

# Ver intentos de acceso SSH
sudo grep "Failed password" /var/log/auth.log

# Ver logs de Apache
sudo tail -f /var/log/apache2/error.log

# Verificar headers de seguridad
curl -I http://rustikan

# Test de penetración con curl
curl -X POST http://rustikan/api/test -d "test=<script>alert('xss')</script>"
```

## Recursos Adicionales

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [Mozilla Security Headers](https://observatory.mozilla.org/)
- [SSL Labs Test](https://www.ssllabs.com/ssltest/)

## Contacto y Reporte de Vulnerabilidades

Si encuentras una vulnerabilidad:
1. NO la publiques públicamente
2. Documenta la vulnerabilidad (pasos, impacto, evidencia)
3. Envía reporte a: security@rustikan.local
EOFREADME

check_status "Guía de uso creada"

echo -e "\n${GREEN}=== OWASP ZAP CONFIGURADO ===${NC}"
echo -e "\n${YELLOW}Ejecutar análisis:${NC}"
echo "  Básico:   $SECURITY_DIR/zap-scan-basic.sh"
echo "  Completo: $SECURITY_DIR/zap-scan-full.sh"
echo "  Parches:  $SECURITY_DIR/apply-security-patches.sh"

echo -e "\n${YELLOW}Ubicaciones:${NC}"
echo "  Herramientas: $SECURITY_DIR/tools/"
echo "  Reportes:     $SECURITY_DIR/reports/"
echo "  Guía:         $SECURITY_DIR/README.md"

echo -e "\n${YELLOW}Próximos pasos:${NC}"
echo "  1. Ejecutar escaneo básico"
echo "  2. Revisar reporte generado"
echo "  3. Aplicar parches automáticos"
echo "  4. Corregir vulnerabilidades manualmente"
echo "  5. Ejecutar escaneo completo para verificar"

echo -e "\n${YELLOW}NOTA IMPORTANTE:${NC}"
echo "  - No ejecutes escaneos en sitios de terceros sin permiso"
echo "  - Los escaneos pueden generar mucho tráfico"
echo "  - Revisa los falsos positivos en los reportes"
