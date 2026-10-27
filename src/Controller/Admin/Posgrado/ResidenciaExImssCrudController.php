<?php

namespace App\Controller\Admin\Posgrado;

use App\Entity\Posgrado\Residencia;
use App\Entity\Posgrado\ResidenciaInterface;
use App\Security\Voter\PosgradoAccessVoter;
use App\Service\Posgrado\ResidenciaManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(PosgradoAccessVoter::ACCESS)]
class ResidenciaExImssCrudController extends AbstractResidenciaCrudController
{
    private const array DETAIL_FIELDS = [
        'categoria', 'delegacion', 'tipoEspecialidad', 'especialidad', 'clues', 'sede',
        'subsede', 'nivelAtencion', 'tipoDelegacionUmae', 'grado', 'folio', 'tipo',
        'ciclo', 'residente', 'monto', 'tipoMoneda',
    ];

    public function __construct(
        private readonly ResidenciaManagerInterface $residenciaManager,
    ) {}

    protected function residenciaType(): string
    {
        return ResidenciaInterface::TIPO_EXTRANJERO_IMSS;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Residencia Ex IMSS')
            ->setEntityLabelInPlural('Residencias Ex IMSS')
            ->setPageTitle(Crud::PAGE_INDEX, 'Residencias Extranjeros IMSS')
            ->setSearchFields([
                'delegacion.nombre', 'especialidad.nombre', 'sede.nombre', 'folio', 'ciclo',
                'residente.nacionalidad', 'residente.usuario.nombre',
                'residente.usuario.apellidoPaterno', 'residente.usuario.apellidoMaterno',
                'residente.usuario.correo',
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        $bulkUpload = Action::new('bulkUpload', 'Carga masiva', 'fa fa-file-excel')
            ->linkToRoute('admin_posgrado_residencia_ex_imss_carga')
            ->createAsGlobalAction();

        $singleUpload = Action::new('singleUpload', 'Carga unitaria', 'fa fa-user-plus')
            ->linkToRoute('admin_posgrado_residencia_ex_imss_carga_unitaria')
            ->createAsGlobalAction();

        return $actions
            ->add(Crud::PAGE_INDEX, $bulkUpload)
            ->add(Crud::PAGE_INDEX, $singleUpload)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, fn(Action $action): Action => $action->setLabel('Detalle'))
            ->setPermission(Action::DETAIL, 'ROLE_SUPER')
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

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof Residencia);

        $this->residenciaManager->actualizarResidenciaExImss($entityInstance);

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function indexFields(): iterable
    {
        yield Field::new('folio', 'Folio/Oficio');
        yield Field::new('ciclo', 'Ciclo');
        yield Field::new('delegacion', 'OOAD');
        yield Field::new('especialidad');
        yield Field::new('grado', 'Grado');
        yield Field::new('residente', 'Residente');
    }

    private function detailFields(): iterable
    {
        yield IdField::new('id');

        foreach (self::DETAIL_FIELDS as $property) {
            yield Field::new($property);
        }
    }

    private function formFields(): iterable
    {
        // TODO: 'Datos Generales' tab with embedded ResidenteType — pending ResidenteType migration

        yield FormField::addTab('Datos Residencia', 'fa fa-id-card');
        yield ChoiceField::new('tipo', 'Tipo Residente')
            ->setChoices(['Extranjero IMSS' => ResidenciaInterface::TIPO_EXTRANJERO_IMSS])
            ->setColumns(3);
        yield Field::new('ciclo')->setColumns(3);
        yield Field::new('folio', 'Folio IMSS / Núm Oficio')->setColumns(3);
        yield IntegerField::new('grado')->setColumns(3);
        yield ChoiceField::new('tipoDelegacionUmae', 'OOAD / UMAE')
            ->setChoices(['OOAD' => 'Delegacion', 'UMAE' => 'UMAE'])
            ->setColumns(2);
        yield Field::new('delegacion', 'OOAD')->setColumns(4);
        yield Field::new('tipoEspecialidad')->setColumns(3);
        yield Field::new('especialidad')->setColumns(9);
        yield Field::new('sede')->setColumns(10);
        yield Field::new('subsede');
        yield NumberField::new('monto')->setColumns(3);
        yield ChoiceField::new('tipoMoneda')
            ->setChoices(['MXN' => 'MXN', 'USD' => 'USD'])
            ->setColumns(3);
        yield BooleanField::new('is_delete', 'Eliminar');
    }
}
