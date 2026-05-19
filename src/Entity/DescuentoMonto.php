<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: \App\Repository\DescuentoMontoRepository::class)]
#[ORM\Table(name: 'descuento_monto')]
class DescuentoMonto
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^(0|[1-9][0-9]*)$/', message: 'Solo se pueden ingresar números')]
    private $numAlumnos;

    /**
     * @var float
     */
    #[ORM\Column(type: 'float', precision: 24, scale: 4, nullable: false)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private $descuentoInscripcion;

    /**
     * @var float
     */
    #[ORM\Column(type: 'float', precision: 24, scale: 4, nullable: false)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private $descuentoColegiatura;

    /**
     * @var MontoCarrera
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\MontoCarrera::class, inversedBy: 'descuentos')]
    #[ORM\JoinColumn(name: 'monto_carrera_id', referencedColumnName: 'id')]
    private  $montoCarrera;

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
     * @return int
     */
    public function getNumAlumnos()
    {
        return $this->numAlumnos;
    }

    /**
     * @param int $numAlumnos
     */
    public function setNumAlumnos($numAlumnos)
    {
        $this->numAlumnos = $numAlumnos;
    }

    /**
     * @return float
     */
    public function getDescuentoInscripcion()
    {
        return $this->descuentoInscripcion;
    }

    /**
     * @param float $descuentoInscripcion
     */
    public function setDescuentoInscripcion($descuentoInscripcion)
    {
        $this->descuentoInscripcion = $descuentoInscripcion;
    }

    /**
     * @return float
     */
    public function getDescuentoColegiatura()
    {
        return $this->descuentoColegiatura;
    }

    /**
     * @param float $descuentoColegiatura
     */
    public function setDescuentoColegiatura($descuentoColegiatura)
    {
        $this->descuentoColegiatura = $descuentoColegiatura;
    }

    /**
     * @return MontoCarrera
     */
    public function getMontoCarrera()
    {
        return $this->montoCarrera;
    }

    /**
     * @param MontoCarrera $montoCarrera
     */
    public function setMontoCarrera($montoCarrera)
    {
        $this->montoCarrera = $montoCarrera;
    }

    /**
     * @return float
     */
    public function getImporteCAI() {
        $montoCarrera = $this->getMontoCarrera();
        $cai =
            ($montoCarrera->getMontoInscripcion()*((100-$this->getDescuentoInscripcion())/100.0))
            + ($montoCarrera->getMontoColegiatura()*((100-$this->getDescuentoColegiatura())/100.0));

        return round($cai, 2);
    }

}
