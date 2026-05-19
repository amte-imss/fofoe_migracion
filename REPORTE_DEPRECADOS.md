# Reporte de Funciones Deprecadas — Migración Symfony 3 → Symfony 8

**Proyecto original:** `sf-boilerplate-imms` (Symfony 3.x / PHP 7.x)
**Proyecto migrado:** `fofoe_migracion` (Symfony 8.x / PHP 8.5)
**Base de datos:** PostgreSQL 16 — `fofoe_dev`
**Fecha de reporte:** 5 de mayo de 2026

---

## 1. Rutas — Anotaciones docblock → Atributos PHP 8

**Paquete eliminado:** `sensio/framework-extra-bundle`, `doctrine/annotations`

| | Original | Migrado |
|---|---|---|
| Sintaxis | `/** @Route(...) */` en docblock | `#[Route(...)]` nativo PHP 8 |
| Clase | `@Route("/prefijo")` en clase | `#[Route('/prefijo')]` en clase |
| Método | `@Route("/{id}", methods={"GET"})` | `#[Route('/{id}', methods: ['GET'])]` |

```php
// ❌ ANTES
/**
 * @Route("/enfermeria/solicitud")
 */
class SolicitudController {
    /**
     * @Route("/{id}", methods={"GET"}, name="enfermeria.solicitud.show")
     */
    public function show() {}
}

// ✅ DESPUÉS
#[Route('/enfermeria')]
class EnfermeriaController {
    #[Route('/solicitud/{id}', name: 'enfermeria.solicitud.show', methods: ['GET'])]
    public function show() {}
}
```

**Archivos afectados:**
- `src/Controller/Enfermeria/EnfermeriaController.php`
- `src/Controller/Enfermeria/AlumnoController.php`
- `src/Controller/DashboardController.php`
- `src/Controller/SecurityController.php`

---

## 2. `getDoctrine()` → Inyección de `EntityManagerInterface`

**Deprecado en:** Symfony 4.2 — **Eliminado en:** Symfony 6.0
**Ocurrencias en original:** 20 usos en controladores Enfermería

```php
// ❌ ANTES
$this->getDoctrine()->getRepository(Solicitud::class)->find($id);
$this->getDoctrine()->getManager()->persist($data);
$this->getDoctrine()->getManager()->flush();
$repo = $this->getDoctrine()->getRepository(Alumno::class);
```

```php
// ✅ DESPUÉS — inyección en constructor o método
use Doctrine\ORM\EntityManagerInterface;

public function edit(
    Solicitud $solicitud,
    Request $request,
    EntityManagerInterface $em
): Response {
    $repo = $em->getRepository(Alumno::class);
    $em->persist($data);
    $em->flush();
}
```

**Archivos afectados:**
- `src/Controller/Enfermeria/EnfermeriaController.php`
- `src/Controller/Enfermeria/AlumnoController.php`

---

## 3. Anotaciones ORM docblock → Atributos PHP 8

**Deprecado en:** Doctrine ORM 2.10 — **Eliminado en:** Doctrine ORM 3.x

```php
// ❌ ANTES (doctrine/annotations requerido)
/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Enfermeria\AlumnoRepository")
 * @ORM\Table(name="lote_alumno_escuela_enf")
 */
class Alumno {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $nombre;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Enfermeria\Solicitud")
     * @ORM\JoinColumn(name="lote_id", referencedColumnName="id")
     */
    private $solicitud;
}
```

```php
// ✅ DESPUÉS (PHP 8 nativo, sin paquetes extra)
#[ORM\Entity(repositoryClass: \App\Repository\Enfermeria\AlumnoRepository::class)]
#[ORM\Table(name: 'lote_alumno_escuela_enf')]
class Alumno {
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer', nullable: false)]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private $nombre;

    #[ORM\ManyToOne(targetEntity: Solicitud::class)]
    #[ORM\JoinColumn(name: 'lote_id', referencedColumnName: 'id')]
    private $solicitud;
}
```

**Archivos afectados:** Todas las entidades en `src/Entity/` (66 archivos)

---

## 4. Namespace `AppBundle\` → `App\`

**Razón:** Eliminación de la arquitectura de bundles en Symfony 4+

```php
// ❌ ANTES
use AppBundle\Entity\Enfermeria\Alumno;
use AppBundle\Repository\Enfermeria\AlumnoRepository;
repositoryClass="AppBundle\Repository\Enfermeria\AlumnoRepository"
$alumno = new \AppBundle\Entity\Enfermeria\Alumno();
```

```php
// ✅ DESPUÉS
use App\Entity\Enfermeria\Alumno;
use App\Repository\Enfermeria\AlumnoRepository;
repositoryClass: \App\Repository\Enfermeria\AlumnoRepository::class
$alumno = new Alumno();
```

**Afecta:** Todos los controladores, entidades y repositorios migrados.

---

## 5. `getUsername()` → `getUserIdentifier()`

**Deprecado en:** Symfony 5.3 — **Eliminado en:** Symfony 6.0
**Ocurrencias en original:** 6 usos

```php
// ❌ ANTES
$user->getUsername()
$this->getUser()->getUsername()
```

```php
// ✅ DESPUÉS
$user->getUserIdentifier()
$this->getUser()->getUserIdentifier()
```

```twig
{# ❌ ANTES #}
{{ app.user.username }}

{# ✅ DESPUÉS #}
{{ app.user.userIdentifier }}
```

**Archivos afectados:**
- `templates/layouts/app.html.twig`
- `src/Controller/SecurityController.php`

---

## 6. `YEAR()` en DQL → Rango de fechas

**Razón:** `YEAR()` no es una función DQL estándar de Doctrine; es SQL puro no portable.

```php
// ❌ ANTES — lanzaba error en Doctrine
$qb->andWhere('YEAR(a.fechaInicio) = :year')
   ->setParameter('year', $year);
```

```php
// ✅ DESPUÉS — compatible con cualquier motor de BD
$qb->andWhere('a.fechaInicio >= :yearStart AND a.fechaInicio < :yearEnd')
   ->setParameter('yearStart', new \DateTime("$year-01-01"))
   ->setParameter('yearEnd',   new \DateTime(($year + 1) . "-01-01"));
```

**Archivos afectados:**
- `src/Repository/Enfermeria/AlumnoRepository.php` — método `findByFilters()`

---

## 7. `repositoryClass` como string → `::class`

**Razón:** String literal no verificado en compilación; `::class` es seguro y refactorizable.

```php
// ❌ ANTES — error silencioso si hay typo
repositoryClass="AppBundle\Repository\Enfermeria\AlumnoRepository"
```

```php
// ✅ DESPUÉS — verificado en tiempo de compilación
repositoryClass: \App\Repository\Enfermeria\AlumnoRepository::class
```

**Archivos afectados:** Todas las entidades con repositorio personalizado.

---

## 8. Twig `|max` / `|min` en escalares → Ternarios

**Razón:** Twig no tiene filtros `|max(n)` ni `|min(n)` para valores escalares (solo la función `max()` para arrays).

```twig
{# ❌ ANTES — lanza error en Twig 3 #}
{% set pStart = (page - 2)|max(1) %}
{% set pEnd   = (page + 2)|min(totalPages) %}
```

```twig
{# ✅ DESPUÉS #}
{% set pStart = page - 2 > 1          ? page - 2    : 1 %}
{% set pEnd   = page + 2 < totalPages ? page + 2    : totalPages %}
```

**Archivos afectados:**
- `templates/enfermeria/alumno/index.html.twig` — paginación

---

## 9. Clase `repositoryClass` inexistente → Creación del Repository

**Razón:** La entidad `Unidad` referenciaba una clase de repositorio que no existía en el proyecto original migrado, causando error al intentar usar `$em->getRepository(Unidad::class)`.

```php
// ❌ ANTES — clase no existía en el nuevo namespace
#[ORM\Entity(repositoryClass: \App\Repository\UnidadRepository::class)]
// → Symfony lanzaba: "Class App\Repository\UnidadRepository not found"
```

```php
// ✅ DESPUÉS — creado src/Repository/UnidadRepository.php
class UnidadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Unidad::class);
    }
}
```

**Archivos creados:**
- `src/Repository/UnidadRepository.php`

---

## Resumen de impacto

| # | Cambio | Severidad | Archivos afectados |
|---|---|---|---|
| 1 | `@Route` → `#[Route]` | 🔴 Crítico | 4 controladores |
| 2 | `getDoctrine()` → `EntityManagerInterface` | 🔴 Crítico | 2 controladores (20 ocurrencias) |
| 3 | Anotaciones ORM → Atributos PHP 8 | 🔴 Crítico | 66 entidades |
| 4 | Namespace `AppBundle\` → `App\` | 🔴 Crítico | Todos los archivos migrados |
| 5 | `getUsername()` → `getUserIdentifier()` | 🟡 Medio | 6 ocurrencias |
| 6 | `YEAR()` DQL → rango de fechas | 🟡 Medio | 1 repositorio |
| 7 | `repositoryClass` string → `::class` | 🟡 Medio | 66 entidades |
| 8 | Twig `\|max`/`\|min` → ternarios | 🟢 Bajo | 1 template |
| 9 | Repository inexistente → creación | 🟡 Medio | 1 archivo nuevo |
