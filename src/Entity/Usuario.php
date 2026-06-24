<?php

namespace App\Entity;

use App\Entity\Posgrado\Residente;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'usuario')]
#[ORM\UniqueConstraint(columns: ['matricula', 'correo'])]
#[ORM\Entity(repositoryClass: 'App\Repository\UserRepository')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(
    fields: ['matricula', 'correo'],
    errorPath: 'correo',
    message: 'El correo y nombre de usuario (matricula) ya estan asignados a otro usuario.'
)]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface, EquatableInterface
{
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 30, unique: true, nullable: true)]
    #[Assert\Length(min: 6, max: 30)]
    private ?string $matricula = null;

    #[ORM\Column(type: 'string', length: 250, nullable: true)]
    private ?string $nombre = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $apellidoPaterno = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $apellidoMaterno = null;

    #[ORM\Column(type: 'bigint', nullable: true)]
    private ?int $regims = null;

    #[ORM\Column(type: 'string', length: 64)]
    private string $contrasena = '';

    #[ORM\Column(type: 'string', length: 254, nullable: true)]
    #[Assert\Email]
    private ?string $correo = null;

    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    private ?string $telefono = null;

    #[ORM\Column(type: 'boolean')]
    private bool $activo = false;

    #[ORM\ManyToOne(targetEntity: Departamento::class, inversedBy: 'usuarios')]
    #[ORM\JoinColumn(name: 'departamento_id', referencedColumnName: 'id')]
    private ?Departamento $departamento = null;

    #[ORM\ManyToMany(targetEntity: Delegacion::class, inversedBy: 'usuarios')]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id')]
    private Collection $delegaciones;

    #[ORM\ManyToMany(targetEntity: Unidad::class)]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id')]
    private Collection $unidades;

    #[ORM\Column(type: 'string', length: 18, nullable: true)]
    #[Assert\Length(min: 18, max: 18)]
    private string $curp = '';

    #[ORM\Column(type: 'string', length: 13, nullable: true)]
    #[Assert\Length(min: 12, max: 13)]
    private string $rfc = '';

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $sexo = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $fechaIngreso = null;

    #[ORM\ManyToOne(targetEntity: Categoria::class)]
    #[ORM\JoinColumn(name: 'categoria_id', referencedColumnName: 'id')]
    private ?Categoria $categoria = null;

    #[ORM\ManyToMany(targetEntity: Permiso::class, inversedBy: 'usuarios')]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id')]
    private Collection $permisos;

    #[ORM\ManyToOne(targetEntity: Institucion::class, inversedBy: 'usuarios')]
    #[ORM\JoinColumn(name: 'institucion_id', referencedColumnName: 'id', nullable: true)]
    private ?Institucion $institucion = null;

    #[ORM\ManyToOne(targetEntity: Campus::class, inversedBy: 'usuarios')]
    #[ORM\JoinColumn(name: 'campus_id', referencedColumnName: 'id', nullable: true)]
    private mixed $campus = null;

    #[ORM\OneToOne(targetEntity: Residente::class, cascade: ['persist'], mappedBy: 'usuario')]
    private ?Residente $residente = null;

    #[ORM\OneToMany(targetEntity: 'App\Entity\Posgrado\CargaMasiva', mappedBy: 'usuario')]
    private Collection $cargaMasivas;

    #[ORM\ManyToOne(targetEntity: Delegacion::class)]
    #[ORM\JoinColumn(name: 'delegacion_institucion_id', referencedColumnName: 'id', nullable: true)]
    private ?Delegacion $delegacionInstitucion = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $updatedAt;

    private ?string $plainPassword = null;
    private ?string $rol           = null;

    public function __construct()
    {
        $this->delegaciones = new ArrayCollection();
        $this->unidades     = new ArrayCollection();
        $this->permisos     = new ArrayCollection();
        $this->cargaMasivas = new ArrayCollection();
        $this->fechaIngreso = new \DateTime();
        $this->createdAt    = Carbon::now();
        $this->updatedAt    = Carbon::now();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContrasena(): string
    {
        return $this->contrasena;
    }

    public function setContrasena(string $contrasena): self
    {
        $this->contrasena = $contrasena;
        return $this;
    }

    // PasswordAuthenticatedUserInterface
    public function getPassword(): string
    {
        return $this->contrasena;
    }

    // UserInterface — replaces getUsername() in Sf 6
    public function getUserIdentifier(): string
    {
        return $this->correo ?: $this->matricula;
    }

    public function getRoles(): array
    {
        $roles = [];
        foreach ($this->getPermisos() as $permiso) {
            $roles[] = sprintf('ROLE_%s', $permiso->getClave());
        }
        return $roles;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void {}

    public function getCorreo(): ?string { return $this->correo; }
    public function setCorreo(?string $correo): self { $this->correo = $correo; return $this; }

    public function getTelefono(): ?string { return $this->telefono; }
    public function setTelefono(?string $telefono): self { $this->telefono = $telefono; return $this; }

    public function getPlainPassword(): ?string { return $this->plainPassword; }
    public function setPlainPassword(?string $plainPassword): self { $this->plainPassword = $plainPassword; return $this; }

    public function setMatricula(?string $matricula): self { $this->matricula = $matricula; return $this; }
    public function getMatricula(): ?string { return $this->matricula; }

    public function setNombre(?string $nombre): self { $this->nombre = $nombre; return $this; }
    public function getNombre(): ?string { return $this->nombre; }

    public function setApellidoPaterno(?string $apellidoPaterno): self { $this->apellidoPaterno = $apellidoPaterno; return $this; }
    public function getApellidoPaterno(): ?string { return $this->apellidoPaterno; }

    public function setApellidoMaterno(?string $apellidoMaterno): self { $this->apellidoMaterno = $apellidoMaterno; return $this; }
    public function getApellidoMaterno(): ?string { return $this->apellidoMaterno; }

    public function setRegims(?int $regims): self { $this->regims = $regims; return $this; }
    public function getRegims(): ?int { return $this->regims; }

    public function setActivo(bool $activo): self { $this->activo = $activo; return $this; }
    public function getActivo(): bool { return $this->activo; }

    public function setCurp(?string $curp): self { $this->curp = $curp ?: ''; return $this; }
    public function getCurp(): string { return $this->curp; }

    public function setRfc(?string $rfc): self { $this->rfc = $rfc ?: ''; return $this; }
    public function getRfc(): string { return $this->rfc; }

    public function setSexo(?string $sexo): self { $this->sexo = $sexo; return $this; }
    public function getSexo(): ?string { return $this->sexo; }

    public function setFechaIngreso(?\DateTimeInterface $fechaIngreso): self { $this->fechaIngreso = $fechaIngreso; return $this; }
    public function getFechaIngreso(): ?\DateTimeInterface { return $this->fechaIngreso; }

    public function setDepartamento(?Departamento $departamento): self { $this->departamento = $departamento; return $this; }
    public function getDepartamento(): ?Departamento { return $this->departamento; }

    public function setCategoria(?Categoria $categoria): self { $this->categoria = $categoria; return $this; }
    public function getCategoria(): ?Categoria { return $this->categoria; }

    public function getInstitucion(): ?Institucion { return $this->institucion; }
    public function setInstitucion(?Institucion $institucion): self { $this->institucion = $institucion; return $this; }

    public function getResidente(): ?Residente { return $this->residente; }
    public function setResidente(?Residente $residente): self { $this->residente = $residente; return $this; }

    public function getCampus(): mixed { return $this->campus; }
    public function setCampus(mixed $campus): self { $this->campus = $campus; return $this; }

    public function getFullName(): string
    {
        return trim(implode(' ', array_filter([
            $this->nombre,
            $this->apellidoPaterno,
            $this->apellidoMaterno,
        ])));
    }

    public function addDelegacione(Delegacion $delegacione): self
    {
        if (!$this->delegaciones->contains($delegacione)) {
            $this->delegaciones[] = $delegacione;
            $delegacione->addUsuario($this);
        }
        return $this;
    }

    public function removeDelegacione(Delegacion $delegacione): void
    {
        if ($this->delegaciones->contains($delegacione)) {
            $this->delegaciones->removeElement($delegacione);
            $delegacione->removeUsuario($this);
        }
    }

    public function getDelegaciones(): Collection { return $this->delegaciones; }

    public function getDelegacionesUnidadesInstitucion(): array
    {
        return array_merge(
            $this->delegaciones->toArray(),
            $this->unidades->toArray(),
            $this->institucion ? [$this->institucion] : []
        );
    }

    public function addUnidad(Unidad $unidad): self
    {
        if (!$this->unidades->contains($unidad)) {
            $this->unidades[] = $unidad;
        }
        return $this;
    }

    public function removeUnidad(Unidad $unidad): void
    {
        $this->unidades->removeElement($unidad);
    }

    public function getUnidades(): Collection { return $this->unidades; }

    public function setPermisos(Collection $permisos): self
    {
        foreach ($this->permisos as $permiso) {
            $permiso->removeUsuario($this);
        }
        $this->permisos = $permisos;
        foreach ($this->permisos as $permiso) {
            $permiso->addUsuario($this);
        }
        return $this;
    }

    public function addPermiso(Permiso $permiso): self
    {
        if (!$this->getPermisos()->contains($permiso)) {
            $this->permisos[] = $permiso;
        }
        return $this;
    }

    public function removePermiso(Permiso $permiso): void
    {
        $this->permisos->removeElement($permiso);
    }

    public function getPermisos(): Collection { return $this->permisos; }

    public function setRol(?string $rol): void { $this->rol = $rol; }

    public function getRol(): ?string
    {
        if ($this->rol === null && count($this->getRoles()) > 0) {
            return $this->getRoles()[0];
        }
        return $this->rol;
    }

    public function getCargaMasivas(): Collection { return $this->cargaMasivas; }
    public function setCargaMasivas(Collection $cargaMasivas): self { $this->cargaMasivas = $cargaMasivas; return $this; }

    public function getDelegacionInstitucion(): ?Delegacion { return $this->delegacionInstitucion; }
    public function setDelegacionInstitucion(?Delegacion $delegacion): self { $this->delegacionInstitucion = $delegacion; return $this; }

    public function getUnidadesEnfermeria(): Collection { return $this->unidades; }

    public function addUnidadEnfermeria(Unidad $unidad): self { return $this->addUnidad($unidad); }
    public function removeUnidadEnfermeria(Unidad $unidad): void { $this->removeUnidad($unidad); }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt(): \DateTimeInterface { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function isEqualTo(UserInterface $user): bool
    {
        return $user instanceof Usuario && $user->getId() === $this->getId();
    }

    public function __toString(): string
    {
        return '[' . $this->getUserIdentifier() . '] ' . $this->getFullName();
    }
}
