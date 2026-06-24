<?php

namespace App\Controller;

use App\Entity\Solicitud;
use App\Entity\Unidad;
use App\Entity\Usuario;
use App\Exception\CouldFindUserRelationWithInstitucion;
use App\Exception\CouldNotFindPago;
use App\Exception\CouldNotFindSolicitud;
use App\Exception\Posgrado\CouldFindUserRelationWithResidente;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

abstract class DIEControllerController extends AbstractController
{
    public function __construct(
        protected RequestStack $requestStack,
        protected EntityManagerInterface $em,
        protected SerializerInterface $serializer,
    ) {}

    protected function getFormErrors(FormInterface $form, bool $deepGlobal = false, bool $deepField = false): array
    {
        $errors = [];

        // Global
        foreach ($form->getErrors($deepGlobal) as $error) {
            $errors[$form->getName()][] = $error->getMessage();
        }

        // Fields
        foreach ($form as $child /** @var Form $child */) {
            if (!$child->isValid()) {
                foreach ($child->getErrors($deepField) as $error) {
                    $errors[$child->getName()][] = $error->getMessage();
                }
            }
        }

        return $errors;
    }

    protected function jsonResponse(array $data): JsonResponse
    {
        $json = [];
        $status = 200;

        if (isset($data['status'])) {
            $json['status'] = $data['status'];
            if (!$data['status']) {
                $status = Response::HTTP_UNPROCESSABLE_ENTITY;
            }
        } else {
            $json['status'] = true;
        }

        if (isset($data['message'])) {
            $json['message'] = $data['message'];
        } elseif (isset($data['status']) && $json['status']) {
            $json['message'] = 'Solicitud procesada con éxito';
        }

        if (isset($data['error'])) {
            $json['error'] = $data['error'];
        }

        if (isset($data['object'])) {
            $json['data'] = $data['object'];
        }

        if (isset($data['meta'])) {
            $json['meta'] = $data['meta'];
        }

        return new JsonResponse($json, $status);
    }

    protected function jsonErrorResponse(
        FormInterface $form,
        array $data = [],
        bool $deepGlobal = false,
        bool $deepField = false
    ): JsonResponse {
        return new JsonResponse([
            'message' => $data['message'] ?? 'Ocurrió un error al procesar la solicitud. Por favor verifique la información ingresada.',
            'status'  => false,
            'errors'  => $this->serializer->normalize(
                $this->getFormErrors($form, $deepGlobal, $deepField),
                'json'
            ),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function successResponse(string $message): JsonResponse
    {
        return new JsonResponse([
            'message' => $message,
            'status'  => true,
        ], Response::HTTP_OK);
    }

    protected function failedResponse(string $message): JsonResponse
    {
        return new JsonResponse([
            'message' => $message,
            'status'  => false,
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    protected function httpErrorResponse(
        string $message = '',
        int $http_code = Response::HTTP_FORBIDDEN
    ): JsonResponse {
        return new JsonResponse([
            'message' => $message,
            'status'  => false,
        ], $http_code);
    }

    protected function isUserDelegacionActivated(): bool
    {
        $session = $this->requestStack->getSession();
        return
            !empty($session->get('user_delegacion'))
            || empty($session->get('user_unidad'));
    }

    protected function getUserDelegacionId(): mixed
    {
        $session = $this->requestStack->getSession();
        $query_delegacion = $session->get('user_delegacion');
        $result = null;

        /** @var Usuario|null $user */
        $user = $this->getUser();

        if ($user) {
            if (!$query_delegacion) {
                $del_object = $user->getDelegaciones()->first();
                $result = $del_object ? $del_object->getId() : null;
            } else {
                if ($user->getDelegaciones()->exists(
                    fn($key, $value) => $value->getId() == $query_delegacion
                )) {
                    $result = $query_delegacion;
                }
            }
        }

        return $result;
    }

    protected function getUserUnidad(): ?Unidad
    {
        $session = $this->requestStack->getSession();
        $query_unidad = $session->get('user_unidad');
        $result = null;

        /** @var Usuario|null $user */
        $user = $this->getUser();

        if ($user && $user->getUnidades()) {
            if (!$query_unidad) {
                $del_object = $user->getUnidades()->first();
                $result = $del_object ? $del_object->getId() : null;
            } else {
                if ($user->getUnidades()->exists(
                    fn($key, $value) => $value->getId() == $query_unidad
                )) {
                    $result = $query_unidad;
                }
            }
        }

        if ($result) {
            return $this->em->getRepository(Unidad::class)->find($result);
        }

        return null;
    }

    protected function getUserUnidadId(): mixed
    {
        $session = $this->requestStack->getSession();
        $query_unidad = $session->get('user_unidad');
        $result = null;

        /** @var Usuario|null $user */
        $user = $this->getUser();

        if ($user && $user->getUnidades()) {
            if (!$query_unidad) {
                $del_object = $user->getUnidades()->first();
                $result = $del_object ? $del_object->getId() : null;
            } else {
                if ($user->getUnidades()->exists(
                    fn($key, $value) => $value->getId() == $query_unidad
                )) {
                    $result = $query_unidad;
                }
            }
        }

        return $result;
    }

    protected function validarSolicitudDelegacion(Solicitud $solicitud): bool
    {
        $session = $this->requestStack->getSession();
        $delegacion = $solicitud->getDelegacion();

        if (!$delegacion) {
            return true;
        }

        $delegacion_came = $session->get('user_delegacion');

        if ($delegacion_came) {
            return $delegacion_came == $delegacion->getId();
        }

        /** @var Usuario $user */
        $user = $this->getUser();
        $delegaciones = $user->getDelegaciones();

        return count($delegaciones) > 0
            && $delegaciones->first()->getId() === $delegacion->getId();
    }

    protected function validarSolicitudUnidad(Solicitud $solicitud): bool
    {
        $session = $this->requestStack->getSession();
        $unidad = $solicitud->getUnidad();

        /** @var Usuario $user */
        $user = $this->getUser();

        if (!$unidad || !$user->getUnidades()) {
            return false;
        }

        $unidad_came = $session->get('user_unidad');

        if ($unidad_came) {
            return $unidad_came == $unidad->getId();
        }

        $unidades = $user->getUnidades();

        return count($unidades) > 0
            && $unidades->first()->getId() === $unidad->getId();
    }

    protected function isGrantedUserAccessToSolicitud(Solicitud $solicitud): bool
    {
        return $this->validarSolicitudDelegacion($solicitud)
            || $this->validarSolicitudUnidad($solicitud);
    }

    protected function createNotFindUserRelationWithInstitucionException(): CouldFindUserRelationWithInstitucion
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();
        throw CouldFindUserRelationWithInstitucion::withId($usuario->getId());
    }

    protected function createNotFindUserRelationWithResidenteException(): CouldFindUserRelationWithResidente
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();
        throw CouldFindUserRelationWithResidente::withId($usuario->getId());
    }

    protected function createNotFindSolicitudException(mixed $id): CouldNotFindSolicitud
    {
        throw CouldNotFindSolicitud::withId($id);
    }

    protected function createNotFindPagoException(mixed $id): CouldNotFindPago
    {
        throw CouldNotFindPago::withId($id);
    }
}
