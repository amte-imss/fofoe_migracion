<?php

namespace App\Controller\EduPer;

use App\Controller\DIEControllerController;
use App\Entity\EduPer\{Curso, Duracion, Modalidad, TipoSolicitud};
use App\Form\Type\EduPer\CursoType;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/edu-per')]
class CatalogoController extends DIEControllerController
{
    #[Route('/tipo-solicitud', name: 'api.edu-per.tipo-solicitud.index', methods: ['GET'])]
    public function tipos(): JsonResponse
    {
        return $this->json(['data' => $this->options($this->em->getRepository(TipoSolicitud::class)->findBy(['active' => true], ['name' => 'ASC']))]);
    }

    #[Route('/modalidad', name: 'api.edu-per.modalidad.index', methods: ['GET'])]
    public function modalidades(Request $request): JsonResponse
    {
        $type = $this->em->getRepository(TipoSolicitud::class)->findOneBy(['code' => $request->query->get('type', '')]);
        return $this->json(['data' => $this->options($this->em->getRepository(Modalidad::class)->findBy(['active' => true, 'tipoSolicitudId' => $type?->getId() ?? 0], ['name' => 'ASC']))]);
    }

    #[Route('/duracion', name: 'api.edu-per.duracion.index', methods: ['GET'])]
    public function duraciones(Request $request): JsonResponse
    {
        $modalidad = $this->em->getRepository(Modalidad::class)->findOneBy(['code' => $request->query->get('modalidad', '')]);
        return $this->json(['data' => $this->options($this->em->getRepository(Duracion::class)->findBy(['active' => true, 'modalidadId' => $modalidad?->getId() ?? 0], ['name' => 'ASC']), true)]);
    }

    #[Route('/curso', name: 'api.edu-per.curso.index', methods: ['GET'])]
    public function cursos(): JsonResponse
    {
        return $this->json(['data' => $this->options($this->em->getRepository(Curso::class)->findBy(['active' => true], ['name' => 'ASC']))]);
    }

    #[Route('/curso', name: 'api.edu-per.curso.store', methods: ['POST'])]
    public function crearCurso(Request $request): JsonResponse
    {
        $curso = new Curso();
        $form = $this->createForm(CursoType::class, $curso);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json(['status' => false, 'errors' => $this->getFormErrors($form, true)], Response::HTTP_BAD_REQUEST);
        }
        $this->em->persist($curso);
        $this->em->flush();
        return $this->json(['status' => true, 'data' => $this->options([$curso])[0]]);
    }

    private function options(array $items, bool $duration = false): array
    {
        return array_map(static function ($item) use ($duration): array {
            $option = ['id' => $item->getId(), 'name' => $item->getName(), 'code' => $item->getCode()];
            if ($duration) { $option['amount'] = $item->getAmount(); $option['period'] = $item->getPeriod(); }
            return $option;
        }, $items);
    }
}
