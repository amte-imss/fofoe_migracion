<?php

namespace App\Controller\IE;

use App\Controller\DIEControllerController;
use App\Entity\CampoClinico;
use App\Entity\Institucion;
use App\Entity\Pago;
use App\Entity\Solicitud;
use App\Form\Type\ComprobantePagoType\ComprobantePagoType;
use App\Repository\CampoClinicoRepository;
use App\Repository\PagoRepositoryInterface;
use App\Service\InstitucionManager;
use App\Service\UploaderComprobantePagoInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/ie')]
final class CargaComprobantePagoController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/pagos/{id}/carga-de-comprobante-de-pago', name: 'ie#carga_de_comprobante_de_pago')]
    public function cargaDeComprobanteDePago(
        int $id,
        Request $request,
        PagoRepositoryInterface $pagoRepository,
        NormalizerInterface $normalizer,
        UploaderComprobantePagoInterface $uploaderComprobantePago,
        InstitucionManager $institucionManager,
    ): Response {
        /** @var Pago $pago */
        $pago = $pagoRepository->find($id);
        if (!$pago) {
            throw $this->createNotFindPagoException($id);
        }

        /** @var Institucion $institucion */
        $institucion = $this->getUser()->getInstitucion();
        if (!$institucion) {
            throw $this->createNotFindUserRelationWithInstitucionException();
        }

        $form = $this->createForm(ComprobantePagoType::class, $pago, [
            'action' => $this->generateUrl('ie#carga_de_comprobante_de_pago', ['id' => $id]),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Pago $pago */
            $pago   = $form->getData();
            $cedula = $form->get('cedulaFile');

            if (
                $pago->isRequiereFactura()
                && !$institucion->getCedulaIdentificacion()
                && (!$cedula || !$cedula->getData())
            ) {
                $cedula->addError(new FormError(
                    'Para la emisión de la factura es necesaria la Cédula de Identificación Fiscal'
                ));
            } else {
                if ($pago->isRequiereFactura() && $cedula?->getData()) {
                    $institucion->setCedulaFile($cedula->getData());
                }

                $pago->setMontoRegistrado($pago->getMonto());
                $pago->setFechaPagoRegistrada($pago->getFechaPago());

                $uploaderComprobantePago->update($pago);
                $this->em->flush();

                $this->addFlash('success', $this->getSuccessFlashMessage($pago));

                return new RedirectResponse($this->getRedirectRoute($pago->getSolicitud()));
            }
        }

        return $this->render('ie/solicitud/carga_de_comprobante_de_pago.html.twig', [
            'gestionPago' => $normalizer->normalize($pago->getGestionPago()),
            'id'          => $id,
            'institucion' => $this->getNormalizeInstitucion($normalizer, $institucion),
            'errors'      => $this->getFormErrors($form),
        ]);
    }

    private function getNormalizeInstitucion(NormalizerInterface $normalizer, Institucion $institucion): array
    {
        return $normalizer->normalize($institucion, 'json', [
            'attributes' => ['id', 'cedulaIdentificacion'],
        ]);
    }

    private function getSuccessFlashMessage(Pago $pago): string
    {
        $solicitud = $pago->getSolicitud();

        if ($solicitud->isPagoUnico()) {
            return sprintf(
                '¡El comprobante de la solicitud %s, con referencia %s, se ha cargado correctamente!',
                $solicitud->getNoSolicitud(),
                $solicitud->getReferenciaBancaria()
            );
        }

        $campoClinico = $this->getCampoClinico($solicitud, $pago->getReferenciaBancaria());

        if ($campoClinico) {
            return sprintf(
                '¡El comprobante del campo clínico %s, con referencia %s, se ha cargado correctamente!',
                $campoClinico->getUnidad()->getNombre(),
                $campoClinico->getReferenciaBancaria()
            );
        }

        $this->setCriticalLogUpdateComprobantePago($solicitud);

        return '';
    }

    private function getCampoClinico(Solicitud $solicitud, string $referenciaBancaria): ?CampoClinico
    {
        /** @var CampoClinico|false $result */
        $result = $solicitud->getCamposClinicos()->matching(
            CampoClinicoRepository::getCampoClinicoByReferenciaBancaria($referenciaBancaria)
        )->first();

        return $result ?: null;
    }

    private function setCriticalLogUpdateComprobantePago(Solicitud $solicitud): void
    {
        $this->logger->critical(
            'Se esta tratando de cargar un comprobante de pago sin haber asignado el tipo de pago a la solicitud',
            ['id' => $solicitud->getId()]
        );
    }

    private function getRedirectRoute(Solicitud $solicitud): string
    {
        return $solicitud->isPagoUnico()
            ? $this->generateUrl('ie#detalle_de_solicitud', ['id' => $solicitud->getId()])
            : $this->generateUrl('ie#detalle_de_solicitud_multiple', ['id' => $solicitud->getId()]);
    }
}
