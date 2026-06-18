<?php

namespace App\Security;

use App\Entity\Usuario;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCaptchaChecker implements UserCheckerInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {}

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof Usuario) {
            return;
        }

        $session = $this->requestStack->getSession();
        $captcha = $this->requestStack->getCurrentRequest()?->request->get('captcha');

        /*
        if (empty($session->get('captcha_phrase'))) {
            throw new NotFoundHttpException();
        }

        if ($session->get('captcha_phrase') !== mb_strtolower($captcha)) {
            throw new CustomUserMessageAuthenticationException(
                'El código no coincide con el que ingresaste. Por favor vuelve a intentarlo o genere uno nuevo.'
            );
        }
        */
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        if (!$user instanceof Usuario) {
            return;
        }
    }
}
