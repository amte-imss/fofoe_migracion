<?php

namespace App\Controller\Admin\Posgrado;

use App\Admin\Field\PlainField;
use App\Entity\Posgrado\Residencia;
use App\Entity\Posgrado\ResidenciaInterface;
use App\Form\Type\Admin\Posgrado\ResidenteType;
use App\Security\Voter\PosgradoAccessVoter;
use App\Service\Posgrado\ResidenciaExNoImssManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Vich\UploaderBundle\Form\Type\VichFileType;

#[IsGranted('ROLE_ADM_FOFOE')]
#[IsGranted(PosgradoAccessVoter::ACCESS)]
class ResidenciaExNoImssCrudController extends AbstractResidenciaCrudController
{
    private const array DETAIL_FIELDS = [
        'delegacion', 'especialidad', 'sede', 'subsede', 'nivelAtencion', 'tipoDelegacionUmae',
        'grado', 'folio', 'tipo', 'ciclo', 'residente', 'monto', 'tipoMoneda',
    ];

    public function __construct(
        private readonly ResidenciaExNoImssManagerInterface $residenciaManager,
    ) {}

    protected function residenciaType(): string
    {
        return ResidenciaInterface::TIPO_EXTRANJERO_NO_IMSS;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Residencia Ex NO IMSS')
            ->setEntityLabelInPlural('Residencias Ex NO IMSS')
            ->setPageTitle(Crud::PAGE_INDEX, 'Residencias Extranjeros NO IMSS');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $action): Action => $action->setLabel('Registrar residencia'))
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, fn(Action $action): Action => $action->setLabel('Detalle'))
            ->setPermission(Action::DETAIL, 'ROLE_SUPER');
    }

    public function createEntity(string $entityFqcn): Residencia
    {
        $residencia = new Residencia();
        $residencia->setTipo(ResidenciaInterface::TIPO_EXTRANJERO_NO_IMSS);

        return $residencia;
    }

    public function configureFields(string $pageName): iterable
    {
        return match ($pageName) {
            Crud::PAGE_INDEX => $this->indexFields(),
            Crud::PAGE_DETAIL => $this->detailFields(),
            default => $this->formFields(),
        };
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof Residencia);

        $this->residenciaManager->registrarResidenciaExNoImss($entityInstance);

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof Residencia);

        $this->residenciaManager->actualizarResidenciaExNoImss($entityInstance);

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function indexFields(): iterable
    {
        yield Field::new('folio', 'Folio/Oficio');
        yield TextField::new('residente.nacionalidad', 'Nacionalidad')->setSortable(false);
        yield Field::new('delegacion', 'OOAD');
        yield Field::new('especialidad', 'Especialidad');
        yield Field::new('ciclo', 'Ciclo');
        yield TextField::new('residente.usuario.fullName', 'Residente')->setSortable(false);
    }

    private function detailFields(): iterable
    {
        yield IdField::new('id');

        foreach (self::DETAIL_FIELDS as $property) {
            yield Field::new($property);
        }

        yield PlainField::new('oficioAceptacion', 'Oficio de Aceptación')
            ->setTemplatePath('admin/field/residencia_oficio_link.html.twig');
    }

    private function formFields(): iterable
    {
        yield FormField::addFieldset('Datos Generales', 'fa fa-id-card');
        yield PlainField::new('residente', false)
            ->onlyOnForms()
            ->setFormType(ResidenteType::class)
            ->setFormTypeOption('required', true);

        yield FormField::addFieldset('Datos Residencia', 'fa fa-id-card');
        yield ChoiceField::new('tipo', 'Tipo Residente')
            ->setChoices(['Extranjero NO IMSS' => ResidenciaInterface::TIPO_EXTRANJERO_NO_IMSS])
            ->setFormTypeOption('empty_data', ResidenciaInterface::TIPO_EXTRANJERO_NO_IMSS)
            ->setRequired(true)
            ->setColumns(3);
        yield Field::new('ciclo')->setRequired(true)->setColumns(4);
        yield Field::new('folio', 'Núm Oficio')->setRequired(true)->setColumns(3);
        yield Field::new('grado')->setColumns(2);
        yield TextField::new('oficioFile', 'Oficio de Aceptación')
            ->setFormType(VichFileType::class)
            ->setFormTypeOptions(['allow_delete' => false, 'download_link' => false])
            ->setColumns(5);
        yield Field::new('especialidad')->setColumns(7);
        yield ChoiceField::new('tipoDelegacionUmae', 'OOAD / UMAE')
            ->setChoices(['OOAD' => 'Delegacion', 'UMAE' => 'UMAE'])
            ->setColumns(4);
        yield Field::new('delegacion', 'OOAD')->setColumns(4);
        yield Field::new('sede')->setColumns(6);
        yield Field::new('subsede')->setColumns(6);
        yield DateField::new('fechaInicio')->renderAsNativeWidget()->setRequired(true)->setColumns(3);
        yield DateField::new('fechaTermino')->renderAsNativeWidget()->setRequired(true)->setColumns(3);
        yield BooleanField::new('isDelete', 'Eliminar');
    }
}
