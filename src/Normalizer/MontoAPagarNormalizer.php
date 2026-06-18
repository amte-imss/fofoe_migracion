<?php

namespace App\Normalizer;

use App\Calculator\CampoClinicoCalculatorInterface;
use App\Entity\CampoClinico;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class MontoAPagarNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly ObjectNormalizer $normalizer,
        private readonly CampoClinicoCalculatorInterface $calculator,
    ) {}

    public function normalize(mixed $campoClinico, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($campoClinico, $format, $context);

        if (!array_key_exists('monto', $data) || $data['monto'] >= 0 || !$campoClinico->getMontoCarrera()) {
            return $data;
        }

        $data['monto'] = $this->calculator->getMontoAPagar($campoClinico, $campoClinico->getSolicitud());

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof CampoClinico;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [CampoClinico::class => true];
    }
}
