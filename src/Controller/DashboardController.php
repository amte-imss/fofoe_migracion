<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(): Response
    {
        // SUPER y ADM ven el dashboard principal (no redirigir)
        if ($this->isGranted('ROLE_SUPER') || $this->isGranted('ROLE_ADM_FOFOE')) {
            return $this->render('dashboard/index.html.twig');
        }

        $roles = $this->getUser()->getRoles();

        // Roles FOFOE — van al panel de pagos
        $fofoeRoles = [
            'ROLE_FOFOE_INICIO', 'ROLE_FOFOE_VALIDAR_PAGO',
            'ROLE_FOFOE_VALIDAR_PAGO_MULTIPLE', 'ROLE_FOFOE_REGISTRAR_FACTURA',
            'ROLE_FOFOE_DETALLE_INSTITUCION_EDUCATIVA',
            'ROLE_FOFOE_REPORTE_INGS', 'ROLE_FOFOE_REPORTE_OP',
        ];
        foreach ($fofoeRoles as $fofoeRole) {
            if (in_array($fofoeRole, $roles, true)) {
                return $this->redirectToRoute('fofoe.enfermeria.index');
            }
        }

        // Roles con ruta propia — usar getRoles() directos del usuario (sin herencia)
        $directRoles = method_exists($this->getUser(), 'getDirectRoles')
            ? $this->getUser()->getDirectRoles()
            : $roles;

        foreach ($directRoles as $role) {
            switch ($role) {
                case 'ROLE_CAME':
                case 'ROLE_CAME_MINUS':
                case 'ROLE_JDES':
                case 'ROLE_JDES_MINUS':
                    return $this->redirectToRoute('came.solicitud.index');

                case 'ROLE_IE':
                    return $this->redirectToRoute('enfermeria.index');

                case 'ROLE_POSGRADO':
                    return $this->redirectToRoute('posgrado.index');

                case 'ROLE_CONVENIOS':
                case 'ROLE_CONVENIOS_OBSERVER':
                    return $this->redirectToRoute('convenios.index');
            }
        }

        // Fallback: dashboard genérico
        return $this->render('dashboard/index.html.twig');
    }
}

