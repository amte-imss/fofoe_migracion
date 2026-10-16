<?php

namespace App\Form\Type\Came\ValidarSolicitudCC;

use App\Entity\CampoClinico;
use App\Entity\Solicitud;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValidarSolicitudCCType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Solicitud $solicitud */
        $solicitud = $builder->getData();

        /** @var CampoClinico $campo */
        foreach ($solicitud->getCamposClinicos() as $campo) {
            $builder->add(
                'campo_' . $campo->getId(),
                CampoClinicoValidacionLugaresType::class,
                ['mapped' => false, 'data' => $campo]
            );
            $builder->get('campo_' . $campo->getId())->setData($campo);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => Solicitud::class,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'solicitud';
    }
}
