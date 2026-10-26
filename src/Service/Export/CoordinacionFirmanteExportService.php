<?php

namespace App\Service\Export;

use App\Entity\CoordinacionFirmante;
use App\Repository\CoordinacionFirmanteRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class CoordinacionFirmanteExportService implements ExportInterface
{
    public function __construct(
        private readonly CoordinacionFirmanteRepository $coordinacionFirmanteRepository,
    ) {
    }

    public function export(): string
    {
        $headers = ['NOMBRE', 'ESTADO'];

        $rows = array_map(
            static fn (CoordinacionFirmante $coordination): array => [
                mb_strtoupper((string) $coordination->getNombre()),
                $coordination->getActiva() ? 'ACTIVA' : 'INACTIVA',
            ],
            $this->coordinacionFirmanteRepository->findAll(),
        );

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle('Coordinaciones Firmantes')
            ->setSubject('Lista de Exportación de Coordinaciones Firmantes')
            ->setDescription('Contiene la lista de Coordinaciones Firmantes.');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($rows, null, 'A2');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        ob_start();
        try {
            $writer->save('php://output');

            return (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }
    }
}
