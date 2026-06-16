<?php

namespace App\Form\Type\RegistoMontos;

use App\Entity\CampoClinico;
use App\Entity\Solicitud;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SolicitudRegistroMontosType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Solicitud $solicitud */
        $solicitud = $builder->getData();

        /** @var CampoClinico $campo */
        foreach ($solicitud->getCampoClinicos() as $campo) {
            if ($campo->getLugaresAutorizados() <= 0) {
                continue;
            }

            $builder->add(
                'campo_' . $campo->getId(),
                CampoClinicoRegistroMontosType::class,
                ['mapped' => false]
            );
            $builder->get('campo_' . $campo->getId())->setData($campo);
        }

        $builder
            ->add('urlArchivoFile', FileType::class)
            ->add('confirmacionOficioAdjunto');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => Solicitud::class,
            'csrf_protection' => false,
        ]);
    }
}
