<?php

namespace App\Form\Type\ValidacionMontos;

use App\Entity\DescuentoMonto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DescuentoMontoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numAlumnos')
            ->add('descuentoInscripcion')
            ->add('descuentoColegiatura');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => DescuentoMonto::class,
            'csrf_protection' => false,
        ]);
    }
}
