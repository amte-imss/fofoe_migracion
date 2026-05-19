<?php

namespace App\Form\Enfermeria;

use App\Entity\Enfermeria\Solicitud;
use App\Entity\Unidad;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SolicitudType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('periodo')
            ->add('fechaInicio', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('fechaFin', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('unidad', EntityType::class, [
                'class'        => Unidad::class,
                'choice_label' => 'nombre',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => Solicitud::class,
            'csrf_protection' => false,
        ]);
    }
}
