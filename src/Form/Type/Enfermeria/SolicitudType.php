<?php

namespace App\Form\Type\Enfermeria;

use App\Entity\Enfermeria\Solicitud;
use App\Entity\Unidad;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SolicitudType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => ['Licenciatura' => Solicitud::TYPE_LICENCIATURA, 'Posgrado' => Solicitud::TYPE_POSGRADO],
                'placeholder' => 'Seleccionar...',
                'constraints' => [new NotBlank()],
            ])
            ->add('periodo')
            ->add('fechaInicio')
            ->add('fechaFin')
            ->add('unidad', EntityType::class, [
                'class' => Unidad::class,
                'choice_label' => 'nombre',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Solicitud::class,
            'csrf_protection' => false,
        ]);
    }
}
