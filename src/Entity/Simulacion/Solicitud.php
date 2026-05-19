<?php

namespace App\Entity\Simulacion;

use App\Entity\Pago;
use App\Entity\SolicitudInterface;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\Simulacion\SolicitudRepository::class)]
#[ORM\Table(name: 'simulacion_solicitud')]
class Solicitud
{

    const STATUS_PAGO_NUEVO = 0;
    const STATUS_PAGO_PENDIENTE_VALIDACION = 1;
    const STATUS_PAGO_RECHAZADO = 2;
    const STATUS_PAGO_POR_FACTURAR = 3;
    const STATUS_PAGO_ACEPTADO = 4;

    const STATUS_PAGO_RECHAZADO_TEXTO = 'Pago rechazado por FOFOE';
    const STATUS_PAGO_ACEPTADO_TEXTO = 'Pago aceptado por FOFOE';

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;


    #[ORM\ManyToOne(targetEntity: \App\Entity\Institucion::class, inversedBy: 'simulacionSolicitudes', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'institucion_id', referencedColumnName: 'id')]
    private $institucion;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Simulacion\Curso::class, inversedBy: 'simulacionSolicitudes', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'curso_id', referencedColumnName: 'id')]
    private $curso;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Escolaridad::class, inversedBy: 'simulacionSolicitudes', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'escolaridad_id', referencedColumnName: 'id')]
    private $escolaridad;


    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    private $numHoras;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    private $participantes;

    /**
     * @var float
     */
    #[ORM\Column(type: 'float', nullable: false)]
    private $monto;

    /**
     * @var float
     */
    #[ORM\Column(type: 'string', nullable: false)]
    private $modalidad;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: false)]
    private $status;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    private $statusPago;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\OneToMany(targetEntity: \App\Entity\Pago::class, mappedBy: 'simulacionSolicitud', cascade: ['persist'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $pagos;

    public function __construct()
    {
        $this->status = SolicitudInterface::CREADA;
        $this->statusPago = 0;
        $this->pagos = new ArrayCollection();
        $this->createdAt = Carbon::now();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getInstitucion()
    {
        return $this->institucion;
    }

    /**
     * @param mixed $institucion
     */
    public function setInstitucion($institucion)
    {
        $this->institucion = $institucion;
    }

    /**
     * @return int
     */
    public function getNumHoras()
    {
        return $this->numHoras;
    }

    /**
     * @param int $numHoras
     */
    public function setNumHoras($numHoras)
    {
        $this->numHoras = $numHoras;
    }

    /**
     * @return int
     */
    public function getParticipantes()
    {
        return $this->participantes;
    }

    /**
     * @param int $maxParticipantes
     */
    public function setParticipantes($maxParticipantes)
    {
        $this->participantes = $maxParticipantes;
    }

    /**
     * @return float
     */
    public function getMonto()
    {
        return $this->monto;
    }

    /**
     * @param float $monto
     */
    public function setMonto($monto)
    {
        $this->monto = $monto;
    }

    /**
     * @return mixed
     */
    public function getCurso()
    {
        return $this->curso;
    }

    /**
     * @param mixed $curso
     */
    public function setCurso($curso)
    {
        $this->curso = $curso;
    }

    /**
     * @return mixed
     */
    public function getEscolaridad()
    {
        return $this->escolaridad;
    }

    /**
     * @param mixed $escolaridad
     */
    public function setEscolaridad($escolaridad)
    {
        $this->escolaridad = $escolaridad;
    }

    /**
     * @return float
     */
    public function getModalidad()
    {
        return $this->modalidad;
    }

    /**
     * @param float $modalidad
     */
    public function setModalidad($modalidad)
    {
        $this->modalidad = $modalidad;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getNoSolicitud()
    {
        return str_pad(''.$this->getId(),8,'0', STR_PAD_LEFT);
    }

    /**
     * @return int
     */
    public function getStatusPago()
    {
        return $this->statusPago;
    }

    /**
     * @param int $statusPago
     */
    public function setStatusPago($statusPago)
    {
        $this->statusPago = $statusPago;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAtFormatted()
    {
        return $this->createdAt->format('d-m-Y H:i:s');
    }

    public function getReferenciaBancaria()
    {
        return '4' . ($this->createdAt->format('Y')) . str_pad($this->id, 8, '0', STR_PAD_LEFT);
    }

    /**
     * @param Pago $pago
     * @return Solicitud
     */
    public function addPago(Pago $pago)
    {
        $this->pagos[] = $pago;

        return $this;
    }

    /**
     * @param Pago $pago
     */
    public function removePago(Pago $pago)
    {
        $this->pagos->removeElement($pago);
    }

    /**
     * @return Collection
     */
    public function getPagos()
    {
        return $this->pagos;
    }

    /**
     * @return Pago|null
     */
    public function getPago()
    {
        $result = null;
        $pagos = $this->getPagos();
        if(count($pagos)>0) {
            $result = $pagos[0];
        }
        return $result;
    }

}