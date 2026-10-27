<?php

namespace App\Service\Posgrado;

use App\Entity\Pago;
use App\Entity\Permiso;
use App\Entity\Posgrado\CargaMasiva;
use App\Entity\Posgrado\Residencia;
use App\Entity\Posgrado\ResidenciaInterface;
use App\Entity\Posgrado\Residente;
use App\Entity\Usuario;
use App\Event\Posgrado\ResidenciaEvent;
use App\Repository\ConfiguracionGlobalRepository;
use App\Repository\Posgrado\ResidenciaRepository;
use App\Service\GeneradorRefenciaBancaria2025;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Exception as SpreadsheetException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ResidenciaManager implements ResidenciaManagerInterface
{
    public const string CLAVE_CONFIG_MONTO_RES_IMSS = 'POSGR_RES_EX_IMMS_MONTO';
    public const string CLAVE_CONFIG_MONEDA_RES_IMSS = 'POSGR_RES_EX_IMMS_MONEDA';
    public const string PREFIX_MATRICULA = 'RES_';

    private const string FOLIO_TYPE = 'EXTRANJERO_IMSS';
    private const string RESIDENT_PERMISSION = 'ALUMNO_POSGRADO';
    private const string DEFAULT_CURRENCY = 'USD';
    private const string FILE_ERROR_MESSAGE = 'Ocurrió un error al procesar los datos del archivo';
    private const string BIRTH_DATE_PATTERN = '/\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])/';
    private const string EMAIL_PATTERN = '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/';
    private const int CURP_MIN_LENGTH = 18;
    private const int BATCH_SIZE = 10;

    private const array HEADERS = [
        'CATEGORIA',
        'DELEGACION',
        'OOAD',
        'TIPO_ESPECIALIDAD',
        'ESPECIALIDAD',
        'CLUES',
        'SEDE_ACADEMICA',
        'SUBSEDE',
        'NIVEL DE ATENCION',
        'DELEGACION/UMAE',
        'GRADO',
        'CURP',
        'FOLIO IMSS',
        'GENERO',
        'AP_PATERNO',
        'AP_MATERNO',
        'NOMBRE',
        'TIPO',
        'NACIONALIDAD',
        'CORREO',
        'CELULAR',
        'PASAPORTE',
    ];

    private const array OBLIGATORY_HEADERS = [
        'NOMBRE',
        'AP_PATERNO',
        'CORREO',
        'FOLIO IMSS',
        'GRADO',
        'NACIONALIDAD',
        'SEDE_ACADEMICA',
        'ESPECIALIDAD',
        'CURP',
        'PASAPORTE',
    ];

    private const array REQUIRED_FIELDS = [
        'CURP',
        'NOMBRE',
        'AP_PATERNO',
        'AP_MATERNO',
        'CATEGORIA',
        'OOAD',
        'TIPO_ESPECIALIDAD',
        'ESPECIALIDAD',
        'SEDE_ACADEMICA',
        'SUBSEDE',
        'FOLIO IMSS',
        'NACIONALIDAD',
        'PASAPORTE',
        'CORREO',
    ];

    private const array MAX_LENGTHS = [
        'NOMBRE'            => 250,
        'AP_PATERNO'        => 250,
        'AP_MATERNO'        => 250,
        'CATEGORIA'         => 50,
        'OOAD'              => 100,
        'TIPO_ESPECIALIDAD' => 15,
        'ESPECIALIDAD'      => 100,
        'SEDE_ACADEMICA'    => 150,
        'SUBSEDE'           => 250,
        'FOLIO IMSS'        => 30,
        'NACIONALIDAD'      => 100,
        'PASAPORTE'         => 15,
        'CORREO'            => 254,
    ];

    /** Column positions used by the single-entry form (see buildSingleEntryRow()). */
    private const array SINGLE_ENTRY_HEADERS = [
        'CATEGORIA'         => 0,
        'DELEGACION'        => 1,
        'OOAD'              => 2,
        'TIPO_ESPECIALIDAD' => 3,
        'ESPECIALIDAD'      => 4,
        'CLUES'             => false,
        'SEDE_ACADEMICA'    => 5,
        'SUBSEDE'           => 6,
        'NIVEL DE ATENCION' => false,
        'DELEGACION/UMAE'   => 7,
        'GRADO'             => 8,
        'CURP'              => 9,
        'FOLIO IMSS'        => 10,
        'GENERO'            => 11,
        'AP_PATERNO'        => 12,
        'AP_MATERNO'        => 13,
        'NOMBRE'            => 14,
        'TIPO'              => false,
        'NACIONALIDAD'      => 15,
        'CORREO'            => 16,
        'CELULAR'           => 17,
        'PASAPORTE'         => 18,
    ];

    /** @var array<string, int|false> */
    private array $indexHeaders;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ResidenciaRepository $residenciaRepository,
        private readonly ConfiguracionGlobalRepository $configuracionGlobalRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly GeneradorRefenciaBancaria2025 $generadorReferenciaBancaria,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
        $this->indexHeaders = array_fill_keys(self::HEADERS, false);
    }

    public function processDataFromXlsx(string $filenameXlsx, mixed $ciclo, Usuario $usuario): array
    {
        $spreadsheet = $this->loadSpreadsheet($filenameXlsx);

        $validation = $this->validateFile($spreadsheet);
        if (!$validation['status']) {
            return [[], [], 'status' => false, 'errores' => $validation['errores']];
        }

        $this->entityManager->beginTransaction();
        try {
            $cargaMasiva = $this->createCargaMasiva($usuario);
            [$headers, $data] = $this->processDataFromSpreadsheet($spreadsheet, $ciclo, $cargaMasiva);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        return [$data, $headers, 'status' => true];
    }

    /**
     * Registers a single foreign IMSS resident from the single-entry form data.
     */
    public function processDatoUnitario(array $data, Usuario $usuario): array
    {
        $existingFolios = $this->residenciaRepository->getFoliosYaExistentes(
            [$data['folioImss']],
            self::FOLIO_TYPE
        );
        if (count($existingFolios) > 0) {
            return ['data' => [], 'errores' => [$data['folioImss']], 'status' => false];
        }

        $this->indexHeaders = self::SINGLE_ENTRY_HEADERS;
        $rowData = $this->buildSingleEntryRow($data);

        $this->entityManager->beginTransaction();
        try {
            $cargaMasiva = $this->createCargaMasiva($usuario);
            $residencia = $this->makeResidencia($rowData, $data['ciclo'], $cargaMasiva);

            if ($residencia->getFolio()) {
                $this->dispatcher->dispatch(
                    new ResidenciaEvent($residencia),
                    ResidenciaEvent::RESIDENCIA_REGISTRADA
                );
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        return [
            'data' => [
                'curp'         => $data['curp'],
                'folioImss'    => $data['folioImss'],
                'nombre'       => trim($data['nombre'] . ' ' . $data['apellidoPaterno'] . ' ' . $data['apellidoMaterno']),
                'especialidad' => $data['especialidad'],
                'ciclo'        => $data['ciclo'],
            ],
            'errores' => [],
            'status'  => true,
        ];
    }

    public function actualizarResidenciaExImss(Residencia $residencia): void
    {
        $this->updateUsuarioForResidente($residencia);

        /** @var Pago|null $pago */
        $pago = $residencia->getLastPago();
        if ($pago && !$pago->getFechaPago()) {
            $pago->setMonto($residencia->getMonto());
            $pago->setTipoMoneda($residencia->getTipoMoneda());
        }

        $this->entityManager->persist($residencia);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(
            new ResidenciaEvent($residencia),
            ResidenciaEvent::RESIDENCIA_ACTUALIZADA
        );
    }

    /**
     * @return array{status: bool, errores: list<string>}
     */
    public function validateFile(Spreadsheet $spreadsheet): array
    {
        [$headers, $rows] = $this->readSheet($spreadsheet);
        $this->checkHeaders($headers);

        $folios = [];
        foreach ($rows as $index => $row) {
            if (!$this->hasData($row)) {
                continue;
            }
            $this->checkData($row, $index + 1);
            $folios[] = $this->rawCell($row, 'FOLIO IMSS');
        }

        $existingFolios = $this->residenciaRepository->getFoliosYaExistentes($folios, self::FOLIO_TYPE);
        $errors = array_map(static fn(array $item): string => (string) $item['folio'], $existingFolios);

        return ['status' => count($errors) === 0, 'errores' => $errors];
    }

    private function loadSpreadsheet(string $filename): Spreadsheet
    {
        try {
            $spreadsheet = IOFactory::createReaderForFile($filename)
                ->setReadDataOnly(true)
                ->load($filename);
        } catch (SpreadsheetException) {
            throw new InvalidArgumentException(self::FILE_ERROR_MESSAGE);
        }

        if ($spreadsheet->getSheetCount() < 1) {
            throw new InvalidArgumentException('El archivo no contiene datos');
        }

        return $spreadsheet;
    }

    /**
     * @return array{0: list<mixed>, 1: list<list<mixed>>} headers and data rows
     */
    private function readSheet(Spreadsheet $spreadsheet): array
    {
        $rows = [];
        try {
            foreach ($spreadsheet->getActiveSheet()->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $cells = [];
                foreach ($cellIterator as $cell) {
                    $cells[] = $cell->getValue();
                }
                $rows[] = $cells;
            }
        } catch (SpreadsheetException) {
            throw new InvalidArgumentException(self::FILE_ERROR_MESSAGE);
        }

        $headers = array_shift($rows) ?? [];

        return [$headers, $rows];
    }

    /**
     * @return array{0: list<mixed>, 1: list<list<mixed>>}
     */
    private function processDataFromSpreadsheet(Spreadsheet $spreadsheet, mixed $ciclo, CargaMasiva $cargaMasiva): array
    {
        [$headers, $rows] = $this->readSheet($spreadsheet);
        $this->checkHeaders($headers);

        $cargaMasivaId = $cargaMasiva->getId();
        $data = [];

        foreach ($rows as $index => $row) {
            if (!$this->hasData($row)) {
                continue;
            }

            $data[] = $row;
            $residencia = $this->makeResidencia($row, $ciclo, $cargaMasiva);

            if ($residencia->getFolio()) {
                $this->dispatcher->dispatch(
                    new ResidenciaEvent($residencia),
                    ResidenciaEvent::RESIDENCIA_REGISTRADA
                );
                $this->entityManager->flush();
            }

            if (($index + 1) % self::BATCH_SIZE === 0) {
                $this->entityManager->clear();
                // clear() detaches every entity; re-attach the batch upload so the next rows can reference it
                $cargaMasiva = $this->entityManager->getReference(CargaMasiva::class, $cargaMasivaId);
            }
        }

        return [$headers, $data];
    }

    private function createCargaMasiva(Usuario $usuario): CargaMasiva
    {
        $cargaMasiva = new CargaMasiva();
        $cargaMasiva->setUsuario($usuario);
        $this->entityManager->persist($cargaMasiva);
        $this->entityManager->flush();

        return $cargaMasiva;
    }

    private function buildSingleEntryRow(array $data): array
    {
        return [
            /* 0  CATEGORIA         */ '',
            /* 1  DELEGACION        */ '',
            /* 2  OOAD              */ $data['ooad'] ?? '',
            /* 3  TIPO_ESPECIALIDAD */ $data['tipoEspecialidad'] ?? '',
            /* 4  ESPECIALIDAD      */ $data['especialidad'] ?? '',
            /* 5  SEDE_ACADEMICA    */ $data['sedeAcademica'] ?? '',
            /* 6  SUBSEDE           */ $data['subsede'] ?? '',
            /* 7  DELEGACION/UMAE   */ $data['delegacionUmae'] ?? '',
            /* 8  GRADO             */ $data['grado'] ?? 0,
            /* 9  CURP              */ $data['curp'] ?? '',
            /* 10 FOLIO IMSS        */ $data['folioImss'] ?? '',
            /* 11 GENERO            */ $data['genero'] ?? '',
            /* 12 AP_PATERNO        */ $data['apellidoPaterno'] ?? '',
            /* 13 AP_MATERNO        */ $data['apellidoMaterno'] ?? '',
            /* 14 NOMBRE            */ $data['nombre'] ?? '',
            /* 15 NACIONALIDAD      */ $data['nacionalidad'] ?? '',
            /* 16 CORREO            */ $data['correo'] ?? '',
            /* 17 CELULAR           */ $data['celular'] ?? '',
            /* 18 PASAPORTE         */ $data['pasaporte'] ?? '',
        ];
    }

    private function checkData(array $row, int $rowNumber): void
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (trim((string) $this->rawCell($row, $field)) === '') {
                throw new InvalidArgumentException("El {$field} del registro # {$rowNumber}, es obligatorio");
            }
        }

        $curp = (string) $this->rawCell($row, 'CURP');
        if (mb_strlen($curp) < self::CURP_MIN_LENGTH) {
            throw new InvalidArgumentException("El CURP del registro # {$rowNumber} : {$curp}, no cumple con el formato correcto");
        }

        foreach (self::MAX_LENGTHS as $field => $maxLength) {
            $value = (string) $this->rawCell($row, $field);
            if (mb_strlen($value) > $maxLength) {
                throw new InvalidArgumentException("El {$field} del registro # {$rowNumber} : {$value}, es mayor a {$maxLength} letras");
            }
        }

        $email = (string) $this->rawCell($row, 'CORREO');
        if (!preg_match(self::EMAIL_PATTERN, $email)) {
            throw new InvalidArgumentException("El CORREO del registro # {$rowNumber} : {$email}, no es un correo válido");
        }
    }

    private function hasData(array $row): bool
    {
        return array_any($row, static fn(mixed $item): bool => (bool) $item);
    }

    private function checkHeaders(array $headers): void
    {
        foreach (self::HEADERS as $header) {
            $this->indexHeaders[$header] = array_search($header, $headers, true);
        }

        foreach (self::OBLIGATORY_HEADERS as $header) {
            if ($this->indexHeaders[$header] === false) {
                throw new InvalidArgumentException('El archivo no contiene la columna ' . $header);
            }
        }
    }

    private function hasColumn(string $header): bool
    {
        return ($this->indexHeaders[$header] ?? false) !== false;
    }

    /**
     * Raw cell value; null when the column does not exist in the file.
     */
    private function rawCell(array $row, string $header): mixed
    {
        $index = $this->indexHeaders[$header] ?? false;

        return $index === false ? null : ($row[$index] ?? null);
    }

    /**
     * Cell value falling back to $default for empty values (legacy `?:` semantics).
     */
    private function cell(array $row, string $header, mixed $default = ''): mixed
    {
        return $this->rawCell($row, $header) ?: $default;
    }

    private function extractBirthDate(?string $curp): ?string
    {
        if (!$curp) {
            return null;
        }

        return preg_match(self::BIRTH_DATE_PATTERN, $curp, $matches) ? $matches[0] : null;
    }

    private function makeUsuarioForResidente(array $rowData): Usuario
    {
        $usuario = new Usuario();
        $usuario->setNombre($this->cell($rowData, 'NOMBRE'));
        $usuario->setApellidoPaterno($this->cell($rowData, 'AP_PATERNO'));
        $usuario->setCorreo($this->cell($rowData, 'CORREO', null));
        $usuario->setMatricula($this->rawCell($rowData, 'CURP') . '_' . bin2hex(random_bytes(4)));
        $usuario->setActivo(true);

        if ($this->hasColumn('AP_MATERNO')) {
            $usuario->setApellidoMaterno($this->cell($rowData, 'AP_MATERNO'));
        }
        if ($this->hasColumn('GENERO')) {
            $usuario->setSexo($this->cell($rowData, 'GENERO'));
        }
        if ($this->hasColumn('CELULAR')) {
            $usuario->setTelefono($this->cell($rowData, 'CELULAR'));
        }

        $password = bin2hex(random_bytes(4));
        if ($this->hasColumn('CURP')) {
            $usuario->setCurp($this->cell($rowData, 'CURP'));
            $password = $this->extractBirthDate($usuario->getCurp()) ?? $password;
        }

        $usuario->setContrasena($this->passwordHasher->hashPassword($usuario, $password));
        $usuario->setPlainPassword($password);

        $this->entityManager->persist($usuario);
        $this->entityManager->flush();
        $this->entityManager->refresh($usuario);

        $residentPermission = $this->entityManager
            ->getRepository(Permiso::class)
            ->findOneBy(['clave' => self::RESIDENT_PERMISSION]);

        if (!$residentPermission) {
            throw new \LogicException(sprintf('Permission "%s" not found', self::RESIDENT_PERMISSION));
        }

        $usuario->addPermiso($residentPermission);
        $this->entityManager->persist($usuario);
        $this->entityManager->flush();

        return $usuario;
    }

    private function updateUsuarioForResidente(Residencia $residencia): void
    {
        $residente = $residencia->getResidente();
        $usuario = $residente->getUsuario();

        // CURP is used as both the login and the resident folio
        $usuario->setMatricula($usuario->getCurp());
        $residente->setFolio($usuario->getCurp());

        if ($this->extractBirthDate($usuario->getCurp()) !== null) {
            // TODO: legacy test-only password; production should use the CURP birth date
            $password = '12345678';
            $usuario->setContrasena($this->passwordHasher->hashPassword($usuario, $password));
        }

        $this->entityManager->persist($usuario);
    }

    private function makeResidente(array $rowData): Residente
    {
        $residente = new Residente();
        $residente->setEstatus('NUEVO');
        // The resident folio stores the CURP
        $residente->setFolio($this->cell($rowData, 'CURP'));
        $residente->setNacionalidad($this->cell($rowData, 'NACIONALIDAD'));
        $residente->setGrado($this->parseGrado($this->rawCell($rowData, 'GRADO')));
        $residente->setSede($this->cell($rowData, 'SEDE_ACADEMICA'));
        $residente->setSubsede($this->cell($rowData, 'SUBSEDE'));
        $residente->setEspecialidad($this->cell($rowData, 'ESPECIALIDAD'));
        $residente->setPasaporte($this->cell($rowData, 'PASAPORTE'));
        $residente->setTipo(ResidenciaInterface::TIPO_EXTRANJERO_IMSS);
        $residente->setUsuario($this->makeUsuarioForResidente($rowData));

        if ($residente->getFolio()) {
            $this->entityManager->persist($residente);
        }

        return $residente;
    }

    private function makeResidencia(array $rowData, mixed $ciclo, CargaMasiva $cargaMasiva): Residencia
    {
        $residencia = new Residencia();
        $residencia->setTipo(ResidenciaInterface::TIPO_EXTRANJERO_IMSS);
        $residencia->setResidente($this->makeResidente($rowData));
        $residencia->setEspecialidad($this->cell($rowData, 'ESPECIALIDAD'));
        $residencia->setGrado($this->parseGrado($this->rawCell($rowData, 'GRADO')));
        $residencia->setSede($this->cell($rowData, 'SEDE_ACADEMICA'));
        $residencia->setSubsede($this->cell($rowData, 'SUBSEDE'));
        $residencia->setFolio($this->cell($rowData, 'FOLIO IMSS'));
        $residencia->setCiclo($ciclo);
        $residencia->setEstatus(ResidenciaInterface::NUEVO);
        $residencia->setCargaMasiva($cargaMasiva);

        $this->configMontoResidencia($residencia);

        if ($this->hasColumn('CATEGORIA')) {
            $residencia->setCategoria($this->rawCell($rowData, 'CATEGORIA'));
        }
        if ($this->hasColumn('DELEGACION')) {
            $residencia->setDelegacion($this->rawCell($rowData, 'DELEGACION'));
        }
        if ($this->hasColumn('OOAD')) {
            // OOAD intentionally overrides DELEGACION (legacy behavior)
            $residencia->setDelegacion($this->rawCell($rowData, 'OOAD'));
        }
        if ($this->hasColumn('TIPO_ESPECIALIDAD')) {
            $residencia->setTipoEspecialidad($this->rawCell($rowData, 'TIPO_ESPECIALIDAD'));
        }
        if ($this->hasColumn('CLUES')) {
            $residencia->setClues($this->cell($rowData, 'CLUES'));
        }
        if ($this->hasColumn('NIVEL DE ATENCION')) {
            $residencia->setNivelAtencion($this->cell($rowData, 'NIVEL DE ATENCION', null));
        }
        if ($this->hasColumn('DELEGACION/UMAE')) {
            $residencia->setTipoDelegacionUmae($this->cell($rowData, 'DELEGACION/UMAE'));
        }

        if ($residencia->getFolio()) {
            $this->entityManager->persist($residencia);
            $residencia->addPago($this->makePago($residencia));
            $this->entityManager->persist($residencia);
        }

        return $residencia;
    }

    private function configMontoResidencia(Residencia $residencia): void
    {
        $amountConfig = $this->configuracionGlobalRepository->findOneBy(
            ['clave' => self::CLAVE_CONFIG_MONTO_RES_IMSS]
        );
        $currencyConfig = $this->configuracionGlobalRepository->findOneBy(
            ['clave' => self::CLAVE_CONFIG_MONEDA_RES_IMSS]
        );

        $residencia->setMonto($amountConfig ? (float) $amountConfig->getValor() : null);
        $residencia->setTipoMoneda($currencyConfig ? $currencyConfig->getValor() : self::DEFAULT_CURRENCY);
    }

    private function makePago(Residencia $residencia): Pago
    {
        $pago = new Pago();
        $pago->setMonto($residencia->getMonto());
        $pago->setTipoMoneda($residencia->getTipoMoneda());
        $pago->setResidencia($residencia);
        $pago->setReferenciaBancaria($this->generadorReferenciaBancaria->generateNextReference());

        return $pago;
    }

    /**
     * Extracts the integer grade, supporting formats such as "R5", "r3" or "5".
     */
    private function parseGrado(mixed $value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        return preg_match('/\d+/', (string) $value, $matches) ? (int) $matches[0] : 0;
    }
}
