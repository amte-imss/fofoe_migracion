<?php

namespace App\Normalizer;

use App\Entity\Usuario;

class UsuarioNormalizer
{
    public function serialize(Usuario $usuario): array
    {
        $data = [
            'id'             => $usuario->getId(),
            'matricula'      => $usuario->getMatricula(),
            'nombre'         => $usuario->getNombre(),
            'apellidoPaterno'=> $usuario->getApellidoPaterno(),
            'apellidoMaterno'=> $usuario->getApellidoMaterno(),
            'regims'         => $usuario->getRegims(),
            'correo'         => $usuario->getCorreo(),
            'telefono'       => $usuario->getTelefono(),
            'activo'         => $usuario->getActivo(),
            'departamento'   => $usuario->getDepartamento(),
            'curp'           => $usuario->getCurp(),
            'rfc'            => $usuario->getRfc(),
            'sexo'           => $usuario->getSexo(),
            'fechaIngreso'   => $usuario->getFechaIngreso(),
            'categoria'      => $usuario->getCategoria(),
            'permisos'       => $usuario->getPermisos(),
            'institucion'    => $usuario->getInstitucion(),
            'residente'      => $usuario->getResidente(),
            'rol'            => $usuario->getRol(),
            'roles'          => $usuario->getRoles(),
            'cargaMasivas'   => $usuario->getCargaMasivas(),
            'delegacion'     => $this->resolveDelegacion($usuario),
        ];

        return $data;
    }

    private function resolveDelegacion(Usuario $usuario): ?array
    {
        if ($this->isGranted(['ROLE_JDES', 'ROLE_JDES_MINUS'], $usuario->getRoles())) {
            $delegacion = $usuario->getUnidades()->first()->getDelegacion();
            return [
                'id'     => $delegacion->getId(),
                'nombre' => $delegacion->getNombre(),
            ];
        }

        if (!$usuario->getDelegaciones()->count()) {
            return null;
        }

        $delegacion = $usuario->getDelegaciones()->first();
        return [
            'id'     => $delegacion->getId(),
            'nombre' => $delegacion->getNombre(),
        ];
    }

    public function isGranted(array $searchRoles, array $userRoles): bool
    {
        foreach ($searchRoles as $role) {
            if (in_array($role, $userRoles, strict: true)) {
                return true;
            }
        }
        return false;
    }
}
