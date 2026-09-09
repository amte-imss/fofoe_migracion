<?php

namespace App\Service;

use App\Entity\Solicitud;
use Knp\Snappy\Pdf;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Twig\Environment;

class GeneradorReferenciaBancariaPDF implements GeneradorReferenciaBancariaPDFInterface
{
    private const JSON_FORMAT = 'json';

    public function __construct(
        private readonly Pdf                 $pdf,
        private readonly Environment         $twig,
        private readonly NormalizerInterface $normalizer,
    ) {}

    public function generarPDF(Solicitud $solicitud, string $directoryOutput): Finder
    {
        $baseName    = $directoryOutput . '/' . $solicitud->getNoSolicitud();
        $institucion = $solicitud->getInstitucion();
        $campos      = $solicitud->getCamposClinicos();
        $esPagoUnico = $solicitud->getTipoPago() == Solicitud::TIPO_PAGO_UNICO;

        if ($esPagoUnico) {
            $this->generarPDFPago(
                $solicitud, $institucion, $campos, $esPagoUnico,
                $solicitud->getReferenciaBancaria(),
                $baseName . '.pdf'
            );
        } else {
            $i = 1;
            foreach ($campos as $campo) {
                if ($campo->getLugaresAutorizados() > 0) {
                    $this->generarPDFPago(
                        $solicitud, $institucion, [$campo], $esPagoUnico,
                        $campo->getReferenciaBancaria(),
                        $baseName . '-' . $i++ . '.pdf'
                    );
                }
            }
        }

        return (new Finder())->files()->in($directoryOutput);
    }

    private function generarPDFPago(
        Solicitud $solicitud,
        mixed     $institucion,
        mixed     $campos,
        bool      $esPagoUnico,
        mixed     $referencia,
        string    $output
    ): void {
        $this->pdf->generateFromHtml(
            $this->twig->render('ie/formato/solicitud/referencia_pago.html.twig', [
                'institucion' => $this->getNormalizeInstitucion($institucion),
                'solicitud'   => $this->getNormalizeSolicitud($solicitud),
                'campos'      => $campos,
                'esPagoUnico' => $esPagoUnico,
                'referencia'  => $referencia,
            ]),
            $output,
            ['page-size' => 'Letter', 'encoding' => 'utf-8'],
            true
        );
    }

    private function getNormalizeInstitucion(mixed $institucion): array
    {
        return $this->normalizer->normalize($institucion, self::JSON_FORMAT, [
            'attributes' => ['id', 'nombre', 'rfc'],
        ]);
    }

    private function getNormalizeSolicitud(Solicitud $solicitud): array
    {
        return $this->normalizer->normalize($solicitud, self::JSON_FORMAT, [
            'attributes' => ['id', 'noSolicitud', 'monto', 'tipoPago'],
        ]);
    }
}
