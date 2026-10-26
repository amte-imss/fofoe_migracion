<?php

namespace App\Repository;

use App\Entity\Disciplina;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<Disciplina>
 */
interface DisciplinaRepositoryInterface extends ObjectRepository
{
    /**
     * Returns the disciplines list.
     *
     * @param Disciplina[]|null $disciplinas
     */
    public function getDisciplinas(
        ?array $disciplinas = null,
        string $lookingForBy = '',
        string $orderDirection = 'DESC',
        string $orderBy = 'fecha_creacion',
    ): array;

    /**
     * Returns the number of disciplines.
     */
    public function getDisciplinasCount(
        string $lookingForBy = '',
        string $orderDirection = 'DESC',
        string $orderBy = 'fecha_creacion',
    ): int;

    /**
     * Returns the disciplines matching the given name.
     *
     * @return Disciplina[]
     */
    public function getDisciplinaPorNombre(string $name): array;

    /**
     * Truncates the given text to the given number of characters.
     */
    public function textLimit(string $text, int $limit): string;
}
