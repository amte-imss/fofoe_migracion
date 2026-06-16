<?php

namespace App\Form\Type\ComprobantePagoType;

use App\Entity\Institucion;
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
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('monto')
            ->add('fechaPago', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',  // corregido: YYYY → yyyy
            ])
            ->add('comprobantePagoFile', FileType::class)
            ->add('requiereFactura', TextType::class)
            ->add('cedulaFile', FileType::class, [
                'data_class'    => Institucion::class,
                'property_path' => 'solicitud.institucion.cedulaFile',
                'constraints'   => [
                    new File([
                        'mimeTypes'        => ['application/pdf', 'application/x-pdf'],
                        'maxSize'          => '2M',
                        'mimeTypesMessage' => 'Solo se admiten archivos PDF de máx 2MB',
                        'maxSizeMessage'   => 'Solo se admiten archivos PDF de máx 2MB',
                    ]),
                ],
            ]);

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
            'data_class'      => Pago::class,
            'csrf_protection' => false,
            // 'cascade_validation' eliminado: removida en Symfony 3+,
            // usar constraint #[Valid] en la entidad
        ]);
    }
}
