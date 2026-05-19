<?php

namespace App\Controller\Admin;

use App\Entity\Permiso;
use App\Repository\PermissionRepository;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/permisos')]
class PermisoCrudController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private PermissionRepository   $permissionRepo,
        private RoleRepository         $roleRepo
    ) {}

    #[Route('', name: 'admin.permisos.index', methods: ['GET'])]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER');

        return $this->render('admin/permisos/index.html.twig', [
            'permisos' => $this->permissionRepo->findAllOrdered(),
            'roles'    => $this->roleRepo->findAllOrdered(),
        ]);
    }

    #[Route('/nuevo', name: 'admin.permisos.new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER');

        if ($request->isMethod('POST')) {
            return $this->save(new Permiso(), $request);
        }

        return $this->render('admin/permisos/form.html.twig', [
            'permiso' => new Permiso(),
            'roles'   => $this->roleRepo->findAllOrdered(),
            'accion'  => 'Nuevo permiso',
        ]);
    }

    #[Route('/{id}/editar', name: 'admin.permisos.edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER');

        $permiso = $this->permissionRepo->find($id);
        if (!$permiso) {
            throw $this->createNotFoundException('Permiso no encontrado.');
        }

        if ($request->isMethod('POST')) {
            return $this->save($permiso, $request);
        }

        return $this->render('admin/permisos/form.html.twig', [
            'permiso' => $permiso,
            'roles'   => $this->roleRepo->findAllOrdered(),
            'accion'  => 'Editar permiso',
        ]);
    }

    #[Route('/{id}/eliminar', name: 'admin.permisos.delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER');

        $permiso = $this->permissionRepo->find($id);
        if ($permiso) {
            $this->em->remove($permiso);
            $this->em->flush();
            $this->addFlash('success', 'Permiso eliminado.');
        }

        return $this->redirectToRoute('admin.permisos.index');
    }

    private function save(Permiso $permiso, Request $request): Response
    {
        $data = $request->request;
        $permiso->setNombre($data->get('nombre', ''));
        $permiso->setClave(strtoupper($data->get('clave', '')));

        $rolId = (int) $data->get('rolId', 0);
        if ($rolId) {
            $rol = $this->roleRepo->find($rolId);
            $permiso->setRol($rol);
        }

        $this->em->persist($permiso);
        $this->em->flush();

        $this->addFlash('success', 'Permiso guardado correctamente.');
        return $this->redirectToRoute('admin.permisos.index');
    }
}
