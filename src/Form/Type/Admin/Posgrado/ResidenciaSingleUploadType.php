<?php

namespace App\Form\Type\Admin\Posgrado;

use App\Util\AcademicCycles;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ResidenciaSingleUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ciclo', ChoiceType::class, [
                'label' => 'Ciclo Académico',
                'choices' => AcademicCycles::recent(),
            ])
            ->add('grado', TextType::class, ['label' => 'Grado'])
            ->add('tipoEspecialidad', TextType::class, ['label' => 'Tipo Especialidad'])
            ->add('especialidad', HiddenType::class, [
                'constraints' => [new NotBlank(message: 'Debes seleccionar una especialidad.')],
            ])
            ->add('ooad', HiddenType::class, [
                'constraints' => [new NotBlank(message: 'Debes seleccionar una OOAD.')],
            ])
            ->add('sedeAcademica', HiddenType::class, [
                'constraints' => [new NotBlank(message: 'Debes seleccionar una sede académica.')],
            ])
            ->add('subsede', HiddenType::class, ['required' => false])
            ->add('delegacionUmae', HiddenType::class, ['required' => false])
            ->add('curp', TextType::class, ['label' => 'CURP'])
            ->add('folioImss', TextType::class, ['label' => 'Folio IMSS'])
            ->add('genero', ChoiceType::class, [
                'label' => 'Género',
                'choices' => ['Masculino' => 'M', 'Femenino' => 'F'],
            ])
            ->add('apellidoPaterno', TextType::class, ['label' => 'Apellido Paterno'])
            ->add('apellidoMaterno', TextType::class, ['label' => 'Apellido Materno'])
            ->add('nombre', TextType::class, ['label' => 'Nombre(s)'])
            ->add('nacionalidad', HiddenType::class, [
                'constraints' => [new NotBlank(message: 'Debes seleccionar una nacionalidad.')],
            ])
            ->add('pasaporte', TextType::class, ['label' => 'Pasaporte'])
            ->add('correo', TextType::class, ['label' => 'Correo'])
            ->add('celular', TextType::class, ['label' => 'Celular', 'required' => false]);
    }
}
