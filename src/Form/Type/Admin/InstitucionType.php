<?php

namespace App\Form\Type\Admin;

use App\Entity\Institucion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InstitucionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('razonSocial')
            ->add('rfc')
            ->add('delegacion')
            ->add('correo')
            ->add('nombre')
            ->add('sitioWeb', null, ['required' => false])
            ->add('direccion', null, ['required' => false])
            ->add('fax', null, ['required' => false])
            ->add('extension', null, ['required' => false])
            ->add('telefono', null, ['required' => false])
            ->add('cedulaIdentificacion', null, ['required' => false])
            ->add('cedulaFile', FileType::class, [
                'label' => 'Cédula de Identificación (PDF)',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Institucion::class,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'institucion';
    }
}
