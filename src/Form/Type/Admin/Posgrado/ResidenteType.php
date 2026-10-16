<?php

namespace App\Form\Type\Admin\Posgrado;

use App\Entity\Posgrado\Residente;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResidenteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nacionalidad', null, ['required' => true])
            ->add('usuario', UsuarioResidenteType::class, [
                'label' => false,
                'attr'  => ['expanded' => false],
            ])
            ->add('pasaporte', null, [
                'label' => 'Pasaporte',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Residente::class,
        ]);
    }
}
