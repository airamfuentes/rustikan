# 🚀 Guía de Deploy a Producción — Rustikan

Stack:
- **Dominio:** IONOS (rustikan.com)
- **DNS / CDN / SSL:** Cloudflare (gratis)
- **Hosting:** DigitalOcean Droplet provisionado por **Laravel Forge**
- **Base de datos:** MySQL en el mismo Droplet
- **Email:** Resend
- **Repositorio:** GitHub

> **Tiempo estimado total:** 1-2 horas si es la primera vez.

---

## 📋 Antes de empezar — Cuentas que necesitas

Crea (o ten a mano) cuentas en:

1. **GitHub** — para subir el código
2. **DigitalOcean** ([digitalocean.com](https://digitalocean.com)) — el servidor
3. **Laravel Forge** ([forge.laravel.com](https://forge.laravel.com)) — automatiza la configuración (~$12/mes)
4. **IONOS** — donde compres el dominio
5. **Cloudflare** ([cloudflare.com](https://cloudflare.com)) — gratis
6. **Resend** ([resend.com](https://resend.com)) — gratis hasta 3000 emails/mes
7. **Google reCAPTCHA** ([google.com/recaptcha/admin](https://www.google.com/recaptcha/admin)) — gratis

---

## 🪜 Paso 1 — Subir el código a GitHub

Si aún no tienes el repo en GitHub:

```bash
# Desde la raíz del proyecto local
git remote -v   # comprueba si ya tiene remoto
# Si no lo tiene:
git remote add origin https://github.com/airamfuentes/rustikan.git
git push -u origin main
```

**Antes de hacer push verifica que `.env` NO está incluido:**
```bash
git status --ignored | grep .env
# debe aparecer .env como ignored, NO como tracked
```

---

## 🪜 Paso 2 — Comprar dominio en IONOS

1. Ve a [ionos.es](https://www.ionos.es) → busca `rustikan.com` (o el TLD que prefieras)
2. Compra solo el dominio (no contrates hosting de IONOS, no lo necesitamos)
3. Apunta el dominio luego — primero hay que pasar por Cloudflare

---

## 🪜 Paso 3 — Configurar Cloudflare (DNS + CDN gratis)

1. Crea cuenta en [cloudflare.com](https://cloudflare.com) y haz clic en **Add a site**
2. Añade `rustikan.com` → elige el plan **Free**
3. Cloudflare te dará **2 nameservers** del estilo:
   ```
   alex.ns.cloudflare.com
   tina.ns.cloudflare.com
   ```
4. Vuelve a IONOS → Panel del dominio → **Nameservers** → cambia a los de Cloudflare
   - La propagación tarda entre 5 min y 24 h
5. **Importante:** En Cloudflare → **SSL/TLS** → modo **Full (strict)** _(lo activarás más tarde, cuando tengas certificado en el servidor)_
   - De momento déjalo en **Flexible** o **Full**

---

## 🪜 Paso 4 — Crear servidor con Laravel Forge

1. Crea cuenta en [forge.laravel.com](https://forge.laravel.com) → plan **Hobby ($12/mes)**
2. Conecta tu cuenta de **DigitalOcean** (Forge te pedirá API token de DO)
3. **Create Server** → elige:
   - **Provider:** DigitalOcean
   - **Region:** Frankfurt o Ámsterdam (más cerca de Canarias = más rápido)
   - **Server Size:** $6/mes (1GB RAM, 1 vCPU) — suficiente para empezar
   - **PHP Version:** 8.3
   - **Server Type:** App Server (con MySQL)
   - **Database:** MySQL — apunta el `Database Password` que Forge genera
4. Espera ~10 min mientras Forge provisiona el servidor
5. Forge te dará la **IP del servidor** (ej. `159.65.123.45`) — guárdala

---

## 🪜 Paso 5 — Apuntar el dominio al servidor (Cloudflare DNS)

En Cloudflare → tu dominio → **DNS** → añade:

| Type | Name | Content                | Proxy status |
|------|------|------------------------|--------------|
| A    | @    | `IP_DEL_DROPLET`       | ✅ Proxied   |
| A    | www  | `IP_DEL_DROPLET`       | ✅ Proxied   |

> El icono naranja (Proxied) hace que Cloudflare actúe como CDN delante de tu servidor. Importante para velocidad y protección.

---

## 🪜 Paso 6 — Crear el sitio en Forge

1. En Forge, dentro de tu servidor, click **New Site**:
   - **Root Domain:** `rustikan.com`
   - **Aliases:** `www.rustikan.com`
   - **Web Directory:** `/public`
   - **PHP Version:** 8.3
   - **Project Type:** Laravel
2. Click **Add Site** y espera a que termine

### 6.1 Conectar el repositorio Git

1. Sitio → **App** tab → **Install Repository**
2. **Provider:** GitHub
3. **Repository:** `airamfuentes/rustikan`
4. **Branch:** `main`
5. ✅ Marca **Install Composer Dependencies**
6. Click **Install Repository**

### 6.2 Pegar el script de deploy

1. Sitio → **Deployments** tab → **Edit Deployment Script**
2. Borra el contenido por defecto y pega el contenido de [deploy.sh](deploy.sh) del proyecto
3. **Update**

### 6.3 Activar Quick Deploy

Activa el toggle **Quick Deploy** — así cada `git push origin main` deployará automáticamente.

---

## 🪜 Paso 7 — Configurar variables de entorno en Forge

Sitio → **Environment** tab → edita el `.env`:

```env
APP_NAME=Rustikan
APP_ENV=production
APP_KEY=                                    # Forge lo genera al crear el sitio. Si está vacío: pega lo que de `php artisan key:generate --show`
APP_DEBUG=false
APP_URL=https://rustikan.com

APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_ES

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=forge                           # Forge crea esta DB automáticamente
DB_USERNAME=forge
DB_PASSWORD=                                # ⚠️ pega el password que Forge te dio al crear el servidor

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_DOMAIN=.rustikan.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_HTTP_ONLY=true

CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
BROADCAST_CONNECTION=log

# Email (configurar en Paso 8)
MAIL_MAILER=resend
MAIL_FROM_ADDRESS="noreply@rustikan.com"
MAIL_FROM_NAME="Rustikan"
RESEND_API_KEY=

# reCAPTCHA (configurar en Paso 9)
RECAPTCHA_SITE_KEY=
RECAPTCHA_SECRET_KEY=

# Cloudflare proxies
TRUSTED_PROXIES=*

VITE_APP_NAME="${APP_NAME}"
```

Click **Save**.

---

## 🪜 Paso 8 — Configurar Resend (emails)

1. Crea cuenta en [resend.com](https://resend.com)
2. **Domains** → **Add Domain** → `rustikan.com`
3. Resend te dará registros DNS (TXT, MX). Añádelos en **Cloudflare DNS** (Type: TXT, MX según indique)
4. Espera a que Resend valide el dominio (5-30 min)
5. **API Keys** → **Create API Key** → copia el `re_xxx`
6. Pégalo en Forge → Environment → `RESEND_API_KEY`

---

## 🪜 Paso 9 — Configurar reCAPTCHA

1. Ve a [google.com/recaptcha/admin](https://www.google.com/recaptcha/admin)
2. **+** → registra un nuevo sitio:
   - **Type:** reCAPTCHA v2 (casilla "No soy un robot")
   - **Domains:** `rustikan.com`, `www.rustikan.com`, `localhost` (para seguir trabajando local)
3. Copia **Site Key** y **Secret Key**
4. Pégalos en Forge → Environment → `RECAPTCHA_SITE_KEY` y `RECAPTCHA_SECRET_KEY`

---

## 🪜 Paso 10 — SSL (HTTPS)

### Opción A — SSL con Let's Encrypt (recomendado)

1. En Forge → Sitio → **SSL** tab
2. Click **Let's Encrypt**
3. Marca `rustikan.com` y `www.rustikan.com`
4. **Obtain Certificate** — espera 1-2 min
5. Forge configura nginx automáticamente

### Opción B — SSL via Cloudflare (alternativa)

Si Let's Encrypt falla por DNS, usa Cloudflare Origin Certificate:
1. Cloudflare → **SSL/TLS** → **Origin Server** → **Create Certificate**
2. Forge → SSL → **Install Existing Certificate** → pega cert y key
3. Cloudflare → **SSL/TLS** → **Overview** → modo **Full (strict)**

### Forzar HTTPS en Cloudflare

Cloudflare → **SSL/TLS** → **Edge Certificates** → activa:
- ✅ Always Use HTTPS
- ✅ Automatic HTTPS Rewrites
- ✅ Minimum TLS Version: 1.2

---

## 🪜 Paso 11 — Primer deploy

1. Forge → Sitio → **Deployments** → **Deploy Now**
2. Mira el log — debe terminar con `Build successful` y `[OK]`
3. Visita `https://rustikan.com` — la home debe cargar

### Si ves error 500

- Revisa logs en Forge → Server → **Logs** → `nginx/laravel.log`
- Causas típicas: `APP_KEY` vacía, `RESEND_API_KEY` vacía con código que la usa, DB no migrada

---

## 🪜 Paso 12 — Crear usuario admin en producción

⚠️ **NO ejecutes `db:seed`** en producción (crearía credenciales débiles).

En lugar de eso, conéctate por SSH (Forge → Server → **SSH Key**) y ejecuta:

```bash
ssh forge@TU_IP_SERVIDOR
cd /home/forge/rustikan.com
php artisan tinker
```

Dentro de tinker:

```php
\App\Models\User::create([
    'name' => 'Tu Nombre',
    'email' => 'tu-email@rustikan.com',
    'password' => bcrypt('UNA_CONTRASEÑA_FUERTE_DE_VERDAD'),
    'role' => 'admin',
    'email_verified_at' => now(),
]);
exit
```

Ahora puedes loguearte en `https://rustikan.com/login`.

---

## 🪜 Paso 13 — Categorías iniciales

Si tu DB de producción está vacía (sin categorías), puedes ejecutar **solo** el seeder de categorías:

```bash
php artisan db:seed --class=CategoriaSeeder
```

> No ejecutes `TiendaSeeder` ni `ProductoSeeder` en producción — son datos ficticios.

---

## 🪜 Paso 14 — Configurar queue worker (envío de emails async)

En Forge → Server → **Daemons** → **New Daemon**:
- **Command:** `php /home/forge/rustikan.com/artisan queue:work --sleep=3 --tries=3 --max-time=3600`
- **User:** `forge`
- **Directory:** `/home/forge/rustikan.com`

Esto mantiene un proceso vivo que procesa la cola de emails.

---

## 🪜 Paso 15 — Cron de Laravel (tareas programadas)

Forge ya configura esto automáticamente al crear el sitio Laravel. Puedes verificarlo en:
Forge → Server → **Scheduler** → debe haber una entrada `* * * * * php artisan schedule:run`

---

## ✅ Checklist final antes de "abrir al público"

- [ ] `https://rustikan.com` carga la home
- [ ] `https://www.rustikan.com` redirige a `https://rustikan.com` (Cloudflare → Page Rules)
- [ ] HTTP redirige a HTTPS (Cloudflare → Always Use HTTPS)
- [ ] Login con tu usuario admin funciona
- [ ] El panel admin (`/admin/dashboard`) es accesible solo a admins
- [ ] Crear una tienda de prueba como admin funciona (subir imagen incluida)
- [ ] El email de confirmación llega al registrar un nuevo usuario
- [ ] reCAPTCHA aparece en login/registro y valida
- [ ] El mapa Leaflet de la home carga las tiendas
- [ ] El carrito persiste al recargar
- [ ] Hacer un pedido de prueba completo funciona
- [ ] Ver pedido como owner y como admin funciona
- [ ] **Cambia la contraseña** del usuario admin si la pusiste débil de prueba

---

## 🔄 Cómo hacer cambios después de lanzar

```bash
# En tu máquina local
git add -A
git commit -m "Descripción del cambio"
git push origin main
```

**Forge detecta el push y deploya automáticamente** (si activaste Quick Deploy). Tarda ~30-60 segundos.

Para deploys manuales o ver el log: Forge → Sitio → Deployments → Deploy Now / Recent Deployments.

---

## 🆘 Comandos útiles en producción (vía SSH)

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Limpiar todos los caches
php artisan optimize:clear

# Re-cachear todo
php artisan optimize

# Estado de queue
php artisan queue:monitor

# Reiniciar queue tras un cambio de código
php artisan queue:restart

# Activar modo mantenimiento
php artisan down --secret="bypass-rustikan-XXX"
# Visita rustikan.com/bypass-rustikan-XXX para entrar saltando el modo mantenimiento

# Desactivar modo mantenimiento
php artisan up
```

---

## 💰 Coste estimado mensual

| Servicio | Coste |
|---|---|
| Dominio IONOS (.com) | ~12€/año (~1€/mes) |
| DigitalOcean Droplet | $6/mes (~5,5€) |
| Laravel Forge | $12/mes (~11€) |
| Cloudflare | Gratis |
| Resend | Gratis (3000 emails/mes) |
| reCAPTCHA | Gratis |
| **Total** | **~17,5€/mes** |

Cuando tengas más tráfico, puedes escalar el Droplet a $12 o $24/mes desde el panel de DigitalOcean (1 click, sin downtime).

---

## 📚 Recursos

- Forge docs: https://forge.laravel.com/docs/introduction.html
- Laravel deployment: https://laravel.com/docs/deployment
- Cloudflare DNS: https://developers.cloudflare.com/dns/
- Resend Laravel: https://resend.com/docs/send-with-laravel
