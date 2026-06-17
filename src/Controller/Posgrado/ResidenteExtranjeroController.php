<?php

namespace App\Controller\Posgrado;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/posgrado')]
final class ResidenteExtranjeroController extends AbstractController
{
    #[Route('/residentes/{tipo}', name: 'posgrado.residentes-imss.index', methods: ['GET'])]
    public function index(string $tipo): Response
    {
        if (!$this->isGranted('ROLE_POSGRADO') && !$this->isGranted('ROLE_CAME') && !$this->isGranted('ROLE_SUPER')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('posgrado/residentes/index.html.twig', [
            'tipo' => $tipo,
            'title' => $tipo === 'no_imss'
                ? 'Alumnos extranjeros rotación parcial de especialidad'
                : 'Alumnos extranjeros en Ciclo académico anual',
        ]);
    }
}
