<?php

namespace App\Service;

use App\DTO\Sied;
use App\Entity\Unidad;
use App\Repository\DepartmentRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SIEDManager implements SIEDManagerInterface
{
    public function __construct(
        private readonly string $siedURL,
        private readonly LoggerInterface $logger,
        private readonly DepartmentRepositoryInterface $departmentRepository,
    ) {}

    public function getDataFromSIEDByMatriculaYClaveDelegacional(string $matricula, int $claveDelegacional): ?Sied
    {
        $this->logger->info('SOLICITANDO RECURSOS A SIED...', ['class' => self::class]);

        $delegacion = str_pad((string) $claveDelegacional, 2, '0', STR_PAD_LEFT);

        $body = [
            'Delegacion' => $delegacion,
            'Matricula'  => $matricula,
            'RFC'        => '',
        ];

        try {
            $client          = new \SoapClient($this->siedURL, ['trace' => 1, 'exceptions' => true]);
            $resultado_siap  = $client->__soapCall('ConsultaSIED', [$body]);
            $xmlResult       = simplexml_load_string($resultado_siap->ConsultaSIEDResult->any);

            if (empty($xmlResult)) {
                $this->logger->critical('No se obtuvo respuesta de SIED', [
                    'class'   => self::class,
                    'message' => $xmlResult,
                ]);
                throw new \RuntimeException('No se logró conectar con servidor remoto.');
            }

            $jsonDecodeResult = json_encode($xmlResult);
            if (!$jsonDecodeResult) {
                $this->logger->critical('¡Hubo un problema al convertir el texto a json!', [
                    'class' => self::class,
                    'value' => $xmlResult,
                ]);
            }

            $usuario = json_decode($jsonDecodeResult, true);
            if (is_null($usuario)) {
                $this->logger->critical('¡Hubo un problema al convertir el json a array!', [
                    'class' => self::class,
                    'value' => $jsonDecodeResult,
                ]);
            }
        } catch (\Exception $exception) {
            $this->logger->critical('¡Hubo un problema al solicitar el registro!', [
                'class'   => self::class,
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'body'    => $body,
            ]);

            throw new \RuntimeException('¡Hubo un problema al solicitar el registro!');
        }

        $this->logger->info('FINALIZO LA SOLICITUD DE RECURSOS A SIED...', ['class' => self::class]);

        if (isset($usuario['qry']['ERROR'])) {
            throw new NotFoundHttpException('No se encontró el registro');
        }

        return $this->makeSiedFromArrayWithNombreUnidad($usuario, $matricula, $delegacion);
    }

    private function makeSiedFromArray(array $usuarioSiap, string $matricula, string $delegacion): Sied
    {
        $sied = new Sied();

        $sied->nombre           = $usuarioSiap['EMPLEADOS']['NOMBRE'];
        $sied->apaterno         = $usuarioSiap['EMPLEADOS']['APE_PATERNO'];
        $sied->amaterno         = $usuarioSiap['EMPLEADOS']['APE_MATERNO'];
        $sied->curp             = $usuarioSiap['EMPLEADOS']['EMP_RECURP'];
        $sied->rfc              = $usuarioSiap['EMPLEADOS']['RFC'];
        $sied->sexo             = $usuarioSiap['EMPLEADOS']['SEXO'];
        $sied->fechaIngreso     = $usuarioSiap['EMPLEADOS']['FECHAINGRESO'];
        $sied->correoInstitucional = $usuarioSiap['EMPLEADOS']['CORREO'];
        $sied->delegacion       = $delegacion;
        $sied->matricula        = $matricula;
        $sied->antiguedad       = $usuarioSiap['EMPLEADOS']['ANTIGUEDAD'];
        $sied->adscripcion      = $usuarioSiap['EMPLEADOS']['ADSCRIPCION'];
        $sied->nombreCategoria  = $usuarioSiap['EMPLEADOS']['PUE_DESPUE'];
        $sied->claveCategoria   = $usuarioSiap['EMPLEADOS']['EMP_KEYPUE'];

        return $sied;
    }

    private function makeSiedFromArrayWithNombreUnidad(array $usuarioSiap, string $matricula, string $delegacion): Sied
    {
        $sied        = $this->makeSiedFromArray($usuarioSiap, $matricula, $delegacion);
        $adscripcion = $this->departmentRepository->findOneBy(['claveDepartamental' => $sied->adscripcion]);

        $sied->adscripcionId = $adscripcion?->getId();

        /** @var Unidad|null $unidad */
        $unidad      = $adscripcion?->getUnidad();
        $sied->unidad = $unidad?->getNombre() ?? '';

        return $sied;
    }
}
