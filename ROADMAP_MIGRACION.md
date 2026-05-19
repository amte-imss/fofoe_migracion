# Roadmap de Migración: Symfony 3.4 + PHP 5.6 → Symfony 8.0 + PHP 8.5

**Proyecto origen:** `sf-boilerplate-imms` (rama `testing`)  
**Destino:** `fofoe_migracion`  
**Fecha de inicio:** 29 de Abril 2026  
**Última actualización:** 19 de Mayo 2026

---

## 🚀 Cómo levantar el proyecto

### Requisitos previos
- PHP 8.5 via Homebrew: `/opt/homebrew/opt/php@8.5/bin/php`
- PostgreSQL 16 corriendo (Homebrew)
- Node.js + npm

### Pasos

**1. Crear `.env.local`**
```bash
DATABASE_URL="postgresql://fofoe_user:fofoe_pass@127.0.0.1:5432/fofoe_dev?serverVersion=16&charset=utf8"
MAILER_DSN=smtp://localhost:1025
WKHTMLTOPDF_PATH="/usr/local/bin/wkhtmltopdf"
```

**2. Instalar dependencias**
```bash
/opt/homebrew/opt/php@8.5/bin/php /opt/homebrew/bin/composer install
npm install
```

**3. Compilar assets**
```bash
npm run dev        # una vez
npm run watch      # modo watch (recompila al editar)
```

**4. Levantar el servidor**
```bash
/opt/homebrew/opt/php@8.5/bin/php -d memory_limit=512M -S localhost:8000 -t public/
```

**5. Limpiar caché (si hay errores)**
```bash
/opt/homebrew/opt/php@8.5/bin/php bin/console cache:clear
```

> ⚠️ Usar siempre PHP 8.5 explícitamente para Composer:
> ```bash
> /opt/homebrew/opt/php@8.5/bin/php /opt/homebrew/bin/composer <comando>
> ```

---

## 📬 Correo (Mailer)

`MAILER_DSN=smtp://localhost:1025` — requiere servidor SMTP local para pruebas:

```bash
brew install mailpit
mailpit   # UI en http://localhost:8025
```

---

## 🔐 URLs del sistema

| URL | Descripción |
|-----|-------------|
| `http://localhost:8000/login` | Login administradores (CPEI, CAME, JDES, DEIS) |
| `http://localhost:8000/admin` | Panel EasyAdmin |
| `http://localhost:8000/enfermeria/` | Módulo Escuelas de Enfermería |
| `http://localhost:8000/enfermeria-alumno/login/{id}` | Login del alumno (id = ID de solicitud) |

---

## 🟢 Estado general

| Fase | Estado |
|------|--------|
| Fase 0 — Preparar entorno | ✅ Completada |
| Fase 1 — Crear proyecto Symfony 8 | ✅ Completada |
| Fase 2 — Migrar estructura | 🟡 En progreso |
| Fase 3 — Migrar código PHP 8 | 🟡 En progreso |
| Fase 4 — Configurar base de datos | ✅ Completada |

| Módulo | Estado |
|--------|--------|
| Seguridad / Login admin | ✅ Funcional |
| Dashboard | ✅ Funcional |
| EasyAdmin (Admin panel) | ✅ Funcional |
| Enfermería — CPEI/CAME | 🟡 Ver sección abajo |
| Enfermería — Alumno | 🟡 Ver sección abajo |
| FOFOE | ⚠️ No probado en detalle |
| Posgrado / Residencias | 🔲 Pendiente pruebas |
| CAME / Convenios | 🔲 Pendiente pruebas |

---

## 🏥 Módulo Enfermería — Estado al 19/Mayo/2026

### ✅ Funciona

- **Login CPEI/CAME/JDES/DEIS** → `/login`
- **Historial de solicitudes** → `/enfermeria/` (filtro por año/OOAD)
- **Vista de solicitud** → `/enfermeria/solicitud/{id}`
- **Login del alumno** → `/enfermeria-alumno/login/{solicitudId}`
  - Página limpia (sin foto ni menú de sesión)
  - Muestra escuela, periodo y fechas de la solicitud
  - Formulario CURP + email
- **Dashboard del alumno** → `/enfermeria-alumno/`
  - Foto de perfil + nombre y email en el menú superior
  - Links "Inicio" y "Cerrar sesión" en submenu
  - Datos en React: nombre, CURP, email, escuela, periodo, fechas, estado, monto
  - Botón "Descargar referencia de pago"
  - Botón "Cargar comprobante de pago" (según status)

### ⚠️ Pendiente — PDF de referencia bancaria

`AlumnoController::downloadReferencia()` usa `KnpSnappy\Pdf` → requiere `wkhtmltopdf`, que **no tiene binario para macOS Apple Silicon**. En Linux/producción funciona sin cambios.

**Configuración actual:**
- `config/bundles.php` → `KnpSnappyBundle` registrado
- `config/packages/knp_snappy.yaml` → lee `WKHTMLTOPDF_PATH` del entorno
- `.env.local` → `WKHTMLTOPDF_PATH="/usr/local/bin/wkhtmltopdf"` (no existe en esta Mac)

**Opciones:**

- **Linux/Docker (producción):** `apt-get install wkhtmltopdf` — funciona directamente
- **Docker en macOS:** `docker compose up -d` (el `compose.yaml` ya existe en el proyecto)
- **Reemplazar por Dompdf (nativo PHP, sin binario externo):**
  ```bash
  /opt/homebrew/opt/php@8.5/bin/php /opt/homebrew/bin/composer require dompdf/dompdf
  ```
  Actualizar `AlumnoController::downloadReferencia()` para usar Dompdf en lugar de KnpSnappy.

---

### Archivos clave del módulo Enfermería

```
src/
├── Controller/
│   ├── Enfermeria/
│   │   ├── AlumnoController.php        # Admin: listado y detalle de alumnos
│   │   ├── EnfermeriaController.php    # Historial de solicitudes (CPEI/CAME)
│   │   └── SolicitudController.php     # CRUD de solicitudes
│   └── EnfermeriaAlumno/
│       └── AlumnoController.php        # Login, dashboard, PDF, carga comprobante
├── Entity/Enfermeria/
│   ├── Alumno.php                      # Entidad alumno
│   └── Solicitud.php                   # Solicitud/periodo de inscripción
templates/
├── enfermeria/                         # Vistas CPEI/CAME/JDES
│   ├── index.html.twig                 # Historial
│   ├── create.html.twig
│   ├── show.html.twig / edit.html.twig
│   ├── alumno/                         # Gestión de alumnos desde admin
│   └── fofoe/
├── enfermeria-alumno/
│   ├── login.html.twig                 # Login del alumno (sin menú)
│   ├── index.html.twig                 # Dashboard (React)
│   ├── carga.html.twig                 # Carga comprobante de pago
│   └── sin_sesion.html.twig
layouts/
├── gov_base.html.twig                  # Base gob.mx (header + footer institucional)
├── ie.html.twig                        # Layout alumnos/escuelas
└── came.html.twig                      # Layout CPEI/CAME/JDES
assets/
├── css/enfermeria/create.scss          # Estilos base (h1/h2/h3, colores IMSS)
└── js/enfermeria-alumno/pages/index.js # Componente React del dashboard
```

### Notas del layout

`gov_base.html.twig` expone los bloques sobreescribibles:
- `menu_bar` — contenedor completo (foto + datos + delegación)
- `menu_avatar` — imagen de perfil (dentro de `menu_bar`)
- `userbar_content` — nombre/datos del usuario
- `userbar_aside` — delegación/OOAD
- `submenu` — links de navegación

`ie.html.twig` carga `enfermeria.base.css` y muestra el submenu solo si `app.user` está autenticado.

`login.html.twig` (alumno) sobreescribe `submenu` y `menu_bar` vacíos → página limpia.

`index.html.twig` (alumno) sobreescribe `submenu` y `userbar_content` con datos del alumno. La sesión del alumno es independiente de Symfony (`enfermeria_alumno_id` en sesión, no `app.user`).

---

## 🔄 Referencia de migración

### Diferencias estructurales Symfony 3.4 → 8.0

| Symfony 3.4 (origen) | Symfony 8.0 (destino) |
|----------------------|----------------------|
| `app/config/config.yml` | `config/packages/*.yaml` |
| `app/config/security.yml` | `config/packages/security.yaml` |
| `app/config/routing.yml` | `config/routes.yaml` |
| `app/config/services.yml` | `config/services.yaml` |
| `src/AppBundle/` | `src/` (sin bundle) |
| `web/` | `public/` |
| `app/console` | `bin/console` |

### Cambios de código PHP 8

**Annotations → Atributos:**
```php
// ANTES
/** @ORM\Entity(repositoryClass="AppBundle\Repository\SolicitudRepository") */
class Solicitud {}

// DESPUÉS
#[ORM\Entity(repositoryClass: SolicitudRepository::class)]
class Solicitud {}
```

**Rutas:**
```php
// ANTES
/** @Route("/fofoe/solicitud", name="fofoe_solicitud") */
public function solicitudAction() {}

// DESPUÉS
#[Route('/fofoe/solicitud', name: 'fofoe_solicitud')]
public function solicitud(): Response {}
```

**Inyección de dependencias:**
```php
// ANTES
public function __construct(EntityManager $em, SolicitudManager $sm) {}

// DESPUÉS (constructor promotion PHP 8)
public function __construct(
    private readonly EntityManagerInterface $em,
    private readonly SolicitudManager $sm,
) {}
```

**SwiftMailer → Symfony Mailer:**
```php
// DESPUÉS
$email = (new Email())
    ->from('from@example.com')
    ->to('to@example.com')
    ->subject('Asunto')
    ->html($body);
$this->mailer->send($email);
```

### Módulos por migrar (prioridad de negocio)

| Prioridad | Módulo | Controladores origen |
|-----------|--------|----------------------|
| 1 | FOFOE | `Controller/Fofoe/` |
| 2 | Posgrado / Residencias | `Controller/Posgrado/` |
| 3 | Enfermería | `Controller/Enfermeria/` |
| 4 | Convenios / CAME | `Controller/Came/` |
| 5 | EduPer | `Controller/EduPer/` |
| 6 | Admin / IE | `Controller/Admin/`, `Controller/IE/` |

---

## 📚 Referencias

- [Upgrade Symfony](https://symfony.com/doc/current/setup/upgrade_major.html)
- [Rector — migración automática](https://github.com/rectorphp/rector)
- [EasyAdmin 4](https://symfony.com/bundles/EasyAdminBundle/current/index.html)
- [Doctrine Migrations v3](https://www.doctrine-project.org/projects/doctrine-migrations/en/3.0/index.html)
- [PhpSpreadsheet](https://phpspreadsheet.readthedocs.io/)
- [Symfony Mailer](https://symfony.com/doc/current/mailer.html)
- [wkhtmltopdf downloads](https://wkhtmltopdf.org/downloads.html)
