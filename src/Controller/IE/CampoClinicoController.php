<?php

namespace App\Controller\IE;

use App\Controller\DIEControllerController;
use App\Entity\CampoClinico;
use App\Entity\Institucion;
use App\Entity\Solicitud;
use App\Entity\SolicitudInterface;
use App\Form\Type\RegistraCampoClinico\CampoClinicoType;
use App\ObjectValues\SolicitudId;
use App\Repository\IE\DetalleSolicitudMultiple\DetalleSolicitudMultiple;
use App\Repository\SolicitudRepositoryInterface;
use App\Service\CampoClinicoManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/ie')]
class CampoClinicoController extends DIEControllerController
{
    #[Route('/solicitudes/{id}/detalle-de-solicitud-multiple', name: 'ie#detalle_de_solicitud_multiple')]
    public function index(
        int $id,
        SolicitudRepositoryInterface $solicitudRepository,
        DetalleSolicitudMultiple $detalleSolicitudMultiple,
        NormalizerInterface $normalizer
    ): Response {
        $solicitud = $this->checkPermissionSolicitud($id, $solicitudRepository);

        $solicitud = $detalleSolicitudMultiple->getDetalleBySolicitud(
            SolicitudId::fromString($solicitud->getId())
        );

        return $this->render('ie/campo_clinico/detalle_de_solicitud_multiple.html.twig', [
            'solicitud' => $normalizer->normalize($solicitud),
        ]);
    }

    #[Route('/api/campo_clinico', methods: ['POST'], name: 'ie.campo_clinico.store')]
    public function store(
        Request $request,
        CampoClinicoManagerInterface $campoClinicoManager,
        SolicitudRepositoryInterface $solicitudRepository
    ): Response {
        $solicitud_id = $request->request->all('campo_clinico')['solicitud'];
        $solicitud    = $this->checkPermissionSolicitud($solicitud_id, $solicitudRepository);

        if ($solicitud->getEstatus() != SolicitudInterface::CREADA) {
            return $this->httpErrorResponse('No puedes modificar la solicitud ' . $solicitud->getNoSolicitud());
        }

        $form = $this->createForm(CampoClinicoType::class);
        $form->handleRequest($request);

        if ($solicitud && $form->isSubmitted() && $form->isValid()) {
            return $this->jsonResponse($campoClinicoManager->create($form->getData()));
        }

        return $this->jsonErrorResponse($form);
    }

    #[Route('/api/campo_clinico/{campo_clinico_id}', methods: ['DELETE'], name: 'ie.campo_clinico.delete', requirements: ['campo_clinico_id' => '\d+'])]
    public function delete(
        Request $request,
        CampoClinicoManagerInterface $campoClinicoManager,
        int $campo_clinico_id
    ): Response {
        /** @var CampoClinico $campoClinico */
        $campoClinico = $this->em->getRepository(CampoClinico::class)->find($campo_clinico_id);

        if (!$campoClinico) {
            return $this->httpErrorResponse('Not Found', Response::HTTP_NOT_FOUND);
        }

        $this->checkPermissionCampoClinico($campoClinico);

        if ($campoClinico->getSolicitud()->getEstatus() != Solicitud::CREADA) {
            return $this->httpErrorResponse('Campo Clinico only can delete if solicitud.status is "CREADA"');
        }
        if ($campoClinico->getSolicitud()->getCampoClinicos()->count() <= 1) {
            return $this->httpErrorResponse('Must exist almost one campoclinico');
        }

        return $this->jsonResponse($campoClinicoManager->delete($campoClinico));
    }

    private function checkPermissionInstitucion(): void
    {
        /** @var \App\Entity\Usuario $user */
        $user = $this->getUser();

        if (!$user->getInstitucion()) {
            $this->createNotFindUserRelationWithInstitucionException();
        }
    }

    private function checkPermissionCampoClinico(CampoClinico $campo): void
    {
        /** @var Institucion $institucion */
        $institucion = $this->getUser()->getInstitucion();

        if ($campo->getConvenio()->getInstitucion()->getId() != $institucion->getId()) {
            throw $this->createAccessDeniedException();
        }
    }

    private function checkPermissionSolicitud(int $id, SolicitudRepositoryInterface $solicitudRepository): Solicitud
    {
        $this->checkPermissionInstitucion();

        $solicitud = $solicitudRepository->find($id);

        if (!$solicitud) {
            $this->createNotFindSolicitudException($id);
        }

        return $solicitud;
    }
}
