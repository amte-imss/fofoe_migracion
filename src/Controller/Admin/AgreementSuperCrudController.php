<?php

namespace App\Controller\Admin;

use App\Entity\Convenio;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER')]
class AgreementSuperCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Convenio::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Convenio')
            ->setEntityLabelInPlural('Convenio - Super Usuario')
            ->setPageTitle(Crud::PAGE_INDEX, 'Convenio');
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
        yield Field::new('numero');
        yield Field::new('razonSocial', 'Razón Social');
        yield Field::new('rfc', 'RFC');
        yield TextField::new('institucion.nombre', 'Institución');
        yield TextField::new('ciclosAcademicosFormatted', 'Ciclos Académicos')->setSortable(false);
        yield TextField::new('nivelAcademico.nombre', 'Nivel o Grado Académico');
        yield TextField::new('disciplina.nombre', 'Disciplina');
        yield TextField::new('carrera.nombre', 'Carrera');
        yield TextField::new('fechaFirmaFormatted', 'Fecha firma de Convenio')->setSortable(false);
        yield TextField::new('vigenciaFormatted', 'Vencimiento del Convenio')->setSortable(false);
    }

    private function formFields(): iterable
    {
        yield Field::new('numero', 'Numero');
        yield Field::new('fuica', 'FUICA');
        yield TextField::new('nombre', 'Nombre');
        yield TextField::new('razonSocial', 'Razón Social');
        yield Field::new('rfc', 'RFC');
        yield AssociationField::new('institucion', 'Institución')
            ->setFormTypeOption('choice_label', 'razonSocialRFC');
        yield ChoiceField::new('tipo', 'Tipo')
            ->setChoices(['General' => 'General', 'Específico' => 'Específico']);
        yield ChoiceField::new('sector', 'Sector')
            ->setChoices(['Público' => 'Público', 'Privado' => 'Privado']);
        yield Field::new('objetivoColaboracion', 'Objetivo de Colaboración');
        yield AssociationField::new('nivelAcademico', 'Nivel o grado Académico')
            ->setFormTypeOption('choice_label', 'nombre');
        yield AssociationField::new('disciplina', 'Disciplina')
            ->setFormTypeOption('choice_label', 'nombre');
        yield AssociationField::new('carrera', 'Carrera')
            ->setFormTypeOption('choice_label', 'nombre');
        yield TextField::new('nombrePrograma', 'Nombre del Programa');
        yield $this->dateField('fechaFirma', 'Fecha de Firma');
        yield TextField::new('vigenciaText', 'Vigencia (Años)');
        yield $this->dateField('vigencia', 'Fecha de Vigencia de Convenio (campo vigencia)');
        yield $this->dateField('fechaVenceConvenio', 'Fecha de Vigencia de Convenio');
        yield $this->dateField('fechaOta', 'Fecha de Emisión OTA');
        yield $this->dateField('fechaVenceOta', 'Fecha de Vigencia de Emisión OTA');
        yield $this->dateField('fechaRvoe', 'Fecha de Emisión RVOE');
        yield $this->dateField('fechaVenceRvoe', 'Fecha de Vigencia de Emisión RVOE');
        yield Field::new('cartaIntencion', 'Carta Intención');
        yield $this->dateField('fechaCartaIntencion', 'Fecha de Carta de Intención');
        yield $this->dateField('fechaEmiteComaem', 'Fecha de Emisión de Acreditación (COMAEM u otros)');
        yield $this->dateField('fechaVenceComaem', 'Fecha de Vigencia de Emisión de Acreditación (COMAEM u otros)');
        yield AssociationField::new('cargoFirmaIdFirst', 'Cargo Firmante IMSS 1')
            ->setFormTypeOption('choice_label', 'nombre');
        yield TextField::new('recibe_signfirst', 'Nombre del Firmante IMSS 1');
        yield AssociationField::new('cargoFirmaIdSecond', 'Cargo Firmante IMSS 2')
            ->setFormTypeOption('choice_label', 'nombre');
        yield TextField::new('recibeSignsecond', 'Nombre del Firmante IMSS 2');
        yield TextField::new('cargoEmiteSignfirst', 'Cargo de la Institución Firmante 1');
        yield TextField::new('nombreEmiteSignfirst', 'Nombre Institución del Firmante 1');
        yield TextField::new('cargoEmiteSignsecond', 'Cargo de la Institución Firmante 2');
        yield TextField::new('nombreEmiteSignsecond', 'Nombre Institución del Firmante 2');
        yield TextField::new('url_convenio', 'URL de documentos del convenio:');
        yield BooleanField::new('isCamex', 'Registrado por CAME');
    }

    private function dateField(string $property, string $label): DateField
    {
        return DateField::new($property, $label)->renderAsNativeWidget();
    }
}
