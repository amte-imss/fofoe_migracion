<?php

namespace App\Controller\IE;

use App\Controller\DIEControllerController;
use App\Entity\Institucion;
use App\Entity\Solicitud;
use App\Repository\CampoClinicoRepository;
use App\Repository\InstitucionRepositoryInterface;
use App\Repository\SolicitudRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/ie')]
class FormatoPagoController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/solicitudes/{id}/generar-formato-de-pago', name: 'ie#generar_formato_de_pago_unico')]
    public function generarFormatoDePagoUnico(int $id, SolicitudRepositoryInterface $solicitudRepository): Response
    {
        $institucionId = $this->getUser()->getInstitucion()->getId();
        $solicitud     = $solicitudRepository->find($id);

        $institucion = $solicitud?->getInstitucion();
        $institucion = ($institucion && $institucion->getId() == $institucionId) ? $institucion : null;
        $solicitud   = $institucion ? $solicitud : null;

        $esPagoUnico = $solicitud && $solicitud->getTipoPago() == Solicitud::TIPO_PAGO_UNICO;
        $campos      = $esPagoUnico ? $solicitud->getCamposClinicos() : null;

        return $this->render('ie/formato/referencia_pago.html.twig', [
            'institucion' => $this->getNormalizeInstitucion($institucion),
            'solicitud'   => $this->getNormalizeSolicitud($solicitud),
            'campos'      => $this->getNormalizeCampos($campos),
            'esPagoUnico' => $esPagoUnico,
        ]);
    }

    #[Route('/solicitudes/{id}/camposClinicos/{campoId}/generar-formato-de-pago', name: 'ie#generar_formato_de_pago_multiple')]
    public function generarFormatoDePagoMultiple(
        int $id,
        int $campoId,
        SolicitudRepositoryInterface $solicitudRepository,
        InstitucionRepositoryInterface $institucionRepository,
        CampoClinicoRepository $campoClinicoRepository
    ): Response {
        /** @var Institucion $institucion */
        $institucion = $institucionRepository->find($this->getUser()->getInstitucion()->getId());
        $campo       = $campoClinicoRepository->getAllCamposClinicosByRequest($campoId, null, true);
        $solicitud   = $solicitudRepository->find($id);

        return $this->render('ie/formato/referencia_pago.html.twig', [
            'institucion' => $this->getNormalizeInstitucion($institucion),
            'solicitud'   => $this->getNormalizeSolicitud($solicitud),
            'campos'      => $this->getNormalizeCampos($campo),
        ]);
    }

    private function getNormalizeInstitucion(?Institucion $institucion): ?array
    {
        return $this->normalizer->normalize($institucion, 'json', [
            'attributes' => ['id', 'nombre', 'rfc'],
        ]);
    }

    private function getNormalizeSolicitud(?Solicitud $solicitud): ?array
    {
        return $this->normalizer->normalize($solicitud, 'json', [
            'attributes' => ['id', 'noSolicitud', 'monto', 'tipoPago'],
        ]);
    }

    private function getNormalizeCampos(mixed $campos): ?array
    {
        return $this->normalizer->normalize($campos, 'json', [
            'attributes' => [
                'fechaInicial', 'fechaFinal', 'lugaresAutorizados',
                'referenciaBancaria', 'monto',
                'estatus'            => ['id', 'nombre'],
                'unidad'             => ['nombre'],
                'nombreCicloAcademico',
                'displayCarrera',
            ],
        ]);
    }
}
