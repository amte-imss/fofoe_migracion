<?php

namespace App\Form\Type\ComprobantePagoType;

use App\Entity\Pago;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PagoComprobanteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('monto')
            ->add('fechaPago', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ])
            ->add('referenciaBancaria')
            ->add('solicitud')
            ->add('comprobantePagoFile', FileType::class)
            ->add('requiereFactura');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => Pago::class,
            'csrf_protection' => false,
        ]);
    }
}
