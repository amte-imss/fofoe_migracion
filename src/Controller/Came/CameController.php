<?php

namespace App\Controller\Came;

use App\Controller\DIEControllerController;
use App\Entity\ConfiguracionGlobal;
use App\Entity\Usuario;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/came')]
class CameController extends DIEControllerController
{

    public function menu(): Response
    {
        /** @var Usuario $user */
        $user = $this->getUser();

        $config = $this->em
            ->getRepository(ConfiguracionGlobal::class)
            ->findOneBy(['clave' => ConfiguracionGlobal::POSGRADO_CAME]);

        $session = $this->requestStack->getSession();
        $delegacion_came = $session->get('user_delegacion');
        $unidad_came     = $session->get('user_unidad');

        $rol = match(true) {
            $this->isGranted('ROLE_CAME')       => 'CPEI',
            $this->isGranted('ROLE_JDES')       => 'DEIS',
            $this->isGranted('ROLE_CAME_MINUS') => 'CAME',
            $this->isGranted('ROLE_JDES_MINUS') => 'JDES',
            default => '',
        };

        return $this->render('came/menu.html.twig', [
            'usuario'                    => $user,
            'isUserDelegacionActivated' => $this->isUserDelegacionActivated(),
            'delegacion_came'            => $delegacion_came,
            'unidad_came'                => $unidad_came,
            'posgrado_came'              => $config?->getValor(),
            'rol'                        => $rol,
        ]);
    }

    /**
     * Guarda la OOAD o Unidad seleccionada en sesión para filtrar solicitudes.
     * Formato del valor: "D_{id}" para delegación, "U_{id}" para unidad.
     */
    #[Route('/usuario/delegacion_unidad', name: 'came.usuario.delegacion_unidad', methods: ['POST'])]
    public function setDelegacion(Request $request): Response
    {
        $valor = $request->request->get('delegacion_unidad_came', '');
        $partes = explode('_', $valor, 2);

        if (count($partes) === 2 && in_array($partes[0], ['D', 'U'], true)) {
            $session = $request->getSession();
            if ($partes[0] === 'D') {
                $session->set('user_delegacion', $partes[1]);
                $session->remove('user_unidad');
            } else {
                $session->set('user_unidad', $partes[1]);
                $session->remove('user_delegacion');
            }
        }

        // Regresar a la página de origen o al índice
        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('came.solicitud.index');
    }
}
