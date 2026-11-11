<?php

namespace App\Controller\Admin\CamposClinicos;

use App\Repository\Admin\AdminRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADM_FOFOE')]
class UserCameCrudController extends AbstractTypedUserCrudController
{
    protected function userTypeKey(): string
    {
        return 'CAME';
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Usuario CAME')
            ->setEntityLabelInPlural('CAME')
            ->setPageTitle(Crud::PAGE_INDEX, 'CAME')
            ->setSearchFields([
                'matricula', 'delegaciones.nombre', 'permisos.nombre',
                'nombre', 'apellidoPaterno', 'apellidoMaterno', 'correo', 'curp',
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $action): Action => $action->setLabel('Crear usuario CAME'));
    }

    public function configureFields(string $pageName): iterable
    {
        return in_array($pageName, [Crud::PAGE_EDIT, Crud::PAGE_NEW], true)
            ? $this->formFields($pageName)
            : $this->displayFields();
    }

    private function displayFields(): iterable
    {
        yield Field::new('matricula', 'Matrícula');
        yield Field::new('correo', 'Correo electrónico');
        yield TextField::new('fullName', 'Nombre')->setSortable(false);
        yield ArrayField::new('permisos', 'Rol / Permisos');
        yield BooleanField::new('activo', 'Activo');
        yield ArrayField::new('delegacionesUnidadesInstitucion', 'Delegaciones/Unidades/Institución');
    }

    private function formFields(string $pageName): iterable
    {
        yield Field::new('matricula', 'Matrícula')->setRequired(true)->setColumns(3);
        yield Field::new('correo', 'Correo electrónico')->setRequired(false)->setColumns(4);
        yield AssociationField::new('delegaciones', 'OOADs')
            ->setFormTypeOptions([
                'choice_label' => 'nombre',
                'query_builder' => AdminRepository::getAllDelegations(...),
            ])
            ->setColumns(5);
        yield BooleanField::new('activo', 'Activo');

        yield Field::new('nombre', 'Nombre')->setRequired(false)->setColumns(3);
        yield Field::new('apellidoPaterno', 'Apellido paterno')->setRequired(false)->setColumns(3);
        yield Field::new('apellidoMaterno', 'Apellido materno')->setColumns(3);

        yield Field::new('curp', 'CURP')->setRequired(false)->setColumns(3);
        yield Field::new('rfc', 'RFC')->setRequired(false)->setColumns(2);
        yield ChoiceField::new('sexo', 'Sexo')
            ->setChoices(['Masculino' => 'Masculino', 'Femenino' => 'Femenino'])
            ->setRequired(false)
            ->setColumns(3);
        yield $this->plainPasswordField($pageName)->setColumns(4);
    }
}
