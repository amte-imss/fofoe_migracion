<?php

namespace App\Repository;

use App\Entity\Convenio;
use App\Entity\Institucion as EntityInstitucion;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ConvenioRepository extends ServiceEntityRepository implements ConvenioRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Convenio::class);
    }

    public function getAllNivelesByConvenio(int $id): array
    {
        return $this->createQueryBuilder('convenio')
            ->leftJoin('convenio.carrera', 'carrera')
            ->leftJoin('carrera.nivelAcademico', 'nivel_academico')
            ->innerJoin('convenio.conveniosCiclosAcademicos', 'convenio_ciclos')
            ->innerJoin('convenio_ciclos.cicloAcademico', 'ciclo_academico')
            ->where('convenio.institucion = :id')
            ->andWhere('ciclo_academico.id <> :idCiclosServicioSoscial')
            ->setParameter('id', $id)
            ->setParameter('idCiclosServicioSoscial', 3)
            ->orderBy('convenio.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getAllBySolicitud(int $solicitud_id): array
    {
        return $this->createQueryBuilder('convenio')
            ->join('convenio.camposClinicos', 'campo_clinico')
            ->join('campo_clinico.solicitud', 'solicitud')
            ->where('solicitud.id = :solicitud_id')
            ->setParameter('solicitud_id', $solicitud_id)
            ->getQuery()
            ->getResult();
    }

    public function getConveniosByDelegacion(int $delegacion_id = 1): array
    {
        return $this->createQueryBuilder('convenio')
            ->innerJoin('convenio.cicloAcademico', 'cicloAcademico')
            ->where('convenio.delegacion = :delegacion_id')
            ->andWhere('cicloAcademico.activo = true')
            ->setParameter('delegacion_id', $delegacion_id)
            ->getQuery()
            ->getResult();
    }

    public function getConvenioGeneral(int $institucion_id, string $vigencia): mixed
    {
        return $this->createQueryBuilder('c')
            ->where('c.institucion = :institucion_id')
            ->andWhere('c.vigencia >= :vigencia')
            ->setParameter('institucion_id', $institucion_id)
            ->setParameter('vigencia', $vigencia)
            ->orderBy('c.vigencia', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getConveniosUnicosByInstitucionId(int $id): array
    {
        return $this->createQueryBuilder('convenio')
            ->join('convenio.camposClinicos', 'camposClinicos')
            ->where('convenio.institucion = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

    public function getAllConvenios(
        bool $isCamex = false,
        mixed $allDescription = false,
        mixed $lookingForBy = '',
        string $orderDirecctionBy = 'DESC',
        string $orderBy = 'nombre'
    ): array {
        $convenios = $this->createQueryBuilder('convenio')
            ->orderBy('convenio.' . $orderBy, $orderDirecctionBy);

        if ($isCamex) {
            $convenios->where('convenio.is_camex = :isCamex')
                ->setParameter('isCamex', true);
        }

        $convenios = $convenios->getQuery()->getResult();

        if (empty($convenios)) {
            return [];
        }

        if (!empty($lookingForBy)) {
            return $this->getFilteredAgreements($convenios, $lookingForBy, $isCamex);
        }

        $conveniosList = [];
        foreach ($convenios as $objConvenio) {
            $conveniosList[$objConvenio->getId()] = $this->mapConvenioToArray($objConvenio, $isCamex, $allDescription);
        }

        return $conveniosList;
    }

    public function getCountConvenios(bool $isCamex = false, mixed $lookingForBy = ''): int
    {
        $qb = $this->createQueryBuilder('convenio');

        if ($isCamex) {
            $qb->where('convenio.is_camex = :isCamex')->setParameter('isCamex', true);
        }

        $convenios = $qb->getQuery()->getResult();

        if ($lookingForBy !== '') {
            $convenios = $this->getFilteredAgreements($convenios, $lookingForBy, $isCamex);
        }

        return count($convenios);
    }

    public function deleteConvenioCame(int $idConvenioCame): mixed
    {
        $convenio = $this->find($idConvenioCame);

        if (!empty($convenio)) {
            $this->getEntityManager()->remove($convenio);
            $this->getEntityManager()->flush();
        }

        return $this->find($idConvenioCame);
    }

    public function validateStrWithWhiteSpaces(string $stringChain): bool
    {
        return str_contains($stringChain, ' ');
    }

    public function dateSlashValidate(string $dateToValidate): bool
    {
        return str_contains($dateToValidate, '/') || str_contains($dateToValidate, '-');
    }

    public function getConvenioByNameorSector(string $nombre, string $sector, string $vigencia): mixed
    {
        $where = "LOWER(unaccent(convenio.nombre)) LIKE CONCAT('%', LOWER(unaccent('" . $nombre . "')),'%') AND
        LOWER(unaccent(convenio.sector)) LIKE CONCAT('%',LOWER(unaccent('" . $sector . "')),'%') AND
        convenio.vigencia = '" . $vigencia . "'";

        $convenios = $this->createQueryBuilder('convenio')
            ->where($where)
            ->getQuery()
            ->getResult();

        return !empty($convenios);
    }

    public function getFilteredAgreements(array $convenios, mixed $lookingForBy, bool $isCamex = false, mixed $allDescription = false): array
    {
        $conveniosList = [];
        $search        = mb_strtolower($lookingForBy, 'UTF-8');

        foreach ($convenios as $objConvenio) {
            $tipo = $isCamex ? $objConvenio->getTipoCame() : $objConvenio->getTipo();

            $matches =
                str_contains(mb_strtolower($objConvenio->getNombre(), 'UTF-8'), $search) ||
                str_contains(mb_strtolower($objConvenio->getSector(), 'UTF-8'), $search) ||
                str_contains(mb_strtolower($tipo, 'UTF-8'), $search) ||
                ($objConvenio->getCarrera() && str_contains(mb_strtolower($objConvenio->getCarrera()->getNombre(), 'UTF-8'), $search)) ||
                ($objConvenio->getDelegacion() && str_contains(mb_strtolower($objConvenio->getDelegacion()->getNombre(), 'UTF-8'), $search)) ||
                ($objConvenio->getDelegacion() && str_contains(mb_strtolower($objConvenio->getDelegacion()->getGrupoDelegacion(), 'UTF-8'), $search)) ||
                ($objConvenio->getCicloAcademico() && str_contains(mb_strtolower($objConvenio->getCicloAcademico()->getNombre(), 'UTF-8'), $search)) ||
                ($objConvenio->getInstitucion() && str_contains(mb_strtolower($objConvenio->getInstitucion()->getNombre(), 'UTF-8'), $search));

            if ($matches) {
                $conveniosList[$objConvenio->getId()] = $this->mapConvenioToArray($objConvenio, $isCamex, $allDescription);
            }
        }

        return $conveniosList;
    }

    public function getConveniosPorRfcDeInstitucion(mixed $instituciones, bool $esForm = false, ?int $idConvenio = null): array
    {
        $convenios = [];

        foreach ($instituciones as $objInstitucion) {
            $id          = is_array($objInstitucion) ? $objInstitucion['id'] : $objInstitucion->getId();
            $objConvenio = $this->getConveniosPorInstitucionId($id);

            foreach ($objConvenio as $objConvenioRetrieved) {
                if (strtolower($objConvenioRetrieved->getTipo()) !== 'general') continue;

                if ($esForm) {
                    if (!is_null($idConvenio) && $objConvenioRetrieved->getId() == $idConvenio) {
                        $convenios[$objConvenioRetrieved->getNombre()] = $objConvenioRetrieved->getId();
                        break;
                    }
                    $convenios[$objConvenioRetrieved->getNombre()] = $objConvenioRetrieved->getId();
                } else {
                    $convenios[$objConvenioRetrieved->getId()] = [
                        'id'     => $objConvenioRetrieved->getId(),
                        'nombre' => $objConvenioRetrieved->getNombre(),
                    ];
                }
            }
        }

        return $convenios;
    }

    public function getExpiredValidity(\DateTimeInterface $fechaVigencia): int
    {
        $datetime1 = new \DateTime($fechaVigencia->format('Y-m-d'));
        $datetime2 = new \DateTime(date('Y-m-d'));

        return (int) $datetime1->diff($datetime2)->format('%R%a');
    }

    public function getExpiredValidityClass(\DateTimeInterface $fechaVigencia = null): string
    {
        if (empty($fechaVigencia)) {
            return '';
        }

        $vencio = $this->getExpiredValidity($fechaVigencia);

        return match(true) {
            $vencio < -365                    => 'expiredValiditySuccess',
            $vencio > -365 && $vencio <= -180 => 'expiredValidityWarn',
            $vencio > -180                    => 'expiredValidityDanger',
            default                           => '',
        };
    }

    public function conversorOfFileSize(int $fileSize): string
    {
        return (string) (($fileSize / 1024) / 1024);
    }

    public function getConveniosPorInstitucionId(int $idInstitucion): array
    {
        return $this->createQueryBuilder('convenio')
            ->where('convenio.institucion = :id')
            ->setParameter('id', $idInstitucion)
            ->getQuery()
            ->getResult();
    }

    public function getDateFormatToSave(mixed $dateToSave): ?\DateTimeInterface
    {
        $fecha = str_replace('/', '-', $dateToSave);

        return !empty($dateToSave)
            ? \DateTime::createFromFormat('Y-m-d', date('Y-m-d', strtotime($fecha)))
            : \DateTime::createFromFormat('Y-m-d', date('Y-m-d', strtotime('+3 month')));
    }

    public function getRelationalGeneralAgreement(string $name): mixed
    {
        return $this->createQueryBuilder('convenio')
            ->where('convenio.nombre = :nombre')
            ->andWhere("convenio.tipo = 'General'")
            ->setParameter('nombre', $name)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getListaInstituciones(mixed $instituciones): array
    {
        $list = [];
        foreach ($instituciones as $objInstitucion) {
            $list[$objInstitucion->getId()] = [
                'id'     => $objInstitucion->getId(),
                'nombre' => $objInstitucion->getNombre(),
            ];
        }
        return $list;
    }

    public function getRazonSocialInstitucion(mixed $instituciones): string
    {
        $razonSocial       = 'ambigua';
        $esLaMismaRazon    = '';
        $contadorDiferentes = 0;

        foreach ($instituciones as $objInstitucion) {
            if ($esLaMismaRazon !== $objInstitucion->getRazonSocial()) {
                $esLaMismaRazon = $objInstitucion->getRazonSocial();
                $razonSocial    = $objInstitucion->getRazonSocial();
                $contadorDiferentes++;
            }
        }

        return $contadorDiferentes > 1 ? 'ambigua' : $razonSocial;
    }

    public function getCalculatedFuica(string $rfc, string $tipoConvenio, mixed $fechaFirmaConvenio, string $aniosVigencia, int $idConvenio): string
    {
        $rfcSub           = substr($rfc, 0, 5);
        $tipoConvenioSub  = substr($tipoConvenio, 0, 1);
        $explode          = explode(' ', $aniosVigencia);
        $aniosVigenciaSub = strlen($explode[0]) === 1 ? $this->addZerosToText($explode[0], 1) : $explode[0];
        $idConvenioSub    = strlen((string) $idConvenio) < 4 ? $this->addZerosToText($idConvenio, 4 - strlen((string) $idConvenio)) : $idConvenio;

        $fechaFirmaConvenioSub = is_string($fechaFirmaConvenio)
            ? substr($fechaFirmaConvenio, 2, 2)
            : $fechaFirmaConvenio->format('y');

        return strtoupper($rfcSub . $tipoConvenioSub . $fechaFirmaConvenioSub . $aniosVigenciaSub . $idConvenioSub);
    }

    public function addZerosToText(string $text, int $zerosQty = 2, string $directionOfTheZeros = 'left'): string
    {
        $zeros = str_repeat('0', $zerosQty);

        return $directionOfTheZeros === 'left' ? $zeros . $text : $text . $zeros;
    }

    public function getFechaVigenciaCalculada(string $aniosVigencia, \DateTimeInterface $fechaFirma): \DateTimeInterface
    {
        $explode        = explode(' ', $aniosVigencia);
        $fechaFirmaStr  = $fechaFirma instanceof \DateTimeInterface
            ? $fechaFirma->format('Y-m-d')
            : $fechaFirma;

        return \DateTime::createFromFormat('Y-m-d',
            date('Y-m-d', strtotime('+' . $explode[0] . ' year', strtotime($fechaFirmaStr)))
        );
    }

    public function getConvenioById(int $idConvenio): mixed
    {
        return $this->createQueryBuilder('convenio')
            ->where('convenio.id = :idConvenio')
            ->setParameter('idConvenio', $idConvenio)
            ->getQuery()
            ->getResult();
    }

    public function textLimit(string $text, int $limit): string
    {
        if (strlen($text) <= $limit) {
            return $text;
        }

        $text = strip_tags($text);
        return substr($text, 0, strpos(wordwrap($text, $limit), "\n")) . ' ...';
    }

    public function getAdminConvenios(array $filters, ?int $page = null, ?int $perPage = null): mixed
    {
        $qb = $this->createAdminQueryBuilder($filters);

        if (!is_null($page) && !is_null($perPage)) {
            $qb->setFirstResult(($page - 1) * $perPage)->setMaxResults($perPage);
        }

        return $qb;
    }

    public function paginateAdminConvenios(array $filters, ?int $page = null, ?int $perPage = null): array
    {
        $total = (int) (clone $this->createAdminQueryBuilder($filters))
            ->select('count(convenio.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $data = $this->getAdminConvenios($filters, $page, $perPage)
            ->orderBy('convenio.numero')
            ->getQuery()
            ->getResult();

        return ['data' => $data, 'perPage' => $perPage, 'page' => $page, 'total' => $total];
    }

    public function paginateApiConvenios(array $filters, ?int $page = null, ?int $perPage = null): mixed
    {
        $hoy  = (new \DateTime())->format('Y-m-d');
        $year = Carbon::now()->format('Y');

        $total   = (int) (clone $this->createApiQueryBuilder($filters))->select('COUNT(convenio.id)')->getQuery()->getSingleScalarResult();
        $activos = (int) (clone $this->createApiQueryBuilder($filters))->select('COUNT(convenio.id)')->andWhere('convenio.fecha_vence_convenio >= :hoy')->setParameter('hoy', $hoy)->getQuery()->getSingleScalarResult();
        $nuevos  = (int) (clone $this->createApiQueryBuilder($filters))->select('COUNT(convenio.id)')->andWhere('convenio.fecha_firma >= :fi AND convenio.fecha_firma <= :ff')->setParameter('fi', "{$year}-01-01")->setParameter('ff', "{$year}-12-31")->getQuery()->getSingleScalarResult();

        $hace1anio  = (new \DateTime())->modify('+1 year')->format('Y-m-d');
        $hace6meses = (new \DateTime())->modify('+6 months')->format('Y-m-d');

        $cntSuccess = (int) (clone $this->createApiQueryBuilder($filters))->select('COUNT(convenio.id)')->andWhere('convenio.cancelacionAnticipada = false OR convenio.cancelacionAnticipada IS NULL')->andWhere('convenio.fecha_vence_convenio >= :d1')->setParameter('d1', $hace1anio)->getQuery()->getSingleScalarResult();
        $cntWarn    = (int) (clone $this->createApiQueryBuilder($filters))->select('COUNT(convenio.id)')->andWhere('convenio.cancelacionAnticipada = false OR convenio.cancelacionAnticipada IS NULL')->andWhere('convenio.fecha_vence_convenio >= :d2')->andWhere('convenio.fecha_vence_convenio < :d3')->setParameter('d2', $hace6meses)->setParameter('d3', $hace1anio)->getQuery()->getSingleScalarResult();
        $cntDanger  = (int) (clone $this->createApiQueryBuilder($filters))->select('COUNT(convenio.id)')->andWhere('convenio.cancelacionAnticipada = false OR convenio.cancelacionAnticipada IS NULL')->andWhere('convenio.fecha_vence_convenio < :d4')->setParameter('d4', $hace6meses)->getQuery()->getSingleScalarResult();

        $data = $this->getApiConvenios($filters, $page, $perPage)->orderBy('convenio.numero')->getQuery()->getResult();

        $result = [
            'data'                    => $data,
            'total_vigentes'          => $activos,
            'total_nuevos'            => $nuevos,
            'total_inactivos'         => $total - $activos,
            'indicadoresVencimiento'  => ['Vigentes' => $cntSuccess, 'Por Vencer' => $cntWarn, 'Críticos' => $cntDanger],
            'perPage'                 => $perPage,
            'page'                    => $page,
            'total'                   => $total,
        ];

        if (empty($filters)) {
            $tiposRaw = array_map('trim', array_column(
                (clone $this->createApiQueryBuilder([]))->select('convenio.tipo')->distinct(true)->getQuery()->getResult(),
                'tipo'
            ));
            $tiposDeConvenio = array_values(array_unique(array_map(
                fn($t) => $t === 'Proyecto Especial' ? 'Especial' : $t,
                $tiposRaw
            )));
            array_unshift($tiposDeConvenio, 'todos');

            $delegacion = (clone $this->createApiQueryBuilder([]))->select('delegacionInstitucion.id', 'delegacionInstitucion.nombre')->distinct(true)->orderBy('delegacionInstitucion.id', 'ASC')->getQuery()->getResult();
            array_unshift($delegacion, ['id' => -1, 'nombre' => 'todos']);

            $cancelados  = (int) (clone $this->createApiQueryBuilder([]))->select('COUNT(convenio.id)')->andWhere('convenio.cancelacionAnticipada = true')->getQuery()->getSingleScalarResult();
            $activos2    = (int) (clone $this->createApiQueryBuilder([]))->select('COUNT(convenio.id)')->andWhere('convenio.cancelacionAnticipada = false OR convenio.cancelacionAnticipada IS NULL')->andWhere('convenio.fecha_vence_convenio >= :hoy')->setParameter('hoy', $hoy)->getQuery()->getSingleScalarResult();
            $inactivos2  = (int) (clone $this->createApiQueryBuilder([]))->select('COUNT(convenio.id)')->andWhere('convenio.cancelacionAnticipada = false OR convenio.cancelacionAnticipada IS NULL')->andWhere('convenio.fecha_vence_convenio < :hoy')->setParameter('hoy', $hoy)->getQuery()->getSingleScalarResult();

            $estatusTraducidos = ['Todos'];
            if ($activos2 > 0) $estatusTraducidos[]   = 'Activo';
            if ($inactivos2 > 0) $estatusTraducidos[]  = 'Inactivo';
            if ($cancelados > 0) $estatusTraducidos[]  = 'Cancelado';

            $result['tipo_convenios']   = $tiposDeConvenio;
            $result['estatus_convenios'] = $estatusTraducidos;
            $result['ooad']             = $delegacion;
        }

        return $result;
    }

    public function getApiConvenios(array $filters, ?int $page = null, ?int $perPage = null): QueryBuilder
    {
        $qb = $this->createApiQueryBuilder($filters);

        if (!is_null($page) && !is_null($perPage)) {
            $qb->setFirstResult(($page - 1) * $perPage)->setMaxResults($perPage);
        }

        return $qb;
    }

    public function nextNumber(): int
    {
        $result = $this->createQueryBuilder('convenio')
            ->where('convenio.numero is not null')
            ->orderBy('convenio.numero', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->setLockMode(\Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE)
            ->getOneOrNullResult();

        return $result ? $result->getNumero() + 1 : 1;
    }

    public function getConveniosGeneralesVigentesByInstitucion(mixed $institucionId): array
    {
        return $this->createQueryBuilder('convenio')
            ->where('convenio.institucionId = :institucion_id')
            ->andWhere("convenio.tipo = 'General'")
            ->andWhere('convenio.fecha_vence_convenio >= :date')
            ->andWhere('convenio.cancelacionAnticipada = false')
            ->setParameter('institucion_id', $institucionId)
            ->setParameter('date', new \DateTime())
            ->orderBy('convenio.numero')
            ->getQuery()
            ->getResult();
    }

    private function createAdminQueryBuilder(array $filters): QueryBuilder
    {
        $qb = $this->createQueryBuilder('convenio')
            ->join('convenio.institucion', 'institucion')
            ->leftJoin('institucion.delegacion', 'delegacionInstitucion')
            ->where('1 = 1');

        if (!empty($filters['came_delegacion'])) {
            $sub = $this->_em->createQueryBuilder()
                ->from('App\Entity\ConvenioDelegacion', 'delegacionConvenio')
                ->select('convenioTmp.id')
                ->join('delegacionConvenio.delegacion', 'delegacionTmp')
                ->join('delegacionConvenio.convenio', 'convenioTmp')
                ->where('delegacionTmp.id in (:cameDelegacion)')
                ->andWhere('convenioTmp.id = convenio.id');

            $qb->where('(' . $qb->expr()->in('convenio.id', $sub->getDQL()) . ' OR convenio.todasDelegaciones = true)')
                ->setParameter('cameDelegacion', $filters['came_delegacion']);
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $qb->andWhere('(' .
                'upper(unaccent(convenio.fuica)) like upper(unaccent(concat(\'%\', :fuica,\'%\'))) ' .
                'OR upper(unaccent(convenio.rfc)) like upper(unaccent(concat(\'%\', :rfc, \'%\'))) ' .
                'OR upper(unaccent(convenio.razon_social)) like upper(unaccent(concat(\'%\', :razon_social, \'%\'))) ' .
                'OR upper(unaccent(convenio.nombre)) like upper(unaccent(concat(\'%\', :nombre, \'%\'))) ' .
                'OR upper(unaccent(institucion.nombre)) like upper(unaccent(concat(\'%\', :nombre_institucion, \'%\'))) ' .
                ')')
                ->setParameter('fuica', $search)
                ->setParameter('rfc', $search)
                ->setParameter('razon_social', $search)
                ->setParameter('nombre', $search)
                ->setParameter('nombre_institucion', $search);
        }

        if (!empty($filters['startDate'])) {
            $qb->andWhere('convenio.vigencia >= :startDate')->setParameter('startDate', $filters['startDate']);
        }
        if (!empty($filters['endDate'])) {
            $qb->andWhere('convenio.vigencia <= :endDate')->setParameter('endDate', $filters['endDate']);
        }

        $now = new \DateTime();
        match ($filters['type'] ?? 'active') {
            'canceled'  => $qb->andWhere('convenio.cancelacionAnticipada = true'),
            'inactive'  => $qb->andWhere('convenio.cancelacionAnticipada = false and convenio.fecha_vence_convenio < :now')->setParameter('now', $now),
            'intencion' => $qb->andWhere('convenio.fechaCartaIntencion is not null and convenio.cartaIntencion = true'),
            'all'       => null,
            default     => $qb->andWhere('convenio.cancelacionAnticipada = false and convenio.fecha_vence_convenio >= :now')->setParameter('now', $now),
        };

        $qb->andWhere("convenio.tipo <> 'Proyecto Especial'");

        return $qb;
    }

    private function createApiQueryBuilder(array $filters): QueryBuilder
    {
        $qb = $this->createQueryBuilder('convenio')
            ->join('convenio.institucion', 'institucion')
            ->leftJoin('institucion.delegacion', 'delegacionInstitucion')
            ->where('1 = 1');

        if (!empty($filters['ooad'])) {
            $qb->andWhere('delegacionInstitucion.id = :ooad')->setParameter('ooad', $filters['ooad']);
        }
        if (!empty($filters['tipo'])) {
            $tipoBusqueda = $filters['tipo'] === 'Especial' ? 'Proyecto Especial' : $filters['tipo'];
            $qb->andWhere('upper(unaccent(convenio.tipo)) like UPPER(unaccent(:tipo))')->setParameter('tipo', '%' . $tipoBusqueda . '%');
        }
        if (!empty($filters['estatus'])) {
            $hoyFiltro = (new \DateTime())->format('Y-m-d');
            match ($filters['estatus']) {
                'canceled' => $qb->andWhere('convenio.cancelacionAnticipada = true'),
                'active'   => $qb->andWhere('convenio.cancelacionAnticipada = false OR convenio.cancelacionAnticipada IS NULL')->andWhere('convenio.fecha_vence_convenio >= :hoyFiltro')->setParameter('hoyFiltro', $hoyFiltro),
                'inactive' => $qb->andWhere('convenio.cancelacionAnticipada = false OR convenio.cancelacionAnticipada IS NULL')->andWhere('convenio.fecha_vence_convenio < :hoyFiltro')->setParameter('hoyFiltro', $hoyFiltro),
                default    => null,
            };
        }

        return $qb;
    }

    private function mapConvenioToArray(mixed $objConvenio, bool $isCamex, mixed $allDescription): array
    {
        $data = [
            'id'                       => $objConvenio->getId(),
            'nombre'                   => $objConvenio->getNombre(),
            'sector'                   => $objConvenio->getSector(),
            'tipo'                     => $isCamex ? $objConvenio->getTipoCame() : $objConvenio->getTipo(),
            'delegacion'               => $objConvenio->getDelegacion()?->getNombre() ?? 'N/A',
            'ciclo'                    => $objConvenio->getCicloAcademico()?->getNombre() ?? 'N/A',
            'carrera'                  => $objConvenio->getCarrera()?->getNombre() ?? 'N/A',
            'institucion'              => $objConvenio->getInstitucion()?->getNombre() ?? 'N/A',
            'vigencia'                 => str_replace('year', 'año', $objConvenio->getVigenciaText()),
            'general'                  => $objConvenio->getConvenioGeneral()?->getNombre() ?? 'N/A',
            'fuica'                    => $objConvenio->getFuica() ?: 'N/A',
            'razonSocial'              => $objConvenio->getRazonSocial() ?: 'N/A',
            'fechaVenceConvenio'       => $objConvenio->getFechaVenceConvenio() ?: '',
            'ota'                      => $objConvenio->getFechaOta() ?: '',
            'venceOta'                 => $objConvenio->getFechaVenceOta() ?: '',
            'rvoe'                     => $objConvenio->getFechaRvoe() ?: '',
            'venceRvoe'                => $objConvenio->getFechaVenceRvoe() ?: '',
            'fechaFirma'               => $objConvenio->getFechaFirma() ?: '',
            'comaem'                   => $objConvenio->getFechaEmiteComaem() ?: '',
            'venceComaem'              => $objConvenio->getFechaVenceComaem() ?: '',
            'coordinacion'             => $objConvenio->getConvenioGeneral()?->getNombre() ?? 'N/A',
            'indicadorVigencia'        => $this->getExpiredValidityClass($objConvenio->getFechaVenceConvenio()),
            'indicadorVigenciaConvenio'=> $this->getExpiredValidityClass($objConvenio->getFechaVenceConvenio()),
            'indicadorVigenciaOta'     => $this->getExpiredValidityClass($objConvenio->getFechaVenceOta()),
            'indicadorVigenciaRvoe'    => $this->getExpiredValidityClass($objConvenio->getFechaVenceRvoe()),
            'indicadorVigenciaComaem'  => $this->getExpiredValidityClass($objConvenio->getFechaEmiteComaem()),
            'disciplina'               => $objConvenio->getDisciplina()?->getNombre() ?? 'N/A',
            'nivel'                    => $objConvenio->getNivelAcademico()?->getNombre() ?? 'N/A',
            'objetivo'                 => !empty($objConvenio->getObjetivoColaboracion())
                ? ($allDescription === true ? $objConvenio->getObjetivoColaboracion() : $this->textLimit($objConvenio->getObjetivoColaboracion(), 20))
                : 'N/A',
            'imssFirmaUno'             => $objConvenio->getRecibeSignfirst() ?: 'N/A',
            'immsFirmaDos'             => $objConvenio->getRecibeSignsecond() ?: 'N/A',
            'institucionFirmaUno'      => $objConvenio->getNombreEmiteSignfirst() ?: 'N/A',
            'institucionFirmaDos'      => $objConvenio->getNombreEmiteSignsecond() ?: 'N/A',
            'cargoImssUno'             => $objConvenio->getCargoFirmaIdFirst()?->getNombre() ?? 'N/A',
            'cargoImssDos'             => $objConvenio->getCargoFirmaIdSecond()?->getNombre() ?? 'N/A',
            'cargoInstitucionUno'      => $objConvenio->getCargoEmiteSignfirst() ?: 'N/A',
            'cargoInstitucionDos'      => $objConvenio->getCargoEmiteSignsecond() ?: 'N/A',
            'programa'                 => $objConvenio->getNombrePrograma() ?: 'N/A',
            'rfc'                      => $objConvenio->getRfc() ?: 'N/A',
            'indicadorCartaVigencia'   => $objConvenio->getIndicadorCartaVigencia(),
            'cartaVigenciaData'        => $objConvenio->getCartaVigenciaData(),
        ];

        if (!$isCamex) {
            $data['tipoCame'] = $objConvenio->getTipoCame();
            $data['came']     = $objConvenio->getIsCamex();
        }

        return $data;
    }
}
