<?php

namespace App\Form\Type;

use App\Entity\CampoClinico;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValidaMontoCampoClinicoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('observaciones')
            ->add('montoCarrera', ValidaMontoCarreraType::class, [
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => CampoClinico::class,
            'csrf_protection' => false,
        ]);
    }
}
