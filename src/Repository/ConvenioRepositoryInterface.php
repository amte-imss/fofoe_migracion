<?php

namespace App\Repository;

use Doctrine\Persistence\ObjectRepository;

interface ConvenioRepositoryInterface extends ObjectRepository
{
    public function getAllNivelesByConvenio(int $id): array;

    public function getConvenioGeneral(int $institucion_id, string $vigencia): mixed;

    public function getConveniosUnicosByInstitucionId(int $id): array;

    public function getAllConvenios(
        bool $isCamex,
        mixed $allDescription = null,
        mixed $lookingForBy = null,
        string $orderDirecctionBy = '',
        string $orderBy = ''
    ): array;

    public function getFilteredAgreements(
        array $convenios,
        mixed $lookingForBy,
        bool $isCamex,
        mixed $allDescription
    ): array;

    public function getCountConvenios(bool $isCamex, mixed $lookingForBy = null): int;

    public function deleteConvenioCame(int $idConvenioCame): mixed;

    public function validateStrWithWhiteSpaces(string $stringChain): bool;

    public function dateSlashValidate(string $dateToValidate): bool;

    public function getConvenioByNameorSector(string $nombre, string $sector, string $vigencia): mixed;

    public function getConveniosPorRfcDeInstitucion(
        mixed $instituciones,
        bool $esForm = false,
        ?int $idConvenio = null
    ): array;

    public function getExpiredValidity(\DateTimeInterface $fechaVigencia): int;

    public function getExpiredValidityClass(\DateTimeInterface $fechaVigencia): string;

    public function conversorOfFileSize(int $fileSize): string;

    public function getConveniosPorInstitucionId(int $idInstitucion): array;

    public function getDateFormatToSave(mixed $dateToSave): ?\DateTimeInterface;

    public function getRelationalGeneralAgreement(string $name): mixed;

    public function getListaInstituciones(mixed $instituciones): array;

    public function getRazonSocialInstitucion(mixed $instituciones): string;

    public function getCalculatedFuica(
        string $rfc,
        string $tipoConvenio,
        mixed $fechaFirmaConvenio,
        string $aniosVigencia,
        int $idConvenio
    ): string;

    public function addZerosToText(
        string $text,
        int $zerosQty = 2,
        string $directionOfTheZeros = 'left'
    ): string;

    public function getFechaVigenciaCalculada(
        string $aniosVigencia,
        \DateTimeInterface $fechaFirma
    ): \DateTimeInterface;

    public function getConvenioById(int $idConvenio): mixed;

    public function textLimit(string $text, int $limit): string;

    public function paginateAdminConvenios(
        array $filters,
        ?int $page = null,
        ?int $perPage = null
    ): array;

    public function paginateApiConvenios(
        array $filters,
        ?int $page = null,
        ?int $perPage = null
    ): array;

    public function getAdminConvenios(
        array $filters,
        ?int $page = null,
        ?int $perPage = null
    ): mixed;

    public function nextNumber(): int;

    public function getConveniosGeneralesVigentesByInstitucion(mixed $institucion): array;
}
