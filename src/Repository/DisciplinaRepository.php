<?php

namespace App\Repository;

use App\Entity\Disciplina;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\String\TruncateMode;

use function Symfony\Component\String\u;

/**
 * @extends ServiceEntityRepository<Disciplina>
 */
class DisciplinaRepository extends ServiceEntityRepository implements DisciplinaRepositoryInterface
{
    private const int DESCRIPTION_PREVIEW_LENGTH = 25;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Disciplina::class);
    }

    /**
     * Returns the active, non-deleted disciplines from the given list, keyed by id.
     *
     * @param Disciplina[]|null $disciplinas
     */
    public function getDisciplinas(
        ?array $disciplinas = null,
        string $lookingForBy = '',
        string $orderDirection = 'DESC',
        string $orderBy = 'fecha_creacion',
    ): array {
        $search = mb_strtolower(trim($lookingForBy));
        $result = [];

        foreach ($disciplinas ?? [] as $discipline) {
            if (!$discipline->getActiva() || $discipline->getBorrado()) {
                continue;
            }

            if ($search !== '' && !str_contains(mb_strtolower((string) $discipline->getNombre()), $search)) {
                continue;
            }

            $result[$discipline->getId()] = [
                'id' => $discipline->getId(),
                'nombre' => $discipline->getNombre(),
                'activa' => $discipline->getActiva(),
                'borrado' => $discipline->getBorrado(),
                'descripcion' => $this->textLimit($discipline->getDescripcion(), self::DESCRIPTION_PREVIEW_LENGTH),
                'fechaCreacion' => $discipline->getFechaCreacion(),
                'fechaModificacion' => $discipline->getFechaModificacion(),
            ];
        }

        return $result;
    }

    /**
     * Returns the number of active, non-deleted disciplines.
     */
    public function getDisciplinasCount(
        string $lookingForBy = '',
        string $orderDirection = 'DESC',
        string $orderBy = 'fecha_creacion',
    ): int {
        return (int) $this->createListQueryBuilder($lookingForBy)
            ->select('COUNT(disciplina.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Returns the active disciplines matching the given name.
     *
     * @return Disciplina[]
     */
    public function getDisciplinaPorNombre(string $name): array
    {
        return $this->createQueryBuilder('disciplina')
            ->where('LOWER(unaccent(disciplina.nombre)) = LOWER(unaccent(:nombre))')
            ->andWhere('disciplina.activa = true')
            ->andWhere('disciplina.borrado = false')
            ->setParameter('nombre', $name)
            ->getQuery()
            ->getResult();
    }

    /**
     * Truncates the given text (HTML stripped) to the given number of characters, keeping whole words.
     */
    public function textLimit(?string $text, int $limit): string
    {
        return u(strip_tags($text ?? ''))
            ->truncate($limit, ' ...', TruncateMode::WordBefore)
            ->toString();
    }

    private function createListQueryBuilder(string $lookingForBy): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('disciplina')
            ->where('disciplina.activa = true')
            ->andWhere('disciplina.borrado = false');

        if (trim($lookingForBy) !== '') {
            $queryBuilder
                ->andWhere('LOWER(disciplina.nombre) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower(trim($lookingForBy)) . '%');
        }

        return $queryBuilder;
    }
}
