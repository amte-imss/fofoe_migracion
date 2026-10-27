<?php

namespace App\Form\Type\Admin\Posgrado;

use App\Util\AcademicCycles;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

final class ResidenciaBulkUploadType extends AbstractType
{
    private const string XLSX_MIME_TYPE = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ciclo', ChoiceType::class, [
                'label' => 'Ciclo Académico',
                'choices' => AcademicCycles::recent(),
            ])
            ->add('submitFile', FileType::class, [
                'label' => 'Archivo Excel con residentes a cargar',
                'attr' => ['accept' => self::XLSX_MIME_TYPE],
                'constraints' => [
                    new File(
                        mimeTypes: [self::XLSX_MIME_TYPE],
                        mimeTypesMessage: 'Solo se admiten archivos de Excel .xlsx',
                    ),
                ],
            ]);
    }
}
