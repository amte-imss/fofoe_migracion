<?php

namespace App\Controller\Fofoe;

use App\Controller\DIEControllerController;
use App\Entity\Pago;
use App\Form\Type\ValidacionDePago\ValidacionPagoType;
use App\ObjectValues\PagoId;
use App\Repository\Fofoe\ValidacionDePago\DetallePago;
use App\Repository\PagoRepositoryInterface;
use App\Service\ProcesadorValidarPagoInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/fofoe')]
final class ValidarPagoController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/pagos/{id}/validacion-de-pago', name: 'fofoe#validacion_de_pago')]
    public function validacionDePago(
        int $id,
        Request $request,
        DetallePago $detallePago,
        PagoRepositoryInterface $pagoRepository,
        ProcesadorValidarPagoInterface $procesadorValidarPago
    ): Response {
        /** @var Pago $pago */
        $pago = $pagoRepository->find($id);
        $form = $this->createForm(ValidacionPagoType::class, $pago, [
            'action' => $this->generateUrl('fofoe#validacion_de_pago', ['id' => $id]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Pago $pago */
            $pago = $form->getData();
            $procesadorValidarPago->procesar($pago);
            $this->addFlash('success', $this->getSuccessFlashMessage($pago));

            return $this->redirectToRoute('fofoe/inicio');
        }

        $pagoDetalle    = $detallePago->detalleByPago(PagoId::fromString($id));
        $pagoNormalized = $this->normalizer->normalize($pagoDetalle, 'json');
        $campos         = $pago->getCamposPagados()['campos'];

        return $this->render('fofoe/pago/validacion_de_pago.html.twig', [
            'pago'          => $pagoNormalized,
            'campos'        => $this->getNormalizeCampo($campos),
            'categoriaPago' => 'CC',
            'errors'        => $this->getFormErrors($form),
            'pagos'         => [],
        ]);
    }

    private function getNormalizeCampo(mixed $campo): mixed
    {
        return $this->normalizer->normalize($campo, 'json', [
            'attributes' => [
                'id', 'displayFormatted', 'monto', 'horario', 'asignatura',
                'estatus'             => ['nombre'],
                'displayCarrera',
                'cicloAcademico'      => ['id', 'nombre'],
                'convenio'            => [
                    'cicloAcademico' => ['nombre'],
                    'carrera'        => ['nombre', 'nivelAcademico' => ['nombre']],
                ],
                'lugaresAutorizados', 'totalTrabajadoresBecados',
                'unidad'              => ['nombre', 'esUmae'],
                'displayFechaInicial', 'fechaInicialFormatted',
                'displayFechaFinal', 'formatoFofoeFileName',
                'validateFormatoFofoe',
            ],
        ]);
    }

    private function getSuccessFlashMessage(Pago $pago): string
    {
        $message = $pago->isValidado()
            ? '¡El comprobante con referencia %s de la solicitud %s se ha validado correctamente!'
            : 'Se ha marcado como pago no válido al comprobante con referencia %s de la solicitud %s';

        return sprintf($message, $pago->getReferenciaBancaria(), $pago->getSolicitud()->getNoSolicitud());
    }
}
