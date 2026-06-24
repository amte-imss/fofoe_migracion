<?php

namespace App\Controller\Fofoe;

use App\Controller\DIEControllerController;
use App\Entity\CampoClinico;
use App\Entity\Solicitud;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment as TwigEnvironment;

#[Route('/fofoe/solicitud')]
class SolicitudController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly MailerInterface $mailer,
        private readonly TwigEnvironment $twig,
        private readonly string $mailerSender = 'no_contestar@educacionensalud.imss.gob.mx',
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/', name: 'fofoe.solicitud.index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        if ($request->isXmlHttpRequest()) {
            $page       = $request->query->getInt('page', 1);
            $limit      = $request->query->getInt('perPage', 10);
            $query      = $this->em->getRepository(Solicitud::class)->searchSolicitudes($request->query->all());
            $pagination = $paginator->paginate($query, $page, $limit);

            $data = [];
            foreach ($pagination as $solicitud) {
                /** @var Solicitud $solicitud */
                $data[] = [
                    'id'          => $solicitud->getId(),
                    'unidad'      => $solicitud->getDisplayDelUMAE(),
                    'esUmae'      => $solicitud->getEsUmae(),
                    'institucion' => $solicitud->getInstitucion()->getNombre(),
                    'noSolicitud' => $solicitud->getNoSolicitud(),
                    'referencia'  => $solicitud->getReferenciaBancaria(),
                    'monto'       => $solicitud->getMonto(),
                    'factura'     => $solicitud->getPago()?->getFactura()?->getFolio() ?? '',
                    'fecha'       => $solicitud->getPago()?->getFechaPagoFormatted() ?? '',
                    'status'      => $solicitud->getEstatusFofoeFormatted(),
                ];
            }

            return new JsonResponse([
                'status' => 'success',
                'data'   => $data,
                'meta'   => [
                    'page'        => $pagination->getCurrentPageNumber(),
                    'total'       => $pagination->getTotalItemCount(),
                    'perPage'     => $pagination->getItemNumberPerPage(),
                    'total_pages' => ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage()),
                ],
            ]);
        }

        return $this->render('fofoe/ciclo_educativo/index.html.twig', [
            'usuario' => $this->getUser(),
        ]);
    }

    #[Route('/{id}', methods: ['GET'], name: 'fofoe.solicitud.show')]
    public function show(Request $request, int $id): Response
    {
        $solicitud = $this->em->getRepository(Solicitud::class)->find($id);

        if (!$solicitud) {
            throw $this->createNotFoundException('Not found for id ' . $id);
        }

        $solicitudJson = [
            'id'          => $solicitud->getId(),
            'unidad'      => $solicitud->getDisplayDelUMAE(),
            'esUmae'      => $solicitud->getEsUmae(),
            'institucion' => [
                'id'     => $solicitud->getInstitucion()->getId(),
                'nombre' => $solicitud->getInstitucion()->getNombre(),
                'rfc'    => $solicitud->getInstitucion()->getRfc(),
            ],
            'referencia'  => $solicitud->getReferenciaBancaria(),
            'noSolicitud' => $solicitud->getNoSolicitud(),
            'monto'       => $solicitud->getMonto(),
            'factura'     => $solicitud->getPago()?->getFactura()?->getFolio() ?? '',
            'fecha'       => $solicitud->getFecha(),
            'status'      => $solicitud->getEstatusFofoeFormatted(),
            'tipoPago'    => $solicitud->getTipoPago(),
            'hasFile'     => (bool) $solicitud->getUrlArchivo(),
        ];

        $camposClinicosJson = [];
        foreach ($solicitud->getCampoClinicos() as $campoClinico) {
            /** @var CampoClinico $campoClinico */
            $camposClinicosJson[] = [
                'id'                => $campoClinico->getId(),
                'unidad'            => $campoClinico->getUnidad()->getNombre(),
                'fechaInicial'      => $campoClinico->getFechaInicialFormatted(),
                'fechaFinal'        => $campoClinico->getFechaFinalFormatted(),
                'horario'           => $campoClinico->getHorario() ?: 'Sin asignar',
                'lugaresAutorizados'=> $campoClinico->getLugaresAutorizados(),
                'lugaresSolicitados'=> $campoClinico->getLugaresSolicitados(),
                'tipo'              => $campoClinico->getCicloAcademico()->getNombre(),
                'carrera'           => $campoClinico->getDisplayCarrera(),
                'hasFile'           => (bool) $campoClinico->getFormatoFofoeFileName(),
                'asignatura'        => $campoClinico->getAsignatura(),
            ];
        }

        $solicitudJson['camposClinicos'] = $camposClinicosJson;

        return $this->render('fofoe/ciclo_educativo/show.html.twig', [
            'usuario'   => $this->getUser(),
            'solicitud' => $solicitudJson,
        ]);
    }

    #[Route('/{id}/validate-oficio-montos', name: 'fofoe.api.validate_oficio_montos')]
    public function validateOficioMontos(Request $request, int $id): Response
    {
        $solicitud = $this->em->getRepository(Solicitud::class)->find($id);
        $data      = json_decode($request->getContent(), true);

        if (!$solicitud) {
            return new JsonResponse(['status' => 'error', 'message' => 'Solicitud no encontrada']);
        }

        if (!isset($data['validate_oficio_montos'])) {
            return new JsonResponse(['status' => 'error', 'message' => 'Datos incompletos']);
        }

        $validateOficioMontos = $data['validate_oficio_montos'];

        if ($validateOficioMontos == 0) {
            $solicitud->setMotiveOficioMontos($data['motive_oficio_montos'] ?? '');
        }

        $solicitud->setValidateOficioMontos($validateOficioMontos);
        $this->em->persist($solicitud);
        $this->em->flush();

        if ($solicitud->getValidateOficioMontos() == 0) {
            $this->sendMailOficioNoValido($solicitud);
        }

        return new JsonResponse(['status' => 'success']);
    }

    private function sendMailOficioNoValido(Solicitud $solicitud): void
    {
        $to = array_filter(
            array_map(
                fn($user) => $user->getCorreo(),
                $solicitud->getDelegacion()->getUsuarios()->toArray()
            )
        );

        $email = (new Email())
            ->from($this->mailerSender)
            ->to(...$to)
            ->subject('Sistema de Administración del FOFOE - Información sobre sus documentos')
            ->html($this->twig->render('emails/fofoe/solicitud/documentos_invalidos.html.twig', [
                'solicitud' => $solicitud,
            ]));

        $this->mailer->send($email);
    }
}
