<?php

namespace App\Service;

use App\Entity\CampoClinico;
use App\Entity\EstatusCampo;
use App\Entity\TrabajadorImss;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CampoClinicoManager implements CampoClinicoManagerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface        $logger,
        private readonly NormalizerInterface    $normalizer,
    ) {}

    public function create(CampoClinico $campoClinico): array
    {
        $campoClinico->setMonto(-1);
        $campoClinico->setEstatus($this->entityManager->getRepository(EstatusCampo::class)->find(1));
        $this->entityManager->persist($campoClinico);

        try {
            foreach ($campoClinico->getTrabajadoresBecados() as $trabajador) {
                /** @var TrabajadorImss $trabajador */
                $trabajador->setCampoClinico($campoClinico);
                $this->entityManager->persist($trabajador);
            }
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            $this->logger->critical($e->getMessage());
            return ['status' => false, 'error' => $e->getMessage()];
        }

        return [
            'status'  => true,
            'message' => 'Campo clínico registrado con éxito',
            'object'  => $this->normalizer->normalize($campoClinico, 'json', [
                'attributes' => [
                    'id', 'periodo',
                    'unidad'         => ['id', 'nombre'],
                    'lugaresAutorizados',
                    'convenio'       => [
                        'id',
                        'carrera'        => ['id', 'nombre', 'nivelAcademico' => ['id', 'nombre']],
                        'cicloAcademico' => ['id', 'nombre'],
                    ],
                    'cicloAcademico' => ['id', 'nombre'],
                    'lugaresSolicitados',
                    'fechaInicial', 'fechaFinal', 'fechaInicialFormatted', 'fechaFinalFormatted',
                    'horario', 'promocion', 'asignatura',
                    'trabajadoresBecados' => [
                        'matricula', 'delegacion' => ['id'], 'nombre',
                        'aPaterno', 'aMaterno', 'adscripcion' => ['id'],
                    ],
                ],
            ]),
        ];
    }

    public function uploadFormatoFofoe(CampoClinico $campoClinico): array
    {
        try {
            $campoClinico->setValidateFormatoFofoe(null);
            $this->entityManager->persist($campoClinico);
            $this->entityManager->flush();

            return ['status' => true, 'message' => 'Formato FOFOE cargado con éxito'];
        } catch (\Exception $e) {
            return ['status' => false, 'error' => $e->getMessage()];
        }
    }

    public function delete(CampoClinico $campoClinico): array
    {
        try {
            foreach ($campoClinico->getTrabajadoresBecados() as $trabajadorBecado) {
                $this->entityManager->remove($trabajadorBecado);
            }
            $this->entityManager->remove($campoClinico);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return ['status' => false, 'error' => $e->getMessage()];
        }

        return ['status' => true];
    }
}
