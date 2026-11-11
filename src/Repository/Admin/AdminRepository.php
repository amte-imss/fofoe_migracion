<?php

namespace App\Repository\Admin;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Static query builder factories used as "query_builder" callables in admin EntityType fields.
 * Not a Doctrine repository: it is not bound to any entity and must not be registered as one.
 */
final class AdminRepository
{
    private function __construct() {}

    public static function getAll(EntityRepository $permissionRepository): QueryBuilder
    {
        return $permissionRepository
            ->createQueryBuilder('permission')
            ->orderBy('permission.nombre', 'ASC');
    }

    public static function getAllRoles(EntityRepository $roleRepository): QueryBuilder
    {
        return $roleRepository
            ->createQueryBuilder('role')
            ->orderBy('role.nombre', 'ASC');
    }

    public static function getAllDelegations(EntityRepository $delegationRepository): QueryBuilder
    {
        return $delegationRepository
            ->createQueryBuilder('delegation')
            ->orderBy('delegation.nombre', 'ASC');
    }

    public static function getAllInstituciones(EntityRepository $institucionRepository): QueryBuilder
    {
        return $institucionRepository
            ->createQueryBuilder('institucion')
            ->orderBy('institucion.nombre', 'ASC');
    }

    public static function getAllUnidadesUMAE(EntityRepository $unidadRepository): QueryBuilder
    {
        return self::unitsOrderedByDelegation($unidadRepository)
            ->where('unidad.esUmae = true');
    }

    public static function getAllUnidadesEnfermeria(EntityRepository $unidadRepository): QueryBuilder
    {
        return self::unitsOrderedByDelegation($unidadRepository)
            ->where('unidad.esEnfermeria = true');
    }

    public static function getAllUnidadesUMAEAndUnidadesEnfermeria(EntityRepository $unidadRepository): QueryBuilder
    {
        return self::unitsOrderedByDelegation($unidadRepository)
            ->where('unidad.esUmae = true OR unidad.esEnfermeria = true');
    }

    private static function unitsOrderedByDelegation(EntityRepository $unidadRepository): QueryBuilder
    {
        return $unidadRepository
            ->createQueryBuilder('unidad')
            ->join('unidad.delegacion', 'delegacion')
            ->orderBy('delegacion.nombre', 'ASC')
            ->addOrderBy('unidad.nombre', 'ASC');
    }
}
