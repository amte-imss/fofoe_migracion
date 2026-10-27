<?php

namespace App\Controller\Residente;

use App\Controller\DIEControllerController;
use App\Entity\Posgrado\Residente;
use App\Entity\Usuario;
use App\Form\Type\Residente\InformacionPersonalResidenteType;
use App\Service\UsuarioManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/residente')]
#[IsGranted('ROLE_ALUMNO_POSGRADO')]
class ResidenteExtranjeroController extends DIEControllerController
{
    public function __construct(
        RequestStack $requestStack,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/perfil', name: 'residente.perfil', methods: ['GET'])]
    public function perfil(): Response
    {
        return $this->render('residente/extranjeroIMSS/index.html.twig', [
            'residente' => $this->normalizeResidente($this->getResidente()),
            'errores' => [],
        ]);
    }

    #[Route('/actualizar-datos', name: 'actualizar.datos', methods: ['POST'])]
    public function update(Request $request, UsuarioManagerInterface $usuarioManager): Response
    {
        $residente = $this->getResidente();

        $form = $this->createForm(InformacionPersonalResidenteType::class, $residente->getUsuario(), [
            'action' => $this->generateUrl('actualizar.datos'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $usuarioManager->update($form->getData());

            $this->addFlash('success', 'Se han actualizado correctamente los datos de contacto');

            return $this->redirectToRoute('residente.perfil');
        }

        return $this->render('residente/extranjeroIMSS/index.html.twig', [
            'residente' => $this->normalizeResidente($residente),
            'errores' => $this->getFormErrors($form),
            'form' => $form,
        ]);
    }

    public function menu(): Response
    {
        return $this->render('residente/extranjeroIMSS/_menu.twig', [
            'residente' => $this->getResidente(),
        ]);
    }

    private function getResidente(): Residente
    {
        /** @var Usuario $user */
        $user = $this->getUser();

        return $user->getResidente();
    }

    private function normalizeResidente(Residente $residente): array
    {
        return $this->normalizer->normalize($residente, 'json', [
            'attributes' => [
                'id',
                'folio',
                'tipo',
                'usuario' => [
                    'nombre', 'apellidoPaterno', 'apellidoMaterno',
                    'telefono', 'correo', 'curp',
                ],
                'nacionalidad',
                'sede',
                'subsede',
                'especialidad',
                'estatus',
                'grado',
                'residencias' => [
                    'id',
                    'especialidad',
                    'sede', 'subsede',
                    'tipoDelegacionUmae',
                    'monto',
                    'estatus',
                    'tipoMoneda',
                    'tipo',
                    'grado',
                    'ciclo',
                    'lastPago' => ['id'],
                    'pagos' => [
                        'id',
                        'monto',
                        'tipoMoneda',
                        'fechaPago',
                        'comprobantePago',
                        'referenciaBancaria',
                        'validado',
                        'requiereFactura',
                        'facturaGenerada',
                        'observaciones',
                    ],
                ],
            ],
        ]);
    }
}
