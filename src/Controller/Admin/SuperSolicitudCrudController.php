<?php

namespace App\Controller\Admin;

use App\Admin\Field\PlainField;
use App\Entity\CampoClinico;
use App\Entity\Solicitud;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER')]
class SuperSolicitudCrudController extends AbstractCrudController
{
    private const array OFFICE_AMOUNTS_VALIDATION_CHOICES = ['Sí' => 1, 'No' => 0];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Solicitud::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Solicitud CC')
            ->setEntityLabelInPlural('Solicitudes CC')
            ->setPageTitle(Crud::PAGE_INDEX, 'Listado de solicitudes')
            ->setPageTitle(Crud::PAGE_DETAIL, fn(Solicitud $solicitud): string => sprintf('Solicitud #%d', $solicitud->getId()))
            ->setPageTitle(Crud::PAGE_EDIT, fn(Solicitud $solicitud): string => sprintf('Editar solicitud #%d', $solicitud->getId()))
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $deleteFofoeDocuments = Action::new('deleteFofoeDocuments', 'Eliminar Documentos FOFOE', 'fa fa-trash')
            ->linkToCrudAction('deleteFofoeDocuments')
            ->addCssClass('btn btn-danger')
            ->askConfirmation('Se eliminarán los formatos FOFOE de todos los campos clínicos de esta solicitud. ¿Continuar?');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, $deleteFofoeDocuments)
            ->disable(Action::NEW, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        return match ($pageName) {
            Crud::PAGE_INDEX => $this->indexFields(),
            Crud::PAGE_DETAIL => $this->detailFields(),
            default => $this->formFields(),
        };
    }

    #[AdminRoute('/{entityId}/delete-fofoe-documents', name: 'delete_fofoe_documents')]
    public function deleteFofoeDocuments(AdminContext $context): Response
    {
        /** @var Solicitud $solicitud */
        $solicitud = $context->getEntity()->getInstance();

        $updatedRows = $this->em->createQueryBuilder()
            ->update(CampoClinico::class, 'c')
            ->set('c.formatoFofoeFileName', ':empty')
            ->set('c.fechaFormatoFofoe', ':empty')
            ->where('c.solicitud = :solicitud')
            ->setParameter('empty', null)
            ->setParameter('solicitud', $solicitud)
            ->getQuery()
            ->execute();

        $updatedRows > 0
            ? $this->addFlash('success', 'Archivo eliminado correctamente de la base de datos.')
            : $this->addFlash('warning', 'No se encontró el registro para eliminar o ya estaba vacío.');

        return $this->redirect(
            $this->adminUrlGenerator
                ->unsetAll()
                ->setController(self::class)
                ->setAction(Action::EDIT)
                ->setEntityId($solicitud->getId())
                ->generateUrl()
        );
    }

    private function indexFields(): iterable
    {
        yield IdField::new('id', 'ID');
        yield Field::new('noSolicitud', 'No. Solicitud');
        yield TextField::new('estatus', 'Estatus');
        yield TextField::new('estatusCameFormatted', 'Estatus CAME (legible)')->setSortable(false);
        yield Field::new('referenciaBancaria', 'Referencia Bancaria');
        yield Field::new('monto', 'Monto');
        yield TextField::new('tipoPago', 'Tipo de Pago');
        yield BooleanField::new('validado', 'Validado')->renderAsSwitch(false);
        yield TextField::new('displayDelUMAE', 'Del/UMAE')->setSortable(false);
        yield BooleanField::new('isTest', 'Es Test')->renderAsSwitch(false);
    }

    private function detailFields(): iterable
    {
        yield IdField::new('id', 'ID');
        yield Field::new('noSolicitud', 'No. Solicitud');
        yield TextField::new('estatus', 'Estatus');
        yield TextField::new('estatusCameFormatted', 'Estatus CAME (legible)');
        yield TextField::new('estatusIEFormatted', 'Estatus IE (legible)');
        yield Field::new('referenciaBancaria', 'Referencia Bancaria');
        yield Field::new('monto', 'Monto');
        yield TextField::new('tipoPago', 'Tipo de Pago');
        yield BooleanField::new('esPagoMultiple', '¿Pago Múltiple?')->renderAsSwitch(false);
        yield BooleanField::new('isPagoUnico', '¿Pago Único?')->renderAsSwitch(false);
        yield BooleanField::new('validado', 'Validado')->renderAsSwitch(false);
        yield DateField::new('fechaComprobante', 'Fecha Comprobante')->setFormat('dd/MM/yyyy');
        yield Field::new('documento', 'Documento');
        yield TextField::new('urlArchivo', 'Nombre de archivo actual');
        yield BooleanField::new('confirmacionOficioAdjunto', 'Confirmó Oficio Adjunto')->renderAsSwitch(false);
        yield ChoiceField::new('validateOficioMontos', 'Validación Oficio Montos')
            ->setChoices(self::OFFICE_AMOUNTS_VALIDATION_CHOICES);
        yield TextareaField::new('motiveOficioMontos', 'Motivo Oficio Montos');
        yield BooleanField::new('formatosFofoeCargados', 'FOFOE Cargados (todos?)')->renderAsSwitch(false);
        yield IntegerField::new('noCamposSolicitados', '# Campos Solicitados');
        yield IntegerField::new('noCamposAutorizados', '# Campos Autorizados');
        yield TextField::new('delegacion.nombre', 'Delegación (derivada)');
        yield TextField::new('unidad.nombre', 'Unidad (derivada)');
        yield BooleanField::new('esUMAE', '¿UMAE?')->renderAsSwitch(false);
        yield TextField::new('displayDelUMAE', 'Display Del/UMAE');
        yield PlainField::new('idDelUMAE', 'ID Del/UMAE');
        yield AssociationField::new('camposClinicos', 'Campos Clínicos');
        yield AssociationField::new('pagos', 'Pagos');
        yield BooleanField::new('isTest', 'Es Test')->renderAsSwitch(false);
    }

    private function formFields(): iterable
    {
        yield FormField::addFieldset('Identificación', 'fa fa-file-lines');
        yield Field::new('noSolicitud', 'No. Solicitud');
        yield ChoiceField::new('estatus', 'Estatus')
            ->setChoices($this->statusChoices())
            ->setHelp('Solo se muestran los estatus permitidos por la lógica de dominio.');

        yield FormField::addFieldset('Pago y Referencias', 'fa fa-money-bill');
        yield Field::new('referenciaBancaria', 'Referencia Bancaria');
        yield Field::new('monto', 'Monto');
        yield Field::new('tipoPago', 'Tipo de Pago');
        yield ChoiceField::new('validado', 'Validado')
            ->setChoices(['Sí' => true, 'No' => false])
            ->setFormTypeOptions(['placeholder' => 'Nulo', 'required' => false]);
        yield DateField::new('fechaComprobante', 'Fecha Comprobante')
            ->renderAsNativeWidget()
            ->setRequired(false);

        yield FormField::addFieldset('Documentación', 'fa fa-paperclip');
        yield Field::new('documento', 'Documento');
        yield TextField::new('urlArchivo', 'Nombre de archivo actual')->setDisabled();
        yield BooleanField::new('confirmacionOficioAdjunto', 'Confirmó Oficio Adjunto');
        yield ChoiceField::new('validateOficioMontos', 'Validación Oficio Montos')
            ->setChoices(self::OFFICE_AMOUNTS_VALIDATION_CHOICES)
            ->setFormTypeOptions(['placeholder' => 'Nulo', 'required' => false]);
        yield TextareaField::new('motiveOficioMontos', 'Motivo Oficio Montos');

        yield FormField::addFieldset('Metadatos', 'fa fa-cog');
        yield BooleanField::new('isTest', 'Es Test');
    }

    /**
     * @return array<string, string>
     */
    private function statusChoices(): array
    {
        $statuses = [
            Solicitud::CREADA,
            Solicitud::REGISTRADA,
            Solicitud::CONFIRMADA,
            Solicitud::EN_VALIDACION_DE_MONTOS_CAME,
            Solicitud::MONTOS_INCORRECTOS_CAME,
            Solicitud::MONTOS_VALIDADOS_CAME,
            Solicitud::FORMATOS_DE_PAGO_GENERADOS,
            Solicitud::CARGANDO_COMPROBANTES,
            Solicitud::EN_VALIDACION_FOFOE,
            Solicitud::CREDENCIALES_GENERADAS,
            Solicitud::CANCELADA,
        ];

        return array_combine($statuses, $statuses);
    }
}
