<?php

namespace App\Controller\Admin\Files;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER')]
final class SolicitudCEFilesPageController extends AbstractController
{
    #[AdminRoute('/files/solicitud-ce', name: 'files_solicitud_ce')]
    public function index(): Response
    {
        return $this->render('admin/files/solicitud_ce.html.twig');
    }
}
