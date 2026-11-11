<?php

namespace App\Controller\Admin;

use App\Entity\ConvenioEliminado;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER')]
class ConvenioEliminadoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ConvenioEliminado::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Convenio Eliminado')
            ->setEntityLabelInPlural('Convenios Eliminados')
            ->setPageTitle(Crud::PAGE_INDEX, 'Convenio Eliminado')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Detalle del Convenio Eliminado')
            ->setDefaultSort(['fechaEliminacion' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW, Action::EDIT, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id');
        yield TextareaField::new('convenioInfo', 'Información del convenio')->onlyOnDetail();
        yield AssociationField::new('usuario', 'Usuario');
        yield TextField::new('rfc', 'RFC');
        yield TextField::new('fuica', 'FUICA');
        yield IntegerField::new('numero', 'Número');
        yield DateTimeField::new('fechaEliminacion', 'Fecha de eliminación')
            ->setFormat('dd/MM/yyyy HH:mm');
    }
}
