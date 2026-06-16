<?php

namespace App\Controller\Fofoe;

use App\Controller\DIEControllerController;
use Symfony\Component\HttpFoundation\Response;

class FofoeController extends DIEControllerController
{
    public function menu(): Response
    {
        return $this->render('fofoe/menu.html.twig', [
            'usuario' => $this->getUser(),
        ]);
    }
}
