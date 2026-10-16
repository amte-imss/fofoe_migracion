<?php

namespace App\Controller\IE;

use App\Controller\DIEControllerController;
use App\Entity\ConfiguracionGlobal;
use App\Entity\Institucion;
use App\Entity\Permiso;
use App\Entity\Usuario;
use App\Form\Type\Institucion\InstitucionStoreType;
use App\Form\Type\Institucion\InstitucionUpdateType;
use App\Form\Type\InstitucionType;
use App\Normalizer\InstitucionPerfilNormalizerInterface;
use App\Repository\ConvenioRepositoryInterface;
use App\Service\InstitucionManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Twig\Environment as TwigEnvironment;

#[Route('/ie')]
class InstitucionController extends DIEControllerController
{
    public function __construct(
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Doctrine\ORM\EntityManagerInterface $em,
        \Symfony\Component\Serializer\SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
        private readonly MailerInterface $mailer,
        private readonly TwigEnvironment $twig,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly string $mailerSender = 'no_contestar@educacionensalud.imss.gob.mx',
    ) {
        parent::__construct($requestStack, $em, $serializer);
    }

    #[Route('/perfil', name: 'ie#perfil', methods: ['POST', 'GET'])]
    #[IsGranted('ROLE_IE')]
    public function perfil(
        Request $request,
        InstitucionManagerInterface $institucionManager,
        ConvenioRepositoryInterface $convenioRepository,
        InstitucionPerfilNormalizerInterface $institucionPerfilNormalizer
    ): Response {
        /** @var Institucion $institucion */
        $institucion = $this->getUser()->getInstitucion();
        if (!$institucion) {
            $this->createNotFindUserRelationWithInstitucionException();
        }

        $form = $this->createForm(InstitucionType::class, $institucion, [
            'action' => $this->generateUrl('ie#perfil', ['id' => $institucion->getId()]),
        ]);
        $form->add('razonSocial');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $institucionManager->update($form->getData());
            $this->addFlash('success', 'Se han actualizado correctamente los datos de contacto de la institución');

            return $institucion->isConfirmacionInformacion()
                ? $this->redirectToRoute('ie#inicio')
                : $this->redirectToRoute('ie#perfil');
        }

        $convenios = $convenioRepository->getAllNivelesByConvenio($institucion->getId());
        $config    = $this->em->getRepository(ConfiguracionGlobal::class)
            ->findOneBy(['clave' => 'ASESOR_INSTITUCION']);

        return $this->render('ie/institucion/perfil.html.twig', [
            'convenios'          => $institucionPerfilNormalizer->normalizeConvenios($convenios),
            'institucion'        => $institucionPerfilNormalizer->normalizeInstitucion($institucion),
            'errores'            => $this->getFormErrors($form),
            'asesorInstitucion'  => $config?->getValor(),
        ]);
    }

    public function menu(): Response
    {
        /** @var Usuario $user */
        $user = $this->getUser();

        return $this->render('ie/institucion/_menu.twig', [
            'institucion' => $user->getInstitucion(),
        ]);
    }

    #[Route('/api/institucion', name: 'ie.api.index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $data = $this->em->getRepository(Institucion::class)
            ->searchByTipoAndNombre(
                $request->get('tipo', 1),
                $request->get('nombre')
            );

        return new JsonResponse([
            'data' => $this->normalizer->normalize($data, 'json', [
                'attributes' => ['id', 'nombre'],
            ]),
        ]);
    }

    #[Route('/api/institucion/{id}', name: 'ie.api.show', methods: ['GET'])]
    public function show(Request $request, int $id): Response
    {
        $data = $this->em->getRepository(Institucion::class)->find($id);

        if (!$data) {
            throw $this->createNotFoundException('Institución');
        }

        return new JsonResponse([
            'data' => $this->normalizer->normalize($data, 'json', [
                'attributes' => [
                    'id', 'nombre', 'telefono', 'correo', 'representante',
                    'rfc', 'direccion', 'areaEstudio', 'tipoUsuario',
                    'usuario' => ['id'],
                ],
            ]),
        ]);
    }

    #[Route('/api/institucion/{id}', name: 'ie.api.update', methods: ['POST'])]
    public function update(Request $request, int $id): Response
    {
        $data = $this->em->getRepository(Institucion::class)->find($id);

        if (!$data) {
            throw $this->createNotFoundException('Institución');
        }

        $formulario = $this->createForm(InstitucionUpdateType::class, $data);
        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $this->em->persist($data);
            $this->em->flush();

            if ($data->getUsuario() === null) {
                $this->createUsuarioInstitucion($data, true);
            }

            return new JsonResponse(['status' => true]);
        }

        return new JsonResponse([
            'status'  => false,
            'message' => 'Error',
            'errors'  => $this->getFormErrors($formulario),
        ], 400);
    }

    #[Route('/api/institucion', name: 'ie.api.store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        $formulario = $this->createForm(InstitucionStoreType::class);
        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $data = $formulario->getData();
            $this->em->persist($data);
            $this->em->flush();
            $this->createUsuarioInstitucion($data);

            return new JsonResponse([
                'status' => true,
                'data'   => $this->normalizer->normalize($data, 'json', [
                    'attributes' => [
                        'id', 'nombre', 'telefono', 'correo', 'representante',
                        'rfc', 'direccion', 'areaEstudio', 'tipoUsuario',
                    ],
                ]),
            ]);
        }

        return new JsonResponse([
            'status'  => false,
            'message' => 'Error',
            'errors'  => $this->getFormErrors($formulario),
        ], 400);
    }

    private function createUsuarioInstitucion(Institucion $institucion, bool $updateAction = false): void
    {
        $permiso = $this->em->getRepository(Permiso::class)->findOneBy(['clave' => 'IE']);
        $usuario = new Usuario();
        $usuario->setMatricula($institucion->getCorreo());
        $usuario->setNombre('');
        $usuario->setApellidoPaterno('');
        $usuario->setApellidoMaterno('');
        $usuario->setActivo(true);
        $usuario->setCorreo($institucion->getCorreo());
        $usuario->addPermiso($permiso);
        $usuario->setContrasena(
            $this->passwordHasher->hashPassword($usuario, '12345678')
        );

        $this->em->persist($usuario);
        $this->em->flush();

        $institucion->setUsuario($usuario);
        if (!$updateAction) {
            $institucion->setRepresentante('');
            $institucion->setDireccion('');
        }

        $this->em->persist($institucion);
        $this->em->flush();

        $email = (new Email())
            ->from($this->mailerSender)
            ->to($institucion->getCorreo())
            ->subject('Sistema de Administración del FOFOE - Bienvenido')
            ->html($this->twig->render('emails/ie/bienvenida.html.twig', ['ie' => $institucion]));

        $this->mailer->send($email);
    }
}
