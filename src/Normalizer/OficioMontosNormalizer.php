<?php

namespace App\Normalizer;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

final class OficioMontosNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly ObjectNormalizer $normalizer,
        private readonly string $institucionDocumentsDir,
        private readonly TokenStorageInterface $tokenStorage,
    ) {}

    public function normalize(mixed $oficioMontosFile, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($oficioMontosFile, $format, $context);

        if (empty($data['urlArchivo'])) {
            return $data;
        }

        $data['urlArchivo'] = sprintf('%s/%s/%s',
            $this->institucionDocumentsDir,
            $this->tokenStorage->getToken()->getUser()->getInstitucion()->getId(),
            $data['urlArchivo']
        );

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof OficioMontosFileInterfaces;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [OficioMontosFileInterfaces::class => true];
    }
}
