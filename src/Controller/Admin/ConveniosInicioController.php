<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ConveniosInicioController extends AbstractController
{
    #[Route('/convenios', name: 'convenios.index', methods: ['GET'])]
    public function index(): Response
    {
        $this->denyUnlessConveniosAccess();

        return $this->redirectToRoute('admin.conveniosadmin.conveniosadmin.index');
    }

    #[Route('/admin/conveniosadmin/conveniosadmin', name: 'admin.conveniosadmin.conveniosadmin.index', methods: ['GET'])]
    public function conveniosCame(): Response
    {
        $this->denyUnlessConveniosAccess();

        return $this->renderInicio('Convenios CAME', 'Acuerdos institucionales registrados para campos clinicos.');
    }

    #[Route('/admin/convenios-especiales', name: 'admin.convenios-especiales.index', methods: ['GET'])]
    public function conveniosEspeciales(): Response
    {
        $this->denyUnlessConveniosAccess();

        return $this->renderInicio('Convenios Especiales', 'Acuerdos especificos y condiciones particulares de convenio.');
    }

    #[Route('/admin/coordfirmante/coordfirmante', name: 'admin.coordfirmante.coordfirmante.index', methods: ['GET'])]
    public function coordinacionesFirmantes(): Response
    {
        $this->denyUnlessConveniosAccess();

        return $this->renderInicio('Catalogo Coord. Firmante', 'Catalogo de coordinaciones y responsables firmantes.');
    }

    #[Route('/admin/disciplina', name: 'disciplina_index', methods: ['GET'])]
    public function disciplinas(): Response
    {
        $this->denyUnlessConveniosAccess();

        return $this->renderInicio('Catalogo de Disciplinas', 'Catalogo de disciplinas asociadas a convenios.');
    }

    #[Route('/admin/institucion', name: 'admin.institucion.index', methods: ['GET'])]
    public function instituciones(): Response
    {
        $this->denyUnlessConveniosAccess();

        return $this->renderInicio('Instituciones Educativas', 'Directorio de instituciones educativas registradas.');
    }

    private function denyUnlessConveniosAccess(): void
    {
        if (!$this->isGranted('ROLE_CONVENIOS') && !$this->isGranted('ROLE_CONVENIOS_OBSERVER') && !$this->isGranted('ROLE_SUPER')) {
            throw $this->createAccessDeniedException();
        }
    }

    private function renderInicio(string $title, string $description): Response
    {
        return $this->render('admin/convenios/inicio.html.twig', [
            'title' => $title,
            'description' => $description,
        ]);
    }
}
