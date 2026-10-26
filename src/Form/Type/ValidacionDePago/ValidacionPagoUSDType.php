<?php

namespace App\Form\Type\ValidacionDePago;

use App\Entity\Pago;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Positive;

class ValidacionPagoUSDType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fechaPago', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('tasaCambio', NumberType::class, [
                'mapped' => false,
                'html5' => true,
                'constraints' => [
                    new Positive(),
                ],
            ])
            ->add('monto', NumberType::class, [
                'html5' => true,
            ])
            // Submitted as '1' / '0' by the React component; mapped manually on POST_SUBMIT
            ->add('validado', TextType::class, [
                'mapped' => false,
            ])
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
            'data_class' => Pago::class,
            'csrf_protection' => false,
        ]);
    }
}
