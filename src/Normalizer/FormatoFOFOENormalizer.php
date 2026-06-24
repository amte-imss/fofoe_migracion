<?php

namespace App\Normalizer;

use App\Repository\IE\SeleccionarFormaPago\ListaCamposClinicosAutorizados\CampoClinico;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class FormatoFOFOENormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly ObjectNormalizer $normalizer,
        private readonly RouterInterface $router,
    ) {}

    public function normalize(mixed $camposClinico, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($camposClinico, $format, $context);

        if ($data['enlaceCalculoCuotas'] === '') {
            return $data;
        }

        $data['enlaceCalculoCuotas'] = $this->router->generate(
            'campo_clinico.formato_fofoe.download',
            ['campo_clinico_id' => $camposClinico->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

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
