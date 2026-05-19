<?php

namespace App\Entity\EduPer;

use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\EduPer\DuracionRepository::class)]
#[ORM\Table(name: 'edu_per_durations')]
class Duracion
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
    #[ORM\Column(type: 'string', length: 100)]
    private $name;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100)]
    private $code;

    /**
     * @var float
     */
    #[ORM\Column(type: 'float')]
    private $amount;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 15)]
    private $period;

    /**
     * @var boolean
     */
    #[ORM\Column(type: 'boolean')]
    private $active;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(type: 'datetime')]
    private $updatedAt;

    #[ORM\ManyToOne(targetEntity: \App\Entity\EduPer\Modalidad::class, inversedBy: 'duraciones', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'modality_id', referencedColumnName: 'id')]
    private $modalidad;

    /**
     * @var string
     */
    #[ORM\Column(type: 'integer', name: 'modality_id')]
    private $modalidadId;

    public function __construct()
    {
        $this->createdAt = Carbon::now();
        $this->updatedAt = Carbon::now();
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return mixed
     */
    public function getModalidad()
    {
        return $this->modalidad;
    }

    /**
     * @param mixed $modalidad
     */
    public function setModalidad($modalidad)
    {
        $this->modalidad = $modalidad;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * @param string $period
     */
    public function setPeriod($period)
    {
        $this->period = $period;
    }

    /**
     * @return string
     */
    public function getModalidadId()
    {
        return $this->modalidadId;
    }

    /**
     * @param string $modalidadId
     */
    public function setModalidadId($modalidadId)
    {
        $this->modalidadId = $modalidadId;
    }


}