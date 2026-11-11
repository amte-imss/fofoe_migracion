<?php

namespace App\Controller\Admin;

use App\Entity\Posgrado\Residencia;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER')]
class SuperResidenciaCrudController extends AbstractCrudController
{
    private const array LABELS = [
        'id' => 'ID',
        'categoria' => 'Categoría',
        'delegacion' => 'Delegación',
        'tipoEspecialidad' => 'Tipo de Especialidad',
        'especialidad' => 'Especialidad',
        'clues' => 'CLUES',
        'sede' => 'Sede',
        'subsede' => 'Sub-sede',
        'nivelAtencion' => 'Nivel de Atención',
        'tipoDelegacionUmae' => 'Tipo Delegación/UMAE',
        'grado' => 'Grado',
        'folio' => 'Folio',
        'tipo' => 'Tipo',
        'ciclo' => 'Ciclo',
        'residente' => 'Residente',
        'monto' => 'Monto',
        'tipoMoneda' => 'Moneda',
        'tasaCambio' => 'Tasa de Cambio',
        'estatus' => 'Estatus',
        'fechaInicio' => 'Fecha Inicio',
        'fechaTermino' => 'Fecha Término',
        'oficioAceptacion' => 'Oficio Aceptación (archivo)',
        'fechaOficioAceptacion' => 'Fecha Oficio',
        'isDelete' => 'Marcado como Eliminado',
        'isTest' => 'Es Test',
    ];

    private const array INDEX_FIELDS = [
        'id', 'folio', 'ciclo', 'tipo', 'delegacion', 'especialidad', 'grado',
        'residente', 'estatus', 'fechaInicio', 'fechaTermino', 'isDelete', 'isTest',
    ];

    /** Form fieldsets: label => [icon, properties] */
    private const array FORM_LAYOUT = [
        'Datos Generales' => ['fa fa-info-circle', [
            'categoria', 'delegacion', 'tipoEspecialidad', 'especialidad', 'clues', 'sede',
            'subsede', 'nivelAtencion', 'tipoDelegacionUmae', 'grado', 'folio', 'tipo', 'ciclo',
        ]],
        'Montos' => ['fa fa-money-bill', ['monto', 'tipoMoneda', 'tasaCambio']],
        'Fechas' => ['fa fa-calendar', ['fechaInicio', 'fechaTermino', 'fechaOficioAceptacion']],
        'Estado' => ['fa fa-check-circle', ['estatus', 'isDelete', 'isTest']],
    ];

    private const array FORM_LABEL_OVERRIDES = [
        'isDelete' => 'Marcar como Eliminado',
    ];

    private const array DATE_FIELDS = ['fechaInicio', 'fechaTermino', 'fechaOficioAceptacion'];

    private const array BOOLEAN_FIELDS = ['isDelete', 'isTest'];

    public static function getEntityFqcn(): string
    {
        return Residencia::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Residencia')
            ->setEntityLabelInPlural('Residencias')
            ->setPageTitle(Crud::PAGE_INDEX, 'Listado de residencias')
            ->setPageTitle(Crud::PAGE_DETAIL, fn(Residencia $residencia): string => sprintf('Residencia #%d', $residencia->getId()))
            ->setPageTitle(Crud::PAGE_EDIT, fn(Residencia $residencia): string => sprintf('Editar residencia #%d', $residencia->getId()))
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        if (in_array($pageName, [Crud::PAGE_EDIT, Crud::PAGE_NEW], true)) {
            yield from $this->formFields();

            return;
        }

        $properties = Crud::PAGE_INDEX === $pageName ? self::INDEX_FIELDS : array_keys(self::LABELS);

        foreach ($properties as $property) {
            yield $this->createField($property, self::LABELS[$property], false);
        }
    }

    private function formFields(): iterable
    {
        foreach (self::FORM_LAYOUT as $fieldsetLabel => [$icon, $properties]) {
            yield FormField::addFieldset($fieldsetLabel, $icon);

            foreach ($properties as $property) {
                $label = self::FORM_LABEL_OVERRIDES[$property] ?? self::LABELS[$property];
                yield $this->createField($property, $label, true);
            }
        }
    }

    private function createField(string $property, string $label, bool $isForm): FieldInterface
    {
        return match (true) {
            'id' === $property => IdField::new($property, $label),
            in_array($property, self::BOOLEAN_FIELDS, true) => BooleanField::new($property, $label)
                ->renderAsSwitch(false)
                ->setSortable('isDelete' !== $property),
            in_array($property, self::DATE_FIELDS, true) => $isForm
                ? DateField::new($property, $label)->renderAsNativeWidget()->setRequired(false)
                : DateField::new($property, $label)->setFormat('dd/MM/yyyy'),
            default => Field::new($property, $label),
        };
    }
}
