<?php

namespace App\Controller\Admin\CoordinacionFirmante;

use App\Controller\DIEControllerController;
use App\Entity\CoordinacionFirmante;
use App\Form\CoordinacionFirmanteType;
use App\Repository\CoordinacionFirmanteRepository;
use App\Service\Export\CoordinacionFirmanteExportService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CoordinacionFirmanteController extends DIEControllerController
{
    private const int PAGE_LIMIT = 10;
    private const int JUMP_LIMIT = 3;

    public function __construct(
        RequestStack $requestStack,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        private readonly PaginatorInterface $paginator,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/admin/coordfirmante/coordfirmante', name: 'admin.coordfirmante.coordfirmante.index', methods: ['GET', 'POST'])]
    public function index(Request $request, CoordinacionFirmanteRepository $coordinacionFirmanteRepository): Response
    {
        return $this->renderList($request, $coordinacionFirmanteRepository);
    }

    #[Route('/admin/coordfirmante/coordfirmante/new', name: 'admin.coordfirmante.coordfirmante.new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $coordination = new CoordinacionFirmante();
        $coordination->setBorrado(false);
        $coordination->setFechaCreacion(new \DateTime());
        $coordination->setFechaModificacion(new \DateTime());

        $form = $this->createForm(CoordinacionFirmanteType::class, $coordination);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($coordination);
            $this->em->flush();

            return $this->redirectToRoute('admin.coordfirmante.coordfirmante.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/coordinacionfirmante/new.html.twig', [
            'form' => $form->createView(),
            'usuario' => $this->getUser(),
        ]);
    }

    #[Route('/admin/coordfirmante/export', name: 'admin.coordfirm.export', methods: ['GET'])]
    public function export(CoordinacionFirmanteExportService $exportService): Response
    {
        $fileName = sprintf('Coordinaciones_Firmantes_%s.xlsx', date('dmY_his'));

        return new Response($exportService->export(), Response::HTTP_OK, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName),
        ]);
    }

    #[Route('/admin/coordfirmante/coordfirmante/edit', name: 'admin.coordfirmante.coordfirmante.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CoordinacionFirmanteRepository $coordinacionFirmanteRepository): Response
    {
        $coordination = $coordinacionFirmanteRepository->find($this->input($request, 'id'));
        if ($coordination === null) {
            throw $this->createNotFoundException();
        }

        $coordination->setBorrado(false);
        $coordination->setFechaModificacion(new \DateTime());

        $form = $this->createForm(CoordinacionFirmanteType::class, $coordination);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('admin.coordfirmante.coordfirmante.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/coordinacionfirmante/edit.html.twig', [
            'form' => $form->createView(),
            'usuario' => $this->getUser(),
        ]);
    }

    #[Route('/admin/coordfirmante/coordfirmante/deleted', name: 'admin.coordfirmante.coordfirmante.delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, CoordinacionFirmanteRepository $coordinacionFirmanteRepository): Response
    {
        $coordination = $coordinacionFirmanteRepository->find($this->input($request, 'id'));
        $message = 'No ha sido posible borrar el convenio, por favor intenta de nuevo.';

        if ($coordination !== null) {
            $coordination->setBorrado(true);
            $coordination->setFechaModificacion(new \DateTime());
            $this->em->flush();

            $message = 'La Coordinación ha sido borrada de forma exitosa.';
        }

        return $this->renderList($request, $coordinacionFirmanteRepository, $message);
    }

    #[Route('/admin/coordfirmante/coordfirmante/search', name: 'admin.coordfirmante.coordfirmante.search', methods: ['GET', 'POST'])]
    public function searchingCoordination(Request $request, CoordinacionFirmanteRepository $coordinacionFirmanteRepository): Response
    {
        return $this->renderList($request, $coordinacionFirmanteRepository);
    }

    private function renderList(
        Request $request,
        CoordinacionFirmanteRepository $coordinacionFirmanteRepository,
        string $message = '',
    ): Response {
        $page = (int) ($this->input($request, 'page') ?: 1);
        $orderBy = (string) ($this->input($request, 'orderBy') ?: '');
        $requestedDirection = (string) ($this->input($request, 'orderDirection') ?: '');
        $search = (string) ($this->input($request, 'coordinacionToSearch') ?: '');

        $orderDirection = match ($requestedDirection !== '' ? $requestedDirection : 'desc') {
            'asc' => 'desc',
            'desc' => 'asc',
            default => $requestedDirection,
        };

        $coordinations = $coordinacionFirmanteRepository->getCoordinacionesFirmantes();
        $totalRecords = (int) $coordinacionFirmanteRepository->getCoordinacionesFirmantesCount();
        $totalPages = (int) ceil($totalRecords / self::PAGE_LIMIT);

        if (!empty($coordinations)) {
            $coordinations = $this->paginator->paginate($coordinations, $page, self::PAGE_LIMIT);
        }

        return $this->render('admin/coordinacionfirmante/index.html.twig', [
            'coordinaciones' => !empty($coordinations) ? $coordinations : null,
            'totalPaginas' => $totalPages,
            'totalRegistros' => $totalRecords,
            'anterior' => $page - 1,
            'siguiente' => $page + 1,
            'page' => $page,
            'isTheLastPage' => $page === $totalPages,
            'isTheLFirstPage' => $page === 1,
            'firstPage' => max($page - self::JUMP_LIMIT, 1),
            'lastPage' => min($page + self::JUMP_LIMIT, $totalPages),
            'minLimit' => ($page * self::PAGE_LIMIT) - self::PAGE_LIMIT + 1,
            'maxLimit' => $page === $totalPages ? $totalRecords : $page * self::PAGE_LIMIT,
            'orderBy' => $orderBy,
            'orderDirection' => $orderDirection,
            'orderDirectionP' => $requestedDirection,
            'criterioDeBusqueda' => $search,
            'coordinacionToSearch' => $search,
            'usuario' => $this->getUser(),
            'message' => $message,
            'is_convenios_observer' => $this->isGranted('ROLE_CONVENIOS_OBSERVER'),
        ]);
    }
}
