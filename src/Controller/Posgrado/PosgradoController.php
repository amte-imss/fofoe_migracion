<?php

namespace App\Controller\Posgrado;

use App\Controller\DIEControllerController;
use App\Entity\ConfiguracionGlobal;
use App\Entity\Usuario;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PosgradoController extends DIEControllerController
{
    const TIPO_RESIDENTE_IMSS    = 'EXTRANJERO_IMSS';
    const TIPO_RESIDENTE_NO_IMSS = 'EXTRANJERO_NO_IMSS';

    public function menu(): Response
    {
        /** @var Usuario $user */
        $user   = $this->getUser();
        $config = $this->em->getRepository(ConfiguracionGlobal::class)
            ->findOneBy(['clave' => ConfiguracionGlobal::POSGRADO_CAME]);

        $rol = match(true) {
            $this->isGranted('ROLE_CAME')       => 'CPEI',
            $this->isGranted('ROLE_JDES')       => 'JDES',
            $this->isGranted('ROLE_CAME_MINUS') => 'CAME',
            $this->isGranted('ROLE_JDES_MINUS') => 'JDES',
            default                             => '',
        };

        if ($config->getValor() == 1) {
            return $this->render('came/menu.html.twig', [
                'usuario'                    => $user,
                'isUserDelegacionActivated'  => $this->isUserDelegacionActivated(),
                'delegacion_came'            => $this->getUserDelegacionId(),
                'unidad_came'               => $this->getUserUnidadId(),
                'posgrado_came'             => $config->getValor(),
                'rol'                        => $rol,
            ]);
        }

        return $this->render('posgrado/menu.html.twig', [
            'usuario' => $user,
        ]);
    }

    protected function getTipoResidentesActivated(?string $tipo = null): string
    {
        return in_array($tipo, [self::TIPO_RESIDENTE_IMSS, self::TIPO_RESIDENTE_NO_IMSS], strict: true)
            ? $tipo
            : self::TIPO_RESIDENTE_IMSS;
    }

    #[Route('/posgrado/tipo_residentes', methods: ['POST'], name: 'posgrado.set_tipo_residentes')]
    public function setTipoResidente(Request $request): Response
    {
        $tipo_residente = $request->request->get('tipo_residentes');
        $tipos          = [self::TIPO_RESIDENTE_IMSS, self::TIPO_RESIDENTE_NO_IMSS];

        $session = $this->requestStack->getSession();
        $session->set(
            'posgrado_tipo_residentes',
            in_array($tipo_residente, $tipos, strict: true) ? $tipo_residente : self::TIPO_RESIDENTE_IMSS
        );

        return $this->redirectToRoute('posgrado.residentes.index');
    }
}
