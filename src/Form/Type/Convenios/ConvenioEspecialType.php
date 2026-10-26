<?php

namespace App\Form\Type\Convenios;

use App\Entity\ConvenioEspecial;
use App\Entity\Delegacion;
use App\Entity\MotivoColaboracion;
use App\Entity\Poblacion;
use App\Entity\Recurso;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConvenioEspecialType extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('razon_social')
            ->add('rfc')
            ->add('nombre')
            ->add('institucion')
            ->add('tipo')
            ->add('sector')
            ->add('objetivo_colaboracion')
            ->add('nivelAcademico')
            ->add('vigencia_text')
            ->add('cargoFirmaIdFirst')
            ->add('cargo_emite_signfirst')
            ->add('recibe_signfirst')
            ->add('nombre_emite_signfirst')
            ->add('cargoFirmaIdSecond')
            ->add('recibe_signsecond')
            ->add('cargo_emite_signsecond')
            ->add('nombre_emite_signsecond')
            ->add('url_convenio')
            ->add('todasDelegaciones')
            ->add('motivo_colaboracion', EntityType::class, [
                'class' => MotivoColaboracion::class,
                'choice_label' => 'nombre',
            ])
            ->add('recurso', EntityType::class, [
                'class' => Recurso::class,
                'choice_label' => 'nombre',
            ])
            ->add('poblacion', EntityType::class, [
                'class' => Poblacion::class,
                'choice_label' => 'nombre',
            ])
            ->add('fecha_firma', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('fecha_vence_convenio', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('delegaciones', EntityType::class, [
                'class' => Delegacion::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
                'data' => $options['ooads'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConvenioEspecial::class,
            'attr' => ['id' => 'convenio'],
            'csrf_protection' => false,
        ]);

        $resolver->setDefault(
            'ooads',
            fn (Options $options): array => $this->entityManager->getRepository(Delegacion::class)->findAll(),
        );
        $resolver->setAllowedTypes('ooads', 'array');
    }

    public function getBlockPrefix(): string
    {
        return 'appbundle_convenio_admin';
    }
}
