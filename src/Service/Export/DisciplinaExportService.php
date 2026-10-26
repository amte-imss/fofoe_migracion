<?php

namespace App\Service\Export;

use App\Entity\Disciplina;
use App\Repository\DisciplinaRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class DisciplinaExportService implements ExportInterface
{
    public function __construct(
        private readonly DisciplinaRepository $disciplinaRepository,
    ) {
    }

    public function export(): string
    {
        $headers = ['NOMBRE', 'DESCRIPCIÓN', 'ESTADO'];

        $rows = array_map(
            static fn (Disciplina $discipline): array => [
                mb_strtoupper((string) $discipline->getNombre()),
                mb_strtoupper((string) $discipline->getDescripcion()),
                $discipline->getActiva() ? 'ACTIVA' : 'INACTIVA',
            ],
            $this->disciplinaRepository->findAll(),
        );

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle('Disciplinas')
            ->setSubject('Lista de Exportación de Disciplinas')
            ->setDescription('Contiene la lista de Disciplinas.');

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
