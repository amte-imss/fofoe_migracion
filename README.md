[![Build](https://img.shields.io/badge/build-TODO-lightgrey)](#)
[![Tests](https://img.shields.io/badge/tests-TODO-lightgrey)](#)
[![PHP](https://img.shields.io/badge/PHP-%3E%3D8.4-777bb4)](#)
[![License](https://img.shields.io/badge/license-TODO-lightgrey)](#)

## Inicio rápido (5 minutos)

```bash
# 1) Clonar e ingresar al proyecto
git clone <URL_DEL_REPOSITORIO>
cd fofoe_migracion

# 2) Configurar entorno local
cp .env .env.local

# 3) Instalar dependencias
composer install
npm install

# 4) Levantar base de datos (Docker)
docker compose up -d database

# 5) Ejecutar migraciones y compilar assets
php bin/console doctrine:migrations:migrate -n
npm run dev

# 6) Iniciar servidor de desarrollo
symfony server:start -d
# alternativa sin Symfony CLI:
php -S localhost:8000 -t public
```

Abrir `http://localhost:8000`.

## Título del proyecto

**FOFOE Migración - Sistema de Educación en Salud IMSS**

Aplicación web institucional para gestionar procesos académicos y administrativos (enfermería, convenios, posgrado y módulos relacionados). El repositorio concentra la migración tecnológica hacia Symfony moderno, con foco en estabilidad funcional, eliminación de deprecaciones y estandarización del flujo de desarrollo.

## Tabla de contenido

- [Inicio rápido (5 minutos)](#inicio-rápido-5-minutos)
- [Título del proyecto](#título-del-proyecto)
- [Tabla de contenido](#tabla-de-contenido)
- [Descripción funcional](#descripción-funcional)
- [Arquitectura y stack](#arquitectura-y-stack)
- [Requisitos previos](#requisitos-previos)
- [Instalación y configuración local](#instalación-y-configuración-local)
- [Ejecución del proyecto](#ejecución-del-proyecto)
- [Pruebas y calidad de código](#pruebas-y-calidad-de-código)
- [Estructura de carpetas](#estructura-de-carpetas)
- [Base de datos y migraciones](#base-de-datos-y-migraciones)
- [Convenciones de desarrollo](#convenciones-de-desarrollo)
- [Despliegue](#despliegue)
- [Troubleshooting](#troubleshooting)
- [FAQ](#faq)
- [Roadmap y estado del proyecto](#roadmap-y-estado-del-proyecto)
- [Contribución](#contribución)
- [Licencia](#licencia)
- [Contacto y ownership](#contacto-y-ownership)
- [Verificación post-instalación](#verificación-post-instalación)

## Descripción funcional

Este sistema resuelve la operación académica y administrativa de procesos de educación en salud del IMSS, centralizando flujos que antes estaban dispersos o con acoplamiento a versiones legacy.

Usuarios principales:
- Personal administrativo (CPEI/CAME/JDES/DEIS).
- Operadores de módulos académicos.
- Alumnos/aspirantes de enfermería (portal de alumno).
- Perfil técnico de soporte y desarrollo.

Módulos clave:
- **Enfermería**: solicitudes, seguimiento, carga de comprobantes.
- **Enfermería Alumno**: login dedicado y dashboard.
- **IE/CAME**: convenios, campos clínicos y operación asociada.
- **Posgrado/FOFOE**: procesos académicos especializados.
- **Admin**: panel de administración con EasyAdmin.

## Arquitectura y stack

- **Backend**: PHP 8.4+ con Symfony 8, Doctrine ORM, componentes de seguridad y formularios.
- **Frontend**: Twig + JavaScript (Webpack Encore, Babel, SASS, componentes React en módulos puntuales).
- **Base de datos**: PostgreSQL 16.
- **Colas**: TODO: confirmar uso de Messenger/cola en producción.
- **Almacenamiento**: sistema de archivos local (`public/uploads`, `var/`), carga de archivos con VichUploader.
- **Servicios adicionales**: SMTP local recomendado (Mailpit), generación de PDF con wkhtmltopdf (KnpSnappy).

Diagrama textual (ASCII):

```text
[ Navegador ]
     |
     v
[ Symfony 8 (Controllers + Services + Security) ]
     |                 |                    |
     |                 |                    +--> [ SMTP / Mailpit ]
     |                 +--> [ Twig + Webpack Encore + React ]
     |
     +--> [ Doctrine ORM ] --> [ PostgreSQL 16 ]
     |
     +--> [ VichUploader ] --> [ public/uploads , var/ ]
     |
     +--> [ KnpSnappy ] --> [ wkhtmltopdf ]
```

## Requisitos previos

- PHP `>=8.4` (requerido por `composer.json`).
- Extensiones PHP: `ctype`, `iconv`, `zip`.
- Composer `2.x`.
- Node.js `TODO: definir versión mínima oficial` (recomendado LTS actual).
- npm `TODO: definir versión mínima oficial` (o Yarn equivalente).
- Docker Engine + Docker Compose (opcional, recomendado para DB y compatibilidad).
- PostgreSQL 16 (si se trabaja sin Docker).

Variables de entorno mínimas (ejemplo en `.env.local`):

```bash
APP_ENV=dev
APP_DEBUG=1
APP_SECRET=TODO_CAMBIAR
DATABASE_URL="postgresql://fofoe_user:fofoe_pass@127.0.0.1:5432/fofoe_dev?serverVersion=16&charset=utf8"
MAILER_DSN="smtp://localhost:1025"
WKHTMLTOPDF_PATH="/usr/local/bin/wkhtmltopdf"
# TODO: agregar variables institucionales faltantes (ej. MAIL_FOFOE, sied_url)
```

## Instalación y configuración local

```bash
# 1) Clonar
git clone <URL_DEL_REPOSITORIO>
cd fofoe_migracion

# 2) Configurar entorno
cp .env .env.local
# editar .env.local con credenciales locales

# 3) Instalar dependencias backend/frontend
composer install
npm install

# 4) Levantar infraestructura con Docker (recomendado)
docker compose up -d

# 5) Preparar base de datos
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate -n

# 6) Cargar datos iniciales (opcional según entorno)
php bin/console doctrine:fixtures:load -n
# alternativa si el equipo usa Alice:
php bin/console hautelook:fixtures:load -n

# 7) Compilar assets
npm run dev
# para desarrollo continuo
npm run watch
```

Alternativa sin Docker:

```bash
# Requiere PostgreSQL 16 local y credenciales configuradas en .env.local
composer install
npm install
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate -n
npm run dev
php -S localhost:8000 -t public
```

## Ejecución del proyecto

Comandos típicos para desarrollo:

```bash
# backend
symfony server:start
# o
php -S localhost:8000 -t public

# frontend (en otra terminal)
npm run watch

# utilidades
php bin/console cache:clear
php bin/console debug:router
```

URL local esperada:
- `http://localhost:8000`
- Login: `http://localhost:8000/login`
- Admin (EasyAdmin): `http://localhost:8000/admin`

Usuario de prueba:
- `TODO: definir usuario/rol de pruebas para onboarding`.

## Pruebas y calidad de código

Comandos por propósito:

```bash
# Pruebas PHP (unitarias/integración)
./bin/phpunit

# Migración/refactor automático
vendor/bin/rector process src

# Verificar estado de Doctrine
php bin/console doctrine:schema:validate

# Calidad frontend (si se incorpora)
# TODO: agregar comando de lint JS/CSS (ESLint/Stylelint/Prettier)
```

Notas:
- `phpunit.dist.xml` está en modo estricto (`failOnDeprecation`, `failOnNotice`, `failOnWarning`).
- Si fallan pruebas por deprecaciones, ejecutar Rector y corregir manualmente los casos restantes.

## Estructura de carpetas

```text
src/           Lógica de negocio (controllers, services, entities, security, forms)
config/        Configuración Symfony, servicios, rutas, paquetes
templates/     Vistas Twig por módulo y layouts compartidos
assets/        JS/SASS/imágenes y entradas de Webpack Encore
migrations/    Migraciones Doctrine versionadas
tests/         Pruebas automatizadas (PHPUnit)
public/        Document root, assets compilados y archivos públicos
var/           Caché, logs y temporales
```

## Base de datos y migraciones

Flujo recomendado:

```bash
# Crear una nueva migración
php bin/console doctrine:migrations:diff

# Ejecutar migraciones pendientes
php bin/console doctrine:migrations:migrate -n

# Ver estado
php bin/console doctrine:migrations:status

# Revertir una versión específica (ejemplo)
php bin/console doctrine:migrations:execute --down "DoctrineMigrations\\VersionYYYYMMDDHHMMSS"
```

Buenas prácticas:
- Generar migraciones pequeñas y atómicas.
- Revisar SQL generado antes de aplicar en QA/PROD.
- Evitar cambios destructivos en una sola entrega (preferir estrategia en dos pasos).
- Respaldar la base antes de migraciones críticas.
- Versionar también fixtures cuando afecten flujos de negocio.

## Convenciones de desarrollo

- **Naming**:
  - Clases PHP en `PascalCase`.
  - Métodos/propiedades en `camelCase`.
  - Archivos Twig descriptivos por módulo.
- **Commits (Conventional Commits)**:
  - `feat: agrega validación de solicitud de enfermería`
  - `fix: corrige generación de referencia bancaria`
  - `chore: actualiza dependencias symfony`
- **Ramas**:
  - `main`/`master`: estable.
  - `develop` o rama de integración: `TODO: confirmar estrategia oficial`.
  - Feature branches: `feature/<tema>`; hotfix: `hotfix/<tema>`.
- **PR checklist**:
  - Migraciones incluidas y probadas.
  - Tests en verde (`./bin/phpunit`).
  - Assets compilados sin errores.
  - Sin credenciales/secretos en código.
  - Evidencia de pruebas funcionales en módulos impactados.

## Despliegue

Flujo recomendado por ambiente:

- **dev**: integración diaria, fixtures permitidas, depuración habilitada.
- **qa**: datos controlados, validación funcional/regresión, revisión de migraciones.
- **prod**: despliegue controlado, `APP_ENV=prod`, caché caliente y rollback definido.

Pipeline sugerido (alto nivel):

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php bin/console doctrine:migrations:migrate -n
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

Seguridad y variables sensibles:
- Nunca commitear secretos en `.env`/`.env.local`.
- Gestionar secretos por vault o variables de entorno del servidor.
- Rotar credenciales de DB/SMTP periódicamente.
- Restringir permisos sobre `var/` y `public/uploads/`.
- Revisar la dependencia de `wkhtmltopdf` por su superficie operativa.

## Troubleshooting

1. **Error de conexión a PostgreSQL (`Connection refused`)**  
   Causa: DB no levantada o `DATABASE_URL` incorrecta.  
   Solución: `docker compose up -d database` y validar host/puerto/credenciales.

2. **`Class ... not found` tras actualizar código**  
   Causa: autoload desactualizado o namespace legacy.  
   Solución: `composer dump-autoload` y revisar migración `AppBundle\` -> `App\`.

3. **Deprecaciones bloquean PHPUnit**  
   Causa: configuración estricta de `phpunit.dist.xml`.  
   Solución: corregir deprecaciones y ejecutar `vendor/bin/rector process src`.

4. **`wkhtmltopdf not found` al generar PDFs**  
   Causa: binario no instalado o ruta inválida.  
   Solución: instalar binario compatible y ajustar `WKHTMLTOPDF_PATH`.

5. **Assets no se reflejan en UI**  
   Causa: build frontend no ejecutado o caché del navegador.  
   Solución: `npm run dev`/`npm run watch`, limpiar `public/build/` si aplica.

6. **`Table does not exist` al navegar**  
   Causa: migraciones pendientes.  
   Solución: `php bin/console doctrine:migrations:migrate -n`.

7. **Correos no salen en local**  
   Causa: `MAILER_DSN` apunta a SMTP inexistente.  
   Solución: iniciar Mailpit y revisar `http://localhost:8025`.

8. **Error de permisos en `var/` o `public/uploads/`**  
   Causa: permisos de filesystem insuficientes.  
   Solución: ajustar permisos del usuario de ejecución del servidor PHP.

9. **`YEAR()` o funciones SQL no portables en Doctrine**  
   Causa: DQL no compatible con funciones SQL legacy.  
   Solución: reemplazar por rangos de fechas o consultas nativas controladas.

10. **Sesión de alumno no reconocida como `app.user`**  
    Causa: flujo de autenticación de alumno independiente del login principal.  
    Solución: validar guard/session del módulo `enfermeria-alumno`.

## FAQ

1. **¿El proyecto ya está 100% migrado a Symfony 8?**  
   No completamente; hay avances significativos y pendientes puntuales por módulo.

2. **¿Puedo correr el sistema sin Docker?**  
   Sí, si tienes PHP/Node/PostgreSQL compatibles y `.env.local` correcto.

3. **¿Qué comando debo correr después de un `git pull`?**  
   `composer install`, `npm install`, `php bin/console doctrine:migrations:migrate -n`, `npm run dev`.

4. **¿Dónde están los layouts principales?**  
   En `templates/base.html.twig`, `templates/base_system.html.twig` y `templates/layouts/`.

5. **¿Cómo genero una migración nueva?**  
   `php bin/console doctrine:migrations:diff` y luego `...:migrate`.

6. **¿Qué hago si falla una ruta después de refactor?**  
   Ejecuta `php bin/console debug:router` y revisa atributos `#[Route]` en controladores.

7. **¿Cómo valido cambios deprecados por migración?**  
   Ejecuta PHPUnit y Rector; usa como referencia `REPORTE_DEPRECADOS.md`.

8. **¿Dónde reporto un bloqueo funcional?**  
   En el canal definido por el equipo de ownership (ver sección final).

## Roadmap y estado del proyecto

Estado general (según `ROADMAP_MIGRACION.md` y `REPORTE_DEPRECADOS.md`):

- Migración tecnológica en curso de Symfony legacy a Symfony 8.
- Rector configurado para modernización incremental (PHP + Symfony + Doctrine).
- Conversión de anotaciones a atributos en progreso.
- Sustitución de APIs deprecadas (ej. `getDoctrine()`) parcialmente completada.
- Módulo Enfermería con avance funcional alto; pendientes en otros módulos.
- Bloqueo técnico relevante: generación PDF con `wkhtmltopdf` en entornos no compatibles.
- Requiere reforzar cobertura de pruebas automáticas por módulo.
- Se recomienda cerrar deprecaciones críticas antes de endurecer pipeline de release.

Acciones prioritarias sugeridas:

```bash
vendor/bin/rector process src
./bin/phpunit
php bin/console doctrine:migrations:migrate -n
npm run build
```

## Contribución

Proceso recomendado:

1. Crear issue con contexto, impacto y propuesta.
2. Crear rama feature/hotfix desde rama base del equipo.
3. Implementar cambios con tests y migraciones cuando aplique.
4. Abrir PR con evidencia (capturas/logs/comandos ejecutados).
5. Atender revisión técnica y funcional antes de merge.

Reporte de bugs:
- Incluir pasos de reproducción.
- Comportamiento esperado vs actual.
- Logs relevantes (`var/log/`) y versión/ambiente.

## Licencia

`TODO: definir licencia oficial del repositorio`.

## Contacto y ownership

- Equipo responsable: `TODO: definir nombre de equipo/área`.
- Canal de soporte técnico: `TODO: definir canal (correo, Slack, Teams o mesa de ayuda)`.
- Escalamiento funcional: `TODO: definir responsables por módulo`.

## Verificación post-instalación

Checklist final:

- [ ] `composer install` ejecuta sin errores.
- [ ] `npm install` y `npm run dev` completan correctamente.
- [ ] `docker compose up -d` levanta servicios requeridos.
- [ ] `php bin/console doctrine:migrations:migrate -n` termina sin fallas.
- [ ] `http://localhost:8000` responde con la app.
- [ ] Login principal y rutas base (`/login`, `/admin`) operan.
- [ ] `./bin/phpunit` finaliza en verde (o con incidencias documentadas).
- [ ] Generación de assets y carga estática funcionan en UI.
- [ ] Logs en `var/log/` sin errores críticos al iniciar.
- [ ] TODO de variables sensibles resuelto para tu ambiente local.
