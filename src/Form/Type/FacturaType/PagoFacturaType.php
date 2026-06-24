<?php

namespace App\Form\Type\FacturaType;

use App\Entity\Pago;
use App\Form\Type\FacturaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PagoFacturaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('facturaGenerada')
            ->add('factura', FacturaType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => Pago::class,
            'csrf_protection' => false,
        ]);
    }
}
