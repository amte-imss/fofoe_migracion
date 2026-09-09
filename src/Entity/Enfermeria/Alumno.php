<?php

namespace App\Entity\Enfermeria;

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
#[ORM\Entity(repositoryClass: \App\Repository\Enfermeria\AlumnoRepository::class)]
#[ORM\Table(name: 'lote_alumno_escuela_enf')]
class Alumno implements UserInterface, \Stringable
{

    const STATUS_INICIO = '';
    const STATUS_EN_ESPERA_PAGO = 'wait_payment';
    const STATUS_ESPERA_VALIDACION = 'wait_validation';

    const STATUS_VALIDATED = 'validated';

    const STATUS_REJECTED = 'rejected';

    const STATUS_INVOICE_PENDING = 'invoice_pending';

    const STATUS_REJECTED_DOCUMENTACION = 'rejected_doc';

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
    private $nombre;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private $email;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 10, nullable: false)]
    private $tipo;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 18, nullable: false)]
    private $curp;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 20, nullable: false)]
    private $status;

    /**
     * @var float
     */
    #[ORM\Column(type: 'float', nullable: true)]
    private $promedio;

    /**
     * @var float
     */
    #[ORM\Column(type: 'float', nullable: true)]
    private $monto;

    /**
     * @var Solicitud
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\Enfermeria\Solicitud::class, inversedBy: 'alumnos', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'lote_id', referencedColumnName: 'id')]
    private $solicitud;

    #[ORM\OneToMany(targetEntity: \App\Entity\Pago::class, mappedBy: 'escuelaEnfermeriaSolicitud', cascade: ['persist'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $pagos;


    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $cedulaIdentificacion;

    #[Vich\UploadableField(mapping: 'alumno_enfermeria_cedula', fileNameProperty: 'cedulaIdentificacion')]
    private $cedulaFile;

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
        $this->status = '';
        $this->pagos = new ArrayCollection();
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

    public function getIdFormatted()
    {
        return str_pad($this->id, 10, '0', STR_PAD_LEFT);
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
     * @return string
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * @param string $tipo
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    }

    /**
     * @return string
     */
    public function getCurp()
    {
        return $this->curp;
    }

    /**
     * @param string $curp
     */
    public function setCurp($curp)
    {
        $this->curp = $curp;
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
        $this->status = ($status === null) ? '' : (string) $status;
    }

    /**
     * @return float
     */
    public function getPromedio()
    {
        return $this->promedio;
    }

    /**
     * @param float $promedio
     */
    public function setPromedio($promedio)
    {
        $this->promedio = $promedio;
    }

    /**
     * @return float
     */
    public function getMonto()
    {
        return $this->monto;
    }

    /**
     * @param float $monto
     */
    public function setMonto($monto)
    {
        $this->monto = $monto;
    }

    /**
     * @return Solicitud
     */
    public function getSolicitud()
    {
        return $this->solicitud;
    }

    /**
     * @param mixed $solicitud
     */
    public function setSolicitud($solicitud)
    {
        $this->solicitud = $solicitud;
    }

    public function getStatusFormatted()
    {
        $statusValues = [
            self::STATUS_INICIO => 'Inicio',
            self::STATUS_EN_ESPERA_PAGO => 'En espera de pago',
            self::STATUS_ESPERA_VALIDACION => 'En espera de validación',
            self::STATUS_VALIDATED => 'Válidado por FOFOE',
            self::STATUS_REJECTED => 'No valido para FOFOE',
            self::STATUS_REJECTED_DOCUMENTACION => 'No valido para FOFOE por documentación',
            self::STATUS_INVOICE_PENDING => 'Por facturar'
        ];
        return $statusValues[$this->status] ?? $this->status ?? 'Sin estado';
    }

    /**
     * @return Collection
     */
    public function getPagos()
    {
        return $this->pagos;
    }

    /**
     * @return Pago|null
     */
    public function getPago()
    {
        $result = null;
        $pagos = $this->getPagos();
        if (count($pagos) > 0) {
            $result = $pagos[0];
        }
        return $result;
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
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @param string $cedulaIdentificacion
     * @return Alumno
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
    public function setCedulaFile($cedulaFile = null)
    {
        $this->cedulaFile = $cedulaFile;
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

    public function __serialize(): array
    {
        return [
            'id'    => $this->id,
            'email' => $this->email,
            'curp'  => $this->curp,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id    = $data['id'];
        $this->email = $data['email'];
        $this->curp  = $data['curp'];
    }

    public function __toString(): string
    {
        return $this->getId() .' - '. $this->getNombre() . ' (' . $this->getCurp() . ')';
    }


}
