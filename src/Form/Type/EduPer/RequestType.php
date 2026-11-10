<?php

namespace App\Form\Type\EduPer;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach (['type', 'modality', 'duration', 'name', 'course'] as $field) {
            $builder->add($field, TextType::class);
        }
        $builder->add('quantity', IntegerType::class, ['required' => false]);
        $builder->add('email', EmailType::class);
        $builder->add('discount', TextType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['csrf_protection' => false]);
    }

    public function getBlockPrefix(): string { return 'request'; }
}
