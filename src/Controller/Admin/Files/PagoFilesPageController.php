<?php

namespace App\Controller\Admin\Files;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER')]
final class PagoFilesPageController extends AbstractController
{
    #[AdminRoute('/files/pagos', name: 'files_pagos')]
    public function index(): Response
    {
        return $this->render('admin/files/pago.html.twig');
    }
}
