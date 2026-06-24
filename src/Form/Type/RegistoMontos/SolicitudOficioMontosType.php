<?php

namespace App\Form\Type\RegistoMontos;

use App\Entity\Solicitud;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SolicitudOficioMontosType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('urlArchivoFile', FileType::class);
    }

    public function getBlockPrefix(): string
    {
        return 'solicitud_registro_montos';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => Solicitud::class,
            'csrf_protection' => false,
        ]);
    }
}
