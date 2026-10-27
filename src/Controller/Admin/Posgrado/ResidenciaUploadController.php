<?php

namespace App\Controller\Admin\Posgrado;

use App\Entity\Delegacion;
use App\Entity\Especialidad;
use App\Entity\Pais;
use App\Entity\Usuario;
use App\Form\Type\Admin\Posgrado\ResidenciaBulkUploadType;
use App\Form\Type\Admin\Posgrado\ResidenciaSingleUploadType;
use App\Security\Voter\PosgradoAccessVoter;
use App\Service\Posgrado\ResidenciaManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminRoute('/posgrado/residencias-ex-imss', name: 'posgrado_residencia_ex_imss')]
#[IsGranted(PosgradoAccessVoter::ACCESS)]
final class ResidenciaUploadController extends AbstractController
{
    public function __construct(
        private readonly ResidenciaManagerInterface $residenciaManager,
        private readonly EntityManagerInterface $em,
    ) {}

    #[AdminRoute('/carga', name: 'carga')]
    public function bulkUpload(Request $request): Response
    {
        $form = $this->createForm(ResidenciaBulkUploadType::class);
        $form->handleRequest($request);

        $result = [];
        $exception = null;

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $result = $this->residenciaManager->processDataFromXlsx(
                    $form->get('submitFile')->getData(),
                    $form->get('ciclo')->getData(),
                    $this->getUser(),
                );
            } catch (\Exception $e) {
                $exception = $e->getMessage();
            }
        }

        return $this->render('admin/posgrado/residencia/bulk_upload.html.twig', [
            'form' => $form,
            'data' => $result[0] ?? [],
            'errors' => $result['errores'] ?? [],
            'exception' => $exception,
        ]);
    }

    #[AdminRoute('/carga-unitaria', name: 'carga_unitaria')]
    public function singleUpload(Request $request): Response
    {
        $form = $this->createForm(ResidenciaSingleUploadType::class);
        $form->handleRequest($request);

        $result = [];
        $exception = null;

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $result = $this->residenciaManager->processDatoUnitario($form->getData(), $this->getUser());
            } catch (\Exception $e) {
                $exception = $e->getMessage();
            }
        }

        $succeeded = !empty($result['data']);

        return $this->render('admin/posgrado/residencia/single_upload.html.twig', [
            'form' => $succeeded ? $this->createForm(ResidenciaSingleUploadType::class) : $form,
            'data' => $result['data'] ?? [],
            'errors' => $result['errores'] ?? [],
            'exception' => $exception,
            'succeeded' => $succeeded,
            'specialties' => $this->em->getRepository(Especialidad::class)->findBy([], ['nombre' => 'ASC']),
            'delegations' => $this->getDelegationCatalog(),
            'countries' => $this->em->getRepository(Pais::class)->findBy([], ['nombre' => 'ASC']),
        ]);
    }

    /**
     * @return Delegacion[]
     */
    private function getDelegationCatalog(): array
    {
        /** @var Usuario $user */
        $user = $this->getUser();
        $userDelegations = $user->getDelegaciones();

        if ($this->isGranted('ROLE_SUPER') || $userDelegations->isEmpty()) {
            return $this->em->getRepository(Delegacion::class)->findBy([], ['nombre' => 'ASC']);
        }

        $delegations = $userDelegations->toArray();
        usort($delegations, fn(Delegacion $a, Delegacion $b): int => strcmp($a->getNombre(), $b->getNombre()));

        return $delegations;
    }
}
