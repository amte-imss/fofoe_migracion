<?php

namespace App\Controller\Admin;

use App\Entity\Rol;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/roles')]
class RolCrudController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private RoleRepository         $roleRepo
    ) {}

    #[Route('', name: 'admin.roles.index', methods: ['GET'])]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER');

        return $this->render('admin/roles/index.html.twig', [
            'roles' => $this->roleRepo->findAllOrdered(),
        ]);
    }

    #[Route('/nuevo', name: 'admin.roles.new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER');

        if ($request->isMethod('POST')) {
            return $this->save(new Rol(), $request);
        }

        return $this->render('admin/roles/form.html.twig', [
            'rol'   => new Rol(),
            'accion' => 'Nuevo rol',
        ]);
    }

    #[Route('/{id}/editar', name: 'admin.roles.edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER');

        $rol = $this->roleRepo->find($id);
        if (!$rol) {
            throw $this->createNotFoundException('Rol no encontrado.');
        }

        if ($request->isMethod('POST')) {
            return $this->save($rol, $request);
        }

        return $this->render('admin/roles/form.html.twig', [
            'rol'   => $rol,
            'accion' => 'Editar rol',
        ]);
    }

    #[Route('/{id}/eliminar', name: 'admin.roles.delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER');

        $rol = $this->roleRepo->find($id);
        if ($rol) {
            $this->em->remove($rol);
            $this->em->flush();
            $this->addFlash('success', 'Rol eliminado.');
        }

        return $this->redirectToRoute('admin.roles.index');
    }

    private function save(Rol $rol, Request $request): Response
    {
        $data = $request->request;
        $rol->setNombre($data->get('nombre', ''));
        $rol->setClave(strtoupper($data->get('clave', '')));

        $this->em->persist($rol);
        $this->em->flush();

        $this->addFlash('success', 'Rol guardado correctamente.');
        return $this->redirectToRoute('admin.roles.index');
    }
}
