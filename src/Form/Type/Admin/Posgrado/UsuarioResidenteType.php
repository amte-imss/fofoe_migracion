<?php

namespace App\Form\Type\Admin\Posgrado;

use App\Entity\Usuario;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsuarioResidenteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre')
            ->add('apellidoPaterno')
            ->add('apellidoMaterno')
            ->add('correo')
            ->add('telefono', null, ['label' => 'Teléfono'])
            ->add('activo', null, ['data' => true])
            ->add('curp', null, ['label' => 'CURP']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuario::class,
        ]);
    }
}
