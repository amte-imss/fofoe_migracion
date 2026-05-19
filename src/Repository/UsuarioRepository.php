<?php

namespace App\Repository;

use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UsuarioRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuario::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Usuario) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user->setContrasena($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Login por matrícula o correo — solo usuarios activos.
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->createQueryBuilder('u')
            ->where('u.matricula = :val OR u.correo = :val')
            ->andWhere('u.activo = true')
            ->setParameter('val', $identifier)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$user) {
            throw new UserNotFoundException(sprintf('Usuario "%s" no encontrado o inactivo.', $identifier));
        }

        return $user;
    }

    /**
     * Primer usuario activo con permiso CAME en la delegación dada.
     */
    public function getCameByDelegacion(int $delegacionId): ?Usuario
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.delegaciones', 'd')
            ->innerJoin('u.permisos', 'p')
            ->where('d.id = :delegacion')
            ->andWhere('p.clave = :clave')
            ->andWhere('u.activo = true')
            ->setParameter('delegacion', $delegacionId)
            ->setParameter('clave', 'CAME')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Primer usuario activo con permiso CAME o JDES en la unidad dada.
     */
    public function getJDESByUnidad(int $unidadId): ?Usuario
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.unidades', 'un')
            ->innerJoin('u.permisos', 'p')
            ->where('un.id = :unidad')
            ->andWhere('p.clave IN (:claves)')
            ->andWhere('u.activo = true')
            ->setParameter('unidad', $unidadId)
            ->setParameter('claves', ['CAME', 'JDES'])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Usuarios activos con permiso CAME en la delegación, ordenados por id DESC.
     */
    public function findUserCAMEByDelegacion(int $delegacionId): ?Usuario
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.delegaciones', 'd')
            ->innerJoin('u.permisos', 'p')
            ->where('d.id = :delegacion')
            ->andWhere('p.clave = :clave')
            ->andWhere('u.activo = true')
            ->setParameter('delegacion', $delegacionId)
            ->setParameter('clave', 'CAME')
            ->orderBy('u.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Usuarios activos con permiso JDES en la unidad, ordenados por id DESC.
     */
    public function findUserJDESByUnidad(int $unidadId): ?Usuario
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.unidades', 'un')
            ->innerJoin('u.permisos', 'p')
            ->where('un.id = :unidad')
            ->andWhere('p.clave = :clave')
            ->andWhere('u.activo = true')
            ->setParameter('unidad', $unidadId)
            ->setParameter('clave', 'JDES')
            ->orderBy('u.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Paginación de usuarios, ocultando los que tienen permiso SUPER (para no-SUPER).
     */
    public function findPaginatedExcludingSuper(int $page = 1, int $perPage = 20, bool $excludeSuper = true): array
    {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.permisos', 'p')
            ->orderBy('u.id', 'DESC');

        if ($excludeSuper) {
            $subQb = $this->createQueryBuilder('u2')
                ->select('u2.id')
                ->innerJoin('u2.permisos', 'p2')
                ->where('p2.clave = :super')
                ->getDQL();

            $qb->where($qb->expr()->notIn('u.id', $subQb))
               ->setParameter('super', 'SUPER');
        }

        return $qb
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }
}
