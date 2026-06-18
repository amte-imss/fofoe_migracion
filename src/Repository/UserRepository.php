<?php

namespace App\Repository;

use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

class UserRepository extends ServiceEntityRepository implements UserLoaderInterface, UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuario::class);
    }

    public function loadUserByIdentifier(string $identifier): ?Usuario
    {
        try {
            return $this->createQueryBuilder('u')
                ->where("(u.matricula is not null and concat(u.matricula, '') = :username) OR (u.matricula is null and u.correo = :email)")
                ->andWhere('u.activo = true')
                ->setParameter('username', $identifier)
                ->setParameter('email', $identifier)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (\Exception) {
            return null;
        }
    }

    public function getCameByDelegacion(int $delegacion_id): ?Usuario
    {
        return $this->createQueryBuilder('usuario')
            ->innerJoin('usuario.delegaciones', 'delegaciones')
            ->innerJoin('usuario.permisos', 'permiso')
            ->where('delegaciones.id = :delegacion')
            ->andWhere('usuario.activo = true')
            ->andWhere('permiso.clave = :clave')
            ->setParameter('delegacion', $delegacion_id)
            ->setParameter('clave', 'CAME')
            ->orderBy('usuario.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getJDESByUnidad(int $unidad_id): ?Usuario
    {
        $qb = $this->createQueryBuilder('usuario')
            ->innerJoin('usuario.unidades', 'unidades')
            ->innerJoin('usuario.permisos', 'permiso')
            ->where('unidades.id = :unidad')
            ->andWhere('usuario.activo = true')
            ->andWhere($qb->expr()->in('permiso.clave', ['CAME', 'JDES']))
            ->setParameter('unidad', $unidad_id)
            ->orderBy('usuario.id', 'DESC')
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findUserCAMEByDelegacion(int $idDel): array
    {
        return $this->createQueryBuilder('usuario')
            ->join('usuario.delegaciones', 'delegacion')
            ->join('usuario.permisos', 'permiso')
            ->where('delegacion = :idDel')
            ->andWhere('usuario.activo = true')
            ->andWhere('permiso.clave = :permiso')
            ->setParameter('idDel', $idDel)
            ->setParameter('permiso', 'CAME')
            ->orderBy('usuario.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findUserJDESByUnidad(int $idUnidad): array
    {
        return $this->createQueryBuilder('usuario')
            ->join('usuario.unidades', 'unidad')
            ->join('usuario.permisos', 'permiso')
            ->where('unidad = :idUnidad')
            ->andWhere('usuario.activo = true')
            ->andWhere('permiso.clave = :permiso')
            ->setParameter('permiso', 'JDES')
            ->setParameter('idUnidad', $idUnidad)
            ->orderBy('usuario.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
