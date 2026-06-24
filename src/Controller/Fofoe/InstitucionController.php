<?php

namespace App\Controller\Fofoe;

use App\Controller\DIEControllerController;
use App\Entity\Institucion;
use App\Entity\SolicitudInterface;
use App\Entity\Usuario;
use App\Form\Type\InstitucionType;
use App\Normalizer\InstitucionPerfilNormalizerInterface;
use App\Repository\ConvenioRepositoryInterface;
use App\Repository\InstitucionRepositoryInterface;
use App\Repository\SolicitudRepositoryInterface;
use App\Service\InstitucionManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/fofoe')]
class InstitucionController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/detalle-ie/{id}', name: 'ie#detalle-ie', methods: ['GET'])]
    public function detalleIE(
        int $id,
        Request $request,
        InstitucionManagerInterface $institucionManager,
        ConvenioRepositoryInterface $convenioRepository,
        InstitucionPerfilNormalizerInterface $institucionPerfilNormalizer,
        InstitucionRepositoryInterface $institucionRepository,
        SolicitudRepositoryInterface $solicitudRepository
    ): Response {
        /** @var Institucion $institucion */
        $institucion = $institucionRepository->find($id);

        $form = $this->createForm(InstitucionType::class, $institucion, [
            'action' => $this->generateUrl('ie#detalle-ie', ['id' => $institucion->getId()]),
        ]);

        $estatus = [
            SolicitudInterface::EN_VALIDACION_FOFOE,
            SolicitudInterface::FORMATOS_DE_PAGO_GENERADOS,
            SolicitudInterface::CREDENCIALES_GENERADAS,
            SolicitudInterface::CARGANDO_COMPROBANTES,
        ];

        $solicitud = $solicitudRepository->getSolicitudesByInstitucion($id, $estatus);
        $convenios = $convenioRepository->getConveniosUnicosByInstitucionId($institucion->getId());

        return $this->render('fofoe/detalle_ie/index.html.twig', [
            'convenios'   => $institucionPerfilNormalizer->normalizeConvenios($convenios),
            'institucion' => $institucionPerfilNormalizer->normalizeInstitucion($institucion),
            'errores'     => $this->getFormErrors($form),
            'pagos'       => $this->getNormalizePagos($solicitud),
        ]);
    }

    public function menu(): Response
    {
        /** @var Usuario $user */
        $user = $this->getUser();

        return $this->render('ie/institucion/_menu.twig', [
            'institucion' => $user->getInstitucion(),
        ]);
    }

    private function getNormalizePagos(mixed $pagos): mixed
    {
        return $this->normalizer->normalize($pagos, 'json', [
            'attributes' => [
                'id', 'noSolicitud', 'referenciaBancaria', 'estatus', 'tipoPago', 'fecha',
                'pagos' => [
                    'id', 'fechaPagoFormatted', 'referenciaBancaria', 'monto',
                    'requiereFactura', 'validado',
                    'factura' => ['zip'],
                ],
            ],
        ]);
    }
}
