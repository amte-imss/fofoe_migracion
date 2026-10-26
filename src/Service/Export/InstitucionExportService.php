<?php

namespace App\Service\Export;

use App\Entity\Institucion;
use App\Repository\InstitucionRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InstitucionExportService implements ExportInterface
{
    public function __construct(
        private readonly InstitucionRepository $institucionRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        #[Autowire('%app.institution_uploads_dir%')]
        private readonly string $institutionUploadsDir,
    ) {
    }

    public function export(): string
    {
        $headers = [
            'RFC', 'RAZÓN SOCIAL', 'NOMBRE COMERCIAL', 'OOAD', 'CORREO', 'SITIO WEB',
            'DIRECCIÓN', 'FAX', 'EXTENSIÓN', 'TELÉFONO', 'CÉDULA FISCAL',
        ];

        $institutions = $this->institucionRepository->getAdminInstituciones([])
            ->getQuery()
            ->getResult();

        $upper = static fn (mixed $value): string => mb_strtoupper((string) $value);

        $rows = array_map(
            fn (Institucion $institution): array => [
                $upper($institution->getRfc()),
                $upper($institution->getRazonSocial()),
                $upper($institution->getNombre()),
                $upper($institution->getDelegacion()?->getNombre()),
                $upper($institution->getCorreo()),
                $upper($institution->getSitioWeb()),
                $upper($institution->getDireccion()),
                $upper($institution->getFax()),
                $upper($institution->getExtension()),
                $upper($institution->getTelefono()),
                $this->cedulaUrl($institution),
            ],
            $institutions,
        );

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle('Instituciones')
            ->setSubject('Lista de Exportación de Instituciones')
            ->setDescription('Contiene la lista de Instituciones.');

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

    private function cedulaUrl(Institucion $institution): string
    {
        if (!file_exists(sprintf('%s/%d', $this->institutionUploadsDir, $institution->getId()))) {
            return '';
        }

        return $this->urlGenerator->generate(
            'name.institucion.cedula',
            ['id' => $institution->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
