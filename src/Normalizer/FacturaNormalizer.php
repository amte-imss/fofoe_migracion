<?php

namespace App\Normalizer;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

final class FacturaNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly ObjectNormalizer $normalizer,
        private readonly string $institucionDocumentsDir,
        private readonly TokenStorageInterface $tokenStorage,
    ) {}

    public function normalize(mixed $facturaFile, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($facturaFile, $format, $context);

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
        return $data instanceof FacturaFileInterface;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [FacturaFileInterface::class => true];
    }
}
