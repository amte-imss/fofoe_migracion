<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tipo_unidad')]
class TipoUnidad
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
    #[ORM\Column(type: 'string', length: 100)]
    private $nombre;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100)]
    private $descripcion;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    private $nivel;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 6)]
    private $grupoTipo;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 50)]
    private $grupoNombre;

    /**
     * @var boolean
     */
    #[ORM\Column(type: 'boolean')]
    private $activo;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $nombre
     * @return TipoUnidad
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

  /**
   * @return string
   */
  public function getDisplayName() {
    return $this->nombre
      . " - " . $this->descripcion;
  }

    /**
     * @param boolean $activo
     * @return TipoUnidad
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * @param string $descripcion
     * @return TipoUnidad
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
     * @param string $grupoTipo
     * @return TipoUnidad
     */
    public function setGrupoTipo($grupoTipo)
    {
        $this->grupoTipo = $grupoTipo;

        return $this;
    }

    /**
     * @return string
     */
    public function getGrupoTipo()
    {
        return $this->grupoTipo;
    }

    /**
     * @param string $grupoNombre
     * @return TipoUnidad
     */
    public function setGrupoNombre($grupoNombre)
    {
        $this->grupoNombre = $grupoNombre;

        return $this;
    }

    /**
     * @return string
     */
    public function getGrupoNombre()
    {
        return $this->grupoNombre;
    }

    /**
     * @return int
     */
    public function getNivel()
    {
        return $this->nivel;
    }

    /**
     * @param int $nivel
     */
    public function setNivel($nivel)
    {
        $this->nivel = $nivel;
    }
}
