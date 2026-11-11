<?php

namespace App\Controller\Admin\Camex;

use App\Controller\DIEControllerController;
use App\Entity\Campus;
use App\Entity\Delegacion;
use App\Entity\Institucion;
use App\Entity\Permiso;
use App\Entity\Rol;
use App\Entity\Usuario;
use App\Form\Type\Admin\Came\UsuarioType;
use Carbon\Carbon;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/convenioscame/institucion')]
class UsuarioController extends DIEControllerController
{
    #[Route(
        '/{institucionId}/usuario',
        name: 'api.came.institucion.usuario.store',
        requirements: ['institucionId' => '\d+'],
        methods: ['POST']
    )]
    public function store(
        Request $request,
        int $institucionId,
        MailerInterface $mailer,
        UserPasswordHasherInterface $passwordHasher,
        #[Autowire(param: 'mailer_sender')] string $mailerSender,
    ): JsonResponse {
        /** @var Usuario $currentUser */
        $currentUser = $this->getUser();
        $isJdes = $this->isJdes();

        $delegacion = $this->resolveUserDelegacion($currentUser, $isJdes);

        if (!$delegacion) {
            return $this->httpErrorResponse('El usuario no tiene una delegación asignada', Response::HTTP_FORBIDDEN);
        }

        $institucion = $this->em->getRepository(Institucion::class)->find($institucionId);

        if (!$institucion) {
            return $this->httpErrorResponse('Institucion no encontrada', Response::HTTP_NOT_FOUND);
        }

        /*
        if (!in_array($institucion->getDelegacion()->getId(), $this->resolveDelegacionIds($currentUser, $isJdes), true)) {
            return $this->httpErrorResponse('Sin permisos para realizar esta acción', Response::HTTP_FORBIDDEN);
        }
        */

        $form = $this->createForm(UsuarioType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return new JsonResponse([
                'status'  => false,
                'message' => 'Error',
                'errors'  => $this->getFormErrors($form, true),
            ], Response::HTTP_BAD_REQUEST);
        }

        $data = $form->getData();
        $usersByCampus = (bool) $data['usersByCampus'];
        $campus = null;

        if ($usersByCampus) {
            $campus = $this->em->getRepository(Campus::class)->find($data['campus']);
            $institucion->setUsersByCampus(true);
            $institutionUser = $institucion->getUsuarioByOADDAndCampus($delegacion, $campus);
        } else {
            $institutionUser = $institucion->getUsuarioByOOAD($delegacion);
        }

        $isNew = false;

        if (!$institutionUser) {
            $isNew = true;
            $institutionUser = new Usuario();
            $institutionUser->setDelegacionInstitucion($delegacion);

            if ($usersByCampus) {
                $institutionUser->setCampus($campus);
            }
        }

        $institutionUser->setNombre($data['name']);
        $institutionUser->setApellidoPaterno($data['last_name']);
        $institutionUser->setApellidoMaterno($data['other_name']);

        if (!empty($data['password'])) {
            $institutionUser->setPlainPassword($data['password']);
            $institutionUser->setContrasena(
                $passwordHasher->hashPassword($institutionUser, $data['password'])
            );
        }

        if ($isNew) {
            $delegacionCode = substr($delegacion->getClaveDelegacional() ?? '', 0, 2);
            $registrationNumber = $delegacionCode . $institucion->getRfc() . substr(md5((string) Carbon::now()), 0, 4);

            if ($this->em->getRepository(Usuario::class)->findOneBy(['matricula' => $registrationNumber])) {
                $registrationNumber = 'X' . $registrationNumber;
            }

            $institutionUser->setMatricula($registrationNumber);
            $institutionUser->setCurp(substr(md5((string) Carbon::now()), 0, 18));
            $institutionUser->setActivo(true);
            $this->assignInstitutionRole($institutionUser);
            $institutionUser->setInstitucion($institucion);

            if (!$institucion->getUsuario()) {
                $institucion->setUsuario($institutionUser);
            }
        }

        $institutionUser->setCorreo($data['correo']);

        $this->em->persist($institutionUser);
        $this->em->flush();

        try {
            $this->sendWelcomeEmail($mailer, $mailerSender, $institutionUser);
        } catch (\Exception) {
        }

        return new JsonResponse([
            'status'  => true,
            'message' => $isNew ? 'Usuario creado con éxito' : 'Usuario actualizado con éxito',
        ]);
    }

    private function assignInstitutionRole(Usuario $user): void
    {
        $rol = $this->em->getRepository(Rol::class)->findOneBy(['clave' => 'IE']);
        $user->setRol($rol);

        $permisos = $this->em->getRepository(Permiso::class)->findBy(['rol' => $rol]);

        foreach ($permisos as $permiso) {
            $user->addPermiso($permiso);
        }
    }

    private function sendWelcomeEmail(MailerInterface $mailer, string $sender, Usuario $user): void
    {
        $email = (new Email())
            ->from($sender)
            ->to($user->getCorreo())
            ->bcc(
                new Address('zurgcom@gmail.com'),
                new Address('eliarteaga1977@gmail.com'),
            )
            ->subject('Sistema de Administración del FOFOE -  Credenciales de acceso')
            ->html($this->renderView('emails/admin/came/institucion/bienvenida.html.twig', [
                'usuario' => $user,
            ]));

        $mailer->send($email);
    }

    private function isJdes(): bool
    {
        return $this->isGranted('ROLE_JDES') || $this->isGranted('ROLE_JDES_MINUS');
    }

    private function resolveUserDelegacion(Usuario $user, bool $isJdes): ?Delegacion
    {
        if ($isJdes) {
            return $this->getUserUnidad()?->getDelegacion();
        }

        return $user->getDelegaciones()->first() ?: null;
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
