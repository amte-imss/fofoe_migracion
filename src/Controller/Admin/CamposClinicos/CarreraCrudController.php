<?php

namespace App\Controller\Admin\CamposClinicos;

use App\Entity\Carrera;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADM_FOFOE')]
class CarreraCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Carrera::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Carrera')
            ->setEntityLabelInPlural('Carrera')
            ->setPageTitle(Crud::PAGE_INDEX, 'Carrera');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->update(
            Crud::PAGE_INDEX,
            Action::NEW,
            fn(Action $action): Action => $action->setLabel('Crear carrera')
        );
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield Field::new('nombre', 'Nombre');
        yield TextField::new('nivelAcademico.nombre', 'Nivel académico')->onlyOnIndex();
        yield AssociationField::new('nivelAcademico', 'Nivel académico')
            ->onlyOnForms()
            ->setFormTypeOption('choice_label', 'nombre');
        yield BooleanField::new('activo', 'Activo');
        yield BooleanField::new('is_came', 'Es con Convenio CAME')->renderAsSwitch(false);
    }
}
