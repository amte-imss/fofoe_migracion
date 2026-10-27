<?php

namespace App\Security;

use App\Entity\Enfermeria\Alumno;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class EnfermeriaAlumnoAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'enfermeria-alumno.login'
            && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $data = $request->request->all('alumno_login');

        $email = $data['email'] ?? null;
        $curp = $data['curp'] ?? null;
        $captcha = $data['captcha'] ?? null;
        $solicitudId = $request->attributes->get('solicitudId');

        if ($request->getSession()->get('captcha_phrase') != $captcha) {
            throw new CustomUserMessageAuthenticationException('El código no coincide.');
        }

        if ($request->request->get('accept_agreement') !== 'on') {
            throw new CustomUserMessageAuthenticationException('Debe aceptar las políticas.');
        }

        return new SelfValidatingPassport(
            new UserBadge($curp, function () use ($email, $curp, $solicitudId) {
                $resultSet = $this->em->getRepository(Alumno::class)->findBy([
                    'email' => $email,
                    'curp' => $curp,
                    'solicitud' => $solicitudId,
                ]);

                if (!$resultSet) {
                    throw new CustomUserMessageAuthenticationException('Credenciales incorrectas');
                }

                return $resultSet[0];
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate('enfermeria-alumno.index'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set('_enfermeria_login_error', $exception->getMessageKey());

        return new RedirectResponse(
            $this->urlGenerator->generate('enfermeria-alumno.login', [
                'solicitudId' => $request->attributes->get('solicitudId'),
            ])
        );
    }
}
