<?php

namespace App\Twig;

use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SecurityRolesExtension extends AbstractExtension
{
    public function __construct(private readonly Security $security)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_granted_any', $this->isGrantedAny(...)),
            new TwigFunction('is_granted_all', $this->isGrantedAll(...)),
        ];
    }

    /** @param string[] $attributes */
    public function isGrantedAny(array $attributes, mixed $subject = null): bool
    {
        foreach ($attributes as $attribute) {
            if ($this->security->isGranted($attribute, $subject)) {
                return true;
            }
        }

        return false;
    }

    /** @param string[] $attributes */
    public function isGrantedAll(array $attributes, mixed $subject = null): bool
    {
        foreach ($attributes as $attribute) {
            if (!$this->security->isGranted($attribute, $subject)) {
                return false;
            }
        }

        return true;
    }
}
