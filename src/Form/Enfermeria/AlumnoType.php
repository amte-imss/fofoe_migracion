<?php

namespace App\Form\Enfermeria;

use App\Entity\Enfermeria\Alumno;
use App\Entity\Enfermeria\Solicitud;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AlumnoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre')
            ->add('curp')
            ->add('email')
            ->add('tipo')
            ->add('solicitud', EntityType::class, [
                'class'        => Solicitud::class,
                'choice_label' => 'id',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => Alumno::class,
            'csrf_protection' => false,
        ]);
    }
}
