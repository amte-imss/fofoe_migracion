<?php

namespace App\Entity;

use App\Entity\Posgrado\Residente;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: \App\Repository\UsuarioRepository::class)]
#[UniqueEntity(fields: ['matricula', 'correo'], errorPath: 'correo', message: 'El correo y nombre de usuario (matricula) ya estan asignados a otro usuario.')]
#[ORM\Table(name: 'usuario')]
#[ORM\UniqueConstraint(columns: ['matricula', 'correo'])]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface, EquatableInterface, \Stringable
{

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var integer
     */
    #[ORM\Column(type: 'string', length: 30, unique: true, nullable: true)]
    #[Assert\Length(min: 6, max: 30, minMessage: 'Este valor es demasiado corto. Debería tener {{ limit }} caracteres o más.', maxMessage: 'Este valor es demasiado largo. Debería tener {{ limit }} caracteres o menos.')]
    private $matricula;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 250, nullable: true)]
    private $nombre;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $apellidoPaterno;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $apellidoMaterno;

    /**
     * @var integer
     */
    #[ORM\Column(type: 'bigint', nullable: true)]
    private $regims;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 64)]
    private $contrasena;

    /**
     * @var string
     */
    #[Assert\Email]
    #[ORM\Column(type: 'string', length: 254, nullable: true)]
    private $correo;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    private $telefono;

    /**
     * @var boolean
     */
    #[ORM\Column(type: 'boolean')]
    private $activo;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Departamento::class, inversedBy: 'usuarios')]
    #[ORM\JoinColumn(name: 'departamento_id', referencedColumnName: 'id')]
    private $departamento;

    #[ORM\ManyToMany(targetEntity: \App\Entity\Delegacion::class, inversedBy: 'usuarios')]
    #[ORM\JoinTable(name: 'usuario_delegacion',
        joinColumns: [new ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'delegacion_id', referencedColumnName: 'id')]
    )]
    private $delegaciones;

    #[ORM\ManyToMany(targetEntity: \App\Entity\Unidad::class)]
    #[ORM\JoinTable(name: 'usuario_unidad',
        joinColumns: [new ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'unidad_id', referencedColumnName: 'id')]
    )]
    private $unidades;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 18, nullable: true)]
    #[Assert\Length(min: 18, max: 18, minMessage: 'Este valor es demasiado corto. Debería tener {{ limit }} caracteres o más.', maxMessage: 'Este valor es demasiado largo. Debería tener {{ limit }} caracteres o menos.')]
    private $curp;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 13, nullable: true)]
    #[Assert\Length(min: 12, max: 13, minMessage: 'Este valor es demasiado corto. Debería tener {{ limit }} caracteres o más.', maxMessage: 'Este valor es demasiado largo. Debería tener {{ limit }} caracteres o menos.')]
    private $rfc;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private $sexo;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'date', nullable: true)]
    private $fechaIngreso;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Categoria::class)]
    #[ORM\JoinColumn(name: 'categoria_id', referencedColumnName: 'id')]
    private $categoria;

    #[ORM\ManyToMany(targetEntity: \App\Entity\Permiso::class)]
    #[ORM\JoinTable(name: 'usuario_permiso',
        joinColumns: [new ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'permiso_id', referencedColumnName: 'id')]
    )]
    private $permisos;

    /**
     * Muchos usuarios pertenecen a una institución
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Institucion::class, inversedBy: 'usuarios')]
    #[ORM\JoinColumn(name: 'institucion_id', referencedColumnName: 'id', nullable: true)]
    private $institucion;

    /**
     * Muchos usuarios pertenecen a un campus
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Campus::class, inversedBy: 'usuarios')]
    #[ORM\JoinColumn(name: 'campus_id', referencedColumnName: 'id', nullable: true)]
    private $campus;

    /**
     * @var Residente
     */
    #[ORM\OneToOne(targetEntity: \App\Entity\Posgrado\Residente::class, cascade: ['persist'], mappedBy: 'usuario')]
    private $residente;

    /** @var string */
    private $plainPassword;

    /** @var string */
    private $rol;

    #[ORM\OneToMany(targetEntity: \App\Entity\Posgrado\CargaMasiva::class, mappedBy: 'usuario')]
    private $cargaMasivas;

    /**
     * Muchos usuarios pueden estar asociados a una delegación de institución
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Delegacion::class)]
    #[ORM\JoinColumn(name: 'delegacion_institucion_id', referencedColumnName: 'id', nullable: true)]
    private $delegacionInstitucion;

    public function __construct()
    {
        $this->delegaciones = new ArrayCollection();
        $this->unidades = new ArrayCollection();
        $this->permisos = new ArrayCollection();
        $this->fechaIngreso = new \DateTime();
        $this->cargaMasivas = new ArrayCollection();
        $this->curp = '';
        $this->rfc = '';
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getContrasena()
    {
        return $this->contrasena;
    }

    /**
     * @param mixed $contrasena
     */
    public function setContrasena($contrasena)
    {
        $this->contrasena = $contrasena;
    }

    /**
     * @return mixed
     */
    public function getCorreo()
    {
        return $this->correo;
    }

    /**
     * @param mixed $correo
     */
    public function setCorreo($correo)
    {
        $this->correo = $correo;
    }

    /**
     * @return mixed
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * @param mixed $telefono
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param mixed $plainPassword
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @inheritDoc
     */
    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];

        /** @var Permiso $permiso */
        foreach ($this->getPermisos() as $permiso) {
            $roles[] = sprintf('ROLE_%s', $permiso->getClave());
        }

        return array_unique($roles);
    }

    /**
     * @inheritDoc
     */
    public function getPassword(): ?string
    {
        return $this->contrasena;
    }

    /**
     * @inheritDoc
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getUserIdentifier(): string
    {
        return $this->correo ?: $this->matricula;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @param integer $matricula
     * @return Usuario
     */
    public function setMatricula($matricula)
    {
        $this->matricula = $matricula;

        return $this;
    }

    /**
     * @return string
     */
    public function getMatricula()
    {
        return $this->matricula;
    }

    /**
     * @param string $nombre
     * @return Usuario
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
     * @param string $apellidoPaterno
     * @return Usuario
     */
    public function setApellidoPaterno($apellidoPaterno)
    {
        $this->apellidoPaterno = $apellidoPaterno;

        return $this;
    }

    /**
     * @return string
     */
    public function getApellidoPaterno()
    {
        return $this->apellidoPaterno;
    }

    /**
     * @param string $apellidoMaterno
     * @return Usuario
     */
    public function setApellidoMaterno($apellidoMaterno)
    {
        $this->apellidoMaterno = $apellidoMaterno;

        return $this;
    }

    /**
     * @return string
     */
    public function getApellidoMaterno()
    {
        return $this->apellidoMaterno;
    }

    /**
     * @param integer $regims
     * @return Usuario
     */
    public function setRegims($regims)
    {
        $this->regims = $regims;

        return $this;
    }

    /**
     * @return integer
     */
    public function getRegims()
    {
        return $this->regims;
    }

    /**
     * @param boolean $activo
     * @return Usuario
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
     * @param string $curp
     * @return Usuario
     */
    public function setCurp($curp)
    {
        $this->curp = $curp ?: '';

        return $this;
    }

    /**
     * @return string
     */
    public function getCurp()
    {
        return $this->curp;
    }

    /**
     * @param string $rfc
     * @return Usuario
     */
    public function setRfc($rfc)
    {
        $this->rfc = $rfc ?: '';

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
     * @param string $sexo
     * @return Usuario
     */
    public function setSexo($sexo)
    {
        $this->sexo = $sexo;

        return $this;
    }

    /**
     * @return string
     */
    public function getSexo()
    {
        return $this->sexo;
    }

    /**
     * @param \DateTime $fechaIngreso
     * @return Usuario
     */
    public function setFechaIngreso($fechaIngreso)
    {
        $this->fechaIngreso = $fechaIngreso;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getFechaIngreso()
    {
        return $this->fechaIngreso;
    }

    /**
     * @param Departamento $departamento
     * @return Usuario
     */
    public function setDepartamento(Departamento $departamento)
    {
        $this->departamento = $departamento;
        return $this;
    }

    /**
     * @return Departamento
     */
    public function getDepartamento()
    {
        return $this->departamento;
    }

    /**
     * @param Categoria $categoria
     * @return Usuario
     */
    public function setCategoria(?Categoria $categoria = null)
    {
        $this->categoria = $categoria;

        return $this;
    }

    /**
     * @return Categoria
     */
    public function getCategoria()
    {
        return $this->categoria;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        $fullName = $this->nombre;

        if ($this->apellidoPaterno !== null) {
            $fullName .= " {$this->apellidoPaterno}";
        }

        if ($this->apellidoMaterno !== null) {
            $fullName .= " {$this->apellidoMaterno}";
        }

        return $fullName;
    }

    /**
     * @param Delegacion $delegacione
     * @return Usuario
     */
    public function addDelegacione(Delegacion $delegacione)
    {
        if (!$this->delegaciones->contains($delegacione)) {
            $this->delegaciones[] = $delegacione;
            $delegacione->addUsuario($this);
        }

        return $this;
    }

    /**
     * @param Delegacion $delegacione
     */
    public function removeDelegacione(Delegacion $delegacione)
    {
        if ($this->delegaciones->contains($delegacione)) {
            $this->delegaciones->removeElement($delegacione);
            $delegacione->removeUsuario($this);
        }
    }

    /**
     * @return Collection
     */
    public function getDelegaciones()
    {
        return $this->delegaciones;
    }

    /**
     * @return array
     */
    public function getDelegacionesUnidadesInstitucion()
    {
        return array_merge($this->delegaciones->toArray(), $this->unidades->toArray(), $this->getInstitucion() ? [$this->getInstitucion()] : []);
    }

    /**
     * @param Unidad $unidad
     * @return Usuario
     */
    public function addUnidad(Unidad $unidad)
    {
        if (!$this->unidades->contains($unidad)) {
            $this->unidades[] = $unidad;
            //$unidad->addUsuario($this);
        }

        return $this;
    }

    /**
     * @param Unidad $unidad
     */
    public function removeUnidad(Unidad $unidad)
    {
        if ($this->unidades->contains($unidad)) {
            $this->unidades->removeElement($unidad);
            //$unidad->removeUsuario($this);
        }
    }

    /**
     * @return Collection
     */
    public function getUnidades()
    {
        return $this->unidades;
    }

    /**
     * @return Institucion
     */
    public function getInstitucion()
    {
        return $this->institucion;
    }

    /**
     * @return Residente
     */
    public function getResidente()
    {
        return $this->residente;
    }

    /**
     * @param $permisos
     * @return Usuario
     */
    public function setPermisos($permisos)
    {
        /** @var Permiso $permiso */
        foreach ($this->permisos as $permiso) {
            $permiso->removeUsuario($this);
        }
        $this->permisos = $permisos;
        /** @var Permiso $permiso */
        foreach ($this->permisos as $permiso) {
            $permiso->addUsuario($this);
        }

        return $this;
    }

    /**
     * @param Permiso $permiso
     * @return Usuario
     */
    public function addPermiso(Permiso $permiso)
    {
        if (!$this->getPermisos()->contains($permiso)) {
            $this->permisos[] = $permiso;
            //$permiso->addUsuario($this);
        }

        return $this;
    }

    /**
     * @param Permiso $permiso
     */
    public function removePermiso(Permiso $permiso)
    {
        $this->permisos->removeElement($permiso);
    }

    /**
     * @return Collection
     */
    public function getPermisos()
    {
        return $this->permisos;
    }

    /**
     * @param Institucion $institucion
     * @return Usuario
     */
    public function setInstitucion(?Institucion $institucion = null)
    {
        $this->institucion = $institucion;

        return $this;
    }

    /**
     * @param Residente $residente
     * @return Usuario
     */
    public function setResidente(?Residente $residente = null)
    {
        $this->residente = $residente;

        return $this;
    }

    /**
     * @param string $rol
     */
    public function setRol($rol)
    {
        $this->rol = $rol;
    }

    /**
     * @return string
     */
    public function getRol()
    {
        if($this->rol === null && count($this->getRoles()) > 0) {
            return $this->getRoles()[0];
        }
        return $this->rol;
    }

    public function __serialize(): array
    {
        return [
            'id'         => $this->id,
            'matricula'  => $this->matricula,
            'correo'     => $this->correo,
            'contrasena' => $this->contrasena,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id         = $data['id'];
        $this->matricula  = $data['matricula'];
        $this->correo     = $data['correo'];
        $this->contrasena = $data['contrasena'];
    }

    public function __toString(): string
    {
        return "[" . $this->getUserIdentifier() . "] "
            . $this->getFullName();
    }

    public function isEqualTo(UserInterface $user): bool
    {
        if ($user instanceof Usuario) {
            return $user->getId() == $this->getId();
        }
        return false;
    }

    /**
     * @return \stdClass
     */
    public function getCargaMasivas()
    {
        return $this->cargaMasivas;
    }

    /**
     * @param \stdClass $cargaMasivas
     * @return Usuario
     */
    public function setCargaMasivas($cargaMasivas)
    {
        $this->cargaMasivas = $cargaMasivas;
        return $this;
    }

    public function getDelegacionInstitucion()
    {
        return $this->delegacionInstitucion;
    }

    public function setDelegacionInstitucion(?Delegacion $delegacion)
    {
        $this->delegacionInstitucion = $delegacion;
    }

    public function getUnidadesEnfermeria()
    {
        return $this->unidades;
    }

    /**
     * @param Unidad $unidad
     * @return Usuario
     */
    public function addUnidadEnfermeria(Unidad $unidad)
    {
        if (!$this->unidades->contains($unidad)) {
            $this->unidades[] = $unidad;
            //$unidad->addUsuario($this);
        }

        return $this;
    }

    /**
     * @param Unidad $unidad
     */
    public function removeUnidadEnfermeria(Unidad $unidad)
    {
        if ($this->unidades->contains($unidad)) {
            $this->unidades->removeElement($unidad);
            //$unidad->removeUsuario($this);
        }
    }

    /**
     * @return mixed
     */
    public function getCampus()
    {
        return $this->campus;
    }

    /**
     * @param mixed $campus
     */
    public function setCampus($campus)
    {
        $this->campus = $campus;
    }

}
