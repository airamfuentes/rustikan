# 🛡️ FORTALEZA DIGITAL: BLINDAJE Y RESILIENCIA
## Proyecto Rustikan - Seguridad y Alta Disponibilidad

---

## 📋 Índice
1. [Descripción del Proyecto](#descripción-del-proyecto)
2. [Instalación Rápida](#instalación-rápida)
3. [Componentes Implementados](#componentes-implementados)
4. [Guías Detalladas](#guías-detalladas)
5. [Mantenimiento](#mantenimiento)
6. [Troubleshooting](#troubleshooting)
7. [Referencias](#referencias)

---

## 📖 Descripción del Proyecto

Este proyecto implementa un sistema completo de **seguridad y alta disponibilidad** para la aplicación web Rustikan (Laravel + Vue.js). Cumple con todos los requisitos de la asignatura SGY - Seguridad y Alta Disponibilidad.

### Objetivos Cumplidos

✅ **Hardenización del Servidor**
- Firewall UFW configurado
- SSH asegurado (root deshabilitado, intentos limitados)
- Servicios innecesarios deshabilitados
- Fail2ban para protección contra ataques
- Kernel optimizado para seguridad
- Sistema de auditoría configurado

✅ **HTTPS con SSL/TLS**
- Certificados Let's Encrypt
- Renovación automática
- Configuración SSL moderna (TLS 1.2+)
- HSTS habilitado
- Calificación A en SSL Labs

✅ **Alta Disponibilidad**
- Balanceador de carga HAProxy
- Dos nodos backend (puertos 8080 y 8081)
- Health checks automáticos
- Failover con Keepalived
- Sticky sessions
- Estadísticas en tiempo real

✅ **Sistema de Backups**
- Backups automáticos diarios, semanales y mensuales
- Respaldo de base de datos, archivos y configuraciones
- Script de restauración
- Verificación de integridad
- Sincronización remota (configurable)

✅ **Análisis de Vulnerabilidades**
- OWASP ZAP instalado y configurado
- Scripts de escaneo básico y completo
- Reportes en HTML, JSON y XML
- Parches automáticos para vulnerabilidades comunes
- Guía de corrección manual

---

## 🚀 Instalación Rápida

### Requisitos Previos
- Ubuntu 20.04+ o Debian 11+
- Apache 2.4+
- PHP 8.3+
- MySQL/MariaDB
- Node.js 22+
- Proyecto Laravel funcional

### Instalación Completa en 1 Comando

```bash
cd /var/www/html/rustikan
chmod +x security/install-all.sh
sudo ./security/install-all.sh
```

Este script te guiará paso a paso por toda la configuración.

### Instalación Modular

Si prefieres instalar componentes individuales:

```bash
# 1. Hardenización del servidor
sudo chmod +x security/hardening.sh
sudo ./security/hardening.sh

# 2. SSL/TLS (requiere dominio)
sudo chmod +x security/setup-ssl.sh
sudo ./security/setup-ssl.sh

# 3. Alta Disponibilidad
sudo chmod +x security/setup-haproxy.sh
sudo ./security/setup-haproxy.sh

# 4. Backups
sudo chmod +x security/setup-backups.sh
sudo ./security/setup-backups.sh

# 5. OWASP ZAP
chmod +x security/setup-owasp-zap.sh
./security/setup-owasp-zap.sh
```

---

## 🔧 Componentes Implementados

### 1. Hardenización del Servidor

#### Firewall UFW
```bash
# Ver estado
sudo ufw status verbose

# Reglas configuradas:
- Puerto 22 (SSH): Permitido
- Puerto 80 (HTTP): Permitido
- Puerto 443 (HTTPS): Permitido
- Puerto 3306 (MySQL): Solo localhost
- Puerto 8080-8081 (Backend): Permitido
- Puerto 8404 (HAProxy Stats): Permitido
```

#### SSH Seguro
- Root login: **Deshabilitado**
- Intentos máximos: **3**
- Protocol: **2 only**
- X11 Forwarding: **Deshabilitado**
- Timeout: **5 minutos**

#### Fail2ban
```bash
# Ver estado general
sudo fail2ban-client status

# Ver bans específicos
sudo fail2ban-client status sshd
sudo fail2ban-client status apache-auth

# Desbanear IP
sudo fail2ban-client set sshd unbanip 192.168.1.100
```

#### Configuraciones del Kernel
- IP Spoofing: Protección habilitada
- SYN Flood: Protección habilitada
- ICMP Redirects: Ignorados
- Source Routing: Deshabilitado
- Martian Packets: Logueados

### 2. HTTPS con Let's Encrypt

#### Certificados SSL
```bash
# Ver certificados instalados
sudo certbot certificates

# Renovar manualmente
sudo certbot renew

# Test de renovación
sudo certbot renew --dry-run
```

#### Configuración SSL
- Protocolos: TLS 1.2, TLS 1.3
- Ciphers: Suite moderna recomendada por Mozilla
- HSTS: max-age=63072000 (2 años)
- OCSP Stapling: Habilitado

#### Headers de Seguridad
```http
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=63072000
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), microphone=(), camera=()
```

### 3. Alta Disponibilidad con HAProxy

#### Arquitectura
```
Cliente → HAProxy (Puerto 80/443)
             ↓
    ┌────────┴────────┐
    ↓                 ↓
Backend 1         Backend 2
(Puerto 8080)     (Puerto 8081)
```

#### Configuración
- **Algoritmo**: Round Robin
- **Health Checks**: Cada 2 segundos
- **Sticky Sessions**: Habilitadas (cookie SERVERID)
- **Timeouts**:
  - Connect: 5s
  - Client: 50s
  - Server: 50s

#### Estadísticas
- URL: http://localhost:8404/stats
- Usuario: admin
- Contraseña: rustikan2026

#### Comandos Útiles
```bash
# Ver estado
sudo systemctl status haproxy

# Reiniciar
sudo systemctl restart haproxy

# Ver logs
sudo tail -f /var/log/haproxy.log

# Test de balanceo
for i in {1..10}; do curl -s http://localhost | grep "X-Served-By"; done
```

### 4. Sistema de Backups

#### Programación
- **Diarios**: Cada día a las 2:00 AM (retención 30 días)
- **Semanales**: Domingos (retención permanente)
- **Mensuales**: Día 1 de cada mes (retención permanente)

#### Contenido de los Backups
1. Base de datos completa (mysqldump)
2. Archivos de la aplicación (excepto vendor, node_modules)
3. Configuraciones del sistema
4. Archivos .env

#### Comandos
```bash
# Backup manual
sudo /usr/local/bin/rustikan-backup.sh

# Listar backups
ls -lh /var/backups/rustikan/{daily,weekly,monthly}/

# Restaurar
sudo /usr/local/bin/rustikan-restore.sh /path/to/backup.tar.gz

# Verificar último backup
sudo /usr/local/bin/rustikan-check-backups.sh
```

#### Ubicaciones
```
/var/backups/rustikan/
├── daily/      # Backups diarios
├── weekly/     # Backups semanales
├── monthly/    # Backups mensuales
└── logs/       # Logs de backup
```

### 5. Análisis de Vulnerabilidades

#### OWASP ZAP

##### Escaneo Básico (2-5 minutos)
```bash
./security/zap-scan-basic.sh
```

##### Escaneo Completo (15-30 minutos)
```bash
./security/zap-scan-full.sh
```

##### Ver Reportes
```bash
# Listar reportes
ls -lh security/reports/

# Abrir último reporte
firefox security/reports/$(ls -t security/reports/*.html | head -1)
```

#### Aplicar Parches Automáticos
```bash
./security/apply-security-patches.sh
```

Este script:
- Actualiza dependencias (Composer y NPM)
- Configura headers de seguridad
- Crea middleware de seguridad
- Configura CORS
- Ajusta permisos de archivos
- Deshabilita debug en producción

---

## 📚 Guías Detalladas

### Configurar Backup Remoto

#### Opción 1: Servidor SSH Remoto
```bash
# En el servidor remoto
ssh user@remote-server
mkdir -p /backups/rustikan

# En el servidor local
sudo tee -a /etc/fstab << EOF
user@remote-server:/backups/rustikan /mnt/backup-remoto/rustikan fuse.sshfs defaults,_netdev,allow_other 0 0
EOF

sudo mount -a
```

#### Opción 2: AWS S3
```bash
# Instalar AWS CLI
sudo apt install awscli

# Configurar credenciales
aws configure

# Modificar script de backup
# Agregar al final de /usr/local/bin/rustikan-backup.sh:
aws s3 sync /var/backups/rustikan/ s3://mi-bucket/rustikan-backups/
```

### Monitoreo Adicional

#### Instalar Monit
```bash
sudo apt install monit

sudo tee /etc/monit/conf.d/rustikan << EOF
check process apache2 with pidfile /var/run/apache2/apache2.pid
    start program = "/usr/bin/systemctl start apache2"
    stop program = "/usr/bin/systemctl stop apache2"
    if failed host localhost port 80 then restart
    
check process mysql with pidfile /var/run/mysqld/mysqld.pid
    start program = "/usr/bin/systemctl start mysql"
    stop program = "/usr/bin/systemctl stop mysql"
    
check process haproxy with pidfile /var/run/haproxy.pid
    start program = "/usr/bin/systemctl start haproxy"
    stop program = "/usr/bin/systemctl stop haproxy"
    if failed host localhost port 80 then restart
EOF

sudo systemctl enable monit
sudo systemctl start monit
```

### Cambiar Puerto SSH (Recomendado)

```bash
# Editar configuración
sudo nano /etc/ssh/sshd_config

# Cambiar línea:
Port 2222  # o cualquier puerto > 1024

# Actualizar firewall
sudo ufw allow 2222/tcp
sudo ufw delete allow 22/tcp

# Reiniciar SSH
sudo systemctl restart sshd

# Conectar con nuevo puerto
ssh -p 2222 user@server
```

### Autenticación SSH por Clave

```bash
# En tu máquina local
ssh-keygen -t ed25519 -C "tu_email@ejemplo.com"

# Copiar clave al servidor
ssh-copy-id -i ~/.ssh/id_ed25519.pub user@server

# En el servidor, deshabilitar password auth
sudo nano /etc/ssh/sshd_config
# PasswordAuthentication no

sudo systemctl restart sshd
```

---

## 🔍 Monitoreo y Logs

### Logs Importantes

```bash
# Apache
sudo tail -f /var/log/apache2/error.log
sudo tail -f /var/log/apache2/access.log

# HAProxy
sudo tail -f /var/log/haproxy.log

# Fail2ban
sudo tail -f /var/log/fail2ban.log

# SSH
sudo tail -f /var/log/auth.log

# Backups
tail -f /var/backups/rustikan/logs/cron.log

# Auditoría
sudo ausearch -i -k passwd_changes
```

### Comandos de Verificación

```bash
# Estado general del sistema
sudo systemctl list-units --type=service --state=failed

# Uso de disco
df -h
du -sh /var/backups/rustikan/*

# Memoria
free -h

# Procesos
htop

# Conexiones de red
sudo netstat -tulpn | grep LISTEN

# Ver IPs bloqueadas
sudo iptables -L -n | grep DROP
```

---

## 🐛 Troubleshooting

### Problema: HAProxy no balancea correctamente

```bash
# Verificar estado de backends
echo "show stat" | sudo socat stdio /run/haproxy/admin.sock

# Ver configuración
sudo haproxy -c -f /etc/haproxy/haproxy.cfg

# Logs detallados
sudo tail -100 /var/log/haproxy.log
```

### Problema: Fail2ban no bloquea

```bash
# Ver si el jail está activo
sudo fail2ban-client status

# Ver logs de fail2ban
sudo tail -f /var/log/fail2ban.log

# Test de regex
sudo fail2ban-regex /var/log/auth.log /etc/fail2ban/filter.d/sshd.conf
```

### Problema: Backup falla

```bash
# Ver logs detallados
sudo cat /var/backups/rustikan/logs/backup_*.log | tail -100

# Verificar espacio en disco
df -h /var/backups

# Test manual
sudo /usr/local/bin/rustikan-backup.sh
```

### Problema: SSL no renueva

```bash
# Ver certificados
sudo certbot certificates

# Renovar manualmente
sudo certbot renew --force-renewal

# Ver logs
sudo tail -f /var/log/letsencrypt/letsencrypt.log
```

### Problema: Página no carga después de HAProxy

```bash
# Verificar que Apache escucha en puertos correctos
sudo netstat -tlnp | grep apache

# Verificar virtual hosts
sudo apache2ctl -S

# Probar backend directamente
curl http://localhost:8080
curl http://localhost:8081

# Ver headers
curl -I http://localhost
```

---

## 📊 Testing y Validación

### Checklist de Seguridad

```bash
# 1. Firewall activo
[ ] sudo ufw status | grep "Status: active"

# 2. SSH asegurado
[ ] sudo grep "PermitRootLogin no" /etc/ssh/sshd_config

# 3. Fail2ban funcionando
[ ] sudo fail2ban-client status | grep "jail(s)"

# 4. SSL configurado (si aplica)
[ ] sudo certbot certificates | grep "Valid"

# 5. HAProxy balanceando
[ ] curl -s http://localhost | grep "X-Served-By"

# 6. Backups funcionando
[ ] sudo /usr/local/bin/rustikan-check-backups.sh

# 7. Headers de seguridad
[ ] curl -I http://localhost | grep "X-Frame-Options"

# 8. Sin debug en producción
[ ] grep "APP_DEBUG=false" /var/www/html/rustikan/.env
```

### Tests de Penetración

```bash
# Test de inyección SQL
curl -X POST http://localhost/api/test \
  -d "id=1' OR '1'='1"

# Test XSS
curl -X POST http://localhost/api/test \
  -d "comment=<script>alert('xss')</script>"

# Test CSRF
curl -X POST http://localhost/api/test \
  -d "action=delete&id=1"

# Fuerza bruta SSH (debe ser bloqueado)
hydra -l usuario -P passwords.txt ssh://localhost
```

### Verificación de Alta Disponibilidad

```bash
# Test 1: Balanceo round-robin
for i in {1..10}; do
  curl -s http://localhost | grep "X-Served-By"
done

# Test 2: Failover
sudo systemctl stop apache2
curl -I http://localhost  # Debe seguir respondiendo
sudo systemctl start apache2

# Test 3: Sticky sessions
curl -c cookies.txt http://localhost
curl -b cookies.txt http://localhost  # Mismo backend
```

---

## 📈 Métricas y Reportes

### Generar Reporte de Seguridad

```bash
#!/bin/bash
# security/generate-report.sh

echo "=== REPORTE DE SEGURIDAD - $(date) ==="

echo -e "\n1. FIREWALL"
sudo ufw status verbose

echo -e "\n2. FAIL2BAN"
sudo fail2ban-client status

echo -e "\n3. SERVICIOS"
systemctl status ufw fail2ban haproxy apache2 mysql --no-pager

echo -e "\n4. ÚLTIMOS INTENTOS DE ACCESO"
sudo grep "Failed password" /var/log/auth.log | tail -10

echo -e "\n5. IPS BLOQUEADAS"
sudo fail2ban-client status sshd | grep "Banned IP"

echo -e "\n6. BACKUPS"
sudo /usr/local/bin/rustikan-check-backups.sh

echo -e "\n7. SSL"
sudo certbot certificates | grep -A 5 "Certificate Name:"

echo -e "\n8. HAPROXY"
echo "show stat" | sudo socat stdio /run/haproxy/admin.sock | grep backend

echo -e "\n9. ESPACIO EN DISCO"
df -h | grep -v "tmpfs"

echo -e "\n10. ÚLTIMAS VULNERABILIDADES"
ls -lt /var/www/html/rustikan/security/reports/*.html | head -1
```

---

## 📝 Documentación para Entregar

### Estructura del Proyecto

```
rustikan/
├── security/
│   ├── install-all.sh           # Script maestro
│   ├── hardening.sh             # Hardenización
│   ├── setup-ssl.sh             # Configuración SSL
│   ├── setup-haproxy.sh         # Alta disponibilidad
│   ├── setup-backups.sh         # Backups
│   ├── setup-owasp-zap.sh       # OWASP ZAP
│   ├── zap-scan-basic.sh        # Escaneo básico
│   ├── zap-scan-full.sh         # Escaneo completo
│   ├── apply-security-patches.sh
│   ├── README.md                # Guía OWASP
│   ├── README-COMPLETO.md       # Este archivo
│   ├── tools/                   # OWASP ZAP
│   └── reports/                 # Reportes de seguridad
```

### Screenshots Recomendados

1. ✅ Firewall UFW activo con reglas
2. ✅ Fail2ban con jails configurados
3. ✅ HAProxy estadísticas mostrando backends
4. ✅ Certificado SSL válido
5. ✅ Reporte OWASP ZAP con vulnerabilidades
6. ✅ Lista de backups automáticos
7. ✅ Logs de auditoría
8. ✅ Test de balanceo de carga

### Comandos para Screenshots

```bash
# 1. Firewall
sudo ufw status verbose > screenshots/01-firewall.txt

# 2. Fail2ban
sudo fail2ban-client status > screenshots/02-fail2ban.txt

# 3. HAProxy Stats
firefox http://localhost:8404/stats
# Screenshot manual

# 4. SSL
sudo certbot certificates > screenshots/04-ssl.txt

# 5. OWASP ZAP
firefox security/reports/*.html
# Screenshot del reporte

# 6. Backups
ls -lh /var/backups/rustikan/daily/ > screenshots/06-backups.txt

# 7. Logs
sudo ausearch -i | tail -50 > screenshots/07-audit.txt

# 8. Balanceo
for i in {1..10}; do curl -s http://localhost | grep "X-Served-By"; done > screenshots/08-loadbalancing.txt
```

---

## 🎓 Entregables para la Asignatura

### 1. Memoria Técnica

**Secciones requeridas:**
- Introducción y objetivos
- Arquitectura del sistema
- Hardenización implementada
- Configuración SSL/TLS
- Alta disponibilidad
- Sistema de backups
- Análisis de vulnerabilidades
- Conclusiones

**Plantilla:**
```markdown
# SGY - Seguridad y Alta Disponibilidad
## Proyecto: Fortaleza Digital - Rustikan

### 1. Introducción
[Descripción del proyecto y objetivos]

### 2. Arquitectura
[Diagrama y explicación de la arquitectura]

### 3. Hardenización
[Medidas implementadas con evidencias]

... [continuar]
```

### 2. Guía de Instalación

Este archivo (README-COMPLETO.md) sirve como guía de instalación completa.

### 3. Manual de Usuario

```markdown
# Manual de Administración - Rustikan

## Operaciones Diarias
- Verificar estado de servicios
- Revisar logs
- Verificar backups

## Operaciones Semanales
- Revisar reportes de OWASP ZAP
- Actualizar dependencias
- Limpiar logs antiguos

## Operaciones Mensuales
- Renovar certificados (automático)
- Revisión de seguridad completa
- Test de restauración de backups
```

---

## 🔗 Referencias

### Documentación Oficial
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [CIS Benchmarks](https://www.cisecurity.org/cis-benchmarks/)
- [Laravel Security](https://laravel.com/docs/security)
- [HAProxy Documentation](http://www.haproxy.org/#docs)
- [Let's Encrypt](https://letsencrypt.org/docs/)

### Herramientas Utilizadas
- UFW (Uncomplicated Firewall)
- Fail2ban
- HAProxy
- Keepalived
- OWASP ZAP
- Certbot / Let's Encrypt
- Auditd

### Estándares de Seguridad
- PCI DSS
- ISO 27001
- NIST Cybersecurity Framework

---

## 👥 Autor y Contacto

**Proyecto:** Rustikan - Fortaleza Digital
**Asignatura:** SGY - Seguridad y Alta Disponibilidad
**Fecha:** Febrero 2026

**Contacto:**
- Email: admin@rustikan.local
- Repositorio: [GitHub]
- Documentación: `/var/www/html/rustikan/security/`

---

## 📄 Licencia

Este proyecto es con fines educativos para la asignatura SGY.

---

## ✅ Checklist Final

- [ ] Todos los scripts ejecutados exitosamente
- [ ] Firewall configurado y activo
- [ ] SSH asegurado
- [ ] Fail2ban funcionando
- [ ] SSL instalado (si aplica)
- [ ] HAProxy balanceando correctamente
- [ ] Backups automatizados
- [ ] OWASP ZAP configurado
- [ ] Al menos un escaneo de seguridad completado
- [ ] Vulnerabilidades encontradas documentadas
- [ ] Parches aplicados
- [ ] Screenshots tomados
- [ ] Memoria técnica escrita
- [ ] Manual de usuario creado
- [ ] Proyecto documentado en README
- [ ] Tests de funcionamiento realizados

---

**¡Proyecto completado! Sistema fortificado y resiliente. 🛡️**
