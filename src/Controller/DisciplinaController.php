<?php

namespace App\Controller;

use App\Entity\Disciplina;
use App\Form\DisciplinaType;
use App\Repository\DisciplinaRepository;
use App\Service\Export\DisciplinaExportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/disciplina')]
class DisciplinaController extends AbstractController
{
    private const int PAGE_LIMIT = 25;
    private const int JUMP_LIMIT = 3;

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('/', name: 'disciplina_index', methods: ['GET'])]
    public function index(Request $request, DisciplinaRepository $disciplinaRepository): Response
    {
        $page = (int) ($request->query->get('page') ?: 1);
        $orderBy = (string) ($request->query->get('orderBy') ?: '');
        $requestedDirection = (string) ($request->query->get('orderDirection') ?: '');
        $orderDirection = $requestedDirection !== '' ? $requestedDirection : 'desc';
        $search = (string) ($request->query->get('disciplinaToSearch') ?: '');

        $disciplines = $disciplinaRepository->getDisciplinas($disciplinaRepository->findAll());

        $totalRecords = count($disciplines);
        $totalPages = (int) ceil($totalRecords / self::PAGE_LIMIT);

        return $this->render('disciplina/index.html.twig', [
            'disciplinas' => $disciplines,
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
            'usuario' => $this->getUser(),
            'errorMessage' => '',
            'successMessage' => '',
            'criterioDeBusqueda' => $search,
            'disciplinaToSearch' => $search,
            'is_convenios_observer' => $this->isGranted('ROLE_CONVENIOS_OBSERVER'),
        ]);
    }

    #[Route('/new', name: 'disciplina_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $discipline = new Disciplina();
        $discipline->setBorrado(false);
        $discipline->setFechaCreacion(new \DateTime());
        $discipline->setFechaModificacion(new \DateTime());

        $form = $this->createForm(DisciplinaType::class, $discipline);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($discipline);
            $this->em->flush();

            return $this->redirectToRoute('disciplina_show', ['id' => $discipline->getId()]);
        }

        return $this->render('disciplina/new.html.twig', [
            'disciplina' => $discipline,
            'form' => $form->createView(),
            'usuario' => $this->getUser(),
        ]);
    }

    #[Route('/export', name: 'admin.disciplina.export', methods: ['GET'])]
    public function export(DisciplinaExportService $exportService): Response
    {
        $fileName = sprintf('Disciplinas_%s.xlsx', date('dmY_his'));

        return new Response($exportService->export(), Response::HTTP_OK, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName),
        ]);
    }

    #[Route('/{id}', name: 'disciplina_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Disciplina $disciplina): Response
    {
        return $this->render('disciplina/show.html.twig', [
            'disciplina' => $disciplina,
            'delete_form' => $this->createDeleteForm($disciplina)->createView(),
            'usuario' => $this->getUser(),
        ]);
    }

    #[Route('/{id}/edit', name: 'disciplina_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Disciplina $disciplina): Response
    {
        $editForm = $this->createForm(DisciplinaType::class, $disciplina);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $disciplina->setFechaModificacion(new \DateTime());
            $this->em->flush();

            return $this->redirectToRoute('disciplina_index');
        }

        return $this->render('disciplina/edit.html.twig', [
            'disciplina' => $disciplina,
            'edit_form' => $editForm->createView(),
            'delete_form' => $this->createDeleteForm($disciplina)->createView(),
            'usuario' => $this->getUser(),
        ]);
    }

    #[Route('/{id}', name: 'disciplina_delete', requirements: ['id' => '\d+'], methods: ['POST', 'DELETE'])]
    public function delete(Request $request, Disciplina $disciplina): Response
    {
        $form = $this->createDeleteForm($disciplina);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $disciplina->setBorrado(true);
            $disciplina->setFechaModificacion(new \DateTime());
            $this->em->flush();
        }

        return $this->redirectToRoute('disciplina_index', [], Response::HTTP_SEE_OTHER);
    }

    private function createDeleteForm(Disciplina $disciplina): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('disciplina_delete', ['id' => $disciplina->getId()]))
            ->setMethod('DELETE')
            ->getForm();
    }
}
