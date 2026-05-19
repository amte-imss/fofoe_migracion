<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @Vich\Uploadable
 */
#[ORM\Entity(repositoryClass: \App\Repository\InstitucionRepository::class)]
#[ORM\Table(name: 'institucion')]
class Institucion implements \Stringable
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
    #[Assert\Length(min: 1, max: 255, minMessage: 'Este valor es demasiado corto. Debería tener {{ limit }} caracteres o más.', maxMessage: 'Este valor es demasiado largo. Debería tener {{ limit }} caracteres o menos.')]
    protected $nombre;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $razonSocial;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 16, nullable: true)]
    #[Assert\Regex(pattern: '/^(0|[1-9][0-9]*)$/', message: 'Solo se pueden ingresar números')]
    #[Assert\Length(min: 8, max: 15, minMessage: 'Este valor es demasiado corto. Debería tener {{ limit }} caracteres.', maxMessage: 'Este valor es demasiado largo. Debería tener {{ limit }} caracteres.')]
    protected $telefono;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 254, nullable: true)]
    protected $correo;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 254, nullable: true)]
    protected $fax;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $sitioWeb;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $cedulaIdentificacion;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $cedulaIdentificacion2;

    /**
     * @Vich\UploadableField(mapping="institucion_cedulas", fileNameProperty="cedulaIdentificacion")
     * @var File|null
     */
    private $cedulaFile;

    /**
     * @Vich\UploadableField(mapping="institucion_cedulas2", fileNameProperty="cedulaIdentificacion2")
     * @var File|null
     */
    private $cedulaFile2;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 13, nullable: true)]
    #[Assert\Length(min: 12, max: 13, minMessage: 'Este valor es demasiado corto. Debería tener {{ limit }} caracteres o más.', maxMessage: 'Este valor es demasiado largo. Debería tener {{ limit }} caracteres o menos.')]
    protected $rfc;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $direccion;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 250, nullable: true)]
    protected $representante;

    /**
     * @var Convenio
     */
    #[ORM\OneToMany(targetEntity: \App\Entity\Convenio::class, mappedBy: 'institucion')]
    protected $convenios;

    /**
     * @var Usuario
     */
    #[ORM\OneToOne(targetEntity: \App\Entity\Usuario::class, inversedBy: 'institucion', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id')]
    protected $usuario;

    /**
     * @var Usuario
     */
    #[ORM\OneToMany(targetEntity: \App\Entity\Usuario::class, mappedBy: 'institucion')]
    protected $usuarios;

    #[ORM\Column(type: 'date', nullable: true)]
    protected $fechaCedulaIdentificacion;

    #[ORM\Column(type: 'date', nullable: true)]
    protected $fechaCedulaIdentificacion2;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $confirmacionInformacion;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 5, nullable: true)]
    #[Assert\Length(max: 5, minMessage: 'Este valor es demasiado corto. Debería tener {{ limit }} caracteres o más.', maxMessage: 'Este valor es demasiado largo. Debería tener {{ limit }} caracteres o menos.')]
    protected $extension;


    #[ORM\OneToMany(targetEntity: \App\Entity\Simulacion\Solicitud::class, mappedBy: 'institucion')]
    #[ORM\OrderBy(['grado' => 'DESC'])]
    private $simulacionSolicitudes;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 5, nullable: true)]
    #[Assert\Length(max: 5, minMessage: 'Este valor es demasiado corto. Debería tener {{ limit }} caracteres o más.', maxMessage: 'Este valor es demasiado largo. Debería tener {{ limit }} caracteres o menos.')]
    protected $area_estudio;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $validatedAt;

    #[ORM\Column(type: 'boolean', nullable: true)]
    protected $requireValidation;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $promted_at;

    /**
     * @var Delegacion
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Delegacion::class)]
    #[ORM\JoinColumn(name: 'delegacion_id', referencedColumnName: 'id')]
    protected $delegacion;

    /**
     * @var boolean
     */
    #[ORM\Column(type: 'boolean', nullable: false)]
    protected $usersByCampus;

    public function __construct()
    {
        $this->convenios = new ArrayCollection();
        $this->simulacionSolicitudes = new ArrayCollection();
        $this->usuarios = new ArrayCollection();
        $this->usersByCampus = false;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $nombre
     * @return Institucion
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
     * @param string $razonSocial
     * @return Institucion
     */
    public function setRazonSocial($razonSocial)
    {
        $this->razonSocial = $razonSocial;

        return $this;
    }

    /**
     * @return string
     */
    public function getRazonSocial()
    {
        return $this->razonSocial;
    }

    /**
     * @param string $telefono
     * @return Institucion
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;

        return $this;
    }

    /**
     * @return string
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * @param string $correo
     * @return Institucion
     */
    public function setCorreo($correo)
    {
        $this->correo = $correo;

        return $this;
    }

    /**
     * @return string
     */
    public function getCorreo()
    {
        return $this->correo;
    }

    /**
     * @param string $fax
     * @return Institucion
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @param string $sitioWeb
     * @return Institucion
     */
    public function setSitioWeb($sitioWeb)
    {
        $this->sitioWeb = $sitioWeb;

        return $this;
    }

    /**
     * @return string
     */
    public function getSitioWeb()
    {
        return $this->sitioWeb;
    }

    /**
     * @param string $cedulaIdentificacion
     * @return Institucion
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
     * @param string $cedulaIdentificacion
     * @return Institucion
     */
    public function setCedulaIdentificacion2($cedulaIdentificacion)
    {
        $this->cedulaIdentificacion2 = $cedulaIdentificacion;

        return $this;
    }

    /**
     * @return string
     */
    public function getCedulaIdentificacion2()
    {
        return $this->cedulaIdentificacion2;
    }

    /**
     * @param string $rfc
     * @return Institucion
     */
    public function setRfc($rfc)
    {
        $this->rfc = $rfc;

        return $this;
    }

    /**
     * @return string
     */
    public function getRfc()
    {
        return $this->rfc;
    }

    /**
     * @param string $direccion
     * @return Institucion
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;

        return $this;
    }

    /**
     * @return string
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * @param string $representante
     * @return Institucion
     */
    public function setRepresentante($representante)
    {
        $this->representante = $representante;

        return $this;
    }

    /**
     * @return string
     */
    public function getRepresentante()
    {
        return $this->representante;
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
    public function setCedulaFile($cedulaFile = null)
    {
        $this->cedulaFile = $cedulaFile;
    
        if ($cedulaFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
            $this->setFechaCedulaIdentificacion(new \DateTime());
        }
    }

    /**
     * @return File
     */
    public function getCedulaFile2()
    {
        return $this->cedulaFile2;
    }

    /**
     * @param File $cedulaFile2
     */
    public function setCedulaFile2($cedulaFile = null)
    {
        $this->cedulaFile2 = $cedulaFile;
        if ($cedulaFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
            $this->setFechaCedulaIdentificacion2(new \DateTime());
        }
    }

    /**
     * @param Convenio $convenio
     * @return Institucion
     */
    public function addConvenio(Convenio $convenio)
    {
        $this->convenios[] = $convenio;

        return $this;
    }

    /**
     * @param Convenio $convenio
     */
    public function removeConvenio(Convenio $convenio)
    {
        $this->convenios->removeElement($convenio);
    }

    /**
     * @return Collection
     */
    public function getConvenios()
    {
        return $this->convenios;
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
     * @return Institucion
     */
    public function setUsuario(?Usuario $usuario = null)
    {
        $this->usuario = $usuario;
        /*
         * ni idea de porque hicieron esto
        if ($usuario) {
            $this->setRepresentante($usuario->getFullName());
        }
        */
        return $this;
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
    public function getFechaCedulaIdentificacion2()
    {
        return $this->fechaCedulaIdentificacion2;
    }

    /**
     * @param DateTime $fechaCedulaIdentificacion
     */
    public function setFechaCedulaIdentificacion2($fechaCedulaIdentificacion)
    {
        $this->fechaCedulaIdentificacion2 = $fechaCedulaIdentificacion;
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

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    public function getDelegacionesToString() {
      $dels = [];
      /** @var Convenio $convenio */
      foreach ($this->convenios as $convenio) {
        $del = $convenio->getDelegacion();
        if($del){
            if (! in_array($del->getNombre(), $dels)) {
                $dels[] = $del->getNombre();
            }
        }
      }

      return implode(' , ', $dels );
    }

  /**
   * @return string
   */
    public function __toString(): string
    {
      return $this->getNombre();
    }

    public function getRazonSocialRFC(){
        return $this->getRazonSocial() . ' - ' . $this->getRfc();
    }

    /**
     * @return mixed
     */
    public function getSimulacionSolicitudes()
    {
        return $this->simulacionSolicitudes;
    }

    /**
     * @param mixed $simulacionSolicitudes
     */
    public function setSimulacionSolicitudes($simulacionSolicitudes)
    {
        $this->simulacionSolicitudes = $simulacionSolicitudes;
    }

    /**
     * @return string
     */
    public function getAreaEstudio()
    {
        return $this->area_estudio;
    }

    /**
     * @param string $area_estudio
     */
    public function setAreaEstudio($area_estudio)
    {
        $this->area_estudio = $area_estudio;
    }

    public function getTipoUsuario()
    {
        return $this->getConvenios()->count() > 0 ? 1 : 2;
    }

    /**
     * @return mixed
     */
    public function getValidatedAt()
    {
        return $this->validatedAt;
    }

    /**
     * @param mixed $validatedAt
     */
    public function setValidatedAt($validatedAt)
    {
        $this->validatedAt = $validatedAt;
    }

    /**
     * @return mixed
     */
    public function getRequireValidation()
    {
        return $this->requireValidation;
    }

    /**
     * @param mixed $requireValidation
     */
    public function setRequireValidation($requireValidation)
    {
        $this->requireValidation = $requireValidation;
    }

    /**
     * @return mixed
     */
    public function getPromtedAt()
    {
        return $this->promted_at;
    }

    /**
     * @param mixed $promted_at
     */
    public function setPromtedAt($promted_at)
    {
        $this->promted_at = $promted_at;
    }

    /**
     * @return Delegacion
     */
    public function getDelegacion()
    {
        return $this->delegacion;
    }

    /**
     * @param Delegacion $delegacion
     */
    public function setDelegacion($delegacion)
    {
        $this->delegacion = $delegacion;
    }

    /** @return ArrayCollection|Usuario[] */
    public function getUsuarios()
    {
        return $this->usuarios;
    }

    public function addUsuario(Usuario $usuario)
    {
        if (!$this->usuarios->contains($usuario)) {
            $this->usuarios[] = $usuario;
            $usuario->setInstitucion($this); // importante para sincronizar
        }
    }

    public function removeUsuario(Usuario $usuario)
    {
        if ($this->usuarios->contains($usuario)) {
            $this->usuarios->removeElement($usuario);
            $usuario->setInstitucion(null);
        }
    }

    public function getUsuarioByOOAD(Delegacion $ooad)
    {
        foreach ($this->getUsuarios() as $usuario) {
            if ($usuario->getDelegacionInstitucion() && $usuario->getDelegacionInstitucion()->getId() == $ooad->getId()) {
                return $usuario;
            }
        }
        return null;
    }

    public function getUsuarioByOADDAndCampus(Delegacion $ooad, Campus $campus){
        foreach ($this->getUsuarios() as $usuario) {
            if ($usuario->getDelegacionInstitucion() && $usuario->getDelegacionInstitucion()->getId() == $ooad->getId()) {
                if($usuario->getCampus() && $usuario->getCampus()->getId() == $campus->getId()){
                    return $usuario;
                }
            }
        }
        return null;
    }

    /**
     * @return bool
     */
    public function isUsersByCampus()
    {
        return $this->usersByCampus;
    }

    /**
     * @param bool $usersByCampus
     */
    public function setUsersByCampus($usersByCampus)
    {
        $this->usersByCampus = $usersByCampus;
    }
}
