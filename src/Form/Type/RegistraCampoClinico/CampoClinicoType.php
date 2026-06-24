<?php

namespace App\Form\Type\RegistraCampoClinico;

use App\Entity\CampoClinico;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class CampoClinicoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('solicitud')
            ->add('convenio')
            ->add('fechaInicial', DateType::class, [
                'widget' => 'choice',
            ])
            ->add('fechaFinal', DateType::class, [
                'widget' => 'choice',
            ])
            ->add('lugaresSolicitados')
            ->add('lugaresAutorizados')
            ->add('unidad')
            ->add('horario')
            ->add('asignatura')
            ->add('promocion')
            ->add('cicloAcademico')
            ->add('trabajadoresBecados', CollectionType::class, [
                'entry_type'    => TrabajadorBecadoType::class,
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => ['Default', 'regIE'],
            'data_class'        => CampoClinico::class,
            'csrf_protection'   => false,
        ]);
    }
}
