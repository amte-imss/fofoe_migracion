<?php

namespace App\Repository;

use App\Entity\ConvenioDelegacion;
use App\Entity\ConvenioEspecial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConvenioEspecial>
 */
class ConvenioEspecialRepository extends ServiceEntityRepository
{
    private const TYPE_GENERAL = 'General';
    private const TYPE_SPECIAL_PROJECT = 'Proyecto Especial';
    private const AMBIGUOUS_BUSINESS_NAME = 'ambigua';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConvenioEspecial::class);
    }

    public function getConveniosByDelegacion(int $delegacionId = 1): array
    {
        return $this->createQueryBuilder('convenio')
            ->innerJoin('convenio.cicloAcademico', 'cicloAcademico')
            ->where('convenio.delegacion = :delegacionId')
            ->andWhere('cicloAcademico.activo = true')
            ->setParameter('delegacionId', $delegacionId)
            ->getQuery()
            ->getResult();
    }

    public function getConvenioGeneral(mixed $institucionId, mixed $vigencia): ?ConvenioEspecial
    {
        return $this->createQueryBuilder('c')
            ->where('c.institucion = :institucionId')
            ->andWhere('c.vigencia >= :vigencia')
            ->setParameter('institucionId', $institucionId)
            ->setParameter('vigencia', $vigencia)
            ->orderBy('c.vigencia', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getConveniosUnicosByInstitucionId(mixed $id): array
    {
        return $this->createQueryBuilder('convenio')
            ->join('convenio.camposClinicos', 'camposClinicos')
            ->where('convenio.institucion = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

    /**
     * Counts agreements, optionally filtered by CAMEX flag and search text.
     */
    public function getCountConvenios(bool $isCamex = false, string $lookingForBy = ''): int
    {
        $queryBuilder = $this->createQueryBuilder('convenio');

        if ($isCamex) {
            $queryBuilder
                ->where('convenio.is_camex = :isCamex')
                ->setParameter('isCamex', true);
        }

        if ($lookingForBy === '') {
            return (int) $queryBuilder
                ->select('COUNT(convenio.id)')
                ->getQuery()
                ->getSingleScalarResult();
        }

        // FIXME: getFilteredAgreements() is not defined in this repository
        return count($this->getFilteredAgreements($queryBuilder->getQuery()->getResult(), $lookingForBy, $isCamex));
    }

    /**
     * Deletes a CAME agreement. Returns null when the deletion succeeded.
     */
    public function deleteConvenioCame(mixed $idConvenioCame): ?ConvenioEspecial
    {
        $convenio = $this->find($idConvenioCame);

        if ($convenio) {
            $entityManager = $this->getEntityManager();
            $entityManager->remove($convenio);
            $entityManager->flush();
        }

        return $this->find($idConvenioCame);
    }

    /**
     * Checks whether a catalog name (career, institution, delegation, academic cycle) contains spaces.
     */
    public function validateStrWithWhiteSpaces(?string $stringChain): bool
    {
        return str_contains((string) $stringChain, ' ');
    }

    /**
     * Checks whether a date string contains "/" or "-".
     */
    public function dateSlashValidate(?string $dateToValidate): bool
    {
        $date = (string) $dateToValidate;

        return str_contains($date, '/') || str_contains($date, '-');
    }

    /**
     * Checks whether an agreement exists matching name, sector and validity date.
     */
    public function getConvenioByNameorSector(string $nombre, string $sector, mixed $vigencia): bool
    {
        $convenios = $this->createQueryBuilder('convenio')
            ->where("LOWER(unaccent(convenio.nombre)) LIKE CONCAT('%', LOWER(unaccent(:nombre)), '%')")
            ->andWhere("LOWER(unaccent(convenio.sector)) LIKE CONCAT('%', LOWER(unaccent(:sector)), '%')")
            ->andWhere('convenio.vigencia = :vigencia')
            ->setParameter('nombre', $nombre)
            ->setParameter('sector', $sector)
            ->setParameter('vigencia', $vigencia)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        return !empty($convenios);
    }

    /**
     * Returns the "general" agreements of the given institutions.
     * Form mode: [nombre => id]; otherwise: [id => ['id' => ..., 'nombre' => ...]].
     */
    public function getConveniosPorRfcDeInstitucion(iterable $instituciones, bool $esForm = false, mixed $idConvenio = null): array
    {
        $convenios = [];

        foreach ($instituciones as $institucion) {
            $institucionId = is_array($institucion) ? $institucion['id'] : $institucion->getId();

            foreach ($this->getConveniosPorInstitucionId($institucionId) as $convenio) {
                if (strtolower((string) $convenio->getTipo()) !== 'general') {
                    continue;
                }

                if (!$esForm) {
                    $convenios[$convenio->getId()] = [
                        'id' => $convenio->getId(),
                        'nombre' => $convenio->getNombre(),
                    ];
                    continue;
                }

                $convenios[$convenio->getNombre()] = $convenio->getId();

                if ($idConvenio !== null && $convenio->getId() == $idConvenio) {
                    break;
                }
            }
        }

        return $convenios;
    }

    /**
     * Signed day difference between the validity date and today (e.g. "-120 días").
     * Negative = days remaining, positive = days since expiration.
     */
    public function getExpiredValidity(\DateTimeInterface $fechaVigencia): string
    {
        $validityDate = \DateTimeImmutable::createFromInterface($fechaVigencia)->setTime(0, 0);

        return $validityDate->diff(new \DateTimeImmutable('today'))->format('%R%a días');
    }

    /**
     * CSS class representing the validity status.
     */
    public function getExpiredValidityClass(?\DateTimeInterface $fechaVigencia): string
    {
        if ($fechaVigencia === null) {
            return 'expiredValidityDanger';
        }

        $days = (int) str_replace(' días', '', $this->getExpiredValidity($fechaVigencia));

        return match (true) {
            $days < -365 => 'expiredValiditySuccess',
            $days <= -180 => 'expiredValidityWarn',
            default => 'expiredValidityDanger',
        };
    }

    /**
     * Converts a file size in bytes to MB.
     */
    public function conversorOfFileSize(int|float $fileSize): float
    {
        return $fileSize / 1024 / 1024;
    }

    public function getConveniosPorInstitucionId(mixed $idInstitucion): array
    {
        return $this->createQueryBuilder('convenio')
            ->where('convenio.institucion = :id')
            ->setParameter('id', $idInstitucion)
            ->getQuery()
            ->getResult();
    }

    /**
     * Parses a date (accepts "/" or "-"). Defaults to three months from today when empty.
     */
    public function getDateFormatToSave(?string $dateToSave): \DateTime
    {
        $timestamp = !empty($dateToSave)
            ? strtotime(str_replace('/', '-', $dateToSave))
            : strtotime('+3 month');

        return new \DateTime(date('Y-m-d', (int) $timestamp));
    }

    public function getRelationalGeneralAgreement(string $name): ?ConvenioEspecial
    {
        return $this->createQueryBuilder('convenio')
            ->where('convenio.nombre = :nombre')
            ->andWhere('convenio.tipo = :tipo')
            ->setParameter('nombre', $name)
            ->setParameter('tipo', self::TYPE_GENERAL)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Institution list for form selectors: [id => ['id' => ..., 'nombre' => ...]].
     */
    public function getListaInstituciones(iterable $instituciones): array
    {
        $list = [];

        foreach ($instituciones as $institucion) {
            $list[$institucion->getId()] = [
                'id' => $institucion->getId(),
                'nombre' => $institucion->getNombre(),
            ];
        }

        return $list;
    }

    /**
     * Returns the shared business name of the institutions, or "ambigua" when they differ.
     */
    public function getRazonSocialInstitucion(iterable $instituciones): string
    {
        $businessName = self::AMBIGUOUS_BUSINESS_NAME;
        $lastBusinessName = '';
        $changes = 0;

        foreach ($instituciones as $institucion) {
            if ($lastBusinessName != $institucion->getRazonSocial()) {
                $lastBusinessName = $institucion->getRazonSocial();
                $businessName = (string) $institucion->getRazonSocial();
                $changes++;
            }
        }

        return $changes > 1 ? self::AMBIGUOUS_BUSINESS_NAME : $businessName;
    }

    /**
     * Builds the FUICA code: RFC(5) + type(1) + signing year(2) + validity years(2) + id(4).
     */
    public function getCalculatedFuica(
        string $rfc,
        string $tipoConvenio,
        string|\DateTimeInterface $fechaFirmaConvenio,
        string $aniosVigencia,
        int|string $idConvenio,
    ): string {
        $years = explode(' ', $aniosVigencia)[0];
        $yearsPart = strlen($years) === 1 ? $this->addZerosToText($years, 1) : $years;

        $idPart = str_pad((string) $idConvenio, 4, '0', STR_PAD_LEFT);

        $signingYearPart = is_string($fechaFirmaConvenio)
            ? substr($fechaFirmaConvenio, 2, 2)
            : $fechaFirmaConvenio->format('y');

        return strtoupper(substr($rfc, 0, 5) . substr($tipoConvenio, 0, 1) . $signingYearPart . $yearsPart . $idPart);
    }

    /**
     * Pads a text with the given number of zeros on the left or right.
     */
    public function addZerosToText(string $text, int $zerosQty = 2, string $directionOfTheZeros = 'left'): string
    {
        $zeros = str_repeat('0', max(0, $zerosQty));

        return match ($directionOfTheZeros) {
            'left' => $zeros . $text,
            'right' => $text . $zeros,
            default => '',
        };
    }

    /**
     * Validity end date = signing date + N years (N taken from strings like "3 años").
     */
    public function getFechaVigenciaCalculada(string $aniosVigencia, string|\DateTimeInterface $fechaFirma): \DateTime
    {
        $years = (int) explode(' ', $aniosVigencia)[0];

        $date = is_string($fechaFirma)
            ? new \DateTime($fechaFirma)
            : \DateTime::createFromInterface($fechaFirma);

        return $date->setTime(0, 0)->modify(sprintf('+%d year', $years));
    }

    public function getConvenioById(mixed $idConvenio): array
    {
        return $this->createQueryBuilder('convenio')
            ->where('convenio.id = :idConvenio')
            ->setParameter('idConvenio', $idConvenio)
            ->getQuery()
            ->getResult();
    }

    /**
     * Truncates a text to the given length on a word boundary, appending " ...".
     */
    public function textLimit(string $text, int $limit): string
    {
        if (strlen($text) <= $limit) {
            return $text;
        }

        $text = strip_tags($text);
        $cut = strpos(wordwrap($text, $limit), "\n");

        return substr($text, 0, $cut === false ? $limit : $cut) . ' ...';
    }

    private function createAdminQueryBuilder(array $filters): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('convenio')
            ->join('convenio.institucion', 'institucion')
            ->leftJoin('institucion.delegacion', 'delegacionInstitucion');

        if (!empty($filters['came_delegacion'])) {
            $subQuery = $this->getEntityManager()->createQueryBuilder()
                ->select('convenioTmp.id')
                ->from(ConvenioDelegacion::class, 'delegacionConvenio')
                ->join('delegacionConvenio.delegacion', 'delegacionTmp')
                ->join('delegacionConvenio.convenio', 'convenioTmp')
                ->where('delegacionTmp.id IN (:cameDelegacion)')
                ->andWhere('convenioTmp.id = convenio.id');

            $queryBuilder
                ->andWhere('(' . $queryBuilder->expr()->in('convenio.id', $subQuery->getDQL()) . ' OR convenio.todasDelegaciones = true)')
                ->setParameter('cameDelegacion', $filters['came_delegacion']);
        }

        if (!empty($filters['search'])) {
            $queryBuilder
                ->andWhere(
                    "(UPPER(unaccent(convenio.fuica)) LIKE UPPER(unaccent(CONCAT('%', :search, '%'))) " .
                    "OR UPPER(unaccent(convenio.rfc)) LIKE UPPER(unaccent(CONCAT('%', :search, '%'))) " .
                    "OR UPPER(unaccent(convenio.razon_social)) LIKE UPPER(unaccent(CONCAT('%', :search, '%'))) " .
                    "OR UPPER(unaccent(convenio.nombre)) LIKE UPPER(unaccent(CONCAT('%', :search, '%'))))"
                )
                ->setParameter('search', $filters['search']);
        }

        if (!empty($filters['type'])) {
            match ($filters['type']) {
                'all' => null,
                'inactive' => $queryBuilder
                    ->andWhere('convenio.fecha_vence_convenio < :now')
                    ->setParameter('now', new \DateTime()),
                default => $queryBuilder
                    ->andWhere('convenio.fecha_vence_convenio >= :now')
                    ->setParameter('now', new \DateTime()),
            };
        }

        return $queryBuilder
            ->andWhere('convenio.tipo = :specialProjectType')
            ->setParameter('specialProjectType', self::TYPE_SPECIAL_PROJECT);
    }

    public function getAdminConvenios(array $filters, ?int $page = null, ?int $perPage = null): QueryBuilder
    {
        $queryBuilder = $this->createAdminQueryBuilder($filters);

        if ($page !== null && $perPage !== null) {
            $queryBuilder
                ->setFirstResult(($page - 1) * $perPage)
                ->setMaxResults($perPage);
        }

        return $queryBuilder;
    }

    public function paginateAdminConvenios(array $filters, ?int $page = null, ?int $perPage = null): array
    {
        $total = (int) $this->createAdminQueryBuilder($filters)
            ->select('COUNT(convenio.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $data = $this->getAdminConvenios($filters, $page, $perPage)
            ->orderBy('convenio.numero')
            ->getQuery()
            ->getResult();

        return [
            'data' => $data,
            'perPage' => $perPage,
            'page' => $page,
            'total' => $total,
        ];
    }

    public function nextNumber(): int
    {
        $max = $this->createAdminQueryBuilder(['type' => 'all'])
            ->select('MAX(convenio.numero)')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $max + 1;
    }

    public function getConveniosGeneralesVigentesByInstitucion(mixed $institucionId): array
    {
        return $this->createQueryBuilder('convenio')
            ->where('convenio.institucionId = :institucionId')
            ->andWhere('convenio.tipo = :tipo')
            ->andWhere('convenio.fecha_vence_convenio >= :date')
            ->andWhere('convenio.cancelacionAnticipada = false')
            ->setParameter('institucionId', $institucionId)
            ->setParameter('tipo', self::TYPE_GENERAL)
            ->setParameter('date', new \DateTime())
            ->orderBy('convenio.numero')
            ->getQuery()
            ->getResult();
    }
}
