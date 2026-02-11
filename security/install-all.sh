#!/bin/bash
# Script Maestro - Fortaleza Digital: Blindaje y Resiliencia
# Proyecto: Rustikan - SGY Seguridad y Alta Disponibilidad

echo "╔════════════════════════════════════════════════════════════╗"
echo "║   FORTALEZA DIGITAL: BLINDAJE Y RESILIENCIA              ║"
echo "║   Proyecto Rustikan - Seguridad y Alta Disponibilidad    ║"
echo "╚════════════════════════════════════════════════════════════╝"

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

SCRIPT_DIR="/var/www/html/rustikan/security"

echo -e "\n${CYAN}Este script configurará:${NC}"
echo "  1. Hardenización del servidor (UFW, SSH, Fail2ban)"
echo "  2. HTTPS con Let's Encrypt"
echo "  3. Alta Disponibilidad con HAProxy"
echo "  4. Sistema de Backups automatizado"
echo "  5. Análisis de vulnerabilidades con OWASP ZAP"
echo ""

read -p "¿Deseas ejecutar la instalación completa? [S/n]: " CONFIRM
if [[ ! $CONFIRM =~ ^[Ss]$ ]] && [[ ! -z $CONFIRM ]]; then
    echo "Instalación cancelada"
    exit 0
fi

echo -e "\n${YELLOW}═══════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}PASO 1/5: HARDENIZACIÓN DEL SERVIDOR${NC}"
echo -e "${YELLOW}═══════════════════════════════════════════════════════${NC}"

read -p "¿Ejecutar hardenización? [S/n]: " RUN_HARDENING
if [[ $RUN_HARDENING =~ ^[Ss]$ ]] || [[ -z $RUN_HARDENING ]]; then
    chmod +x $SCRIPT_DIR/hardening.sh
    sudo $SCRIPT_DIR/hardening.sh
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Hardenización completada${NC}"
    else
        echo -e "${RED}✗ Error en hardenización${NC}"
        read -p "¿Continuar con los demás pasos? [S/n]: " CONTINUE
        if [[ ! $CONTINUE =~ ^[Ss]$ ]] && [[ ! -z $CONTINUE ]]; then
            exit 1
        fi
    fi
else
    echo -e "${BLUE}  Omitiendo hardenización${NC}"
fi

echo -e "\n${YELLOW}═══════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}PASO 2/5: CONFIGURACIÓN SSL/TLS${NC}"
echo -e "${YELLOW}═══════════════════════════════════════════════════════${NC}"

echo -e "${CYAN}IMPORTANTE:${NC} Para configurar SSL necesitas:"
echo "  - Un dominio apuntando a este servidor"
echo "  - Puertos 80 y 443 abiertos públicamente"
echo ""

read -p "¿Tienes un dominio configurado y quieres instalar SSL? [s/N]: " RUN_SSL
if [[ $RUN_SSL =~ ^[Ss]$ ]]; then
    chmod +x $SCRIPT_DIR/setup-ssl.sh
    sudo $SCRIPT_DIR/setup-ssl.sh
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ SSL configurado${NC}"
    else
        echo -e "${RED}✗ Error en configuración SSL${NC}"
    fi
else
    echo -e "${BLUE}  Omitiendo SSL (puedes ejecutarlo después)${NC}"
fi

echo -e "\n${YELLOW}═══════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}PASO 3/5: ALTA DISPONIBILIDAD CON HAPROXY${NC}"
echo -e "${YELLOW}═══════════════════════════════════════════════════════${NC}"

read -p "¿Configurar balanceador de carga HAProxy? [S/n]: " RUN_HAPROXY
if [[ $RUN_HAPROXY =~ ^[Ss]$ ]] || [[ -z $RUN_HAPROXY ]]; then
    chmod +x $SCRIPT_DIR/setup-haproxy.sh
    sudo $SCRIPT_DIR/setup-haproxy.sh
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ HAProxy configurado${NC}"
    else
        echo -e "${RED}✗ Error en configuración HAProxy${NC}"
    fi
else
    echo -e "${BLUE}  Omitiendo HAProxy${NC}"
fi

echo -e "\n${YELLOW}═══════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}PASO 4/5: SISTEMA DE BACKUPS${NC}"
echo -e "${YELLOW}═══════════════════════════════════════════════════════${NC}"

read -p "¿Configurar sistema de backups automatizado? [S/n]: " RUN_BACKUP
if [[ $RUN_BACKUP =~ ^[Ss]$ ]] || [[ -z $RUN_BACKUP ]]; then
    chmod +x $SCRIPT_DIR/setup-backups.sh
    sudo $SCRIPT_DIR/setup-backups.sh
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Sistema de backups configurado${NC}"
    else
        echo -e "${RED}✗ Error en configuración de backups${NC}"
    fi
else
    echo -e "${BLUE}  Omitiendo backups${NC}"
fi

echo -e "\n${YELLOW}═══════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}PASO 5/5: ANÁLISIS DE VULNERABILIDADES${NC}"
echo -e "${YELLOW}═══════════════════════════════════════════════════════${NC}"

read -p "¿Instalar OWASP ZAP para análisis de seguridad? [S/n]: " RUN_ZAP
if [[ $RUN_ZAP =~ ^[Ss]$ ]] || [[ -z $RUN_ZAP ]]; then
    chmod +x $SCRIPT_DIR/setup-owasp-zap.sh
    $SCRIPT_DIR/setup-owasp-zap.sh
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ OWASP ZAP configurado${NC}"
        
        echo ""
        read -p "¿Ejecutar análisis de vulnerabilidades ahora? [s/N]: " RUN_SCAN
        if [[ $RUN_SCAN =~ ^[Ss]$ ]]; then
            $SCRIPT_DIR/zap-scan-basic.sh
        fi
    else
        echo -e "${RED}✗ Error en configuración OWASP ZAP${NC}"
    fi
else
    echo -e "${BLUE}  Omitiendo OWASP ZAP${NC}"
fi

echo -e "\n${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║          CONFIGURACIÓN COMPLETADA                         ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════╝${NC}"

echo -e "\n${CYAN}═══ RESUMEN DE SEGURIDAD ===${NC}"

echo -e "\n${YELLOW}Servicios Activos:${NC}"
systemctl is-active ufw > /dev/null 2>&1 && echo -e "  ${GREEN}✓${NC} UFW (Firewall)" || echo -e "  ${RED}✗${NC} UFW"
systemctl is-active fail2ban > /dev/null 2>&1 && echo -e "  ${GREEN}✓${NC} Fail2ban" || echo -e "  ${RED}✗${NC} Fail2ban"
systemctl is-active haproxy > /dev/null 2>&1 && echo -e "  ${GREEN}✓${NC} HAProxy" || echo -e "  ${RED}✗${NC} HAProxy"
systemctl is-active apache2 > /dev/null 2>&1 && echo -e "  ${GREEN}✓${NC} Apache" || echo -e "  ${RED}✗${NC} Apache"
systemctl is-active keepalived > /dev/null 2>&1 && echo -e "  ${GREEN}✓${NC} Keepalived" || echo -e "  ${RED}✗${NC} Keepalived"

echo -e "\n${YELLOW}Archivos de Configuración:${NC}"
[ -f "/etc/ufw/user.rules" ] && echo -e "  ${GREEN}✓${NC} Firewall configurado"
[ -f "/etc/fail2ban/jail.local" ] && echo -e "  ${GREEN}✓${NC} Fail2ban configurado"
[ -f "/etc/haproxy/haproxy.cfg.backup."* ] && echo -e "  ${GREEN}✓${NC} HAProxy configurado"
[ -f "/usr/local/bin/rustikan-backup.sh" ] && echo -e "  ${GREEN}✓${NC} Backups configurados"

echo -e "\n${YELLOW}Scripts Disponibles:${NC}"
echo "  Backup manual:      sudo /usr/local/bin/rustikan-backup.sh"
echo "  Restaurar:          sudo /usr/local/bin/rustikan-restore.sh <archivo>"
echo "  Análisis básico:    $SCRIPT_DIR/zap-scan-basic.sh"
echo "  Análisis completo:  $SCRIPT_DIR/zap-scan-full.sh"
echo "  Aplicar parches:    $SCRIPT_DIR/apply-security-patches.sh"

echo -e "\n${YELLOW}Interfaces Web:${NC}"
echo "  Aplicación:         http://rustikan"
[ -d "/etc/letsencrypt/live" ] && echo "  HTTPS:              https://rustikan"
systemctl is-active haproxy > /dev/null 2>&1 && echo "  HAProxy Stats:      http://localhost:8404/stats (admin/rustikan2026)"

echo -e "\n${YELLOW}Tareas Programadas:${NC}"
sudo crontab -l 2>/dev/null | grep -c "rustikan" > /dev/null && echo -e "  ${GREEN}✓${NC} Backups automáticos (2:00 AM diario)"
sudo crontab -l 2>/dev/null | grep -c "certbot" > /dev/null && echo -e "  ${GREEN}✓${NC} Renovación SSL (3:00 AM diario)"

echo -e "\n${YELLOW}Ubicaciones Importantes:${NC}"
echo "  Proyecto:           /var/www/html/rustikan"
echo "  Scripts:            $SCRIPT_DIR"
echo "  Backups:            /var/backups/rustikan"
echo "  Reportes:           $SCRIPT_DIR/reports"
echo "  Logs Apache:        /var/log/apache2/"
echo "  Logs HAProxy:       /var/log/haproxy.log"
echo "  Logs Fail2ban:      /var/log/fail2ban.log"

echo -e "\n${CYAN}═══ VERIFICACIONES RECOMENDADAS ===${NC}"
echo "1. Revisar configuración del firewall:"
echo "   ${BLUE}sudo ufw status verbose${NC}"
echo ""
echo "2. Ver intentos bloqueados por Fail2ban:"
echo "   ${BLUE}sudo fail2ban-client status sshd${NC}"
echo ""
echo "3. Verificar balanceo de carga:"
echo "   ${BLUE}curl -I http://localhost${NC}"
echo ""
echo "4. Ver último backup:"
echo "   ${BLUE}sudo /usr/local/bin/rustikan-check-backups.sh${NC}"
echo ""
echo "5. Revisar documentación completa:"
echo "   ${BLUE}cat $SCRIPT_DIR/README.md${NC}"

echo -e "\n${CYAN}═══ PRÓXIMOS PASOS ===${NC}"
echo "1. ${YELLOW}Probar todas las funcionalidades${NC}"
echo "2. ${YELLOW}Revisar reportes de seguridad${NC}"
echo "3. ${YELLOW}Configurar backup remoto${NC}"
echo "4. ${YELLOW}Implementar monitoreo adicional${NC}"
echo "5. ${YELLOW}Documentar cambios realizados${NC}"

echo -e "\n${GREEN}¡Sistema de seguridad y alta disponibilidad configurado!${NC}"
echo -e "${CYAN}Documentación completa en: $SCRIPT_DIR/README-COMPLETO.md${NC}"
