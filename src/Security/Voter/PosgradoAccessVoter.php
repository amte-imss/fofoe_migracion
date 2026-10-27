<?php

namespace App\Security\Voter;

use App\Entity\ConfiguracionGlobal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class PosgradoAccessVoter extends Voter
{
    public const string ACCESS = 'POSGRADO_ACCESS';

    private const array CAME_ROLES = ['ROLE_CAME', 'ROLE_CAME_MINUS', 'ROLE_JDES', 'ROLE_JDES_MINUS'];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::ACCESS === $attribute;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if (!$token->getUser() instanceof UserInterface) {
            return false;
        }

        $isCameUser = array_any(
            self::CAME_ROLES,
            fn(string $role): bool => $this->accessDecisionManager->decide($token, [$role])
        );

        if (!$isCameUser) {
            return true;
        }

        $config = $this->em->getRepository(ConfiguracionGlobal::class)
            ->findOneBy(['clave' => ConfiguracionGlobal::POSGRADO_CAME]);

        return (int) ($config?->getValor() ?? 0) !== 0;
    }
}
