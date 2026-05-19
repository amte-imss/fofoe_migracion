<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Usuario;
use App\Repository\SolicitudRepository;
use App\Repository\PagoRepository;

#[ORM\Entity(repositoryClass: \App\Repository\LogRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'bitacora')]
class Log
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\Column(name: 'message', type: 'text')]
    private $message;

    #[ORM\Column(name: 'context', type: 'json')]
    private $context;

    #[ORM\Column(name: 'level', type: 'smallint')]
    private $level;

    #[ORM\Column(name: 'level_name', type: 'string', length: 50)]
    private $levelName;

    #[ORM\Column(name: 'extra', type: 'json')]
    private $extra;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Usuario::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id', nullable: false)]
    private $user;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    #[ORM\PrePersist]
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return Log
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set context
     *
     * @param array $context
     *
     * @return Log
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get context
     *
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set level
     *
     * @param integer $level
     *
     * @return Log
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return integer
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set levelName
     *
     * @param string $levelName
     *
     * @return Log
     */
    public function setLevelName($levelName)
    {
        $this->levelName = $levelName;

        return $this;
    }

    /**
     * Get levelName
     *
     * @return string
     */
    public function getLevelName()
    {
        return $this->levelName;
    }

    /**
     * Set extra
     *
     * @param array $extra
     *
     * @return Log
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;

        return $this;
    }

    /**
     * Get extra
     *
     * @return array
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Log
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set user
     *
     * @param Usuario $user
     *
     * @return Log
     */
    public function setUser(Usuario $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \App\Entity\Usuario
     */
    public function getUser()
    {
        return $this->user;
    }

    public function getContextDisplay() {
        return json_encode(
            array_filter($this->getContext()),
            JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    }

    public function getExtraDisplay() {
        return json_encode(
            array_filter($this->getExtra()),
            JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    }

    public function getResumenDisplay() {
        $res = [];
        if ( array_key_exists('session_id', $this->extra) )
            $res["session_id"] = $this->extra['session_id'];

        if ( array_key_exists('solicitud_id', $this->context) )
            $res["solicitud_id"] = $this->context['solicitud_id'];

        if ( array_key_exists('referencia', $this->context) )
            $res["referencia"] = $this->context['referencia'];

        if ( array_key_exists('folio', $this->context) )
            $res["folio"] = $this->context['folio'];

        if ( array_key_exists('tipo', $this->context) )
            $res["tipo"] = $this->context['tipo'];

        if ( array_key_exists('ciclo', $this->context) )
            $res["ciclo"] = $this->context['ciclo'];

        return json_encode(
                    array_filter($res),
                    JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    }
}
