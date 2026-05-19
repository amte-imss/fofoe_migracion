<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Especialidad
 */
#[ORM\Entity(repositoryClass: \App\Repository\EspecialidadRepository::class)]
#[ORM\Table(name: 'especialidad')]
class Especialidad
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 250)]
    private $nombre;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    private $duracion;

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
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * @param string $nombre
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    /**
     * @return int
     */
    public function getDuracion()
    {
        return $this->duracion;
    }

    /**
     * @param int $duracion
     */
    public function setDuracion($duracion)
    {
        $this->duracion = $duracion;
    }
}

