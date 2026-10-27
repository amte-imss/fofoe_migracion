<?php

namespace App\Service\Residente;

use App\Entity\Posgrado\Residencia;
use App\Event\Posgrado\ReferenciaBancariaResidenteDownloadedEvent;
use Knp\Snappy\Pdf;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

class GeneradorReferenciaPagoResidentePDF implements GeneradorReferenciaPagoResidentePDFInterface
{
    private const array PDF_OPTIONS = [
        'page-size' => 'Letter',
        'encoding' => 'utf-8',
    ];

    public function __construct(
        private readonly Pdf $pdf,
        private readonly Environment $twig,
        private readonly EventDispatcherInterface $dispatcher,
    ) {}

    public function generarPDF(Residencia $residencia): string
    {
        $pago = $residencia->getPagos()->first() ?: null;
        $referencia = $pago?->getReferenciaBancaria() ?? '';

        $html = $this->twig->render('residente/formato/residencia/referencia_pago_ex_imss.html.twig', [
            'residencia' => $residencia,
            'residente' => $residencia->getResidente(),
            'pago' => $pago,
            'referencia' => $referencia,
        ]);

        $output = $this->pdf->getOutputFromHtml($html, self::PDF_OPTIONS);

        $this->dispatcher->dispatch(
            new ReferenciaBancariaResidenteDownloadedEvent($residencia, $referencia),
            ReferenciaBancariaResidenteDownloadedEvent::NAME
        );

        return $output;
    }
}
