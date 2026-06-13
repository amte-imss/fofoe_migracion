<?php

namespace App\Controller;

use App\Entity\ConfiguracionGlobal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    #[Route('/', name: 'homepage')]
    public function index(Request $request): RedirectResponse
    {
        $roles = $this->getUser()->getRoles();

        if ($this->isUserWithFOFOERol($roles)) {
            return $this->redirectToRoute('fofoe.resumen_pagos');
        }

        $referer = $request->headers->get('referer');

        return match($roles[0]) {
            'ROLE_SUPER',
            'ROLE_ADM_FOFOE'                => $this->redirectToRoute('admin'),

            'ROLE_CONVENIOS',
            'ROLE_CONVENIOS_OBSERVER'       => $this->redirectWithSession($request, 'admin.conveniosadmin.conveniosadmin.index', 'is_convenio', true),

            'ROLE_JDES',
            'ROLE_CAME',
            'ROLE_CAME_MINUS',
            'ROLE_JDES_MINUS'               => !str_contains($referer ?? '', 'login/convenio')
                ? $this->redirectToRoute('came.solicitud.index')
                : $this->redirectWithSession($request, 'admin.convenioscame.convenioscame.index', 'is_convenio', true),

            'ROLE_IE'                       => $this->redirectToRoute('ie#inicio'),

            'ROLE_ALUMNO_POSGRADO'          => $this->redirectWithSession($request, 'residente.perfil', 'is_residente', true),

            'ROLE_POSGRADO'                 => $this->redirectToRoute('posgrado.residentes-imss.index', ['tipo' => 'imss']),

            'ROLE_REPORTE_CCS_DET'          => $this->redirectToRoute('pregrado.reporte.show'),

            'ROLE_SIMULACION'               => $this->redirectToRoute('simulacion'),

            'ROLE_ENFERMERIA'               => $this->redirectToRoute('enfermeria.index'),

            'ROLE_REPORTE_CCS_ENF'          => $this->redirectToRoute('enfermeria.reporte_ciclos'),

            'ROLE_ENFERMERIA_ALUMNO'        => $this->redirectEnfermeriaAlumno($request),

            'ROLE_EDU_PER'                  => $this->redirectToRoute('edu-per.index'),

            'ROLE_EDU_PER_PARTICIPANTE'     => $this->redirectEduPerParticipante($request),

            default                         => throw new \Exception('El usuario no tiene un rol asignado.'),
        };
    }

    public function header(): Response
    {
        return $this->render('header.html.twig', [
            'showHeaderFooter' => $this->showHeaderAndFooter(),
        ]);
    }

    public function footer(): Response
    {
        return $this->render('footer.html.twig', [
            'showHeaderFooter' => $this->showHeaderAndFooter(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    private function isUserWithFOFOERol(array $roles): bool
    {
        return !empty(array_intersect($roles, [
            'ROLE_FOFOE_INICIO',
            'ROLE_FOFOE_VALIDAR_PAGO',
            'ROLE_FOFOE_VALIDAR_PAGO_MULTIPLE',
            'ROLE_FOFOE_REGISTRAR_FACTURA',
            'ROLE_FOFOE_DETALLE_INSTITUCION_EDUCATIVA',
        ]));
    }

    private function showHeaderAndFooter(): bool
    {
        $config = $this->em
            ->getRepository(ConfiguracionGlobal::class)
            ->findOneBy(['clave' => 'FRONTEND_SHOW_HEADER_FOOTER']);

        return $config ? (bool) $config->getValor() : true;
    }

    private function redirectWithSession(
        Request $request,
        string $route,
        string $sessionKey,
        mixed $sessionValue,
        array $routeParams = [],
    ): RedirectResponse {
        $request->getSession()->set($sessionKey, $sessionValue);
        return $this->redirectToRoute($route, $routeParams);
    }

    private function redirectEnfermeriaAlumno(Request $request): RedirectResponse
    {
        $user = $this->getUser();
        $request->getSession()->set('is_enfermeria_alumno', true);
        $request->getSession()->set('enfermeria_alumno_solicitud_id', $user->getSolicitud()->getId());
        return $this->redirectToRoute('enfermeria-alumno.index');
    }

    private function redirectEduPerParticipante(Request $request): RedirectResponse
    {
        $request->getSession()->set('is_edu-per_participant', true);
        $request->getSession()->set('edu-per_participant_id', $this->getUser()->getId());
        return $this->redirectToRoute('edu-per.participant.index');
    }
}
