<?php

namespace App\Form\Type\Convenios;

use App\Entity\Convenio;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CargaConveniosType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Subir Archivo',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control form-control-sm mb-4 p-0',
                    'accept' => 'text/csv',
                ],
                'label_attr' => ['class' => 'form-label p-2'],
                'constraints' => [
                    new File(maxSize: '1024k'),
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Cargar Archivo',
                'attr' => ['class' => 'btn btn-primary save d-none'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Convenio::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'appbundle_convenio_carga';
    }
}
