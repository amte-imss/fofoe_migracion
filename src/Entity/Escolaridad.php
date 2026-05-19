<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: \App\Repository\EscolaridadRepository::class)]
#[ORM\Table(name: 'escolaridad')]
class Escolaridad
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
    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 255, minMessage: 'Este valor es demasiado corto. Debería tener {{ limit }} caracteres o más.', maxMessage: 'Este valor es demasiado largo. Debería tener {{ limit }} caracteres o menos.')]
    protected $nombre;

    #[ORM\OneToMany(targetEntity: \App\Entity\Simulacion\Solicitud::class, mappedBy: 'escolaridad')]
    #[ORM\OrderBy(['grado' => 'DESC'])]
    private $simulacionSolicitudes;

    public function __construct()
    {
    }

    /**
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
}