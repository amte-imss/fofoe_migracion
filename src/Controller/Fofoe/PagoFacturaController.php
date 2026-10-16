<?php

namespace App\Controller\Fofoe;

use App\Controller\DIEControllerController;
use App\Entity\CampoClinico;
use App\Entity\EstatusCampoInterface;
use App\Entity\Factura;
use App\Entity\Pago;
use App\Entity\Posgrado\Residencia;
use App\Entity\Posgrado\ResidenciaInterface;
use App\Entity\Solicitud;
use App\Entity\SolicitudInterface;
use App\Form\Type\FacturaType;
use App\Form\Type\FacturaType\PagoFacturaType;
use App\Repository\EstatusCampoRepositoryInterface;
use App\Repository\InstitucionRepositoryInterface;
use App\Repository\PagoRepositoryInterface;
use App\Service\UploaderComprobantePagoInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/fofoe')]
class PagoFacturaController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/pagos/{id}/registrar-factura', name: 'fofoe#registrar_factura', methods: ['POST', 'GET'])]
    public function registrarFactura(
        int $id,
        Request $request,
        InstitucionRepositoryInterface $institucionRepository,
        EstatusCampoRepositoryInterface $campoRepository,
        PagoRepositoryInterface $pagoRepository,
        UploaderComprobantePagoInterface $uploaderComprobantePago
    ): Response {
        $pago        = $pagoRepository->find($id);
        $institucion = $institucionRepository->getInstitucionBySolicitudId($pago->getSolicitud()->getId());

        /** @var Solicitud $solicitud */
        $solicitud = $this->em->getRepository(Solicitud::class)->find($pago->getSolicitud()->getId());

        $pagos = $pagoRepository->getComprobantesPagoValidadosByReferenciaBancaria(
            $pago->getReferenciaBancaria(),
            $pago->getSolicitud()->getId()
        );

        $form = $this->createForm(PagoFacturaType::class, $pago, [
            'action' => $this->generateUrl('fofoe#registrar_factura', ['id' => $id]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Pago $pago */
            $pago    = $form->getData();
            $factura = $pago->getFactura();

            $this->em->beginTransaction();

            $factura->addPago($pago);
            $pago->setFacturaGenerada(true);
            $pago->setFactura($factura);
            $file = $factura->getZipFile();
            $factura->setZipFile(null);

            $pagosValidados = $pagoRepository->getComprobantesPagoValidadosByReferenciaBancaria(
                $pago->getReferenciaBancaria(),
                $pago->getSolicitud()->getId()
            );
            foreach ($pagosValidados as $pagoV) {
                if ($pago->getId() == $pagoV->getId()) continue;
                $pagoV->setFacturaGenerada(true);
                $factura->addPago($pagoV);
            }

            $statusCredGen = $campoRepository->findOneBy(['nombre' => EstatusCampoInterface::CREDENCIALES_GENERADAS]);
            foreach ($pago->getCamposPagados()['campos'] as $campoV) {
                $campoV->setEstatus($statusCredGen);
            }

            $solicitud = $pago->getSolicitud();
            if (
                $solicitud->isPagoUnico() ||
                count(array_filter(
                    $solicitud->getCamposClinicos()->toArray(),
                    fn(CampoClinico $cc) => $cc->getEstatus() && $cc->getEstatus()->getNombre() !== EstatusCampoInterface::CREDENCIALES_GENERADAS
                )) === 0
            ) {
                $solicitud->setEstatus(SolicitudInterface::CREDENCIALES_GENERADAS);
            }

            $this->em->persist($pago);
            $this->em->flush();
            $factura->setZipFile($file);
            $this->em->persist($factura);
            $this->em->flush();
            $this->em->commit();

            $uploaderComprobantePago->sendEmailRegistroFactura($solicitud, $pago, $factura);

            $this->addFlash('success', sprintf(
                'Se ha guardado correctamente la factura con folio %s para la solicitud %s con número de referencia %s',
                $factura->getFolio(),
                $solicitud->getNoSolicitud(),
                $pago->getReferenciaBancaria()
            ));

            return $this->redirectToRoute('fofoe/inicio');
        }

        if ($this->getFormErrors($form, true)) {
            $this->addFlash('danger', 'Ocurrió un error al procesar el registro. Verifique los datos e intente de nuevo');
        }

        return $this->render('fofoe/registrar_factura.html.twig', [
            'institucion' => $this->getNormalizeInstitucion($institucion),
            'solicitud'   => $this->getNormalizeSolicitud($solicitud),
            'pagos'       => $this->getNormalizePago($pagos),
            'errors'      => $this->getFormErrors($form, true),
        ]);
    }

    #[Route('/pagos/{id}/registrar-factura-posgrado', name: 'fofoe#registrar_factura_posgrado', methods: ['POST', 'GET'])]
    public function registrarFacturaPosgrado(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        PagoRepositoryInterface $pagoRepository,
        UploaderComprobantePagoInterface $uploaderComprobantePago
    ): Response {
        /** @var Pago $pago */
        $pago = $pagoRepository->find($id);
        if (empty($pago) || !$pago->getResidencia()) {
            throw $this->createNotFoundException('Not found for id ' . $id);
        }

        $pagos     = $pagoRepository->findBy([
            'referenciaBancaria' => $pago->getReferenciaBancaria(),
            'residencia'         => $pago->getResidencia(),
        ]);
        $residencia = $pago->getResidencia();

        return $this->render('fofoe/detalle_referencia/index.html.twig', [
            'pagos'          => $this->getNormalizePagoResidencia($pagos),
            'categoria_pago' => 'POSGRADO',
            'tipo'           => $residencia->getTipo() === ResidenciaInterface::TIPO_EXTRANJERO_IMSS ? 'imss' : 'noimss',
            'residencia'     => $this->getNormalizeResidencia($residencia),
        ]);
    }

    #[Route('/pagos/posgrado/solicitud/{id}/factura', methods: ['POST'], name: 'fofoe.pago.posgrado.factura')]
    public function uploadPosgrado(Request $request, int $id): Response
    {
        /** @var Pago $pago */
        $pago = $this->em->getRepository(Pago::class)->find($id);
        $form = $this->createForm(FacturaType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Factura $factura */
            $factura = $form->getData();
            $pago->setFactura($factura);
            $pago->setFacturaGenerada(true);

            $residencia = $pago->getResidencia();
            $residencia->setEstatus(ResidenciaInterface::PAGO_VALIDADO_FOFOE);

            $this->em->persist($factura);
            $this->em->persist($pago);
            $this->em->persist($residencia);
            $this->em->flush();

            return new JsonResponse(['status' => true]);
        }

        return new JsonResponse([
            'status'  => false,
            'message' => 'Error',
            'errors'  => $this->getFormErrors($form),
        ], 400);
    }

    #[Route('/api/solicitud', methods: ['GET'], name: 'fofoe.solicitud.index.api')]
    public function indexApi(Request $request): Response
    {
        $perPage     = $request->query->get('perPage', 10);
        $page        = $request->query->get('page', 1);
        $solicitudes = $this->em->getRepository(Solicitud::class)
            ->getSolicitudesPagadas($perPage, $page, $request->query->all());

        return $this->jsonResponse([
            'object' => $this->normalizer->normalize($solicitudes['data'], 'json', [
                'attributes' => [
                    'id', 'noSolicitud', 'estatus', 'tipoPago',
                    'delegacion'  => ['id', 'nombre'],
                    'institucion' => ['id', 'nombre'],
                    'pagos'       => [
                        'id', 'referenciaBancaria', 'validado', 'requiereFactura', 'fechaPagoFormatted',
                        'factura' => ['id', 'folio'],
                    ],
                ],
            ]),
            'meta' => ['total' => $solicitudes['total'], 'perPage' => $perPage, 'page' => $page],
        ]);
    }

    public function update(Factura $pago, EntityManagerInterface $entityManager): bool
    {
        $file = $pago->getZipFile();
        $pago->setZipFile(null);
        $entityManager->persist($pago);
        $pago->setZipFile($file);
        $entityManager->flush();

        return true;
    }

    private function getNormalizePagoResidencia(mixed $pago): mixed
    {
        return $this->normalizer->normalize($pago, 'json', [
            'attributes' => [
                'id', 'monto', 'fechaPago', 'fechaPagoFormatted', 'comprobantePago',
                'observaciones', 'requiereFactura', 'facturaGenerada',
                'residencia' => ['id'],
                'validado', 'referenciaBancaria',
                'factura' => ['id', 'fechaFacturacion', 'folio', 'zip', 'monto'],
            ],
        ]);
    }

    private function getNormalizeResidencia(Residencia $residencia): array
    {
        return $this->normalizer->normalize($residencia, 'json', [
            'attributes' => [
                'id', 'especialidad', 'sede', 'subsede', 'folio',
                'tipoDelegacionUmae', 'monto', 'tipoMoneda', 'tasaCambio', 'tipo', 'grado',
                'residente' => [
                    'id',
                    'usuario' => ['id', 'nombre', 'apellidoPaterno', 'apellidoMaterno', 'rfc', 'correo', 'telefono'],
                ],
                'ciclo',
                'lastPago' => ['id'],
            ],
        ]);
    }

    private function getNormalizeInstitucion(mixed $institucion): mixed
    {
        return $this->normalizer->normalize($institucion, 'json', [
            'attributes' => ['id', 'nombre', 'razonSocial', 'rfc', 'cedulaIdentificacion'],
        ]);
    }

    private function getNormalizeSolicitud(Solicitud $solicitud): array
    {
        return $this->normalizer->normalize($solicitud, 'json', [
            'attributes' => [
                'id', 'noSolicitud', 'fecha', 'referenciaBancaria', 'monto',
                'unidad'     => ['nombre', 'esUmae'],
                'delegacion' => ['nombre'],
                'pagos'      => [
                    'id', 'monto', 'fechaPago', 'fechaPagoFormatted', 'comprobantePago',
                    'requiereFactura', 'facturaGenerada', 'validado', 'referenciaBancaria',
                    'factura' => ['fechaFacturacion', 'folio', 'zip', 'monto'],
                ],
            ],
        ]);
    }

    private function getNormalizePago(mixed $pago): mixed
    {
        return $this->normalizer->normalize($pago, 'json', [
            'attributes' => [
                'id', 'monto', 'fechaPago', 'fechaPagoFormatted', 'comprobantePago',
                'requiereFactura', 'facturaGenerada', 'validado', 'referenciaBancaria',
                'factura'      => ['id', 'fechaFacturacion', 'folio', 'zip', 'monto'],
                'camposPagados' => [
                    'monto',
                    'convenio' => ['delegacion' => ['nombre']],
                ],
            ],
        ]);
    }
}
