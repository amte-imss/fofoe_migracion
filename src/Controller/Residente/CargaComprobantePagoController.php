<?php

namespace App\Controller\Residente;

use App\Calculator\ComprobantePagoCalculatorInterface;
use App\Controller\DIEControllerController;
use App\Entity\Pago;
use App\Entity\Posgrado\Residencia;
use App\Entity\Posgrado\ResidenciaInterface;
use App\Form\Type\Residente\ComprobantePagoType;
use App\Security\Voter\ResidenciaVoter;
use App\Service\UploaderComprobantePagoInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/residente')]
#[IsGranted('ROLE_ALUMNO_POSGRADO')]
final class CargaComprobantePagoController extends DIEControllerController
{
    private const string CEDULA_REQUIRED_MESSAGE = 'Para la emisión de la factura es necesaria la Cédula de Identificación Fiscal';

    #[Route('/pagos/{id:pago}/carga-de-comprobante-de-pago', name: 'residente.carga_de_comprobante_de_pago', methods: ['GET', 'POST'])]
    public function cargaDeComprobanteDePago(
        Pago $pago,
        Request $request,
        NormalizerInterface $normalizer,
        UploaderComprobantePagoInterface $uploaderComprobantePago,
        ComprobantePagoCalculatorInterface $pagoCalculator,
    ): Response {
        $residencia = $pago->getResidencia();

        $this->denyAccessUnlessGranted(ResidenciaVoter::CARGAR_COMPROBANTE_DE_PAGO, $residencia);

        $form = $this->createForm(ComprobantePagoType::class, $pago, [
            'action' => $this->generateUrl('residente.carga_de_comprobante_de_pago', ['id' => $pago->getId()]),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $residente = $residencia->getResidente();
            $cedula = $form->has('cedulaFile') ? $form->get('cedulaFile') : null;
            $cedulaFile = $cedula?->getData();

            if ($pago->isRequiereFactura() && !$residente->getCedulaIdentificacion() && !$cedulaFile) {
                ($cedula ?? $form)->addError(new FormError(self::CEDULA_REQUIRED_MESSAGE));
            } else {
                if ($pago->isRequiereFactura() && $cedulaFile) {
                    $residente->setCedulaFile($cedulaFile);
                }

                $pago->setMontoRegistrado($pago->getMonto());
                $pago->setFechaPagoRegistrada($pago->getFechaPago());

                $uploaderComprobantePago->update($pago);
                $residencia->setEstatus(ResidenciaInterface::EN_VALIDACION_FOFOE);

                $this->em->flush();

                $this->addFlash('success', $this->getSuccessFlashMessage($pago));

                return $this->redirectToRoute('residente.perfil');
            }
        }

        return $this->render('residente/extranjeroIMSS/cargar_comprobante_pago.html.twig', [
            'residencia' => $this->normalizeResidencia($normalizer, $residencia),
            'montoAPagar' => $pagoCalculator->getMontoAPagar($pago),
            'errors' => $this->getFormErrors($form),
        ]);
    }

    private function normalizeResidencia(NormalizerInterface $normalizer, Residencia $residencia): array
    {
        return $normalizer->normalize($residencia, 'json', [
            'attributes' => [
                'id',
                'especialidad',
                'folio',
                'sede', 'subsede',
                'tipoDelegacionUmae',
                'tipo',
                'grado',
                'ciclo',
                'fechaInicio', 'fechaTermino',
                'monto', 'tipoMoneda', 'tasaCambio',
                'lastPago' => ['id', 'observaciones', 'referencia', 'monto'],
                'pagos' => [
                    'id',
                    'monto',
                    'fechaPago',
                    'comprobantePago',
                    'referenciaBancaria',
                    'validado',
                    'observaciones',
                    'tipoMoneda',
                ],
                'residente' => [
                    'id',
                    'folio',
                    'tipo',
                    'nacionalidad',
                    'usuario' => [
                        'nombre', 'apellidoPaterno', 'apellidoMaterno',
                        'telefono', 'correo', 'curp',
                    ],
                    'sede',
                    'subsede',
                    'especialidad',
                    'estatus',
                    'grado',
                ],
            ],
        ]);
    }

    private function getSuccessFlashMessage(Pago $pago): string
    {
        return sprintf(
            '¡El comprobante de pago para la referencia %s, se ha cargado correctamente!',
            $pago->getReferenciaBancaria()
        );
    }
}
