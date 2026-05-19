<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: \App\Repository\CampusRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'campus')]
class Campus
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    protected $nombre;

    /**
     * Muchos camoys pertenecen a una institución
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Institucion::class, inversedBy: 'campus')]
    #[ORM\JoinColumn(name: 'institucion_id', referencedColumnName: 'id', nullable: true)]
    private $institucion;

    /**
     * @var integer
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    private $institucionId;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: false)]
    protected $createdAt;


    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: false)]
    protected $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime("now");
        $this->updatedAt = new \DateTime("now");
    }

    #[ORM\PreUpdate]
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }

    // ===== Getters y Setters =====

    public function getId()
    {
        return $this->id;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getInstitucion()
    {
        return $this->institucion;
    }

    public function setInstitucion(Institucion $institucion)
    {
        $this->institucion = $institucion;
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return int
     */
    public function getInstitucionId()
    {
        return $this->institucionId;
    }

    /**
     * @param int $institucionId
     */
    public function setInstitucionId($institucionId)
    {
        $this->institucionId = $institucionId;
    }

}