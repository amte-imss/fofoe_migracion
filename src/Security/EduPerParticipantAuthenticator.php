<?php

namespace App\Security;

use App\Entity\EduPer\Solicitud;
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
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class EduPerParticipantAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UrlGeneratorInterface $urls,
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'edu-per.login' && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $login = $request->request->all('login');
        $id = (int) $request->attributes->get('solicitudId');
        if (!$id || !$request->getSession()->get('captcha_phrase') ||
            !hash_equals((string) $request->getSession()->get('captcha_phrase'), (string) ($login['captcha'] ?? ''))) {
            throw new CustomUserMessageAuthenticationException('El código no coincide.');
        }
        if ($request->request->get('accept_agreement') !== 'on') {
            throw new CustomUserMessageAuthenticationException('Debe aceptar las políticas.');
        }

        return new SelfValidatingPassport(new UserBadge((string) $id, function () use ($id, $login) {
            $user = $this->em->getRepository(Solicitud::class)->find($id);
            $email = (string) ($login['email'] ?? '');
            $password = (string) ($login['password'] ?? '');
            if (!$user || !hash_equals(mb_strtolower((string) $user->getEmail()), mb_strtolower($email)) ||
                !hash_equals((string) $user->getPassword(), hash('sha512', $password))) {
                throw new CustomUserMessageAuthenticationException('Credenciales incorrectas.');
            }
            return $user;
        }));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $request->getSession()->set('is_edu-per_participant', true);
        $request->getSession()->set('edu-per_participant_id', $token->getUser()->getId());
        return new RedirectResponse($this->urls->generate('edu-per.participant.index'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set('_edu_per_login_error', $exception->getMessageKey());
        return new RedirectResponse($this->urls->generate('edu-per.login', ['solicitudId' => $request->attributes->get('solicitudId')]));
    }
}
