<?php

namespace App\Service;

use App\Calculator\CampoClinicoCalculator2025;
use App\Calculator\CampoClinicoCalculatorInterface;
use App\Entity\CampoClinico;
use App\Entity\Usuario;
use App\Event\FormatoFofofeDownloadedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Snappy\Pdf;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

class GeneradorFormatoFofoe implements GeneradorFormatoFofoeInterface
{
    public const string PDF_NAME = 'fofoe.pdf';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Pdf $pdf,
        private readonly Environment $twig,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly CampoClinicoCalculatorInterface $calculator,
    ) {
    }

    public function responsePdf(string $path, CampoClinico $campoClinico, bool $overwrite = false): string
    {
        $calculator2025 = new CampoClinicoCalculator2025();
        $detail2025     = $calculator2025->getDetail($campoClinico);
        $came           = $this->getCAMEorJDES($campoClinico);
        $file           = "{$path}/{$campoClinico->getSolicitud()->getNoSolicitud()}/cc_{$campoClinico->getId()}/"
            . $this->getFileName($campoClinico);

        if (!file_exists($file) || $overwrite) {
            try {
                $montoCC = $campoClinico->getMonto() > 0
                    ? $campoClinico->getMonto()
                    : $this->calculator->getMontoAPagar($campoClinico, $campoClinico->getSolicitud());

                $this->pdf->generateFromHtml(
                    $this->twig->render('formatos/fofoe.html.twig', [
                        'campo_clinico' => $campoClinico,
                        'came'          => $came,
                        'montoCC'       => $montoCC,
                        'detail2025'    => $detail2025,
                    ]),
                    $file,
                    ['page-size' => 'Letter', 'encoding' => 'utf-8'],
                    $overwrite
                );

                $this->dispatcher->dispatch(
                    new FormatoFofofeDownloadedEvent($campoClinico),
                    FormatoFofofeDownloadedEvent::NAME
                );
            } catch (\Exception) {
            }
        }

        return $file;
    }

    public function getFileName(CampoClinico $campoClinico): string
    {
        $type = $campoClinico->getCicloAcademico()->getId() === 1 ? 'CCS' : 'INT';

        return "{$campoClinico->getSolicitud()->getNoSolicitud()}-{$type}_{$campoClinico->getId()}_FormatoFOFOE.pdf";
    }

    private function getCAMEorJDES(CampoClinico $campoClinico): mixed
    {
        $unidad     = $campoClinico->getUnidad();
        $repository = $this->entityManager->getRepository(Usuario::class);

        return $unidad?->getEsUmae()
            ? $repository->getJDESByUnidad($unidad->getId())
            : $repository->getCamebyDelegacion($campoClinico->getSolicitud()->getDelegacion()->getId());
    }
}
