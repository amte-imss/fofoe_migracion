<?php

namespace App\Vich\Naming;

use App\Entity\Posgrado\Residencia;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

class ResidenteDirectoryNamer implements DirectoryNamerInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
    ) {}

    public function directoryName(object|array $object, PropertyMapping $mapping): string
    {
        if ($object instanceof Residencia) {
            return (string) $object->getResidente()->getId();
        }

        return '';
    }
}
