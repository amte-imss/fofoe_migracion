<?php

namespace App\Entity\Enfermeria;

use App\Annotation\Auditable;
use App\Entity\Pago;
use App\Repository\Enfermeria\AlumnoRepository;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Table(name: 'lote_alumno_escuela_enf')]
#[ORM\Entity(repositoryClass: AlumnoRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Auditable]
#[Vich\Uploadable]
class Alumno implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const STATUS_INICIO = '';
    public const STATUS_EN_ESPERA_PAGO = 'wait_payment';
    public const STATUS_ESPERA_VALIDACION = 'wait_validation';
    public const STATUS_VALIDATED = 'validated';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_INVOICE_PENDING = 'invoice_pending';
    public const STATUS_REJECTED_DOCUMENTACION = 'rejected_doc';

    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private ?string $nombre = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 10, nullable: false)]
    private ?string $tipo = null;

    #[ORM\Column(type: 'string', length: 18, nullable: false)]
    private ?string $curp = null;

    #[ORM\Column(type: 'string', length: 20, nullable: false)]
    private string $status = '';

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $promedio = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $monto = null;

    #[ORM\ManyToOne(targetEntity: Solicitud::class, inversedBy: 'alumnos', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'lote_id', referencedColumnName: 'id')]
    private ?Solicitud $solicitud = null;

    /** @var Collection<int, Pago> */
    #[ORM\OneToMany(targetEntity: Pago::class, mappedBy: 'escuelaEnfermeriaSolicitud', cascade: ['persist'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $pagos;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $cedulaIdentificacion = null;

    #[Vich\UploadableField(mapping: 'alumno_enfermeria_cedula', fileNameProperty: 'cedulaIdentificacion')]
    private ?File $cedulaFile = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $montoDiferencia = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $hasDiferencia = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->status = '';
        $this->pagos = new ArrayCollection();
        $this->createdAt = Carbon::now();
        $this->updatedAt = Carbon::now();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getIdFormatted(): string
    {
        return str_pad((string) $this->id, 10, '0', STR_PAD_LEFT);
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): void
    {
        $this->tipo = $tipo;
    }

    public function getCurp(): ?string
    {
        return $this->curp;
    }

    public function setCurp(string $curp): void
    {
        $this->curp = $curp;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = ($status === null) ? '' : (string) $status;
    }

    public function getPromedio(): ?float
    {
        return $this->promedio;
    }

    public function setPromedio(?float $promedio): void
    {
        $this->promedio = $promedio;
    }

    public function getMonto(): ?float
    {
        return $this->monto;
    }

    public function setMonto(?float $monto): void
    {
        $this->monto = $monto;
    }

    public function getSolicitud(): ?Solicitud
    {
        return $this->solicitud;
    }

    public function setSolicitud(?Solicitud $solicitud): void
    {
        $this->solicitud = $solicitud;
    }

    public function getStatusFormatted(): string
    {
        $statusValues = [
            self::STATUS_INICIO => 'Inicio',
            self::STATUS_EN_ESPERA_PAGO => 'En espera de pago',
            self::STATUS_ESPERA_VALIDACION => 'En espera de validación',
            self::STATUS_VALIDATED => 'Válidado por FOFOE',
            self::STATUS_REJECTED => 'No valido para FOFOE',
            self::STATUS_REJECTED_DOCUMENTACION => 'No valido para FOFOE por documentación',
            self::STATUS_INVOICE_PENDING => 'Por facturar',
        ];

        return $statusValues[$this->status] ?: '';
    }

    /** @return Collection<int, Pago> */
    public function getPagos(): Collection
    {
        return $this->pagos;
    }

    public function getPago(): ?Pago
    {
        $pagos = $this->getPagos();

        return count($pagos) > 0 ? $pagos[0] : null;
    }

    public function getLastPago(): ?Pago
    {
        $pagos = $this->getPagos();

        return count($pagos) > 0 ? $pagos->last() : null;
    }

    public function getRoles(): array
    {
        return ['ROLE_ENFERMERIA_ALUMNO'];
    }

    public function getPassword(): ?string
    {
        return $this->email;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUserIdentifier(): string
    {
        return $this->curp;
    }

    public function eraseCredentials(): void
    {
    }

    public function setCedulaIdentificacion(?string $cedulaIdentificacion): self
    {
        $this->cedulaIdentificacion = $cedulaIdentificacion;

        return $this;
    }

    public function getCedulaIdentificacion(): ?string
    {
        return $this->cedulaIdentificacion;
    }

    public function getCedulaFile(): ?File
    {
        return $this->cedulaFile;
    }

    public function setCedulaFile(?File $cedulaFile = null): void
    {
        $this->cedulaFile = $cedulaFile;
    }

    public function getMontoDiferencia(): ?float
    {
        return $this->montoDiferencia;
    }

    public function setMontoDiferencia(?float $montoDiferencia): void
    {
        $this->montoDiferencia = $montoDiferencia;
    }

    public function hasDiferencia(): ?bool
    {
        return $this->hasDiferencia;
    }

    public function setHasDiferencia(?bool $hasDiferencia): void
    {
        $this->hasDiferencia = $hasDiferencia;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'curp' => $this->curp,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->email = $data['email'];
        $this->curp = $data['curp'];
    }

    public function __toString(): string
    {
        return $this->getId() . ' - ' . $this->getNombre() . ' (' . $this->getCurp() . ')';
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
