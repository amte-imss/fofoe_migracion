<?php

namespace App\DataFixtures;

use App\Entity\Permiso;
use App\Entity\Rol;
use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        // ── Rol IE (Institución Educativa / Escuela de Enfermería) ─────────
        $rolIE = new Rol();
        $rolIE->setNombre('Institución Educativa');
        $rolIE->setClave('IE');
        $manager->persist($rolIE);

        $permisoIE = new Permiso();
        $permisoIE->setNombre('Institución Educativa');
        $permisoIE->setClave('IE');
        $permisoIE->setRol($rolIE);
        $manager->persist($permisoIE);

        // ── Rol SUPER ──────────────────────────────────────────────────────
        $rolSuper = new Rol();
        $rolSuper->setNombre('Super Administrador');
        $rolSuper->setClave('SUPER');
        $manager->persist($rolSuper);

        $permisoSuper = new Permiso();
        $permisoSuper->setNombre('Super Administrador');
        $permisoSuper->setClave('SUPER');
        $permisoSuper->setRol($rolSuper);
        $manager->persist($permisoSuper);

        // ── Usuario escuela (enfermería) ───────────────────────────────────
        $escuela = new Usuario();
        $escuela->setNombre('Escuela Test');
        $escuela->setApellidoPaterno('Enfermería');
        $escuela->setApellidoMaterno('');
        $escuela->setMatricula('ENF001');
        $escuela->setCorreo('escuela@test.com');
        $escuela->setTelefono('');
        $escuela->setActivo(true);
        $escuela->setContrasena($this->hasher->hashPassword($escuela, 'escuela123'));
        $escuela->addPermiso($permisoIE);
        $manager->persist($escuela);

        // ── Usuario super admin ────────────────────────────────────────────
        $super = new Usuario();
        $super->setNombre('Super');
        $super->setApellidoPaterno('Admin');
        $super->setApellidoMaterno('');
        $super->setMatricula('SUPER001');
        $super->setCorreo('super@admin.com');
        $super->setTelefono('');
        $super->setActivo(true);
        $super->setContrasena($this->hasher->hashPassword($super, 'super123'));
        $super->addPermiso($permisoSuper);
        $manager->persist($super);

        $manager->flush();
    }
}

