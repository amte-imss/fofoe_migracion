<?php

namespace App\Form;

use App\Entity\Disciplina;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DisciplinaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', null, [
                'label' => 'Nombre Disciplina',
                'required' => true,
                'attr' => ['class' => 'form-control stylingFieldsForm'],
                'label_attr' => ['class' => 'col-md-12 mt-3 stylingLabelsForm'],
            ])
            ->add('descripcion', TextareaType::class, [
                'label' => 'Descripción de la Disciplina',
                'required' => false,
                'attr' => ['class' => 'form-control cstmStylingObjetivo ckeditor col-md-12'],
                'label_attr' => ['class' => 'col-md-12 mt-3 stylingFieldsFormObj'],
            ])
            ->add('activa', null, [
                'label' => 'Está Activa?',
                'attr' => ['class' => 'form-control stylingFieldsForm'],
                'label_attr' => ['class' => 'col-md-12 mt-3 stylingLabelsForm'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Disciplina::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'appbundle_disciplina';
    }
}
