<?php

namespace App\Form;

use App\Entity\CicloAcademico;
use App\Entity\Convenio;
use App\Entity\Delegacion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConvenioAdminType extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('razon_social')
            ->add('rfc')
            ->add('nombre')
            ->add('institucion')
            ->add('tipo')
            ->add('convenio_general')
            ->add('sector')
            ->add('objetivo_colaboracion')
            ->add('nivelAcademico')
            ->add('disciplina')
            ->add('carrera')
            ->add('nombre_programa')
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
            ->add('cartaIntencion')
            ->add('todasDelegaciones')
            ->add('todosCiclosAcademicos');

        $dateFields = [
            'fecha_firma',
            'fecha_vence_convenio',
            'fecha_ota',
            'fecha_vence_ota',
            'fecha_rvoe',
            'fecha_vence_rvoe',
            'fecha_emite_comaem',
            'fecha_vence_comaem',
            'fechaCartaIntencion',
        ];

        foreach ($dateFields as $field) {
            $builder->add($field, DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'html5'  => true,
            ]);
        }

        $builder
            ->add('delegaciones', EntityType::class, [
                'class'        => Delegacion::class,
                'choice_label' => 'name',
                'multiple'     => true,
                'expanded'     => true,
                'mapped'       => false,
                'data'         => $options['ooads'],
            ])
            ->add('ciclosAcademicos', EntityType::class, [
                'class'        => CicloAcademico::class,
                'choice_label' => 'name',
                'multiple'     => true,
                'expanded'     => true,
                'mapped'       => false,
                'data'         => $options['ciclosAcademicos'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'       => Convenio::class,  // clase directa, no string
            'attr'             => ['id' => 'appBundleConvenioAdmin'],
            'csrf_protection'  => false,
            'ooads'            => $this->em->getRepository(Delegacion::class)->findAll(),
            'ciclosAcademicos' => $this->em->getRepository(CicloAcademico::class)->findAll(),
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'appbundle_convenio_admin';
    }
}
