<?php

namespace App\Form\Type\Admin\Came;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsuarioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('last_name', TextType::class, [
                'required' => true,
            ])
            ->add('other_name', TextType::class, [
                'required' => false,
            ])
            ->add('correo', EmailType::class, [
                'required' => true,
            ])
            ->add('password', PasswordType::class, [
                'required' => false,
            ])
            ->add('usersByCampus', CheckboxType::class, [
                'required' => false,
            ])
            ->add('campus', IntegerType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => null,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'request';
    }
}
