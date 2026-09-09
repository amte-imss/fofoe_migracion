# AGENTS.md

## Contexto rapido
- Stack principal: Symfony 8 (`src/Kernel.php`), PHP `>=8.5` (`composer.json`), Doctrine ORM, Twig y Webpack Encore.
- Proyecto en migracion desde Symfony 3.4; usa Rector para cambios incrementales (`rector.php`, `ROADMAP_MIGRACION.md`).
- Dominios funcionales grandes: `enfermeria`, `fofoe`, `ie`, `came`, `campo_clinico`, `agreement`, `admin` (ver `src/Controller/*` y `templates/*`).

## Arquitectura y limites
- HTTP entra por controladores con atributos de ruta y seguridad por rol; ejemplo: `src/Controller/Enfermeria/EnfermeriaController.php`.
- Logica de negocio se concentra en servicios con interfaz + implementacion (`config/services.yaml`, `src/Service/*`, `src/Service/*Interface.php`).
- Persistencia via Doctrine (entidades en `src/Entity`, repositorios en `src/Repository`); para consultas complejas existen repositorios `*CustomSQL.php`.
- Vistas server-rendered con Twig (`templates/base.html.twig`, `templates/base_system.html.twig`) y, en flujos complejos, frontend JS/React por modulo.
- Frontend dividido por entradas de Encore (`webpack.config.js`), con JS por dominio en `assets/js/*` y estilos SCSS en `assets/css/*`.

## Patrones especificos del proyecto
- Convencion fuerte de nombres por modulo: controladores/rutas/templates/JS comparten prefijos de dominio (`enfermeria`, `ie`, etc.).
- Se usa mezcla de paginas Twig + componentes React; no asumir SPA global (`webpack.config.js`, `assets/app.js`).
- Seguridad basada en roles de negocio (ej. `ROLE_ADM_FOFOE`, `ROLE_IE`, `ROLE_CAME`) en atributos `IsGranted`/config de seguridad.
- Parametros de infraestructura en contenedor para rutas de archivos y herramientas (`config/services.yaml`: `app.path.*`, `app.wkhtmltopdf`).
- Integraciones de documentos: PDF con KnpSnappy y Excel con PhpSpreadsheet (`composer.json`, servicios en `src/Service`).

## Workflows utiles (repo root)
- Dependencias PHP: `composer install`
- Dependencias frontend: `npm install`
- Build frontend dev/prod: `npm run dev` / `npm run build`
- Watch frontend: `npm run watch`
- Consola Symfony: `php bin/console`
- Pruebas: `./bin/phpunit` (config estricta en `phpunit.dist.xml`, falla por deprecations/warnings)
- Migraciones DB: `php bin/console doctrine:migrations:migrate`
- Calidad/migracion: `vendor/bin/rector process src`

## Integraciones y puntos de cuidado
- DB esperada: PostgreSQL (compose en `compose.yaml`/`compose.override.yaml`; ver variables de entorno antes de migrar).
- Admin panel via EasyAdmin (`config/routes/easyadmin.yaml` y controladores en `src/Controller/Admin`).
- Mailer Symfony configurado por DSN/parametros (`composer.json`, `config/packages/*`).
- Subida de archivos con VichUploader (mapeos/paths desde config y parametros).

## Guia para agentes al editar
- Antes de tocar codigo, ubica el modulo y replica su patron existente (controller -> service interface -> repository -> twig/js).
- Si cambias consultas, revisa primero si ese modulo usa `Repository` Doctrine estandar o `CustomSQL`.
- Si agregas UI, declara/ajusta entrypoints de Encore y assets del modulo correspondiente, no en un bundle global unico.
- Mantener cambios compatibles con el estado de migracion (evitar reintroducir APIs legacy de Symfony 3/annotations donde ya hay atributos).

