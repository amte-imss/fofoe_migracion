<?php

namespace App\Controller\Came;

use App\Controller\DIEControllerController;
use App\Entity\Institucion;
use App\Form\Type\InstitucionType;
use App\Service\InstitucionManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class InstitucionController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/came/api/institucion/{id}', methods: ['POST'], name: 'came.institucion.update')]
    public function update(Request $request, InstitucionManagerInterface $institucionManager, int $id): Response
    {
        $institucion = $this->em->getRepository(Institucion::class)->find($id);

        if (!$institucion) {
            return $this->httpErrorResponse('Not Found', Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(InstitucionType::class, $institucion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $institucionManager->update($form->getData());

                $institucion = $this->em->getRepository(Institucion::class)->find($id);

                return $this->jsonResponse([
                    'status'  => true,
                    'message' => 'Los datos de la Institución han sido actualizados con éxito.',
                    'object'  => $this->normalizer->normalize($institucion, 'json', [
                        'attributes' => [
                            'id', 'nombre', 'rfc', 'direccion', 'telefono',
                            'extension', 'correo', 'sitioWeb', 'fax', 'representante',
                        ],
                    ]),
                ]);
            } catch (\Exception $ex) {
                return $this->failedResponse($ex->getMessage());
            }
        }

        return $this->jsonErrorResponse($form, ['message' => 'Se presentó un problema al actualizar la institución']);
    }
}
