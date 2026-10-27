<?php

namespace App\Normalizer;

use App\Entity\ConvenioEspecial;
use App\Entity\ConvenioEspecialDelegacion;

class ConvenioEspecialNormalizer
{
    public function serializer(ConvenioEspecial $convenio): array
    {
        $institution = $convenio->getInstitucion();
        $institutionDelegation = $institution?->getDelegacion();
        $collaborationReason = $convenio->getMotivoColaboracion();
        $population = $convenio->getPoblacion();
        $resource = $convenio->getRecurso();
        $signatureDate = $convenio->getFechaFirma();
        $expirationDate = $convenio->getFechaVenceConvenio();

        $data = [
            'id' => $convenio->getId(),
            'numero' => $convenio->getNumero(),
            'rfc' => $convenio->getRfc(),
            'razon_social' => $convenio->getRazonSocial(),
            'nombre' => $convenio->getNombre(),
            'institucion' => [
                'id' => $convenio->getInstitucionId(),
                'nombre' => $institution?->getNombre(),
                'delegacion' => [
                    'id' => $institutionDelegation?->getId() ?? '',
                    'nombre' => $institutionDelegation?->getNombre() ?? '',
                ],
                'rfc' => $institution?->getRfc(),
                'razon_social' => $institution?->getRazonSocial(),
            ],
            'tipo' => $convenio->getTipo(),
            'sector' => $convenio->getSector(),
            'objetivo_colaboracion' => $convenio->getObjetivoColaboracion(),
            'motivo_colaboracion' => [
                'id' => $collaborationReason?->getId() ?? '',
                'nombre' => $collaborationReason?->getNombre() ?? '',
            ],
            'poblacion' => [
                'id' => $population?->getId() ?? '',
                'nombre' => $population?->getNombre() ?? '',
            ],
            'recurso' => [
                'id' => $resource?->getId() ?? '',
                'nombre' => $resource?->getNombre() ?? '',
            ],
            'fecha_firma' => $signatureDate?->format('Y-m-d') ?? '',
            'fecha_firma_formatted' => $signatureDate?->format('d-m-Y') ?? '',
            'vigencia_text' => $convenio->getVigenciaText(),
            'fecha_vence_convenio' => $expirationDate?->format('Y-m-d') ?? '',
            'fecha_vence_convenio_formatted' => $expirationDate?->format('d-m-Y') ?? '',
            'cargo_emite_signfirst' => $convenio->getCargoEmiteSignfirst(),
            'nombre_emite_signfirst' => $convenio->getNombreEmiteSignfirst(),
            'cargo_emite_signsecond' => $convenio->getCargoEmiteSignsecond(),
            'nombre_emite_signsecond' => $convenio->getNombreEmiteSignsecond(),
            'recibe_signfirst' => $convenio->getRecibeSignfirst(),
            'recibe_signsecond' => $convenio->getRecibeSignsecond(),
            'url_convenio' => $convenio->getUrlConvenio(),
            'fuica' => $convenio->getFuica(),
            'is_came' => $convenio->getIsCamex(),
            'status' => $convenio->getStatus(),
        ];

        $academicLevel = $convenio->getNivelAcademico();
        if ($academicLevel !== null) {
            $data['nivel_academico'] = [
                'id' => $academicLevel->getId(),
                'nombre' => $academicLevel->getNombre(),
            ];
        }

        try {
            $firstSigner = $convenio->getCargoFirmaIdFirst();
            if ($firstSigner !== null) {
                $data['cargoFirmaIdFirst'] = [
                    'id' => $firstSigner->getId(),
                    'nombre' => $firstSigner->getNombre(),
                ];
            }
        } catch (\Exception) {
        }

        try {
            $secondSigner = $convenio->getCargoFirmaIdSecond();
            if ($secondSigner !== null) {
                $data['cargoFirmaIdSecond'] = [
                    'id' => $secondSigner->getId(),
                    'nombre' => $secondSigner->getNombre(),
                ];
            }
        } catch (\Exception) {
        }

        $data['todasDelegaciones'] = $convenio->isTodasDelegaciones();

        $data['delegaciones'] = $convenio->getDelegacionConvenios()
            ->map(static fn (ConvenioEspecialDelegacion $agreementDelegation): array => [
                'id' => $agreementDelegation->getDelegacion()?->getId(),
                'nombre' => $agreementDelegation->getDelegacion()?->getNombre(),
            ])
            ->getValues();

        return $data;
    }

    /**
     * @param ConvenioEspecial[] $convenios
     */
    public function serializarData(array $convenios): array
    {
        return array_map(fn (ConvenioEspecial $convenio): array => $this->serializer($convenio), $convenios);
    }
}
