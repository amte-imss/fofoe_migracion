<?php

namespace App\Controller\Admin;

use App\Entity\Delegacion;
use App\Entity\Institucion;
use App\Entity\Permiso;
use App\Entity\Rol;
use App\Entity\Unidad;
use App\Entity\Usuario;
use App\Repository\PermissionRepository;
use App\Repository\RoleRepository;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/usuarios')]
class UserCrudController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface      $em,
        private UserPasswordHasherInterface $hasher,
        private UsuarioRepository           $usuarioRepo,
        private RoleRepository              $roleRepo,
        private PermissionRepository        $permissionRepo
    ) {}

    // ─── Listado ───────────────────────────────────────────────────────────
    #[Route('', name: 'admin.usuarios.index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADM_FOFOE');

        $page    = max(1, (int) $request->query->get('page', 1));
        $perPage = 20;
        $q       = $request->query->get('q', '');

        $isSuperAdmin = $this->isGranted('ROLE_SUPER');
        $excludeSuper = !$isSuperAdmin;

        if ($q) {
            $qb = $this->usuarioRepo->createQueryBuilder('u')
                ->where('u.nombre LIKE :q OR u.correo LIKE :q OR u.matricula LIKE :q')
                ->setParameter('q', "%{$q}%")
                ->orderBy('u.id', 'DESC');

            if ($excludeSuper) {
                $subQb = $this->usuarioRepo->createQueryBuilder('u2')
                    ->select('u2.id')
                    ->innerJoin('u2.permisos', 'p2')
                    ->where('p2.clave = :super')
                    ->getDQL();
                $qb->andWhere($qb->expr()->notIn('u.id', $subQb))
                   ->setParameter('super', 'SUPER');
            }

            $usuarios = $qb->getQuery()->getResult();
            $total    = count($usuarios);
        } else {
            $usuarios = $this->usuarioRepo->findPaginatedExcludingSuper($page, $perPage, $excludeSuper);
            $total    = (int) $this->usuarioRepo->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->getQuery()
                ->getSingleScalarResult();
        }

        return $this->render('admin/usuarios/index.html.twig', [
            'usuarios'   => $usuarios,
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => (int) ceil($total / $perPage),
            'q'          => $q,
            'roles'      => $this->roleRepo->findAllOrdered(),
        ]);
    }

    // ─── Crear ─────────────────────────────────────────────────────────────
    #[Route('/nuevo', name: 'admin.usuarios.new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADM_FOFOE');

        if ($request->isMethod('POST')) {
            return $this->save(new Usuario(), $request, true);
        }

        return $this->render('admin/usuarios/form.html.twig', [
            'usuario'      => new Usuario(),
            'roles'        => $this->roleRepo->findAllOrdered(),
            'delegaciones' => $this->em->getRepository(Delegacion::class)->findBy([], ['nombre' => 'ASC']),
            'instituciones' => $this->em->getRepository(Institucion::class)->findBy([], ['nombre' => 'ASC']),
            'unidades'     => $this->em->getRepository(Unidad::class)->createQueryBuilder('u')
                ->where('u.esUmae = true OR u.esEnfermeria = true')
                ->orderBy('u.nombre', 'ASC')
                ->getQuery()->getResult(),
            'accion'       => 'Nuevo usuario',
        ]);
    }

    // ─── Editar ────────────────────────────────────────────────────────────
    #[Route('/{id}/editar', name: 'admin.usuarios.edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADM_FOFOE');

        $usuario = $this->usuarioRepo->find($id);
        if (!$usuario) {
            throw $this->createNotFoundException('Usuario no encontrado.');
        }

        // Bloquear edición de SUPER a no-SUPER
        if (!$this->isGranted('ROLE_SUPER') && in_array('ROLE_SUPER', $usuario->getRoles(), true)) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            return $this->save($usuario, $request, false);
        }

        // Detectar rol actual del usuario (primer permiso que pertenezca a un rol)
        $rolActual = null;
        foreach ($usuario->getPermisos() as $permiso) {
            if ($permiso->getRol()) {
                $rolActual = $permiso->getRol()->getId();
                break;
            }
        }

        return $this->render('admin/usuarios/form.html.twig', [
            'usuario'      => $usuario,
            'roles'        => $this->roleRepo->findAllOrdered(),
            'rolActual'    => $rolActual,
            'delegaciones' => $this->em->getRepository(Delegacion::class)->findBy([], ['nombre' => 'ASC']),
            'instituciones' => $this->em->getRepository(Institucion::class)->findBy([], ['nombre' => 'ASC']),
            'unidades'     => $this->em->getRepository(Unidad::class)->createQueryBuilder('u')
                ->where('u.esUmae = true OR u.esEnfermeria = true')
                ->orderBy('u.nombre', 'ASC')
                ->getQuery()->getResult(),
            'accion'       => 'Editar usuario',
        ]);
    }

    // ─── Activar/Desactivar ────────────────────────────────────────────────
    #[Route('/{id}/toggle', name: 'admin.usuarios.toggle', methods: ['POST'])]
    public function toggle(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADM_FOFOE');

        $usuario = $this->usuarioRepo->find($id);
        if (!$usuario) {
            return new JsonResponse(['ok' => false, 'message' => 'No encontrado.'], 404);
        }

        $usuario->setActivo(!$usuario->getActivo());
        $this->em->flush();

        return new JsonResponse(['ok' => true, 'activo' => $usuario->getActivo()]);
    }

    // ─── Eliminar ──────────────────────────────────────────────────────────
    #[Route('/{id}/eliminar', name: 'admin.usuarios.delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER');

        $usuario = $this->usuarioRepo->find($id);
        if ($usuario) {
            $this->em->remove($usuario);
            $this->em->flush();
            $this->addFlash('success', 'Usuario eliminado.');
        }

        return $this->redirectToRoute('admin.usuarios.index');
    }

    // ─── Lógica de guardado compartida ────────────────────────────────────
    private function save(Usuario $usuario, Request $request, bool $esNuevo): Response
    {
        $data = $request->request;

        $usuario->setNombre($data->get('nombre', ''));
        $usuario->setApellidoPaterno($data->get('apellidoPaterno', ''));
        $usuario->setApellidoMaterno($data->get('apellidoMaterno', ''));
        $usuario->setCorreo($data->get('correo', ''));
        $usuario->setMatricula($data->get('matricula', ''));
        $usuario->setTelefono($data->get('telefono', ''));
        $usuario->setCurp($data->get('curp', ''));
        $usuario->setRfc($data->get('rfc', ''));
        $usuario->setSexo($data->get('sexo', ''));
        $regimsRaw = $data->get('regims', '');
        $usuario->setRegims($regimsRaw !== '' ? (int) $regimsRaw : null);
        $usuario->setActivo((bool) $data->get('activo', true));

        $fechaIngresoRaw = $data->get('fechaIngreso', '');
        if ($fechaIngresoRaw) {
            $usuario->setFechaIngreso(new \DateTime($fechaIngresoRaw));
        }

        // Delegaciones OOAD — múltiples
        $delegacionIds = $data->all('delegacionIds') ?? [];
        $usuario->getDelegaciones()->clear();
        foreach ($delegacionIds as $did) {
            $delegacion = $this->em->getRepository(Delegacion::class)->find((int) $did);
            if ($delegacion) {
                $usuario->getDelegaciones()->add($delegacion);
            }
        }

        // Delegación institución (OOAD institución)
        $delegacionInstId = (int) $data->get('delegacionInstitucionId', 0);
        if ($delegacionInstId) {
            $delegacionInst = $this->em->getRepository(Delegacion::class)->find($delegacionInstId);
            $usuario->setDelegacionInstitucion($delegacionInst ?: null);
        } else {
            $usuario->setDelegacionInstitucion(null);
        }

        // Institución
        $institucionId = (int) $data->get('institucionId', 0);
        if ($institucionId) {
            $institucion = $this->em->getRepository(Institucion::class)->find($institucionId);
            $usuario->setInstitucion($institucion ?: null);
        } else {
            $usuario->setInstitucion(null);
        }

        // Unidades (UMAE + Enfermería) — multivalor
        $unidadIds = $data->all('unidadIds') ?? [];
        $usuario->getUnidades()->clear();
        foreach ($unidadIds as $uid) {
            $unidad = $this->em->getRepository(Unidad::class)->find((int) $uid);
            if ($unidad) {
                $usuario->getUnidades()->add($unidad);
            }
        }

        // Hash de contraseña
        $plainPassword = $data->get('plainPassword', '');
        if ($plainPassword) {
            $hashed = $this->hasher->hashPassword($usuario, $plainPassword);
            $usuario->setContrasena($hashed);
        } elseif ($esNuevo) {
            $this->addFlash('error', 'La contraseña es requerida para nuevos usuarios.');
            return $this->render('admin/usuarios/form.html.twig', [
                'usuario' => $usuario,
                'roles'   => $this->roleRepo->findAllOrdered(),
                'accion'  => $esNuevo ? 'Nuevo usuario' : 'Editar usuario',
            ]);
        }

        // Asignar permisos según rol seleccionado
        $rolId = (int) $data->get('rolId', 0);
        if ($rolId) {
            $this->removePermisosFromUser($usuario);
            $rol = $this->roleRepo->find($rolId);
            if ($rol) {
                $this->setPermisosToUser($usuario, $rol);
            }
        }

        $this->em->persist($usuario);
        $this->em->flush();

        $this->addFlash('success', $esNuevo ? 'Usuario creado correctamente.' : 'Usuario actualizado correctamente.');
        return $this->redirectToRoute('admin.usuarios.index');
    }

    private function removePermisosFromUser(Usuario $usuario): void
    {
        foreach ($usuario->getPermisos() as $permiso) {
            $usuario->removePermiso($permiso);
        }
    }

    private function setPermisosToUser(Usuario $usuario, Rol $rol): void
    {
        $permisos = $this->permissionRepo->findByRolClave($rol->getClave());
        foreach ($permisos as $permiso) {
            $usuario->addPermiso($permiso);
        }
    }
}
