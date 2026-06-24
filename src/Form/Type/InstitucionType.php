<?php

namespace App\Form\Type;

use App\Entity\Institucion;
use Carbon\Carbon;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InstitucionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('razonSocial')
            ->add('rfc')
            ->add('direccion')
            ->add('correo')
            ->add('telefono')
            ->add('fax')
            ->add('sitioWeb')
            ->add('representante')
            ->add('cedulaFile', FileType::class)
            ->add('cedulaFile2', FileType::class)
            ->add('isConfirmacionInformacion', TextType::class, ['mapped' => false])
            ->add('extension', TextType::class, ['required' => false]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            /** @var Institucion $institucion */
            $institucion = $event->getData();

            $institucion->setConfirmacionInformacion(
                $event->getForm()->get('isConfirmacionInformacion')->getViewData() === 'checked'
                    ? Carbon::now()
                    : null
            );
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => Institucion::class,
            'csrf_protection' => false,
        ]);
    }
}
