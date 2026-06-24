<?php

namespace App\Form\Type\ValidacionDePago;

use App\Entity\Pago;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValidacionPagoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('monto')
            ->add('fechaPago', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ])
            ->add('validado', TextType::class)
            ->add('observaciones');

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            /** @var Pago $pago */
            $pago = $event->getData();

            $pago->setValidado(
                $event->getForm()->get('validado')->getViewData() === '1'
            );
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => Pago::class,
            'csrf_protection' => false,
        ]);
    }
}
