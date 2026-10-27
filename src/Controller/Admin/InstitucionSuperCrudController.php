<?php

namespace App\Controller\Admin;

use App\Entity\Institucion;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER')]
class InstitucionSuperCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Institucion::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Institución')
            ->setEntityLabelInPlural('Institución - Super Usuario')
            ->setPageTitle(Crud::PAGE_INDEX, 'Institución');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->disable(Action::NEW);
    }

    public function configureFields(string $pageName): iterable
    {
        return Crud::PAGE_INDEX === $pageName ? $this->indexFields() : $this->formFields();
    }

    private function indexFields(): iterable
    {
        yield IdField::new('id');
        yield Field::new('rfc');
        yield Field::new('nombre');
    }

    private function formFields(): iterable
    {
        yield TextField::new('rfc', 'RFC');
        yield TextField::new('razonSocial', 'Razón Social');
        yield TextField::new('nombre', 'Nombre');
        yield AssociationField::new('delegacion', 'OOAD')
            ->setFormTypeOption('choice_label', 'nombre');
        yield EmailField::new('correo', 'Correo');
        yield TextField::new('sitioWeb', 'Sitio web');
        yield Field::new('direccion', 'Dirección');
        yield Field::new('fax', 'Fax');
        yield Field::new('extension', 'Extensión');
        yield Field::new('telefono', 'Teléfono');
    }
}
