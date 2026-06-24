<?php

namespace App\Normalizer;

use App\Entity\Convenio;

class ConvenioNormalizer
{
    public function serializer(Convenio $convenio): array
    {
        $formatDate = static fn(?\DateTimeInterface $date, string $format = 'Y-m-d'): string =>
            $date?->format($format) ?? '';

        $data = [
            'id'                          => $convenio->getId(),
            'numero'                      => $convenio->getNumero(),
            'rfc'                         => $convenio->getRfc(),
            'razon_social'                => $convenio->getRazonSocial(),
            'nombre'                      => $convenio->getNombre(),
            'institucion'                 => [
                'id'          => $convenio->getInstitucionId(),
                'nombre'      => $convenio->getInstitucion()->getNombre(),
                'delegacion'  => [
                    'id'     => $convenio->getInstitucion()->getDelegacion()?->getId() ?? '',
                    'nombre' => $convenio->getInstitucion()->getDelegacion()?->getNombre() ?? '',
                ],
                'rfc'         => $convenio->getInstitucion()->getRfc(),
                'razon_social'=> $convenio->getInstitucion()->getRazonSocial(),
            ],
            'tipo'                        => $convenio->getTipo() === 'Proyecto Especial' ? 'Especial' : $convenio->getTipo(),
            'sector'                      => $convenio->getSector(),
            'objetivo_colaboracion'       => $convenio->getObjetivoColaboracion(),
            'nombre_programa'             => $convenio->getNombrePrograma(),
            'fecha_firma'                 => $formatDate($convenio->getFechaFirma()),
            'fecha_firma_formatted'       => $formatDate($convenio->getFechaFirma(), 'd-m-Y'),
            'vigencia_text'               => $convenio->getVigenciaText(),
            'fecha_vence_convenio'        => $formatDate($convenio->getFechaVenceConvenio()),
            'fecha_vence_convenio_formatted' => $formatDate($convenio->getFechaVenceConvenio(), 'd-m-Y'),
            'fecha_ota'                   => $formatDate($convenio->getFechaOta()),
            'fecha_ota_formatted'         => $formatDate($convenio->getFechaOta(), 'd-m-Y'),
            'fecha_vence_ota'             => $formatDate($convenio->getFechaVenceOta()),
            'fecha_vence_ota_formatted'   => $formatDate($convenio->getFechaVenceOta(), 'd-m-Y'),
            'fecha_rvoe'                  => $formatDate($convenio->getFechaRvoe()),
            'fecha_vence_rvoe'            => $formatDate($convenio->getFechaVenceRvoe()),
            'fecha_vence_rvoe_formatted'  => $formatDate($convenio->getFechaVenceRvoe(), 'd-m-Y'),
            'cargo_emite_signfirst'       => $convenio->getCargoEmiteSignfirst(),
            'nombre_emite_signfirst'      => $convenio->getNombreEmiteSignfirst(),
            'cargo_emite_signsecond'      => $convenio->getCargoEmiteSignsecond(), // typo fix: getcargoEmite → getCargoEmite
            'nombre_emite_signsecond'     => $convenio->getNombreEmiteSignsecond(),
            'recibe_signfirst'            => $convenio->getRecibeSignfirst(),
            'recibe_signsecond'           => $convenio->getRecibeSignsecond(),
            'carta_intencion'             => $convenio->isCartaIntencion(),
            'fecha_carta_intencion'       => $formatDate($convenio->getFechaCartaIntencion()),
            'fecha_carta_intencion_formatted' => $formatDate($convenio->getFechaCartaIntencion(), 'd-m-Y'),
            'fecha_emite_comaem'          => $formatDate($convenio->getFechaEmiteComaem()),
            'fecha_vence_comaem'          => $formatDate($convenio->getFechaVenceComaem()),
            'fecha_vence_comaem_formatted'=> $formatDate($convenio->getFechaVenceComaem(), 'd-m-Y'),
            'url_convenio'                => $convenio->getUrlConvenio(),
            'fuica'                       => $convenio->getFuica(),
            'fecha_carta_intencion_indicator' => $convenio->getIndicadorCartaVigencia(),
            'fecha_vence_convenio_indicator'  => $convenio->getIndicadorFechaVigencia(),
            'fecha_vence_ota_indicator'       => $convenio->getIndicadorFechaVenceOta(),
            'fecha_vence_rveo_indicator'      => $convenio->getIndicadorFechaVenceRvoe(),
            'fecha_vence_comaem_indicator'    => $convenio->getIndicadorFechaVenceComaem(),
            'is_came'                     => $convenio->getIsCamex(),
            'is_umae'                     => $convenio->isUmae(),
            'status'                      => $convenio->getStatus(),
            'todasDelegaciones'           => $convenio->isTodasDelegaciones(),
            'todosCiclosAcademicos'       => $convenio->isTodosCiclosAcademicos(),
        ];

        if ($convenio->getNivelAcademico()) {
            $data['nivel_academico'] = [
                'id'     => $convenio->getNivelAcademico()->getId(),
                'nombre' => $convenio->getNivelAcademico()->getNombre(),
            ];
        }

        if ($convenio->getDisciplina()) {
            $data['disciplina'] = [
                'id'     => $convenio->getDisciplina()->getId(),
                'nombre' => $convenio->getDisciplina()->getNombre(),
            ];
        }

        if ($convenio->getCarrera()) {
            $data['carrera'] = [
                'id'     => $convenio->getCarrera()->getId(),
                'nombre' => $convenio->getCarrera()->getNombre(),
            ];
        }

        if ($convenio->getConvenioGeneral()) {
            $data['convenio_general'] = [
                'id'     => $convenio->getConvenioGeneral()->getId(),
                'nombre' => $convenio->getConvenioGeneral()->getNombre(),
            ];
        }

        try {
            if ($convenio->getCargoFirmaIdFirst()) {
                $data['cargoFirmaIdFirst'] = [
                    'id'     => $convenio->getCargoFirmaIdFirst()->getId(),
                    'nombre' => $convenio->getCargoFirmaIdFirst()->getNombre(),
                ];
            }
        } catch (\Exception) {}  // PHP 8: catch sin variable

        try {
            if ($convenio->getCargoFirmaIdSecond()) {
                $data['cargoFirmaIdSecond'] = [
                    'id'     => $convenio->getCargoFirmaIdSecond()->getId(),
                    'nombre' => $convenio->getCargoFirmaIdSecond()->getNombre(),
                ];
            }
        } catch (\Exception) {}

        $data['ciclosAcademicos'] = array_map(
            fn($cca) => [
                'id'     => $cca->getCicloAcademico()->getId(),
                'nombre' => $cca->getCicloAcademico()->getNombre(),
            ],
            $convenio->getConveniosCiclosAcademicos()->toArray()
        );

        $data['delegaciones'] = array_map(
            fn($dc) => [
                'id'     => $dc->getDelegacion()->getId(),
                'nombre' => $dc->getDelegacion()->getNombre(),
            ],
            $convenio->getDelegacionConvenios()->toArray()
        );

        return $data;
    }

    public function serializarData(array $convenios): array
    {
        return array_map(fn(Convenio $convenio) => $this->serializer($convenio), $convenios);
    }
}
