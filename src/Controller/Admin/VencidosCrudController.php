<?php

namespace App\Controller\Admin;

use App\Entity\Utils\Vencidos;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER')]
class VencidosCrudController extends AbstractCrudController
{
    private const array INDEX_FIELDS = [
        'numero', 'ooad', 'nombrefiscal', 'rfc', 'fuica', 'tipo',
        'disciplina', 'vencimientoDelConvenio', 'status',
    ];

    private const array DETAIL_FIELDS = [
        'numero', 'ooad', 'ifirmante',
        'firmaAutoridadImss1', 'puestoFirmaAutoridadImss1',
        'firmaAutoridadImss2', 'puestoFirmaAutoridadImss2',
        'nombrefiscal', 'nombrecomercial', 'fuica', 'rfc', 'oubicacion',
        'email', 'telefono', 'sector',
        'firmaAutoridadInstitucionEducativa1', 'puestoFirmaAutoridadInstitucionEducativa1',
        'firmaAutoridadInstitucionEducativa2', 'puestoFirmaAutoridadInstitucionEducativa2',
        'firmaAutoridadInstitucionEducativa3', 'puestoFirmaAutoridadInstitucionEducativa3',
        'firmaAutoridadInstitucionEducativa4', 'puestoFirmaAutoridadInstitucionEducativa4',
        'tipo', 'objetivoColaboracion', 'cicloAcademico', 'grado', 'disciplina',
        'nombrePrograma', 'fechaFirmaDeConvenio', 'aao', 'tiempo', 'vencimientoDelConvenio',
        'fechaRvoe', 'vencimientoRvoePuedeHaberFechaNoAplica', 'rvoeEscaneado',
        'fechaOta', 'vencimientoOta', 'otaEscaneada',
        'acreditacionParaMedicinaComaem', 'vencimientoComaem', 'comaemEscaneado',
        'convenioEscaneado', 'observaciones', 'status',
    ];

    public static function getEntityFqcn(): string
    {
        return Vencidos::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Convenio Vencido')
            ->setEntityLabelInPlural('Convenios Vencidos por cargar')
            ->setPageTitle(Crud::PAGE_INDEX, 'Convenios Vencidos por cargar')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Detalle del Convenio Vencido');
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

        $properties = Crud::PAGE_DETAIL === $pageName ? self::DETAIL_FIELDS : self::INDEX_FIELDS;

        foreach ($properties as $property) {
            yield Field::new($property);
        }
    }
}
