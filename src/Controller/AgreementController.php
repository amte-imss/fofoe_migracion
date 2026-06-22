<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AgreementController extends AbstractController
{
    #[Route('/aviso-privacidad', name: 'agreement_privacy.index')]
    public function login(): Response
    {
        return $this->render('agreement/payment_policy.html.twig');
    }
}
