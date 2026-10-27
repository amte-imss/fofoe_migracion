<?php

namespace App\Controller\Enfermeria;

use App\Controller\DIEControllerController;
use App\Entity\ConfiguracionGlobal;
use App\Entity\Enfermeria\Alumno;
use App\Entity\Enfermeria\Solicitud;
use App\Entity\Usuario;
use App\Form\Type\Enfermeria\AlumnoType;
use App\Form\Type\Enfermeria\SolicitudType;
use App\Repository\ConfiguracionGlobalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;

#[Route('/enfermeria/solicitud')]
class SolicitudController extends DIEControllerController
{
    private const DEFAULT_PERPAGE = 10;

    public function __construct(
        protected RequestStack $requestStack,
        protected EntityManagerInterface $em,
        protected SerializerInterface $serializer,
        #[Autowire('%mailer_sender%')]
        private readonly string $mailerSender,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/api', methods: ['GET'], name: 'enfermeria.simulacion.index')]
    public function index(Request $request): JsonResponse
    {
        $solicitudRepository = $this->em->getRepository(Solicitud::class);
        $configuracionGlobalRepository = $this->em->getRepository(ConfiguracionGlobal::class);
        $config = $configuracionGlobalRepository->findOneBy(['clave' => ConfiguracionGlobal::POSGRADO_CAME]);

        $perPage = $request->query->get('perPage', self::DEFAULT_PERPAGE);
        $page = $request->query->get('page', 1);
        $total = 10;

        $query = $request->query->get('query');
        if ($config->getValor() == 0) {
            $data = $solicitudRepository->findByYear($query, $this->getUserUnidadId());
        } else {
            $data = $solicitudRepository->findByYearAndOOAD($query, $this->getUserDelegacionId());
        }

        return new JsonResponse([
            'meta' => [
                'perPage' => $perPage,
                'page' => $page,
                'total' => $total,
            ],
            'data' => $this->serializer->normalize(
                $data,
                'json',
                [
                    'attributes' => [
                        'id', 'periodo', 'createdAtFormatted', 'fechaInicioFormatted',
                        'fechaFinFormatted', 'periodoFormatted', 'unidad' => ['id', 'nombre', 'claveUnidad', 'nombreEnfermeria'],
                    ],
                ]
            ),
        ]);
    }

    #[Route('/create', methods: ['GET'], name: 'enfermeria.solicitud.create')]
    public function create(Request $request): Response
    {
        $configuracionGlobalRepository = $this->em->getRepository(ConfiguracionGlobal::class);
        $config = $configuracionGlobalRepository->findOneBy(['clave' => ConfiguracionGlobal::POSGRADO_CAME]);

        return $this->render('enfermeria/create.html.twig', [
            'escuelaId' => $this->getUserUnidadId() ?: '',
            'asCame' => $config->getValor(),
            'ooad' => $this->getUserDelegacionId(),
        ]);
    }

    #[Route('/{id}', methods: ['GET'], name: 'enfermeria.solicitud.show')]
    public function show(Request $request, int $id): Response
    {
        $solicitudRepository = $this->em->getRepository(Solicitud::class);
        $solicitud = $solicitudRepository->find($id);
        if (!$solicitud) {
            return $this->httpErrorResponse('Not Found', Response::HTTP_NOT_FOUND);
        }

        $options['extension'] = 'png';
        $url = $this->generateUrl('enfermeria-alumno.login', ['solicitudId' => $solicitud->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $solicitudData = $this->serializer->normalize(
            $solicitud,
            'json',
            [
                'attributes' => [
                    'id', 'periodo', 'fechaInicioFormatted', 'fechaFinFormatted', 'periodoFormatted',
                    'alumnos' => ['id', 'nombre', 'email', 'promedio', 'tipo', 'curp', 'monto', 'status', 'statusFormatted'],
                ],
            ]
        );

        $result = (new Builder(
            writer: new PngWriter(),
            data: $url,
            encoding: new Encoding('UTF-8'),
            size: 300,
            margin: 10,
        ))->build();

        return $this->render('enfermeria/show.html.twig', [
            'solicitud' => $solicitudData,
            'qr' => $result->getDataUri(),
        ]);
    }

    #[Route('/api', methods: ['POST'], name: 'enfermeria.solicitud.store')]
    public function store(Request $request): JsonResponse
    {
        /** @var Usuario $user */
        $user = $this->getUser();
        $formulario = $this->createForm(SolicitudType::class);
        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            /** @var Solicitud $data */
            $data = $formulario->getData();
            $this->em->persist($data);
            $this->em->flush();

            return new JsonResponse([
                'status' => true,
                'data' => $this->serializer->normalize($data, 'json', [
                    'attributes' => ['id'],
                ]),
            ]);
        }

        return new JsonResponse([
            'status' => false,
            'message' => 'Error',
            'errors' => $this->getFormErrors($formulario),
        ], 400);
    }

    #[Route('/alumno/api', methods: ['POST'], name: 'enfermeria.solicitud.alumno.store')]
    public function storeAlumno(MailerInterface $mailer, Environment $templating, Request $request): JsonResponse
    {
        $formulario = $this->createForm(AlumnoType::class);
        $formulario->handleRequest($request);

        /** @var ConfiguracionGlobalRepository $configuracionGlobalRepository */
        $configuracionGlobalRepository = $this->em->getRepository(ConfiguracionGlobal::class);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            /** @var Alumno $data */
            $data = $formulario->getData();
            $monto = $configuracionGlobalRepository->findMontoEF($data->getTipo());
            $data->setMonto((float) $monto->getValor());
            $this->em->persist($data);
            $this->em->flush();

            $alumnoRepository = $this->em->getRepository(Alumno::class);
            $alumno = $alumnoRepository->find($data->getId());
            try {
                $this->sendMail($mailer, $templating, $alumno, $alumno->getSolicitud());
            } catch (\Exception) {
            }

            return new JsonResponse([
                'status' => true,
                'data' => $this->serializer->normalize($data, 'json', [
                    'attributes' => ['id'],
                ]),
            ]);
        }

        return new JsonResponse([
            'status' => false,
            'message' => 'Error',
            'errors' => $this->getFormErrors($formulario),
        ], 400);
    }

    #[Route('/{id}/export', methods: ['GET'], name: 'enfermeria.solicitud.export')]
    public function export(Request $request, int $id): Response
    {
        $solicitudRepository = $this->em->getRepository(Solicitud::class);
        $solicitud = $solicitudRepository->find($id);
        if (!$solicitud) {
            return $this->httpErrorResponse('Not Found', Response::HTTP_NOT_FOUND);
        }

        $content = "CURP,NOMBRE,EMAIL,TIPO ALUMNO,MONTO,ESTADO PROCESO\n";
        foreach ($solicitud->getAlumnos() as $alumno) {
            $content .= "{$alumno->getCurp()},{$alumno->getNombre()},{$alumno->getEmail()},{$alumno->getTipo()},{$alumno->getMonto()},{$alumno->getStatusFormatted()}\n";
        }

        return new Response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="alumnos.csv"',
        ]);
    }

    private function sendMail(MailerInterface $mailer, Environment $templating, Alumno $alumno, Solicitud $solicitud): void
    {
        $html = $templating->render('emails/enfermeria/alumno_bienvenida.html.twig', [
            'alumno' => $alumno,
            'solicitud' => $solicitud,
        ]);

        $email = (new Email())
            ->from($this->mailerSender)
            ->to($alumno->getEmail())
            ->subject('Sistema de Administración del FOFOE -  Credenciales de acceso')
            ->html($html);
        $mailer->send($email);

        $emailCopia = (new Email())
            ->from($this->mailerSender)
            ->to('zurgcom@gmail.com', 'eliarteaga1977@gmail.com')
            ->subject('Sistema de Administración del FOFOE -  Credenciales de acceso')
            ->html($html);
        $mailer->send($emailCopia);
    }
}
