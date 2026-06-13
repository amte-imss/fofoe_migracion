<?php

namespace App\Controller\Posgrado;

use App\Entity\ConfiguracionGlobal;
use App\Entity\Delegacion;
use App\Entity\Especialidad;
use App\Entity\Pais;
use App\Entity\Posgrado\Residencia;
use App\Entity\Posgrado\Residente;
use App\Form\Type\Posgrado\ResidenteExtranjeroNoIMSSType;
use App\Repository\Posgrado\ResidenciaRepositoryInterface;
use App\Repository\Posgrado\ResidenteRepositoryInterface;
use App\Service\Posgrado\ResidenciaExNoImssManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResidenteExtranjeroController extends PosgradoController
{
    const DEFAULT_PERPAGE = 10;
    const POSGR_RES_EX_NO_IMSS_CICLOS = 'POSGR_RES_EX_NO_IMSS_CICLOS';

    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
        private readonly ResidenciaExNoImssManagerInterface $residenciaManager,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/posgrado/residentes/{tipo}', methods: ['GET'], name: 'posgrado.residentes-imss.index')]
    public function indexResidenteIMSS(
        Request $request,
        string $tipo,
        ResidenciaRepositoryInterface $residenciaRepository
    ): Response {
        $perPage       = $request->query->get('perPage', self::DEFAULT_PERPAGE);
        $page          = $request->query->get('page', 1);
        $tipoResidente = $this->getTipoResidente($tipo);
        $residentes    = [];
        $ciclos        = $residenciaRepository->getCiclos($tipoResidente);

        $config = $this->em->getRepository(ConfiguracionGlobal::class)
            ->findOneBy(['clave' => ConfiguracionGlobal::POSGRADO_CAME]);

        if (
            $config->getValor() == 0
            && $this->isGranted('ROLE_CAME')
            || $this->isGranted('ROLE_CAME_MINUS')
            || $this->isGranted('ROLE_JDES')
            || $this->isGranted('ROLE_JDES_MINUS')
        ) {
            return $this->redirectToRoute('came.solicitud.index');
        }

        $residentesCargaUrl = $this->generateUrl('easyadmin', [
            'entity' => 'ResidenciaExImss',
            'action' => 'list',
        ]);

        $template = $tipoResidente === self::TIPO_RESIDENTE_IMSS
            ? 'posgrado/residentes_ex_imss/index.html.twig'
            : 'posgrado/residentes_ex_no_imss/index.html.twig';

        return $this->render($template, [
            'residentes'         => $residentes,
            'tipoResidentes'     => $tipoResidente,
            'residentesCargaUrl' => $residentesCargaUrl,
            'posgrado_came'      => $config->getValor(),
            'meta'               => [
                'total'          => count($residentes),
                'perPage'        => $perPage,
                'page'           => $page,
                'ciclos'         => $ciclos,
                'tipoResidentes' => $tipoResidente,
            ],
        ]);
    }

    #[Route('/posgrado/residentes/{tipo}/{id}/detalle', methods: ['GET'], name: 'posgrado.residentes-imss.detalle')]
    public function showResidenteIMSS(
        Request $request,
        string $tipo,
        Residente $residente
    ): Response {
        $config = $this->em->getRepository(ConfiguracionGlobal::class)
            ->findOneBy(['clave' => ConfiguracionGlobal::POSGRADO_CAME]);

        if (
            $config->getValor() == 0
            && ($this->isGranted('ROLE_CAME')
                || $this->isGranted('ROLE_CAME_MINUS')
                || $this->isGranted('ROLE_JDES')
                || $this->isGranted('ROLE_JDES_MINUS'))
        ) {
            return $this->redirectToRoute('came.solicitud.index');
        }

        $tipoResidente = $this->getTipoResidente($tipo);

        $template = $tipoResidente === self::TIPO_RESIDENTE_IMSS
            ? 'posgrado/residentes_ex_imss/show.html.twig'
            : 'posgrado/residentes_ex_no_imss/show.html.twig';

        return $this->render($template, [
            'residente' => $this->normalizer->normalize($residente, 'json', [
                'attributes' => [
                    'id',
                    'tipo',
                    'usuario'     => ['nombre', 'apellidoPaterno', 'apellidoMaterno', 'curp', 'correo', 'telefono'],
                    'residencias' => [
                        'id', 'folio', 'estatus', 'oficioAceptacion', 'especialidad',
                        'tipo', 'fechaInicio', 'fechaTermino', 'ciclo', 'grado',
                        'sede', 'monto', 'tipoMoneda', 'subsede',
                        'pagos' => ['id', 'fechaPago', 'monto', 'validado', 'comprobantePago', 'observaciones'],
                    ],
                    'nacionalidad',
                ],
            ]),
        ]);
    }

    #[Route('/posgrado/api/residentes/{tipo}', methods: ['GET'], name: 'posgrado.api.residentes.imss.index.json')]
    public function indexResidentesIMSSApi(
        Request $request,
        string $tipo,
        ResidenteRepositoryInterface $residenteRepository
    ): Response {
        $perPage = $request->query->get('perPage', self::DEFAULT_PERPAGE);
        $page    = $request->query->get('page', 1);

        $filters          = $request->query->all();
        $filters['tipo']  = $this->getTipoResidente($tipo);
        $filters['query'] = $request->query->get('query', '');

        $result = $residenteRepository->getResidentesExtranjeros($filters, $perPage, $page);

        return $this->jsonResponse([
            'object' => $this->normalizer->normalize($result['data'], 'json', [
                'attributes' => [
                    'id', 'folio', 'tipo',
                    'usuario'     => ['nombre', 'apellidoPaterno', 'apellidoMaterno'],
                    'especialidad', 'nacionalidad', 'fechaInicio', 'fechaTermino',
                    'sede', 'subsede', 'grado', 'estatus',
                    'residencias' => [
                        'id', 'ciclo', 'estatus',
                        'pagos' => ['id', 'validado', 'fechaPago', 'comprobantePago'],
                    ],
                ],
            ]),
            'meta' => [
                'total'   => $result['total'],
                'perPage' => $perPage,
                'page'    => $page,
            ],
        ]);
    }

    #[Route('/posgrado/residentes/{tipo}/nuevo', methods: ['GET'], name: 'posgrado.residentes-imss.create')]
    public function createResidenteIMSS(
        Request $request,
        string $tipo,
        ResidenciaRepositoryInterface $residenciaRepository
    ): Response {
        $paises        = $this->em->getRepository(Pais::class)->findAll();
        $especialidades = $this->em->getRepository(Especialidad::class)->findAll();
        $config        = $this->em->getRepository(ConfiguracionGlobal::class)
            ->findOneBy(['clave' => ConfiguracionGlobal::POSGRADO_CAME]);

        if ($config->getValor() == 1) {
            $ooads = ($this->isGranted('ROLE_JDES') || $this->isGranted('ROLE_JDES_MINUS'))
                ? [$this->getUserUnidad()->getDelegacion()]
                : $this->getUser()->getDelegaciones();
        } else {
            $ooads = $this->em->getRepository(Delegacion::class)->findAll();
        }

        $ciclosAcademicos = $this->em->getRepository(ConfiguracionGlobal::class)
            ->findOneBy(['clave' => self::POSGR_RES_EX_NO_IMSS_CICLOS]);

        $tipoResidente = $this->getTipoResidente($tipo);

        return $this->render('posgrado/residentes_ex_no_imss/create.html.twig', [
            'tipoResidentes' => $tipoResidente,
            'paises'         => $this->normalizer->normalize($paises, 'json', [
                'attributes' => ['id', 'nombre'],
            ]),
            'especialidades' => $this->normalizer->normalize($especialidades, 'json', [
                'attributes' => ['id', 'nombre', 'duracion'],
            ]),
            'ooads'          => $this->normalizer->normalize($ooads, 'json', [
                'attributes' => ['id', 'nombre'],
            ]),
            'ciclos'         => $ciclosAcademicos->getValor(),
        ]);
    }

    #[Route('/posgrado/residentes/no_imss/nuevo', methods: ['POST'], name: 'posgrado.residentes-imss.store')]
    public function storeResidente(Request $request): Response
    {
        $form = $this->createForm(ResidenteExtranjeroNoIMSSType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Residencia $residencia */
            $residencia = $form->getData();

            if ($this->isGranted('ROLE_JDES') || $this->isGranted('ROLE_JDES_MINUS')) {
                $residencia->setTipoDelegacionUmae('UMAE');
            }

            $this->residenciaManager->registrarResidenciaExNoImss($residencia);

            return $this->jsonResponse(['status' => true]);
        }

        return $this->jsonErrorResponse($form);
    }

    #[Route('/posgrado/residentes/no_imss/form', methods: ['GET'], name: 'posgrado.residentes-imss.get')]
    public function showResidente(Request $request): Response
    {
        $form = $this->createForm(ResidenteExtranjeroNoIMSSType::class, new Residencia());
        return $this->render('dummyforms.html.twig', ['form' => $form->createView()]);
    }

    private function getTipoResidente(string $tipo): string
    {
        return $tipo === 'no_imss' ? self::TIPO_RESIDENTE_NO_IMSS : self::TIPO_RESIDENTE_IMSS;
    }
}
