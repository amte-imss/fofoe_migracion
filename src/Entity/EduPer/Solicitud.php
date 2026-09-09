<?php

namespace App\Entity\EduPer;

use App\Entity\Pago;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\UserInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: \App\Repository\EduPer\SolicitudRepository::class)]
#[ORM\Table(name: 'edu_per_request')]
class Solicitud implements UserInterface
{
    const STATUS_INICIO = '';
    const STATUS_EN_ESPERA_PAGO = 'wait_payment';
    const STATUS_ESPERA_VALIDACION = 'wait_validation';

    const STATUS_VALIDATED = 'validated';

    const STATUS_REJECTED = 'rejected';

    const STATUS_INVOICE_PENDING = 'invoice_pending';

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    #[ORM\ManyToOne(targetEntity: \App\Entity\EduPer\TipoSolicitud::class, inversedBy: 'solicitudes', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'type_id', referencedColumnName: 'id')]
    private $tipoSolicitud;

    #[ORM\ManyToOne(targetEntity: \App\Entity\EduPer\Duracion::class, inversedBy: 'duraciones', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'duration_id', referencedColumnName: 'id')]
    private $duracion;

    #[ORM\Column(type: 'float', precision: 24, scale: 4, nullable: false)]
    private $amount;

    #[ORM\ManyToOne(targetEntity: \App\Entity\EduPer\Curso::class, inversedBy: 'solicitudes', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'course_id', referencedColumnName: 'id')]
    private $curso;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private $hasDiscount;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 10, nullable: false)]
    private $status;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $cedulaIdentificacion;

    #[Vich\UploadableField(mapping: 'edu_per_solicitud', fileNameProperty: 'cedulaIdentificacion')]
    private $cedulaFile;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(type: 'datetime')]
    private $updatedAt;

    #[ORM\OneToMany(targetEntity: \App\Entity\Pago::class, mappedBy: 'eduPerSolicitud', cascade: ['persist'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $pagos;


    /**
     * @var string
     */
    #[Assert\Email]
    #[ORM\Column(type: 'string', length: 254, nullable: true)]
    private $email;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 254, nullable: true)]
    private $participant;

    /**
     * @var string
     */
    #[ORM\Column(type: 'integer')]
    private $quantity;


    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255)]
    private $password;

    /** @var string */
    private $plainPassword;

    /**
     * @var float
     */
    #[ORM\Column(type: 'float', nullable: true)]
    private $montoDiferencia;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $hasDiferencia;

    public function __construct()
    {
        $this->status = self::STATUS_INICIO;
        $this->pagos = new ArrayCollection();
        $this->createdAt = Carbon::now();
        $this->updatedAt = Carbon::now();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTipoSolicitud()
    {
        return $this->tipoSolicitud;
    }

    /**
     * @param mixed $tipoSolicitud
     */
    public function setTipoSolicitud($tipoSolicitud)
    {
        $this->tipoSolicitud = $tipoSolicitud;
    }



    /**
     * @return mixed
     */
    public function getDuracion()
    {
        return $this->duracion;
    }

    /**
     * @param mixed $duracion
     */
    public function setDuracion($duracion)
    {
        $this->duracion = $duracion;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getCurso()
    {
        return $this->curso;
    }

    /**
     * @param mixed $curso
     */
    public function setCurso($curso)
    {
        $this->curso = $curso;
    }

    /**
     * @return mixed
     */
    public function getHasDiscount()
    {
        return $this->hasDiscount;
    }

    /**
     * @param mixed $hasDiscount
     */
    public function setHasDiscount($hasDiscount)
    {
        $this->hasDiscount = $hasDiscount;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
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
     */
    public function setCedulaIdentificacion($cedulaIdentificacion)
    {
        $this->cedulaIdentificacion = $cedulaIdentificacion;
    }

    /**
     * @return File|null
     */
    public function getCedulaFile()
    {
        return $this->cedulaFile;
    }

    /**
     * @param File|null $cedulaFile
     */
    public function setCedulaFile($cedulaFile)
    {
        $this->cedulaFile = $cedulaFile;
    }

    /**
     * @return Carbon
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param Carbon $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return Carbon
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param Carbon $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return ArrayCollection
     */
    public function getPagos()
    {
        return $this->pagos;
    }

    /**
     * @param ArrayCollection $pagos
     */
    public function setPagos($pagos)
    {
        $this->pagos = $pagos;
    }

    /**
     * @return Pago|null
     */
    public function getLastPago()
    {
        $result = null;
        $pagos = $this->getPagos();
        if (count($pagos) > 0) {
            $result = $pagos->last();
        }
        return $result;
    }

    public function getRoles(): array
    {
        return ['ROLE_EDU_PER_PARTICIPANTE'];
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * @param string $participant
     */
    public function setParticipant($participant)
    {
        $this->participant = $participant;
    }

    /**
     * @return string
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param string $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    public function getIdFormatted()
    {
        return str_pad($this->id, 10, '0', STR_PAD_LEFT);
    }

    /**
     * @return string
     */
    public function getCreatedAtFormatted()
    {
        return $this->createdAt->format('d/m/Y');
    }

    /**
     * @return string
     */
    public function getStatusFormatted()
    {
        $statusValues = [
            self::STATUS_INICIO => 'Inicio',
            self::STATUS_EN_ESPERA_PAGO => 'En espera de pago',
            self::STATUS_ESPERA_VALIDACION => 'En espera de validación',
            self::STATUS_VALIDATED => 'Válidado por FOFOE',
            self::STATUS_REJECTED => 'No valido para FOFOE',
            self::STATUS_INVOICE_PENDING => 'Por facturar'
        ];
        return $statusValues[$this->status] ?: '';
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
     * @return float
     */
    public function getMontoDiferencia()
    {
        return $this->montoDiferencia;
    }

    /**
     * @param float $montoDiferencia
     */
    public function setMontoDiferencia($montoDiferencia)
    {
        $this->montoDiferencia = $montoDiferencia;
    }

    /**
     * @return bool
     */
    public function hasDiferencia()
    {
        return $this->hasDiferencia;
    }

    /**
     * @param bool $hasDiferencia
     */
    public function setHasDiferencia($hasDiferencia)
    {
        $this->hasDiferencia = $hasDiferencia;
    }
}
