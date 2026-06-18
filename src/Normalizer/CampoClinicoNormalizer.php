<?php

namespace App\Normalizer;

use App\DTO\IE\PerfilInstitucionDTO;
use App\Entity\Institucion;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CampoClinicoNormalizer implements InstitucionPerfilNormalizerInterface
{
    public function __construct(
        private readonly NormalizerInterface $normalizer,
    ) {}

    public function normalizeConvenios(array $camposClinicos): array
    {
        return $this->normalizer->normalize($camposClinicos, 'json', [
            'attributes' => [
                'id', 'numero', 'nombre', 'fuica',
                'vigencia', 'vigenciaFormatted', 'label', 'tipo',
                'carrera'                  => ['nombre', 'nivelAcademico' => ['nombre']],
                'cicloAcademico'           => ['id', 'nombre'],
                'conveniosCiclosAcademicos'=> ['id', 'cicloAcademico' => ['id', 'nombre']],
                'ciclosAcademicosFormatted',
                'nivelAcademico'           => ['id', 'nombre'],
            ],
        ]);
    }

    public function normalizeInstitucion(Institucion $institucion): array
    {
        return $this->normalizer->normalize(new PerfilInstitucionDTO($institucion), 'json', [
            'attributes' => [
                'id', 'nombre', 'razonSocial', 'rfc', 'direccion',
                'correo', 'telefono', 'extension', 'fax', 'sitioWeb',
                'cedulaIdentificacion', 'cedulaIdentificacion2',
                'representante', 'confirmacionInformacion',
            ],
        ]);
    }
}
