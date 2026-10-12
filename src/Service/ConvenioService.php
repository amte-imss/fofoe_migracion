<?php

namespace App\Service;

use App\Entity\Convenio;
use App\Entity\Disciplina;
use Doctrine\ORM\EntityManagerInterface;

class ConvenioService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function storeDisciplina(Convenio $convenio, string $disciplineName): void
    {
        $discipline = new Disciplina();
        $discipline->setNombre($disciplineName);

        $this->entityManager->persist($discipline);
        $this->entityManager->flush();

        $convenio->setDisciplina($discipline);
    }
}
