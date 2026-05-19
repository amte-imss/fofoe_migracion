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

/**
 * Pago
 *
 * @Vich\Uploadable
 */
#[ORM\Entity(repositoryClass: \App\Repository\PagoRepository::class)]
#[ORM\Table(name: 'pago')]
class Pago implements ComprobantePagoInterface, \Stringable
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'decimal', precision: 14, scale: 4)]
    private $monto;

    /**
     * @var string
     */
    #[ORM\Column(type: 'decimal', precision: 14, scale: 4, nullable: true)]
    private $montoRegistrado;

    #[ORM\Column(type: 'date', nullable: true)]
    private $fechaPagoRegistrada;

    #[ORM\Column(type: 'date', nullable: true)]
    private $fechaPago;

    /**
     * @var Solicitud
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Solicitud::class, inversedBy: 'pagos')]
    #[ORM\JoinColumn(name: 'solicitud_id', referencedColumnName: 'id')]
    private $solicitud;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false, name: 'solicitud_id')]
    private $solicitudId;

    /**
     * @var Residencia
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Posgrado\Residencia::class, inversedBy: 'pagos')]
    #[ORM\JoinColumn(name: 'posgrado_residencia_id', referencedColumnName: 'id')]
    private $residencia;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $comprobantePago;

    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="comprobantes_pagos", fileNameProperty="comprobantePago")
     */
    #[Assert\File(maxSize: '2m', mimeTypes: ['application/pdf', 'application/x-pdf'], mimeTypesMessage: 'Solo se admiten archivos PDF')]
    private $comprobantePagoFile;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100)]
    private $referenciaBancaria;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $validado;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $requiereFactura;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $observaciones;

    /**
      * @var Factura
      */
     #[Assert\Valid]
     #[ORM\ManyToOne(targetEntity: \App\Entity\Factura::class, inversedBy: 'pagos', cascade: ['persist'])]
     #[ORM\JoinColumn(name: 'factura_id', referencedColumnName: 'id')]
     private $factura;

    #[ORM\Column(type: 'date', nullable: true)]
    private $fechaCreacion;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $facturaGenerada;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 10, nullable: true, options: ['default' => 'MXN'])]
    private $tipoMoneda;

    /**
     * @var SimulacionSolicitud
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Simulacion\Solicitud::class, inversedBy: 'pagos')]
    #[ORM\JoinColumn(name: 'simulacion_solicitud_id', referencedColumnName: 'id')]
    private $simulacionSolicitud;

    /**
     * @var Alumno
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Enfermeria\Alumno::class, inversedBy: 'pagos')]
    #[ORM\JoinColumn(name: 'escuela_enf_alumno_id', referencedColumnName: 'id')]
    private $escuelaEnfermeriaSolicitud;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false, name: 'escuela_enf_alumno_id')]
    private $escuelaEnfermeriaSolicitudId;

    /**
     * @var \App\Entity\EduPer\Solicitud
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\EduPer\Solicitud::class, inversedBy: 'pagos')]
    #[ORM\JoinColumn(name: 'edu_per_request_id', referencedColumnName: 'id')]
    private $eduPerSolicitud;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false, name: 'edu_per_request_id')]
    private $eduPerSolicitudId;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', nullable: true, name: 'is_test', options: ['default' => false])]
    private $isTest;

    public function __construct()
    {
        $this->isTest = false;
        $this->fechaCreacion = Carbon::now();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $monto
     * @return Pago
     */
    public function setMonto($monto)
    {
        $this->monto = $monto;

        return $this;
    }

    /**
     * @return string
     */
    public function getMonto()
    {
        return $this->monto;
    }

    /**
     * @param string $montoRegistrado
     * @return Pago
     */
    public function setMontoRegistrado($montoRegistrado)
    {
        $this->montoRegistrado = $montoRegistrado;

        return $this;
    }

    /**
     * @return string
     */
    public function getMontoRegistrado()
    {
        return $this->montoRegistrado;
    }

    /**
     * @param Solicitud $solicitud
     * @return Pago
     */
     public function setSolicitud(?Solicitud $solicitud = null)
     {
         $this->solicitud = $solicitud;

         return $this;
     }

     /**
      * @return Solicitud
      */
     public function getSolicitud()
     {
         return $this->solicitud;
     }

    /**
     * @param Residencia $residencia
     * @return Pago
     */
    public function setResidencia(?Residencia $residencia = null)
    {
        $this->residencia = $residencia;

        return $this;
    }

    /**
     * @return residencia
     */
    public function getResidencia()
    {
        return $this->residencia;
    }

    /**
     * @param string $comprobantePago
     * @return Pago
     */
    public function setComprobantePago($comprobantePago)
    {
        $this->comprobantePago = $comprobantePago;

        return $this;
    }

    /**
     * @return string
     */
    public function getComprobantePago()
    {
        return $this->comprobantePago;
    }

    public function isComprobantePagoCargado()
    {
        return null !== $this->comprobantePago;
    }

    /**
     * @param string $referenciaBancaria
     * @return Pago
     */
    public function setReferenciaBancaria($referenciaBancaria)
    {
        $this->referenciaBancaria = $referenciaBancaria;

        return $this;
    }

    /**
     * @return string
     */
    public function getReferenciaBancaria()
    {
        return $this->referenciaBancaria;
    }

    /**
     * @param boolean $validado
     * @return Pago
     */
    public function setValidado($validado)
    {
        $this->validado = $validado;

        return $this;
    }

    /**
     * @return bool
     */
    public function getValidado()
    {
        return $this->validado;
    }

    /**
     * @param string $observaciones
     * @return Pago
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;

        return $this;
    }

    /**
     * @return string
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * @param string $tipoMoneda
     * @return Pago
     */
    public function setTipoMoneda($tipoMoneda)
    {
        $this->tipoMoneda = $tipoMoneda;

        return $this;
    }

    /**
     * @return string
     */
    public function getTipoMoneda()
    {
        return $this->tipoMoneda;
    }

    /**
     * @return Factura
     */
    public function getFactura()
    {
        return $this->factura;
    }

    /**
     * @param Factura $factura
     */
    public function setFactura(?Factura $factura = null)
    {
        $this->factura = $factura;

         return $this;
    }

    /**
     * @return bool
     */
    public function isRequiereFactura()
    {
        return $this->requiereFactura;
    }

    /**
     * @param bool $requiereFactura
     */
    public function setRequiereFactura($requiereFactura)
    {
        $this->requiereFactura = $requiereFactura;
    }

    /**
     * @return File
     */
    public function getComprobantePagoFile()
    {
        return $this->comprobantePagoFile;
    }

    /**
     * @param File $comprobantePagoFile
     */
    public function setComprobantePagoFile($comprobantePagoFile = null)
    {
        $this->comprobantePagoFile = $comprobantePagoFile;

        $this->setFechaCreacion(Carbon::now());
    }

    /**
     * @param DateTime $fechaPago
     * @return Pago
     */
     public function setFechaPago($fechaPago = null)
     {
         $this->fechaPago = $fechaPago;

         return $this;
     }

     /**
      * @return DateTime
      */
     public function getFechaPago()
     {
         return $this->fechaPago;
     }

    /**
     * @return string
     */
     public function getFechaPagoFormatted()
     {
         if($this->getFechaPago()){
             return $this->getFechaPago()->format('d/m/Y');
         }
         return '';
     }

    /**
     * @param DateTime $fechaPagoRegistrada
     * @return Pago
     */
    public function setFechaPagoRegistrada(?DateTime $fechaPagoRegistrada = null)
    {
        $this->fechaPagoRegistrada = $fechaPagoRegistrada;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getFechaPagoRegistrada()
    {
        return $this->fechaPagoRegistrada;
    }

    public function getFechaPagoRegistradaFormatted()
    {
        if($this->getFechaPagoRegistrada()){
            return $this->getFechaPagoRegistrada()->format('d/m/Y');
        }
        return '';
    }

    /**
     * @return DateTime
     */
    public function getFechaCreacion()
    {
        return $this->fechaCreacion;
    }

    /**
     * @param DateTime $fechaCreacion
     */
    public function setFechaCreacion($fechaCreacion)
    {
        $this->fechaCreacion = $fechaCreacion;
    }

    public function isValidado()
    {
        return $this->getValidado();
    }

    public function getGestionPago()
    {
        return GestionPagoDTO::create($this);
    }

    public function getCamposPagados() {
       $campos = $this->solicitud->getCamposClinicos();
       $tiempos = [];
       $camposPagados = [];
       foreach ($campos as $campo) {
         $fechaInicio = $campo->getFechaInicial();
         $inicial = Carbon::instance($fechaInicio);
         if ($this->solicitud->getTipoPago() == Solicitud::TIPO_PAGO_UNICO
         || ($this->solicitud->getTipoPago() == Solicitud::TIPO_PAGO_MULTIPLE
            && $this->referenciaBancaria == $campo->getReferenciaBancaria()) ) {
           $final = $this->getFechaPago() ? Carbon::instance($this->getFechaPago()) : '';
           $tiempos[$campo->getId()] = $this->getFechaPago() ? $final->diffInDays($inicial) : '';
           if($campo->getLugaresAutorizados() > 0) {
               $camposPagados[] = $campo;
           }
         }
       }
       return ['campos' => $camposPagados, 'tiempos' => $tiempos];
     }

     /**
     * @return bool
     */
    public function isFacturaGenerada()
    {
        return $this->facturaGenerada;
    }

    /**
     * @param bool $facturaGenerada
     */
    public function setFacturaGenerada($facturaGenerada)
    {
        $this->facturaGenerada = $facturaGenerada;
    }

    /**
     * @return SimulacionSolicitud
     */
    public function getSimulacionSolicitud()
    {
        return $this->simulacionSolicitud;
    }

    /**
     * @param SimulacionSolicitud $simulacionSolicitud
     */
    public function setSimulacionSolicitud($simulacionSolicitud)
    {
        $this->simulacionSolicitud = $simulacionSolicitud;
    }

    /**
     * @return Alumno
     */
    public function getEscuelaEnfermeriaSolicitud()
    {
        return $this->escuelaEnfermeriaSolicitud;
    }

    /**
     * @param Alumno $escuelaEnfermeriaSolicitud
     */
    public function setEscuelaEnfermeriaSolicitud($escuelaEnfermeriaSolicitud)
    {
        $this->escuelaEnfermeriaSolicitud = $escuelaEnfermeriaSolicitud;
    }

    /**
     * @return int
     */
    public function getEscuelaEnfermeriaSolicitudId()
    {
        return $this->escuelaEnfermeriaSolicitudId;
    }

    /**
     * @param int $escuelaEnfermeriaSolicitudId
     */
    public function setEscuelaEnfermeriaSolicitudId($escuelaEnfermeriaSolicitudId)
    {
        $this->escuelaEnfermeriaSolicitudId = $escuelaEnfermeriaSolicitudId;
    }

    public function getStatusFormatted()
    {
        $status = '';
        if (is_null($this->validado)) {
            $status = 'Pendiente de Validación';
        } elseif($this->validado && $this->isFacturaGenerada()) {
            $status = 'Validado y Facturado';
        }else if($this->validado && $this->isRequiereFactura() &&!$this->isFacturaGenerada()) {
            $status = 'Pendiente de Facturar';
        }else if($this->validado){
            $status = 'Validado';
        }else{
            $status = 'No Validado';
        }
        return $status;
    }

    /**
     * @return EduPer\Solicitud
     */
    public function getEduPerSolicitud()
    {
        return $this->eduPerSolicitud;
    }

    /**
     * @param EduPer\Solicitud $eduPerSolicitud
     */
    public function setEduPerSolicitud($eduPerSolicitud)
    {
        $this->eduPerSolicitud = $eduPerSolicitud;
    }

    /**
     * @return int
     */
    public function getEduPerSolicitudId()
    {
        return $this->eduPerSolicitudId;
    }

    /**
     * @param int $eduPerSolicitudId
     */
    public function setEduPerSolicitudId($eduPerSolicitudId)
    {
        $this->eduPerSolicitudId = $eduPerSolicitudId;
    }

    /**
     * @return bool
     */
    public function isTest()
    {
        return $this->isTest;
    }

    /**
     * @param bool $isTest
     */
    public function setIsTest($isTest)
    {
        $this->isTest = $isTest;
    }

    public function __toString(): string
    {
        return $this->getId() . ' - referencia: ' . $this->getReferenciaBancaria();
    }

    /**
     * @return int
     */
    public function getSolicitudId()
    {
        return $this->solicitudId;
    }
}
