<?php

namespace App\Controller\Admin\Posgrado;

use App\Entity\Posgrado\Residencia;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

abstract class AbstractResidenciaCrudController extends AbstractCrudController
{
    abstract protected function residenciaType(): string;

    public static function getEntityFqcn(): string
    {
        return Residencia::class;
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->leftJoin('entity.cargaMasiva', 'carga')
            ->andWhere('carga.deleted_at IS NULL')
            ->andWhere('entity.deleted_at IS NULL')
            ->andWhere('entity.tipo = :residenciaType')
            ->setParameter('residenciaType', $this->residenciaType());
    }
}
