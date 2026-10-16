<?php

namespace App\Form\Type\Posgrado;

use App\Entity\Posgrado\Residencia;
use App\Entity\Posgrado\ResidenciaInterface;
use App\Form\Type\Admin\Posgrado\ResidenteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResidenteExtranjeroNoIMSSType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('delegacion', null, ['required' => true])
            ->add('especialidad', null, ['required' => true])
            ->add('sede', null, ['required' => true])
            ->add('subsede', null, ['required' => true])
            ->add('grado', null, ['required' => true])
            ->add('folio', null, ['required' => true])  // duplicado eliminado
            ->add('ciclo', null, ['required' => true])
            ->add('fecha_inicio', DateType::class, [
                'widget'   => 'single_text',
                'format'   => 'yyyy-MM-dd',  // corregido: YYYY → yyyy
                'required' => true,
            ])
            ->add('fecha_termino', DateType::class, [
                'widget'   => 'single_text',
                'format'   => 'yyyy-MM-dd',  // corregido: YYYY → yyyy
                'required' => true,
            ])
            ->add('residente', ResidenteType::class, ['required' => true]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            /** @var Residencia $residencia */
            $residencia = $event->getData();
            $residente  = $residencia->getResidente();

            $residencia->setTipo(ResidenciaInterface::TIPO_EXTRANJERO_NO_IMSS);
            $residente->setTipo(ResidenciaInterface::TIPO_EXTRANJERO_NO_IMSS);
            $residente->setEspecialidad($residencia->getEspecialidad());
            $residente->setSede($residencia->getSede());
            $residente->setSubsede($residencia->getSubsede());
            $residente->setGrado($residencia->getGrado());
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => Residencia::class,
            'csrf_protection' => false,
            // 'cascade_validation' eliminado: opción removida en Symfony 3+,
            // reemplazada por la constraint @Valid en la entidad
        ]);
    }
}
