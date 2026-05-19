<?php

namespace App\Entity\Simulacion;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: \App\Repository\Simulacion\CursoRepository::class)]
#[ORM\Table(name: 'simulacion_cursos')]
class Curso
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
    #[Assert\Length(min: 5, max: 255, minMessage: 'Este valor es demasiado corto. Debería tener {{ limit }} caracteres o más.', maxMessage: 'Este valor es demasiado largo. Debería tener {{ limit }} caracteres o menos.')]
    protected $nombre;

    /**
     * @var float
     */
    #[ORM\Column(type: 'float', nullable: false)]
    private $precio;

    #[ORM\OneToMany(targetEntity: \App\Entity\Simulacion\Solicitud::class, mappedBy: 'curso')]
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

    /**
     * @return float
     */
    public function getPrecio()
    {
        return $this->precio;
    }

    /**
     * @param float $precio
     */
    public function setPrecio($precio)
    {
        $this->precio = $precio;
    }
}