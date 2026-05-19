<?php

namespace App\Entity\Posgrado;

use App\Entity\Usuario;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Residente
 *
 * @Vich\Uploadable
 */
#[ORM\Entity(repositoryClass: \App\Repository\Posgrado\ResidenteRepository::class)]
#[ORM\Table(name: 'posgrado_residente')]
class Residente implements \Stringable
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'folio', type: 'string', length: 30)]
    private $folio;

    /**
     * @var string
     */
    #[ORM\Column(name: 'tipo', type: 'string', length: 50, nullable: true)]
    private $tipo;

    /**
     * @var Usuario
     */
    #[ORM\OneToOne(targetEntity: \App\Entity\Usuario::class, inversedBy: 'residente', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id')]
    private $usuario;

    /**
     * @var string
     */
    #[ORM\Column(name: 'nacionalidad', type: 'string', length: 100, nullable: true)]
    private $nacionalidad;

    /**
     * @var string
     */
    #[ORM\Column(name: 'sede', type: 'string', length: 255, nullable: true)]
    private $sede;

    /**
     * @var string
     */
    #[ORM\Column(name: 'subsede', type: 'string', length: 255, nullable: true)]
    private $subsede;

    /**
     * @var string
     */
    #[ORM\Column(name: 'especialidad', type: 'string', length: 255, nullable: true)]
    private $especialidad;

    /**
     * @var string
     */
    #[ORM\Column(name: 'estatus', type: 'string', length: 100)]
    private $estatus;

    #[ORM\OneToMany(targetEntity: \App\Entity\Posgrado\Residencia::class, mappedBy: 'residente')]
    #[ORM\OrderBy(['grado' => 'DESC'])]
    private $residencias;

    /**
     * @var int
     */
    #[ORM\Column(name: 'grado', type: 'integer', nullable: true)]
    private $grado;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $cedulaIdentificacion;

    /**
     * @Vich\UploadableField(mapping="residente_cedulas", fileNameProperty="cedulaIdentificacion")
     * @var File
     */
    #[Assert\File(maxSize: '2m', mimeTypes: ['application/pdf', 'application/x-pdf'], mimeTypesMessage: 'Solo se admiten archivos PDF')]
    protected $cedulaFile;

    #[ORM\Column(type: 'date', nullable: true)]
    protected $fechaCedulaIdentificacion;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $confirmacionInformacion;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 13, nullable: true)]
    #[Assert\Length(min: 8, max: 13, minMessage: 'Este valor es demasiado corto. Debería tener {{ limit }} caracteres o más.', maxMessage: 'Este valor es demasiado largo. Debería tener {{ limit }} caracteres o menos.')]
    private $pasaporte;

    public function __construct()
    {
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
     * Set folio
     *
     * @param string $folio
     *
     * @return Residente
     */
    public function setFolio($folio)
    {
        $this->folio = $folio;

        return $this;
    }

    /**
     * Get folio
     *
     * @return string
     */
    public function getFolio()
    {
        return $this->folio;
    }

    /**
     * Set tipo
     *
     * @param string $tipo
     *
     * @return Residente
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return string
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set usuario
     *
     * @param Usuario $usuario
     *
     * @return Residente
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * Get usuario
     *
     * @return \stdClass
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    
    /**
     * Get usuario
     *
     * @return Usuario
     */
    public function getUsuarioAPI()
    {
        return $this->usuario;
    }

    /**
     * Set nacionalidad
     *
     * @param string $nacionalidad
     *
     * @return Residente
     */
    public function setNacionalidad($nacionalidad)
    {
        $this->nacionalidad = $nacionalidad;

        return $this;
    }

    /**
     * Get nacionalidad
     *
     * @return string
     */
    public function getNacionalidad()
    {
        return $this->nacionalidad;
    }

    /**
     * Set sede
     *
     * @param string $sede
     *
     * @return Residente
     */
    public function setSede($sede)
    {
        $this->sede = $sede;

        return $this;
    }

    /**
     * Get sede
     *
     * @return string
     */
    public function getSede()
    {
        return $this->sede;
    }

    /**
     * Set subsede
     *
     * @param string $subsede
     *
     * @return Residente
     */
    public function setSubsede($subsede)
    {
        $this->subsede = $subsede;

        return $this;
    }

    /**
     * Get subsede
     *
     * @return string
     */
    public function getSubsede()
    {
        return $this->subsede;
    }

    /**
     * Set especialidad
     *
     * @param string $especialidad
     *
     * @return Residente
     */
    public function setEspecialidad($especialidad)
    {
        $this->especialidad = $especialidad;

        return $this;
    }

    /**
     * Get especialidad
     *
     * @return string
     */
    public function getEspecialidad()
    {
        return $this->especialidad;
    }

    /**
     * Set estatus
     *
     * @param string $estatus
     *
     * @return Residente
     */
    public function setEstatus($estatus)
    {
        $this->estatus = $estatus;

        return $this;
    }

    /**
     * Get estatus
     *
     * @return string
     */
    public function getEstatus()
    {
        return $this->estatus;
    }

    /**
     * Set residencias
     *
     * @param \stdClass $residencias
     *
     * @return Residente
     */
    public function setResidencias($residencias)
    {
        $this->residencias = $residencias;

        return $this;
    }

    /**
     * Get residencias
     *
     * @return \stdClass
     */
    public function getResidencias()
    {
        return $this->residencias;
    }

    /**
     * Set grado
     *
     * @param integer $grado
     *
     * @return Residente
     */
    public function setGrado($grado)
    {
        $this->grado = $grado;

        return $this;
    }

    /**
     * Get grado
     *
     * @return int
     */
    public function getGrado()
    {
        return $this->grado;
    }

    /**
     * @param string $cedulaIdentificacion
     * @return Residente
     */
    public function setCedulaIdentificacion($cedulaIdentificacion)
    {
        $this->cedulaIdentificacion = $cedulaIdentificacion;

        return $this;
    }

    /**
     * @return string
     */
    public function getCedulaIdentificacion()
    {
        return $this->cedulaIdentificacion;
    }

    /**
     * @return File
     */
    public function getCedulaFile()
    {
        return $this->cedulaFile;
    }

    /**
     * @param File $cedulaFile
     */
    public function setCedulaFile($cedulaFile)
    {
        $this->cedulaFile = $cedulaFile;

        $this->setFechaCedulaIdentificacion(Carbon::now());
    }

    /**
     * @return DateTime
     */
    public function getFechaCedulaIdentificacion()
    {
        return $this->fechaCedulaIdentificacion;
    }

    /**
     * @param DateTime $fechaCedulaIdentificacion
     */
    public function setFechaCedulaIdentificacion($fechaCedulaIdentificacion)
    {
        $this->fechaCedulaIdentificacion = $fechaCedulaIdentificacion;
    }

    /**
     * @return DateTime
     */
    public function getConfirmacionInformacion()
    {
        return $this->confirmacionInformacion;
    }

    /**
     * @param DateTime $confirmacionInformacion
     */
    public function setConfirmacionInformacion($confirmacionInformacion)
    {
        $this->confirmacionInformacion = $confirmacionInformacion;
    }

    /**
     * @return bool
     */
    public function isConfirmacionInformacion()
    {
        return $this->confirmacionInformacion !== null;
    }

    public function __toString(): string
    {
        return $this->usuario->__toString();
    }


    /**
     * @return string
     */
    public function getPasaporte()
    {
        return $this->pasaporte;
    }

    /**
     * @param string $pasaporte
     */
    public function setPasaporte($pasaporte)
    {
        $this->pasaporte = $pasaporte;
    }
}

