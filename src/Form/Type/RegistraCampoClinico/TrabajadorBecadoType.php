<?php

namespace App\Form\Type\RegistraCampoClinico;

use App\Entity\TrabajadorImss;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrabajadorBecadoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('matricula')
            ->add('delegacion')
            ->add('nombre')
            ->add('aPaterno')
            ->add('aMaterno')
            ->add('correo')
            ->add('curp')
            ->add('adscripcion');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => TrabajadorImss::class,
            'csrf_protection' => false,
        ]);
    }
}
