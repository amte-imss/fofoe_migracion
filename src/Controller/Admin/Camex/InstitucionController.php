<?php

namespace App\Controller\Admin\Camex;

use App\Controller\DIEControllerController;
use App\Entity\Delegacion;
use App\Entity\Usuario;
use App\Repository\InstitucionRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InstitucionController extends DIEControllerController
{
    #[Route(
        '/admin/convenioscame/institucion',
        name: 'admin.convenioscame.institucion.index',
        methods: ['GET']
    )]
    public function index(Request $request, InstitucionRepositoryInterface $institucionRepository): Response
    {
        /** @var Usuario $user */
        $user = $this->getUser();
        $isJdes = $this->isJdes();

        if ($request->isXmlHttpRequest()) {
            $page = $request->query->getInt('page', 1);
            $perPage = $request->query->getInt('perPage', 20);
            $search = $request->query->getString('search');

            $filters = [
                'search' => $search,
                'type'   => 'active',
                // 'came_delegacion' => $this->resolveDelegacionIds($user, $isJdes),
            ];

            $pagination = $institucionRepository->paginateAdminInstituciones($filters, $page, $perPage);
            $pagination['data'] = $this->serializer->normalize($pagination['data'], 'json', [
                'attributes' => [
                    'razonSocial', 'usersByCampus',
                    'id', 'nombre', 'rfc', 'direccion', 'telefono', 'cedulaIdentificacion',
                    'extension', 'correo', 'sitioWeb', 'fax', 'representante',
                    'usuarios' => [
                        'id', 'nombre', 'correo',
                        'delegacionInstitucion' => ['id', 'nombre', 'claveDelegacional'],
                        'campus' => ['id', 'nombre'],
                    ],
                    'convenios' => [
                        'id', 'nombre',
                        'delegacionConvenios' => [
                            'id',
                            'delegacion' => ['id', 'nombre', 'claveDelegacional'],
                        ],
                    ],
                ],
            ]);

            return new JsonResponse($pagination);
        }

        return $this->render('admin/conveniocamex/institucion/index.html.twig', [
            'usuario'      => $user,
            'usuario_ooad' => $isJdes
                ? $this->getUserUnidad()?->getDelegacion()?->getId()
                : ($user->getDelegaciones()->first() ?: null)?->getId(),
        ]);
    }

    private function isJdes(): bool
    {
        return $this->isGranted('ROLE_JDES') || $this->isGranted('ROLE_JDES_MINUS');
    }

    /**
     * @return int[]
     */
    private function resolveDelegacionIds(Usuario $user, bool $isJdes): array
    {
        if ($isJdes) {
            $ooadId = $this->getUserUnidad()?->getDelegacion()?->getId();

            return $ooadId !== null ? $this->getOoadsByUmae($ooadId) : [];
        }

        return $user->getDelegaciones()
            ->map(fn (Delegacion $delegacion): int => $delegacion->getId())
            ->getValues();
    }

    /**
     * @return int[]
     */
    private function getOoadsByUmae(int $ooadUmaeId): array
    {
        return match ($ooadUmaeId) {
            31, 32  => [32, 31],
            35, 36  => [35, 36],
            37, 38  => [37, 38],
            default => [$ooadUmaeId],
        };
    }
}
