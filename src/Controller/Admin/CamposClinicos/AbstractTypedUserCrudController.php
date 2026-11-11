<?php

namespace App\Controller\Admin\CamposClinicos;

use App\Entity\Permiso;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Base CRUD for users of a fixed type (CAME, JDES...): the type defines both
 * which users are listed (by role key) and which permissions they receive (by permission key).
 */
abstract class AbstractTypedUserCrudController extends AbstractCrudController
{
    private const string SUPER_PERMISSION_KEY = 'SUPER';

    public function __construct(
        protected readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    /**
     * Key used both as rol.clave (list filter) and permiso.clave (assigned permissions).
     */
    abstract protected function userTypeKey(): string;

    public static function getEntityFqcn(): string
    {
        return Usuario::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->setPermission(Action::DETAIL, 'ROLE_SUPER');
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->andWhere(sprintf(
                'EXISTS (SELECT typedUser.id FROM %s typedUser
                    JOIN typedUser.permisos typedPermission JOIN typedPermission.rol typedRole
                    WHERE typedUser = entity AND typedRole.clave = :userTypeKey)',
                Usuario::class
            ))
            ->setParameter('userTypeKey', $this->userTypeKey());

        if ($this->isGranted('ROLE_SUPER')) {
            return $qb;
        }

        // Non-super admins must not see users holding the SUPER permission
        return $qb
            ->andWhere(sprintf(
                'NOT EXISTS (SELECT superUser.id FROM %s superUser JOIN superUser.permisos superPermission
                    WHERE superUser = entity AND superPermission.clave = :superPermissionKey)',
                Usuario::class
            ))
            ->setParameter('superPermissionKey', self::SUPER_PERMISSION_KEY);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof Usuario);

        $this->assignTypePermissions($entityInstance);
        $this->hashPlainPassword($entityInstance);

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof Usuario);

        $this->removeAllPermissions($entityInstance);
        $this->assignTypePermissions($entityInstance);
        $this->hashPlainPassword($entityInstance);

        parent::updateEntity($entityManager, $entityInstance);
    }

    protected function plainPasswordField(string $pageName): TextField
    {
        return TextField::new('plainPassword', 'Contraseña')
            ->setFormType(PasswordType::class)
            ->setRequired(Crud::PAGE_NEW === $pageName)
            ->setHelp(Crud::PAGE_EDIT === $pageName ? 'Déjalo vacío para conservar la contraseña actual.' : '');
    }

    private function assignTypePermissions(Usuario $user): void
    {
        $permissions = $this->em->getRepository(Permiso::class)->findBy(['clave' => $this->userTypeKey()]);

        foreach ($permissions as $permission) {
            $user->addPermiso($permission);
        }
    }

    private function removeAllPermissions(Usuario $user): void
    {
        foreach ($user->getPermisos()->toArray() as $permission) {
            $user->removePermiso($permission);
        }
    }

    private function hashPlainPassword(Usuario $user): void
    {
        $plainPassword = $user->getPlainPassword();

        if (empty($plainPassword)) {
            return;
        }

        $user->setContrasena($this->passwordHasher->hashPassword($user, $plainPassword));
    }
}
