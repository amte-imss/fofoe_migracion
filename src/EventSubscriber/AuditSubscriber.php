<?php

namespace App\EventSubscriber;

use App\Entity\AuditLog;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuditSubscriber implements EventSubscriber
{
    private array $pendingLogs       = [];
    private bool  $processingAuditLogs = false;
    private array $ignoredFields     = ['createdAt', 'updatedAt', 'password', 'salt'];

    public function __construct(
        private readonly ManagerRegistry         $registry,
        private readonly ?TokenStorageInterface  $tokenStorage = null,
        private readonly ?RequestStack           $requestStack = null,
    ) {}

    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
            Events::postPersist,
            Events::postFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        if ($this->processingAuditLogs) return;

        $em  = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!$this->isEntityAuditable($entity)) continue;

            $changes = [];
            foreach ($uow->getEntityChangeSet($entity) as $field => $values) {
                if (in_array($field, $this->ignoredFields, strict: true)) continue;

                $oldValue = $this->normalizeValue($values[0]);
                $newValue = $this->normalizeValue($values[1]);

                if ($oldValue === $newValue) continue;

                $changes[$field] = ['old' => $oldValue, 'new' => $newValue];
            }

            if (empty($changes)) continue;

            $this->pendingLogs[] = $this->buildLogRow($entity, 'UPDATE', $changes);
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if (!$this->isEntityAuditable($entity)) continue;
            $this->pendingLogs[] = $this->buildLogRow($entity, 'DELETE', $this->extractEntityData($entity));
        }
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        if ($this->processingAuditLogs) return;

        $entity = $args->getObject();

        if (!$this->isEntityAuditable($entity)) return;

        $this->pendingLogs[] = $this->buildLogRow($entity, 'CREATE', $this->extractEntityData($entity));
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if ($this->processingAuditLogs || empty($this->pendingLogs)) return;

        $this->processingAuditLogs = true;

        try {
            $em          = $this->registry->getManager();
            $logsToPersist = $this->pendingLogs;
            $this->pendingLogs = [];

            foreach ($logsToPersist as $row) {
                $log = new AuditLog();
                $log->setEntityClass($row['entityClass']);
                $log->setEntityId($row['entityId']);
                $log->setAction($row['action']);
                $log->setChangesData(json_encode($row['changesData']));
                $log->setUsername($row['username']);
                $log->setIpAddress($row['ipAddress']);
                $log->setCreatedAt($row['createdAt']);
                $log->setRoute($row['route']);
                $log->setMethod($row['method']);
                $log->setRequestUri($row['requestUri']);
                $em->persist($log);
            }

            $em->flush();
        } finally {
            $this->processingAuditLogs = false;
        }
    }

    private function buildLogRow(object $entity, string $action, array $changesData): array
    {
        return [
            'entityClass' => get_class($entity),
            'entityId'    => $this->extractEntityId($entity),
            'action'      => $action,
            'changesData' => $changesData,
            'username'    => $this->getCurrentUsername(),
            'ipAddress'   => $this->getClientIp(),
            'route'       => $this->getCurrentRoute(),
            'method'      => $this->getCurrentMethod(),
            'requestUri'  => $this->getCurrentRequestUri(),
            'createdAt'   => new \DateTime(),
        ];
    }

    private function isEntityAuditable(object $entity): bool
    {
        if ($entity instanceof AuditLog) return false;

        $reflection = new \ReflectionClass($entity);

        foreach ($reflection->getAttributes() as $attribute) {
            if ($attribute->getName() === 'App\\Annotation\\Auditable') {
                return true;
            }
        }

        return false;
    }

    private function extractEntityId(object $entity): ?string
    {
        if (!method_exists($entity, 'getId')) return null;
        $id = $entity->getId();
        return $id !== null ? (string) $id : null;
    }

    private function extractEntityData(object $entity): array
    {
        $data       = [];
        $reflection = new \ReflectionClass($entity);

        foreach ($reflection->getProperties() as $property) {
            $fieldName = $property->getName();
            if (in_array($fieldName, $this->ignoredFields, strict: true)) continue;
            $property->setAccessible(true);
            $data[$fieldName] = $this->normalizeValue($property->getValue($entity));
        }

        return $data;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value === null || is_scalar($value)) return $value;

        if ($value instanceof \DateTimeInterface) return $value->format('Y-m-d H:i:s');

        if ($value instanceof Collection) {
            return array_values(array_filter(
                array_map(fn($item) => $this->normalizeRelation($item), $value->toArray())
            ));
        }

        if (is_array($value)) {
            $result = [];
            foreach ($value as $key => $item) {
                if (is_scalar($item) || $item === null)           { $result[$key] = $item; continue; }
                if ($item instanceof \DateTimeInterface)           { $result[$key] = $item->format('Y-m-d H:i:s'); continue; }
                $normalized = $this->normalizeRelation($item);
                if ($normalized !== null) $result[$key] = $normalized;
            }
            return $result;
        }

        return $this->normalizeRelation($value);
    }

    private function normalizeRelation(mixed $value): ?array
    {
        if (!is_object($value)) return null;

        return [
            'class' => get_class($value),
            'id'    => method_exists($value, 'getId') ? $value->getId() : null,
        ];
    }

    private function getCurrentUsername(): ?string
    {
        $user = $this->tokenStorage?->getToken()?->getUser();
        if (!$user) return null;
        if (is_object($user)) {
            return method_exists($user, 'getUserIdentifier')
                ? $user->getUserIdentifier()
                : (string) $user;
        }
        return is_string($user) ? $user : null;
    }

    private function getClientIp(): ?string
    {
        return $this->requestStack?->getCurrentRequest()?->getClientIp();
    }

    private function getCurrentRoute(): ?string
    {
        return $this->requestStack?->getCurrentRequest()?->attributes->get('_route');
    }

    private function getCurrentMethod(): ?string
    {
        return $this->requestStack?->getCurrentRequest()?->getMethod();
    }

    private function getCurrentRequestUri(): ?string
    {
        return $this->requestStack?->getCurrentRequest()?->getRequestUri();
    }
}
