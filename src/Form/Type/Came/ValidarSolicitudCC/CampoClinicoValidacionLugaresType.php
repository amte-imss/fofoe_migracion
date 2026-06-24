<?php

namespace App\Form\Type\Came\ValidarSolicitudCC;

use App\Entity\CampoClinico;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class CampoClinicoValidacionLugaresType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var CampoClinico|null $campo */
        $campo = $builder->getData();

        $builder
            ->add('lugaresAutorizados', null, [
                'constraints' => [
                    new LessThanOrEqual(
                        value:   $campo?->getLugaresSolicitados() ?? 'lugaresSolicitados',
                        message: 'El número de lugares autorizados debe ser menor o igual al de solicitados ({{ compared_value }})',
                    ),
                ],
            ])
            ->add('obsValRegistro');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => CampoClinico::class,
            'csrf_protection' => false,
        ]);
    }
}
