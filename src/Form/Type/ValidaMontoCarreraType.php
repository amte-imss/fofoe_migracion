<?php

namespace App\Form\Type;

use App\Entity\MontoCarrera;
use App\Form\Type\ValidacionMontos\DescuentoMontoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValidaMontoCarreraType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('montoInscripcion')
            ->add('montoColegiatura')
            ->add('descuentos', CollectionType::class, [
                'entry_type'   => DescuentoMontoType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => MontoCarrera::class,
            'csrf_protection' => false,
        ]);
    }
}
