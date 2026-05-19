<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\NivelAcademicoRepository::class)]
#[ORM\Table(name: 'nivel_academico')]
class NivelAcademico implements \Stringable
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
     * @var integer
     */
    #[ORM\Column(type: 'integer')]
    private $orderSort;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $nombre
     * @return NivelAcademico
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
     * @return int
     */
    public function getOrderSort()
    {
        return $this->orderSort;
    }

    /**
     * @param int $orderSort
     */
    public function setOrderSort($orderSort)
    {
        $this->orderSort = $orderSort;
    }

    public function __toString(): string
    {
        return $this->nombre;
    }
}
