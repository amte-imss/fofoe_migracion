<?php

namespace App\Entity\Posgrado;

use App\Entity\Usuario;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * CargaMasiva
 */
#[ORM\Entity(repositoryClass: \App\Repository\Posgrado\CargaMasivaRepository::class)]
#[ORM\Table(name: 'posgrado_residencia_carga_masiva')]
class CargaMasiva
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var Usuario
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Usuario::class, inversedBy: 'cargaMasivas')]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id')]
    private $usuario;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $created_at;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $deleted_at;

    /**
     * @var Residencia
     */
    #[ORM\OneToMany(targetEntity: \App\Entity\Posgrado\Residencia::class, mappedBy: 'cargaMasiva')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    protected $residencias;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->residencias = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return Usuario
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * @param Usuario $usuario
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param mixed $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    /**
     * @return mixed
     */
    public function getDeletedAt()
    {
        return $this->deleted_at;
    }

    /**
     * @param mixed $deleted_at
     */
    public function setDeletedAt($deleted_at)
    {
        $this->deleted_at = $deleted_at;
    }

    /**
     * @return Residencia
     */
    public function getResidencias()
    {
        return $this->residencias;
    }

    /**
     * @param Residencia $residencias
     */
    public function setResidencias($residencias)
    {
        $this->residencias = $residencias;
    }

    public function setIsDelete($val)
    {
        $this->deleted_at = !!$val ? new \DateTime() : null;
    }

    public function getIsDelete()
    {
        return !!$this->deleted_at;
    }
}