<?php

namespace App\Controller\Came;

use App\Controller\DIEControllerController;
use App\Entity\Solicitud;
use App\Entity\SolicitudInterface;
use App\Form\Type\Came\ValidarSolicitudCC\ValidarSolicitudCCType;
use App\Service\SolicitudManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ValidaRegistroCampoController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/came/solicitud/{id}/validar', methods: ['GET'], name: 'came.solicitud.validar_get', requirements: ['id' => '\d+'])]
    public function validate(Request $request, int $id): Response
    {
        $delegacion = $this->getUserDelegacionId();
        $unidad     = $this->getUserUnidadId();

        if (is_null($delegacion) && is_null($unidad)) {
            throw $this->createAccessDeniedException();
        }

        $solicitud = $this->checkPermissionSolicitud($id, SolicitudInterface::REGISTRADA, 'came.solicitud.index');
        if (!($solicitud instanceof Solicitud)) {
            return $solicitud;
        }

        $form = $this->createForm(ValidarSolicitudCCType::class, $solicitud);

        return $this->render('came/solicitud/validar_lugares_campos.html.twig', [
            'form'      => $form->createView(),
            'solicitud' => $this->normalizer->normalize($solicitud, 'json', [
                'attributes' => [
                    'id',
                    'campoClinicos' => [
                        'id', 'asignatura', 'horario', 'promocion',
                        'cicloAcademico' => ['id', 'nombre'],
                        'convenio'       => [
                            'cicloAcademico' => ['id', 'nombre'],
                            'id', 'vigencia', 'vigenciaFormatted', 'numero',
                            'label', 'obsValRegistro',
                            'carrera' => ['id', 'nombre', 'nivelAcademico' => ['id', 'nombre']],
                        ],
                        'lugaresSolicitados', 'lugaresAutorizados', 'horario',
                        'trabajadoresBecados' => ['matricula'],
                        'unidad'             => ['id', 'nombre'],
                        'fechaInicial', 'fechaFinal', 'fechaInicialFormatted', 'fechaFinalFormatted',
                    ],
                    'institucion' => [
                        'id', 'nombre', 'fax', 'telefono', 'extension',
                        'correo', 'sitioWeb', 'direccion', 'rfc', 'representante',
                        'convenios' => [
                            'id', 'nombre',
                            'carrera'        => ['id', 'nombre', 'nivelAcademico' => ['id', 'nombre']],
                            'cicloAcademico' => ['id', 'nombre'],
                            'vigencia', 'vigenciaFormatted', 'label',
                        ],
                    ],
                ],
            ]),
        ]);
    }

    #[Route('/came/api/solicitud/{id}/validar', methods: ['POST'], name: 'came.solicitud.validar_post', requirements: ['id' => '\d+'])]
    public function update(Request $request, SolicitudManagerInterface $solicitudManager, int $id): Response
    {
        $solicitud = $this->checkPermissionSolicitud($id, SolicitudInterface::REGISTRADA);
        if (!($solicitud instanceof Solicitud)) {
            return $solicitud;
        }

        $form = $this->createForm(ValidarSolicitudCCType::class, $solicitud);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Solicitud $solicitud */
            return $this->jsonResponse($solicitudManager->validarRegistroSolicitud($solicitud));
        }

        return $this->jsonErrorResponse($form, [], false, true);
    }

    private function checkPermissionSolicitud(int $id, string $checkEstatus = '', ?string $redirectRoute = null): Solicitud|Response
    {
        $solicitud = $this->em->getRepository(Solicitud::class)->find($id);

        if (!$solicitud) {
            $this->addFlash('danger', 'No existe la solicitud indicada');
            return $this->redirectToRoute('came.solicitud.index');
        }
        if (!$this->isGrantedUserAccessToSolicitud($solicitud)) {
            $this->addFlash('danger', 'No puedes modificar una solicitud de otra OOAD / UMAE');
            return $this->redirectToRoute('came.solicitud.index');
        }
        if ($checkEstatus !== '' && $solicitud->getEstatus() != $checkEstatus) {
            if ($redirectRoute) {
                $this->addFlash('danger', 'No es posible procesar la solicitud indicada');
                return $this->redirectToRoute($redirectRoute);
            }
            return $this->httpErrorResponse('No puedes modificar la solicitud ' . $solicitud->getNoSolicitud());
        }

        return $solicitud;
    }
}
