<?php

namespace App\Controller\Fofoe\Enfermeria;

use App\Entity\Enfermeria\Alumno;
use App\Entity\Pago;
use App\Repository\PagoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controlador FOFOE — Validación y rechazo de pagos de Escuelas de Enfermería.
 */
#[Route('/fofoe/pagos')]
class PagoController extends AbstractController
{
    /**
     * GET /fofoe/pagos/enfermeria/solicitud
     * Listado FOFOE de pagos de enfermería filtrado por estado.
     */
    #[Route('/enfermeria/solicitud', name: 'fofoe.pago.enfermeria.index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->render('enfermeria/fofoe/index.html.twig', [
            'statusPago' => $request->query->get('estado', ''),
        ]);
    }

    /**
     * POST /fofoe/pagos/enfermeria/solicitud/{id}/{status}
     * Actualiza el estado del pago: 1 = validar, 0 = rechazar.
     */
    #[Route('/enfermeria/solicitud/{id}/{status}', name: 'fofoe.pago.enfermeria.update', methods: ['POST'])]
    public function updateStatusPago(
        int                    $id,
        int                    $status,
        Request                $request,
        EntityManagerInterface $em,
        MailerInterface        $mailer
    ): JsonResponse {
        /** @var Pago $pago */
        $pago      = $em->getRepository(Pago::class)->find($id);
        if (!$pago) {
            return new JsonResponse(['status' => false, 'message' => 'Pago no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        /** @var Alumno $alumno */
        $alumno = $pago->getEscuelaEnfermeriaSolicitud();

        if ($status === 1) {
            // Validar pago
            if ($pago->isRequiereFactura()) {
                $alumno->setStatus(Alumno::STATUS_INVOICE_PENDING);
            } else {
                $alumno->setStatus(Alumno::STATUS_VALIDATED);
            }
            $alumno->setHasDiferencia(false);
            $alumno->setMontoDiferencia(0);
            $pago->setValidado(true);
        } else {
            // Rechazar pago
            $observaciones   = $request->request->get('observaciones', '');
            $hasDiferencia   = (bool) $request->request->get('has_diferencia', false);
            $montoDiferencia = (float) $request->request->get('monto_diferencia', 0);

            if ($hasDiferencia) {
                $alumno->setHasDiferencia(true);
                $alumno->setMontoDiferencia($montoDiferencia);
                $alumno->setStatus(Alumno::STATUS_REJECTED);
            } else {
                $alumno->setStatus(Alumno::STATUS_REJECTED_DOCUMENTACION);
            }
            $pago->setValidado(false);
            $pago->setObservaciones($observaciones);
        }

        $em->persist($pago);
        $em->persist($alumno);
        $em->flush();

        try {
            $this->sendUpdatePagoEmail($mailer, $alumno, $status);
        } catch (\Throwable $e) {
            // No bloquear flujo si falla el correo
        }

        return new JsonResponse(['status' => true]);
    }

    // -------------------------------------------------------------------------

    private function sendUpdatePagoEmail(MailerInterface $mailer, Alumno $alumno, int $status): void
    {
        $from = $this->getParameter('mailer_sender');

        $email = (new TemplatedEmail())
            ->from(new Address($from))
            ->to(new Address($alumno->getEmail()))
            ->subject('Sistema de Administración del FOFOE — Información sobre su pago')
            ->htmlTemplate('emails/fofoe/enfermeria/update_pago.html.twig')
            ->context([
                'alumno' => $alumno,
                'status' => $status,
            ]);

        $mailer->send($email);
    }
}
