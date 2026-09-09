<?php

namespace App\Entity;

use App\DTO\IE\GestionPagoDTO;
use App\Entity\Enfermeria\Alumno;
use App\Entity\Posgrado\Residencia;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use App\Entity\Simulacion\Solicitud as SimulacionSolicitud;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: \App\Repository\PagoRepository::class)]
#[ORM\Table(name: 'pago')]
class Pago implements ComprobantePagoInterface, \Stringable
{
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    #[ORM\Column(type: 'decimal', precision: 14, scale: 4)]
    private $monto;

    #[ORM\Column(type: 'decimal', precision: 14, scale: 4, nullable: true)]
    private $montoRegistrado;

    #[ORM\Column(type: 'date', nullable: true)]
    private $fechaPagoRegistrada;

    #[ORM\Column(type: 'date', nullable: true)]
    private $fechaPago;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Solicitud::class, inversedBy: 'pagos')]
    #[ORM\JoinColumn(name: 'solicitud_id', referencedColumnName: 'id')]
    private $solicitud;

    #[ORM\Column(type: 'integer', nullable: false, name: 'solicitud_id')]
    private $solicitudId;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Posgrado\Residencia::class, inversedBy: 'pagos')]
    #[ORM\JoinColumn(name: 'posgrado_residencia_id', referencedColumnName: 'id')]
    private $residencia;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $comprobantePago;

    #[Vich\UploadableField(mapping: 'comprobantes_pagos', fileNameProperty: 'comprobantePago')]
    #[Assert\File(
        maxSize: '2m',
        mimeTypes: ['application/pdf', 'application/x-pdf'],
        mimeTypesMessage: 'Solo se admiten archivos PDF'
    )]
    private ?File $comprobantePagoFile = null;

    #[ORM\Column(type: 'string', length: 100)]
    private $referenciaBancaria;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $validado;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $requiereFactura;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $observaciones;

    #[Assert\Valid]
    #[ORM\ManyToOne(targetEntity: \App\Entity\Factura::class, inversedBy: 'pagos', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'factura_id', referencedColumnName: 'id')]
    private $factura;

    #[ORM\Column(type: 'date', nullable: true)]
    private $fechaCreacion;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $facturaGenerada;

    #[ORM\Column(type: 'string', length: 10, nullable: true, options: ['default' => 'MXN'])]
    private $tipoMoneda;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Simulacion\Solicitud::class, inversedBy: 'pagos')]
    #[ORM\JoinColumn(name: 'simulacion_solicitud_id', referencedColumnName: 'id')]
    private $simulacionSolicitud;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Enfermeria\Alumno::class, inversedBy: 'pagos')]
    #[ORM\JoinColumn(name: 'escuela_enf_alumno_id', referencedColumnName: 'id')]
    private $escuelaEnfermeriaSolicitud;

    #[ORM\Column(type: 'integer', nullable: false, name: 'escuela_enf_alumno_id')]
    private $escuelaEnfermeriaSolicitudId;

    #[ORM\ManyToOne(targetEntity: \App\Entity\EduPer\Solicitud::class, inversedBy: 'pagos')]
    #[ORM\JoinColumn(name: 'edu_per_request_id', referencedColumnName: 'id')]
    private $eduPerSolicitud;

    #[ORM\Column(type: 'integer', nullable: false, name: 'edu_per_request_id')]
    private $eduPerSolicitudId;

    #[ORM\Column(type: 'boolean', nullable: true, name: 'is_test', options: ['default' => false])]
    private $isTest;

    public function __construct()
    {
        $this->isTest        = false;
        $this->fechaCreacion = Carbon::now();
    }

    public function getId(): ?int { return $this->id; }

    public function setMonto(mixed $monto): self { $this->monto = $monto; return $this; }
    public function getMonto(): mixed { return $this->monto; }

    public function setMontoRegistrado(mixed $montoRegistrado): self { $this->montoRegistrado = $montoRegistrado; return $this; }
    public function getMontoRegistrado(): mixed { return $this->montoRegistrado; }

    public function setSolicitud(?Solicitud $solicitud = null): self { $this->solicitud = $solicitud; return $this; }
    public function getSolicitud(): ?Solicitud { return $this->solicitud; }

    public function setResidencia(?Residencia $residencia = null): self { $this->residencia = $residencia; return $this; }
    public function getResidencia(): ?Residencia { return $this->residencia; }

    public function setComprobantePago(?string $comprobantePago): self { $this->comprobantePago = $comprobantePago; return $this; }
    public function getComprobantePago(): ?string { return $this->comprobantePago; }

    public function isComprobantePagoCargado(): bool { return null !== $this->comprobantePago; }

    public function setReferenciaBancaria(string $referenciaBancaria): self { $this->referenciaBancaria = $referenciaBancaria; return $this; }
    public function getReferenciaBancaria(): string { return $this->referenciaBancaria; }

    public function setValidado(?bool $validado): self { $this->validado = $validado; return $this; }
    public function getValidado(): ?bool { return $this->validado; }
    public function isValidado(): bool { return (bool) $this->getValidado(); }

    public function setObservaciones(?string $observaciones): self { $this->observaciones = $observaciones; return $this; }
    public function getObservaciones(): ?string { return $this->observaciones; }

    public function setTipoMoneda(?string $tipoMoneda): self { $this->tipoMoneda = $tipoMoneda; return $this; }
    public function getTipoMoneda(): ?string { return $this->tipoMoneda; }

    public function getFactura(): ?Factura { return $this->factura; }
    public function setFactura(?Factura $factura = null): self { $this->factura = $factura; return $this; }

    public function isRequiereFactura(): ?bool { return $this->requiereFactura; }
    public function setRequiereFactura(?bool $requiereFactura): void { $this->requiereFactura = $requiereFactura; }

    public function getComprobantePagoFile(): ?File { return $this->comprobantePagoFile; }
    public function setComprobantePagoFile(?File $comprobantePagoFile = null): void
    {
        $this->comprobantePagoFile = $comprobantePagoFile;
        $this->setFechaCreacion(Carbon::now());
    }

    public function setFechaPago(mixed $fechaPago = null): self { $this->fechaPago = $fechaPago; return $this; }
    public function getFechaPago(): mixed { return $this->fechaPago; }

    public function getFechaPagoFormatted(): string
    {
        return $this->getFechaPago() ? $this->getFechaPago()->format('d/m/Y') : '';
    }

    public function setFechaPagoRegistrada(?\DateTimeInterface $fechaPagoRegistrada = null): self { $this->fechaPagoRegistrada = $fechaPagoRegistrada; return $this; }
    public function getFechaPagoRegistrada(): ?\DateTimeInterface { return $this->fechaPagoRegistrada; }

    public function getFechaPagoRegistradaFormatted(): string
    {
        return $this->getFechaPagoRegistrada() ? $this->getFechaPagoRegistrada()->format('d/m/Y') : '';
    }

    public function getFechaCreacion(): mixed { return $this->fechaCreacion; }
    public function setFechaCreacion(mixed $fechaCreacion): void { $this->fechaCreacion = $fechaCreacion; }

    public function getGestionPago(): GestionPagoDTO { return GestionPagoDTO::create($this); }

    public function getCamposPagados(): array
    {
        $campos       = $this->solicitud->getCamposClinicos();
        $tiempos      = [];
        $camposPagados = [];

        foreach ($campos as $campo) {
            $inicial = Carbon::instance($campo->getFechaInicial());

            if (
                $this->solicitud->getTipoPago() == Solicitud::TIPO_PAGO_UNICO ||
                ($this->solicitud->getTipoPago() == Solicitud::TIPO_PAGO_MULTIPLE &&
                    $this->referenciaBancaria == $campo->getReferenciaBancaria())
            ) {
                $final                     = $this->getFechaPago() ? Carbon::instance($this->getFechaPago()) : '';
                $tiempos[$campo->getId()]  = $this->getFechaPago() ? $final->diffInDays($inicial) : '';
                if ($campo->getLugaresAutorizados() > 0) {
                    $camposPagados[] = $campo;
                }
            }
        }

        return ['campos' => $camposPagados, 'tiempos' => $tiempos];
    }

    public function isFacturaGenerada(): ?bool { return $this->facturaGenerada; }
    public function setFacturaGenerada(?bool $facturaGenerada): void { $this->facturaGenerada = $facturaGenerada; }

    public function getSimulacionSolicitud(): ?SimulacionSolicitud { return $this->simulacionSolicitud; }
    public function setSimulacionSolicitud(mixed $simulacionSolicitud): void { $this->simulacionSolicitud = $simulacionSolicitud; }

    public function getEscuelaEnfermeriaSolicitud(): ?Alumno { return $this->escuelaEnfermeriaSolicitud; }
    public function setEscuelaEnfermeriaSolicitud(mixed $escuelaEnfermeriaSolicitud): void { $this->escuelaEnfermeriaSolicitud = $escuelaEnfermeriaSolicitud; }

    public function getEscuelaEnfermeriaSolicitudId(): ?int { return $this->escuelaEnfermeriaSolicitudId; }
    public function setEscuelaEnfermeriaSolicitudId(mixed $escuelaEnfermeriaSolicitudId): void { $this->escuelaEnfermeriaSolicitudId = $escuelaEnfermeriaSolicitudId; }

    public function getStatusFormatted(): string
    {
        if (is_null($this->validado))                                      return 'Pendiente de Validación';
        if ($this->validado && $this->isFacturaGenerada())                 return 'Validado y Facturado';
        if ($this->validado && $this->isRequiereFactura() && !$this->isFacturaGenerada()) return 'Pendiente de Facturar';
        if ($this->validado)                                               return 'Validado';
        return 'No Validado';
    }

    public function getEduPerSolicitud(): mixed { return $this->eduPerSolicitud; }
    public function setEduPerSolicitud(mixed $eduPerSolicitud): void { $this->eduPerSolicitud = $eduPerSolicitud; }

    public function getEduPerSolicitudId(): ?int { return $this->eduPerSolicitudId; }
    public function setEduPerSolicitudId(mixed $eduPerSolicitudId): void { $this->eduPerSolicitudId = $eduPerSolicitudId; }

    public function isTest(): ?bool { return $this->isTest; }
    public function setIsTest(?bool $isTest): void { $this->isTest = $isTest; }

    public function getSolicitudId(): ?int { return $this->solicitudId; }

    public function __toString(): string
    {
        return $this->getId() . ' - referencia: ' . $this->getReferenciaBancaria();
    }
}
