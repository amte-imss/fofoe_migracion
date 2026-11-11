<?php

namespace App\Controller\Admin;

use App\Entity\Pago;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER')]
class SuperPagoCrudController extends AbstractCrudController
{
    private const array LABELS = [
        'id' => 'ID',
        'monto' => 'Monto',
        'montoRegistrado' => 'Monto Registrado',
        'fechaPagoRegistrada' => 'Fecha Pago Registrada',
        'fechaPago' => 'Fecha de Pago',
        'referenciaBancaria' => 'Referencia Bancaria',
        'tipoMoneda' => 'Moneda',
        'validado' => 'Validado',
        'requiereFactura' => 'Requiere Factura',
        'facturaGenerada' => 'Factura Generada',
        'isTest' => 'Es Test',
        'comprobantePago' => 'Archivo Comprobante',
        'solicitud' => 'Solicitud',
        'residencia' => 'Residencia',
        'factura' => 'Factura',
        'simulacionSolicitud' => 'Simulación Solicitud',
        'escuelaEnfermeriaSolicitud' => 'Alumno (Enfermería)',
        'escuelaEnfermeriaSolicitudId' => 'ID Alumno (Enfermería)',
        'eduPerSolicitud' => 'EduPer Solicitud',
        'eduPerSolicitudId' => 'ID EduPer Solicitud',
        'observaciones' => 'Observaciones',
        'fechaCreacion' => 'Fecha Creación',
    ];

    private const array INDEX_FIELDS = [
        'id', 'monto', 'montoRegistrado', 'fechaPago', 'referenciaBancaria', 'tipoMoneda',
        'validado', 'requiereFactura', 'facturaGenerada', 'isTest', 'fechaCreacion',
    ];

    private const array FORM_FIELDS = [
        'monto', 'montoRegistrado', 'fechaPagoRegistrada', 'fechaPago', 'referenciaBancaria',
        'tipoMoneda', 'validado', 'requiereFactura', 'facturaGenerada', 'isTest',
        'comprobantePago', 'escuelaEnfermeriaSolicitudId', 'eduPerSolicitudId',
        'observaciones', 'fechaCreacion',
    ];

    private const array DATE_FIELDS = ['fechaPagoRegistrada', 'fechaPago', 'fechaCreacion'];

    private const array BOOLEAN_FIELDS = ['requiereFactura', 'facturaGenerada', 'isTest'];

    public static function getEntityFqcn(): string
    {
        return Pago::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Pago')
            ->setEntityLabelInPlural('Pagos')
            ->setPageTitle(Crud::PAGE_INDEX, 'Listado de pagos')
            ->setPageTitle(Crud::PAGE_DETAIL, fn(Pago $pago): string => sprintf('Pago #%d', $pago->getId()))
            ->setPageTitle(Crud::PAGE_EDIT, fn(Pago $pago): string => sprintf('Pago #%d', $pago->getId()))
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
        $properties = match ($pageName) {
            Crud::PAGE_INDEX => self::INDEX_FIELDS,
            Crud::PAGE_DETAIL => array_keys(self::LABELS),
            default => self::FORM_FIELDS,
        };

        $isForm = in_array($pageName, [Crud::PAGE_EDIT, Crud::PAGE_NEW], true);

        foreach ($properties as $property) {
            yield $this->createField($property, $isForm);
        }
    }

    private function createField(string $property, bool $isForm): FieldInterface
    {
        $label = self::LABELS[$property];

        return match (true) {
            'id' === $property => IdField::new($property, $label),
            'validado' === $property => $isForm
                ? ChoiceField::new($property, $label)
                    ->setChoices(['Sí' => true, 'No' => false])
                    ->setFormTypeOptions(['placeholder' => 'Nulo', 'required' => false])
                : BooleanField::new($property, $label)->renderAsSwitch(false),
            in_array($property, self::BOOLEAN_FIELDS, true) => BooleanField::new($property, $label)
                ->renderAsSwitch(false),
            in_array($property, self::DATE_FIELDS, true) => $isForm
                ? Field::new($property, $label)
                : DateField::new($property, $label)->setFormat('dd/MM/yyyy'),
            default => Field::new($property, $label),
        };
    }
}
