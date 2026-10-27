<?php

namespace App\Controller\Fofoe\Enfermeria;

use App\Controller\DIEControllerController;
use App\Entity\Enfermeria\Alumno;
use App\Entity\Pago;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;

#[Route('/fofoe/pagos')]
class PagoController extends DIEControllerController
{
    /**
     * Payment status received from the frontend:
     * 1 = validated (moves to invoice pending or validated)
     * any other value = rejected
     */
    private const STATUS_APPROVED = 1;

    public function __construct(
        protected EntityManagerInterface $em,
        private readonly MailerInterface $mailer,
        protected RequestStack $requestStack,
        protected SerializerInterface $serializer,
        private readonly Environment $twig,
        #[Autowire('%mailer_sender%')]
        private readonly string $mailerSender,
    ) {
        parent::__construct($this->requestStack, $this->em, $this->serializer);
    }

    #[Route('/enfermeria/solicitud', name: 'fofoe.pago.enfermeria.index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->render('enfermeria/fofoe/index.html.twig', [
            'statusPago' => $request->query->get('estado', ''),
        ]);
    }

    #[Route(
        '/enfermeria/solicitud/{id}/{status}',
        name: 'fofoe.pago.enfermeria.update.pago',
        requirements: ['id' => '\d+', 'status' => '\d+'],
        methods: ['POST']
    )]
    public function updateStatusPago(Request $request, int $id, int $status): JsonResponse
    {
        $pago = $this->em->getRepository(Pago::class)->find($id);

        if (!$pago) {
            throw $this->createNotFoundException(sprintf('Payment %d not found.', $id));
        }

        /** @var Alumno $solicitud */
        $solicitud = $pago->getEscuelaEnfermeriaSolicitud();

        if ($status === self::STATUS_APPROVED) {
            $solicitud->setStatus(
                $pago->isRequiereFactura() ? Alumno::STATUS_INVOICE_PENDING : Alumno::STATUS_VALIDATED
            );
            $solicitud->setHasDiferencia(false);
            $solicitud->setMontoDiferencia(0);
            $pago->setValidado(true);
        } else {
            $observaciones = $request->request->get('observaciones', '');
            $hasDiferencia = $request->request->get('has_diferencia', '');
            $montoDiferencia = $request->request->get('monto_diferencia', '');

            if (!empty($hasDiferencia)) {
                $solicitud->setHasDiferencia(true);
                $solicitud->setMontoDiferencia($montoDiferencia);
                $solicitud->setStatus(Alumno::STATUS_REJECTED);
            } else {
                $solicitud->setStatus(Alumno::STATUS_REJECTED_DOCUMENTACION);
            }

            $pago->setValidado(false);
            $pago->setObservaciones($observaciones);
        }

        $this->em->flush();

        $this->sendMail($solicitud, $status);

        return $this->json(['status' => true]);
    }

    private function sendMail(Alumno $solicitud, int $status): void
    {
        $email = (new Email())
            ->from($this->mailerSender)
            ->to($solicitud->getEmail())
            ->subject('Sistema de Administración del FOFOE - Información sobre su pago')
            ->html($this->twig->render('emails/fofoe/enfermeria/update_pago.html.twig', [
                'alumno' => $solicitud,
                'status' => $status,
            ]));

        $this->mailer->send($email);
    }
}
