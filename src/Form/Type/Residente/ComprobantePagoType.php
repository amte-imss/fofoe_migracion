<?php

namespace App\Form\Type\Residente;

use App\Entity\Pago;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;

class ComprobantePagoType extends AbstractType
{
    private const string PDF_ONLY_MESSAGE = 'Solo se admiten archivos PDF de máx 2MB';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('monto')
            ->add('tipoMoneda', null, [
                'required' => true,
            ])
            ->add('tipoMoneda_otro', TextType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [new Length(max: 10)],
            ])
            ->add('fechaPago', DateType::class, [
                'widget' => 'single_text',
            ])
            // Submitted as '1' / '0' by the React component; mapped manually on POST_SUBMIT
            ->add('requiereFactura', TextType::class, [
                'mapped' => false,
            ])
            // Assigned to the Residente in the controller only when an invoice is required
            ->add('cedulaFile', FileType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: ['application/pdf', 'application/x-pdf'],
                        maxSizeMessage: self::PDF_ONLY_MESSAGE,
                        mimeTypesMessage: self::PDF_ONLY_MESSAGE,
                    ),
                ],
            ])
            ->add('comprobantePagoFile', FileType::class);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            /** @var Pago $pago */
            $pago = $event->getData();
            $form = $event->getForm();

            if ($pago->getTipoMoneda() === 'other') {
                $otherCurrency = trim((string) $form->get('tipoMoneda_otro')->getData());
                if ($otherCurrency === '') {
                    $form->get('tipoMoneda_otro')->addError(new FormError('Especifica la moneda del pago.'));
                } else {
                    $pago->setTipoMoneda($otherCurrency);
                }
            }

            $pago->setRequiereFactura(
                $form->get('requiereFactura')->getViewData() === '1'
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
