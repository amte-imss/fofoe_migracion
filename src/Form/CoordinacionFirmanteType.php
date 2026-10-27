<?php

namespace App\Form;

use App\Entity\CoordinacionFirmante;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoordinacionFirmanteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', null, [
                'label' => 'Nombre de Coordinación Firmante',
                'required' => true,
                'attr' => ['class' => 'form-control stylingFieldsForm'],
                'label_attr' => ['class' => 'col-md-12 mt-3 stylingLabelsForm'],
            ])
            ->add('activa', null, [
                'label' => 'Activada?',
                'attr' => ['class' => 'form-control stylingFieldsForm'],
                'label_attr' => ['class' => 'col-md-12 mt-3 stylingLabelsForm'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CoordinacionFirmante::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'appbundle_coordinacionfirmante';
    }
}
