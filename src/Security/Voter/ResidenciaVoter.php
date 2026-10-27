<?php

namespace App\Security\Voter;

use App\Entity\Posgrado\Residencia;
use App\Entity\Usuario;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Residencia>
 */
final class ResidenciaVoter extends Voter
{
    public const string DETALLE_DE_RESIDENCIA = 'detalle_residencia';
    public const string ACTUALIZAR_INFO_PERSONAL_RESIDENTE = 'actualizar_info_personal_residente';
    public const string DESCARGAR_REFERENCIA_BANCARIA = 'descargar_referencia_bancaria';
    public const string CORRECCION_DE_PAGO_FOFOE = 'correccion_de_pago_fofoe';
    public const string CARGAR_COMPROBANTE_DE_PAGO = 'cargar_comprobante_de_pago';
    public const string DESCARGAR_COMPROBANTE_DE_PAGO = 'descargar_comprobante_de_pago';

    private const array ATTRIBUTES = [
        self::DETALLE_DE_RESIDENCIA,
        self::ACTUALIZAR_INFO_PERSONAL_RESIDENTE,
        self::DESCARGAR_REFERENCIA_BANCARIA,
        self::CORRECCION_DE_PAGO_FOFOE,
        self::CARGAR_COMPROBANTE_DE_PAGO,
        self::DESCARGAR_COMPROBANTE_DE_PAGO,
    ];

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
    ) {}

    public function supportsAttribute(string $attribute): bool
    {
        return in_array($attribute, self::ATTRIBUTES, true);
    }

    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, Residencia::class, true);
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $this->supportsAttribute($attribute) && $subject instanceof Residencia;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof Usuario) {
            $vote?->addReason('The user is not authenticated.');
            return false;
        }

        if (!$this->accessDecisionManager->decide($token, ['ROLE_ALUMNO_POSGRADO'])) {
            $vote?->addReason('The user does not have ROLE_ALUMNO_POSGRADO.');
            return false;
        }

        $isOwner = $subject->getResidente()?->getUsuario()?->getId() === $user->getId();

        if (!$isOwner) {
            $vote?->addReason('The residencia does not belong to the current user.');
        }

        return $isOwner;
    }
}
