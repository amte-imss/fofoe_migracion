<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: \App\Repository\MontoCarreraRepository::class)]
#[ORM\Table(name: 'monto_carrera')]
class MontoCarrera
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    #[ORM\Column(type: 'float', precision: 24, scale: 4, nullable: false)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^(0|[1-9][0-9]*)$/', message: 'Solo se pueden ingresar números')]
    private $montoInscripcion;

    #[ORM\Column(type: 'float', precision: 24, scale: 4, nullable: false)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private $montoColegiatura;

    private $solicitud;

  /**
   * @var CampoClinico
   */
  #[ORM\OneToOne(targetEntity: \App\Entity\CampoClinico::class, inversedBy: 'montoCarrera')]
  #[ORM\JoinColumn(name: 'campo_clinico_id', referencedColumnName: 'id')]
  private $campoClinico;

    /**
     * @var Carrera
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Carrera::class, inversedBy: 'montosCarreras')]
    #[ORM\JoinColumn(name: 'carrera_id', referencedColumnName: 'id')]
    private $carrera;

    /**
     * @var Collection
     */
    #[ORM\OneToMany(targetEntity: \App\Entity\DescuentoMonto::class, mappedBy: 'montoCarrera', cascade: ['persist'])]
    private $descuentos;

    public function __construct()
    {
        $this->descuentos = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param float $montoColegiatura
     * @return MontoCarrera
     */
    public function setMontoColegiatura($montoColegiatura)
    {
        $this->montoColegiatura = $montoColegiatura;

        return $this;
    }

    /**
     * @return float
     */
    public function getMontoColegiatura()
    {
        return $this->montoColegiatura;
    }


    /**
     * @param float $montoInscripcion
     * @return MontoCarrera
     */
    public function setMontoInscripcion($montoInscripcion)
    {
        $this->montoInscripcion = $montoInscripcion;

        return $this;
    }

    /**
     * @return float
     */
    public function getMontoInscripcion()
    {
        return $this->montoInscripcion;
    }

    /**
     * @param CampoClinico $campoClinico
     * @return MontoCarrera
     */
    public function setCampoClinico($campoClinico)
    {
      $this->campoClinico = $campoClinico;

      return $this;
    }

    /**
     * @return CampoClinico
     */
    public function getCampoClinico()
    {
      return $this->campoClinico;
    }

    /**
     * @return Solicitud
     */
    public function getSolicitud()
    {
        return $this->campoClinico->getSolicitud();
    }

    public function setCarrera(?Carrera $carrera = null)
    {
        $this->carrera = $carrera;

        return $this;
    }

    /**
     * @return Carrera
     */
    public function getCarrera()
    {
        return $this->carrera;
    }

    /**
     * @param DescuentoMonto $descuento
     */
    public function removeDescuentos(DescuentoMonto $descuento)
    {
        if($this->descuentos->contains($descuento)) {
            $this->descuentos->removeElement($descuento);
        }
    }

    /**
     * @param DescuentoMonto $descuento
     * @return MontoCarrera
     */
    public function addDescuentos(DescuentoMonto $descuento)
    {
        if(!$this->descuentos->contains($descuento)) {
            $this->descuentos[] = $descuento;
            $descuento->setMontoCarrera($this);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getDescuentos()
    {
        return $this->descuentos;
    }

    /**
     * @param Collection $descuentos
     */
    public function setDescuentos($descuentos)
    {
        $this->descuentos = $descuentos;
        foreach ($descuentos as $descuento) {
            $descuento->setMontoCarrera($this);
        }

        return $this;
    }
}
