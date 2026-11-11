<?php

namespace App\Controller\Admin;

use App\Admin\Field\PlainField;
use App\Entity\Delegacion;
use App\Entity\Institucion;
use App\Entity\Permiso;
use App\Entity\Rol;
use App\Entity\Unidad;
use App\Entity\Usuario;
use App\Repository\Admin\AdminRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADM_FOFOE')]
class UserCrudController extends AbstractCrudController
{
    private const string SUPER_PERMISSION_KEY = 'SUPER';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $em,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Usuario::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Usuario')
            ->setEntityLabelInPlural('Usuarios')
            ->setPageTitle(Crud::PAGE_INDEX, 'Usuarios')
            ->setSearchFields([
                'matricula', 'delegaciones.nombre', 'unidades.nombre', 'permisos.nombre',
                'nombre', 'apellidoPaterno', 'apellidoMaterno', 'correo', 'curp',
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $action): Action => $action->setLabel('Crear usuario'))
            ->setPermission(Action::DETAIL, 'ROLE_SUPER');
    }

    public function configureFields(string $pageName): iterable
    {
        return in_array($pageName, [Crud::PAGE_EDIT, Crud::PAGE_NEW], true)
            ? $this->formFields($pageName)
            : $this->displayFields();
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

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

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        /** @var Usuario $user */
        $user = $entityDto->getInstance();

        if (!$user->getPermisos()->isEmpty()) {
            $user->setRol($user->getPermisos()->first()->getRol());
        }

        return parent::createEditFormBuilder($entityDto, $formOptions, $context);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof Usuario);

        $this->assignPermissionsFromRole($entityInstance);
        $this->syncInstitution($entityInstance);
        $this->hashPlainPassword($entityInstance);

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof Usuario);

        // The role is only set by the edit form; inline updates (e.g. the "activo" toggle) must not touch permissions
        if (null !== $entityInstance->getRol()) {
            $this->removeAllPermissions($entityInstance);
            $this->assignPermissionsFromRole($entityInstance);
            $this->syncInstitution($entityInstance);
            $this->hashPlainPassword($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function displayFields(): iterable
    {
        yield Field::new('matricula', 'Matrícula');
        yield TextField::new('fullName', 'Nombre')->setSortable(false);
        yield Field::new('correo', 'Correo electrónico');
        yield ArrayField::new('permisos', 'Rol / Permisos');
        yield BooleanField::new('activo', 'Activo');
        yield ArrayField::new('delegacionesUnidadesInstitucion', 'Delegaciones/Unidades/Institución');
    }

    private function formFields(string $pageName): iterable
    {
        yield Field::new('matricula', 'Matrícula / Usuario')->setRequired(true);
        yield Field::new('nombre', 'Nombre')->setRequired(false);
        yield Field::new('apellidoPaterno', 'Apellido paterno')->setRequired(false);
        yield Field::new('apellidoMaterno', 'Apellido materno');
        yield Field::new('correo', 'Correo electrónico')->setRequired(false);
        yield Field::new('curp', 'CURP')->setRequired(false);
        yield Field::new('rfc', 'RFC')->setRequired(false);
        yield ChoiceField::new('sexo', 'Sexo')
            ->setChoices(['Masculino' => 'Masculino', 'Femenino' => 'Femenino'])
            ->setRequired(false);
        yield BooleanField::new('activo', 'Activo');
        yield TextField::new('plainPassword', 'Contraseña')
            ->setFormType(PasswordType::class)
            ->setRequired(Crud::PAGE_NEW === $pageName)
            ->setHelp(Crud::PAGE_EDIT === $pageName ? 'Déjalo vacío para conservar la contraseña actual.' : '');
        yield $this->entityFormField('rol', 'Rol', Rol::class, [
            'choice_label' => 'nombre',
            'expanded' => true,
            'multiple' => false,
            'required' => true,
        ]);
        yield $this->entityFormField('institucion', 'Institución', Institucion::class, [
            'choice_label' => 'nombre',
            'query_builder' => AdminRepository::getAllInstituciones(...),
            'required' => false,
        ]);
        yield $this->entityFormField('delegacionInstitucion', 'OOAD-Institución', Delegacion::class, [
            'choice_label' => 'nombre',
            'query_builder' => AdminRepository::getAllDelegations(...),
            'required' => false,
        ]);
        yield AssociationField::new('delegaciones', 'OOADs')
            ->setFormTypeOptions([
                'choice_label' => 'nombre',
                'query_builder' => AdminRepository::getAllDelegations(...),
            ]);
        yield AssociationField::new('unidades', 'Unidades UMAE / Unidades Enfermería')
            ->setFormTypeOptions([
                'class' => Unidad::class,
                'choice_label' => 'nombreDelegacionUnidad',
                'query_builder' => AdminRepository::getAllUnidadesUMAEAndUnidadesEnfermeria(...),
            ]);
    }

    /**
     * Form-only EntityType field that works whether or not the property is a mapped Doctrine association.
     */
    private function entityFormField(string $property, string $label, string $class, array $options): PlainField
    {
        return PlainField::new($property, $label)
            ->onlyOnForms()
            ->setFormType(EntityType::class)
            ->setFormTypeOptions(['class' => $class, ...$options]);
    }

    private function assignPermissionsFromRole(Usuario $user): void
    {
        $permissions = $this->em->getRepository(Permiso::class)->findBy(['rol' => $user->getRol()]);

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

    private function syncInstitution(Usuario $user): void
    {
        // A new user has no id yet, so it cannot be linked to any previous institution
        if (null !== $user->getId()) {
            $previousInstitution = $this->em->getRepository(Institucion::class)->findOneBy(['usuario' => $user]);
            $previousInstitution?->setUsuario(null);
            $previousInstitution?->setCorreo('');
            $previousInstitution?->setRepresentante('');
        }

        $newInstitution = $user->getInstitucion();
        $newInstitution?->setUsuario($user);
        $newInstitution?->setCorreo($user->getCorreo());
        $newInstitution?->setRepresentante($user->getFullName());
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
