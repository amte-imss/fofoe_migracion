<?php

namespace App\Controller\Admin\CamposClinicos;

use App\Entity\CampoClinico;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADM_FOFOE')]
class CampoClinicoCrudController extends AbstractCrudController
{
    private const array FOFOE_FORMAT_VALIDATION_CHOICES = ['Sí' => 1, 'No' => 0];

    public static function getEntityFqcn(): string
    {
        return CampoClinico::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Campo Clínico')
            ->setEntityLabelInPlural('Campos Clínicos')
            ->setPageTitle(Crud::PAGE_INDEX, 'Campos Clínicos')
            ->setSearchFields([
                'id', 'solicitud.noSolicitud', 'estatus', 'solicitud.estatus',
                'convenio.carrera.nombre', 'convenio.institucion.nombre',
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, fn(Action $action): Action => $action->setLabel('Detalle'))
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->setPermission(Action::DETAIL, 'ROLE_SUPER')
            ->setPermission(Action::EDIT, 'ROLE_SUPER')
            ->disable(Action::NEW);
    }

    public function configureFields(string $pageName): iterable
    {
        return match ($pageName) {
            Crud::PAGE_INDEX => $this->indexFields(),
            Crud::PAGE_DETAIL => $this->detailFields(),
            default => $this->formFields(),
        };
    }

    private function indexFields(): iterable
    {
        yield IdField::new('id');
        yield TextField::new('convenio.institucion.nombre', 'Institución')->setSortable(false);
        yield Field::new('fechaInicial');
        yield Field::new('fechaFinal');
        yield AssociationField::new('solicitud');
        yield TextField::new('solicitud.fecha', 'Fecha Solicitud')->setSortable(false);
        yield TextField::new('solicitud.estatus', 'Estatus Solicitud')->setSortable(false);
    }

    private function detailFields(): iterable
    {
        yield IdField::new('id');
        yield TextField::new('convenio.institucion.nombre', 'Institución');
        yield AssociationField::new('solicitud');
        yield from $this->editableFields();
    }

    private function formFields(): iterable
    {
        yield from $this->editableFields();
    }

    private function editableFields(): iterable
    {
        yield Field::new('fechaInicial');
        yield Field::new('fechaFinal');
        yield Field::new('horario');
        yield Field::new('promocion');
        yield Field::new('lugaresSolicitados');
        yield Field::new('lugaresAutorizados');
        yield Field::new('asignatura');
        yield Field::new('referenciaBancaria');
        yield Field::new('monto');
        yield Field::new('observaciones');
        yield Field::new('obsValRegistro');
        yield Field::new('estatus', 'Estatus Campo');
        yield ChoiceField::new('validateFormatoFofoe', 'Formato FOFOE válido')
            ->setChoices(self::FOFOE_FORMAT_VALIDATION_CHOICES)
            ->setFormTypeOptions(['placeholder' => 'Seleccionar...', 'required' => false]);
        yield Field::new('motiveFormatoFofoe');
    }
}
