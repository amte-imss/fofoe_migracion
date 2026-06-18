<?php

namespace App\Normalizer;

use App\DTO\IE\PerfilInstitucionDTO;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CedulaIdentificacionNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly string $institucionDir,
    ) {}

    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($data, $format, $context);

        if ($data['cedulaIdentificacion'] === null) {
            return $data;
        }

        $data['cedulaIdentificacion'] = sprintf('%s/%s/%s',
            $this->institucionDir,
            $data->getId(),
            $data['cedulaIdentificacion']
        );

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof PerfilInstitucionDTO;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [PerfilInstitucionDTO::class => true];
    }
}
