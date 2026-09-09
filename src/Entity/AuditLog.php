<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'audit_log')]
class AuditLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $entityClass = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $entityId = null;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $action = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $changesData = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $username = null;

    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $route = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $method = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $requestUri = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function setEntityClass(?string $entityClass): self { $this->entityClass = $entityClass; return $this; }
    public function getEntityClass(): ?string { return $this->entityClass; }

    public function setEntityId(?string $entityId): self { $this->entityId = $entityId; return $this; }
    public function getEntityId(): ?string { return $this->entityId; }

    public function setAction(?string $action): self { $this->action = $action; return $this; }
    public function getAction(): ?string { return $this->action; }

    public function setChangesData(?string $changesData): self { $this->changesData = $changesData; return $this; }
    public function getChangesData(): ?string { return $this->changesData; }

    public function setUsername(?string $username): self { $this->username = $username; return $this; }
    public function getUsername(): ?string { return $this->username; }

    public function setIpAddress(?string $ipAddress): self { $this->ipAddress = $ipAddress; return $this; }
    public function getIpAddress(): ?string { return $this->ipAddress; }

    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }
    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }

    public function setRoute(?string $route): self { $this->route = $route; return $this; }
    public function getRoute(): ?string { return $this->route; }

    public function setMethod(?string $method): self { $this->method = $method; return $this; }
    public function getMethod(): ?string { return $this->method; }

    public function setRequestUri(?string $requestUri): self { $this->requestUri = $requestUri; return $this; }
    public function getRequestUri(): ?string { return $this->requestUri; }
}
