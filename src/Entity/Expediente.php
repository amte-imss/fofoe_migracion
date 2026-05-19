<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\ExpedienteRepository::class)]
#[ORM\Table(name: 'expediente')]
class Expediente
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
    #[ORM\Column(type: 'text', length: 255, nullable: true)]
    private $descripcion;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255)]
    private $urlArchivo;

    /**
     * @var Solicitud
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Solicitud::class)]
    #[ORM\JoinColumn(name: 'solicitud_id', referencedColumnName: 'id')]
    private $solicitud;

    /**
     * @var int
     */
    #[ORM\Column(name: 'solicitud_id', type: 'integer')]
    private $solicitudId;

    /**
     * @var DateTime
     */
    #[ORM\Column(name: 'fecha', type: 'date')]
    private $fecha;

    public function __construct()
    {
        $this->fecha = new DateTime();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $descripcion
     * @return Expediente
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * @param DateTime $fecha
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    }

    /**
     * @param string $urlArchivo
     * @return Expediente
     */
    public function setUrlArchivo($urlArchivo)
    {
        $this->urlArchivo = $urlArchivo;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrlArchivo()
    {
        return $this->urlArchivo;
    }

    /**
     * @param integer $solicitudId
     * @return Expediente
     */
    public function setSolicitudId($solicitudId)
    {
        $this->solicitudId = $solicitudId;
        return $this;
    }

    /**
     * @return int
     */
    public function getSolicitudId()
    {
        return $this->solicitudId;
    }

    /**
     * @return DateTime
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * @param Solicitud $solicitud
     * @return Expediente
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
}
