# Docentes Gamificando

Sitio web dedicado a la comunidad docente interesada en la **gamificación educativa**. Recopila recursos, aplicaciones, concursos y entrevistas relacionados con el uso de la gamificación en el aula.

## Desarrolladores

| Desarrollador | GitHub |
|---|---|
| Daniel Bucaloiu | [@MedhoMs](https://github.com/MedhoMs) |
| Airam Fuentes | [@Airamfuentes](https://github.com/Airamfuentes) |

---

## Stack tecnológico

### Backend
- **PHP 8.2+**
- **Laravel 11** — framework base
- **Statamic CMS 5** — CMS flat-file sobre Laravel

### Frontend
- **Tailwind CSS 3** — estilos utilitarios
- **Vite 6** — bundler y dev server
- **Antlers** — motor de plantillas de Statamic
- **@tailwindcss/typography** — tipografía para contenido de texto

### Herramientas de desarrollo
- **Laravel Debugbar** — depuración en desarrollo
- **Laravel Pint** — formateo de código PHP
- **PHPUnit 11** — tests

---

## Estructura del contenido

El proyecto gestiona el contenido mediante colecciones de Statamic (archivos YAML/Markdown, sin base de datos):

| Colección | Descripción |
|---|---|
| `pages` | Páginas estáticas del sitio |
| `contest` | Concursos y competiciones de gamificación |
| `gamificapps` | Aplicaciones y herramientas de gamificación |
| `interview` | Entrevistas a docentes y expertos |
| `team` | Miembros del equipo |

Las colecciones `contest`, `gamificapps` e `interview` usan taxonomías (`tags`, `language_tags`) para clasificar el contenido.

---

## Búsqueda

El sitio incorpora un buscador interno usando el **motor de búsqueda local de Statamic**. El índice se genera a partir de los campos `title`, `summary`, `subtitle`, `description` y `about` de las colecciones `pages`, `contest`, `gamificapps` e `interview`. Los índices se almacenan como archivos JSON en `storage/statamic/search/`.

Para regenerar el índice tras añadir contenido nuevo:

```bash
php artisan statamic:search:update --all
```

---

## Requisitos

- PHP >= 8.2
- Composer
- Node.js + pnpm
- Laragon (u otro entorno local compatible con Laravel)

---

## Instalación

```bash
# Clonar el repositorio
git clone https://github.com/<usuario>/statamicdocentes.git
cd statamicdocentes

# Instalar dependencias PHP
composer install

# Instalar dependencias JS
pnpm install

# Copiar variables de entorno
cp .env.example .env
php artisan key:generate

# Compilar assets
pnpm run build

# Generar el índice de búsqueda
php artisan statamic:search:update --all
```

---

## Desarrollo local

```bash
# Servidor Laravel (con Laragon activo)
php artisan serve

# Vite en modo watch
pnpm run dev
```

El panel de administración de Statamic está disponible en `/cp`.

---

## Tests

```bash
php artisan test
```

---

## Licencia

Proyecto académico / educativo. Todos los derechos reservados © 2026 Docentes Gamificando.


## Contributing

Thank you for considering contributing to Statamic! We simply ask that you review the [contribution guide][contribution] before you open issues or send pull requests.


## Code of Conduct

In order to ensure that the Statamic community is welcoming to all and generally a rad place to belong, please review and abide by the [Code of Conduct](https://github.com/statamic/cms/wiki/Code-of-Conduct).


## Important Links

- [Statamic Main Site](https://statamic.com)
- [Statamic Documentation][docs]
- [Statamic Core Package Repo][cms-repo]
- [Statamic Migrator](https://github.com/statamic/migrator)
- [Statamic Discord][discord]

[docs]: https://statamic.dev/
[discord]: https://statamic.com/discord
[contribution]: https://github.com/statamic/cms/blob/master/CONTRIBUTING.md
[cms-repo]: https://github.com/statamic/cms
