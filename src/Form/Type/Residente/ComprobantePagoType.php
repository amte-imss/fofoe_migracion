<?php

namespace App\Form\Type\Residente;

use App\Entity\Pago;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

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

            $pago->setRequiereFactura(
                $event->getForm()->get('requiereFactura')->getViewData() === '1'
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
