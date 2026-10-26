<?php

namespace App\Controller\Fofoe\Posgrado;

use App\Calculator\ComprobantePagoCalculatorInterface;
use App\Controller\DIEControllerController;
use App\Entity\Pago;
use App\Entity\Posgrado\ResidenciaInterface;
use App\Form\Type\ValidacionDePago\ValidacionPagoUSDType;
use App\Service\Fofoe\ValidarPagoPosgradoInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[Route('/fofoe')]
final class ValidarPagoPosgradoController extends DIEControllerController
{
    #[Route('/pagos/posgrado/{id:pago}/validacion-de-pago', name: 'fofoe.posgrado#validacion_de_pago', methods: ['GET', 'POST'])]
    public function validacionDePago(
        Pago $pago,
        Request $request,
        NormalizerInterface $normalizer,
        ComprobantePagoCalculatorInterface $pagoCalculator,
        ValidarPagoPosgradoInterface $procesadorValidarPago,
    ): Response {
        $residencia = $pago->getResidencia();

        $form = $this->createForm(ValidacionPagoUSDType::class, $pago, [
            'action' => $this->generateUrl('fofoe.posgrado#validacion_de_pago', ['id' => $pago->getId()]),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $procesadorValidarPago->procesar($pago, $form->get('tasaCambio')->getData());

            $this->addFlash('success', $this->getSuccessFlashMessage($pago));

            return $this->redirectToRoute('fofoe.posgrado.inicio', [
                'tipo' => $residencia->getTipo() === ResidenciaInterface::TIPO_EXTRANJERO_IMSS ? 'imss' : 'noimss',
            ]);
        }

        return $this->render('fofoe/pago/validacion_de_pago.html.twig', [
            'montoAPagar' => $pagoCalculator->getMontoAPagar($pago),
            'pago' => $this->normalizePago($normalizer, $pago),
            'categoriaPago' => 'POSGRADO',
            'errors' => $this->getFormErrors($form),
            'pagos' => $this->normalizeHistorialPagos($normalizer, $residencia->getPagos()),
        ]);
    }

    private function getSuccessFlashMessage(Pago $pago): string
    {
        return $pago->isValidado()
            ? sprintf('¡El comprobante para el %s se ha validado correctamente!', $pago->getResidencia()->getFolio())
            : sprintf('Se ha marcado como pago no válido al comprobante con referencia %s', $pago->getReferenciaBancaria());
    }

    /**
     * @param Collection<int, Pago> $pagos
     */
    private function normalizeHistorialPagos(NormalizerInterface $normalizer, Collection $pagos): array
    {
        return $normalizer->normalize($pagos, 'json', [
            'attributes' => [
                'id',
                'validado',
                'facturaGenerada',
                'fechaPago',
                'monto',
                'observaciones',
                'fechaPagoFormatted',
                'requiereFactura',
                'comprobantePago',
            ],
        ]);
    }

    private function normalizePago(NormalizerInterface $normalizer, Pago $pago): array
    {
        return $normalizer->normalize($pago, 'json', [
            'attributes' => [
                'id',
                'monto',
                'montoRegistrado',
                'fechaPago',
                'fechaPagoRegistrada',
                'tipoMoneda',
                'fechaPagoFormatted',
                'comprobantePago',
                'observaciones',
                'requiereFactura',
                'facturaGenerada',
                'validado',
                'referenciaBancaria',
                'residencia' => [
                    'id', 'folio', 'sede', 'ciclo', 'tipo',
                    'tipoDelegacionUmae', 'delegacion', 'monto', 'tipoMoneda', 'tasaCambio',
                    'residente' => [
                        'id',
                        'usuario' => ['id', 'nombre', 'apellidoPaterno', 'apellidoMaterno', 'correo'],
                    ],
                ],
                'factura' => [
                    'id',
                    'fechaFacturacion',
                    'folio',
                    'zip',
                    'monto',
                ],
            ],
        ]);
    }
}
