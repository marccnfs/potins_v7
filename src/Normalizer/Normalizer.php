<?php


namespace App\Normalizer;


use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class Normalizer implements NormalizerInterface
{
    abstract public function normalize($data, string $format = null, array $context = []): array;

    abstract public function supportsNormalization($data, string $format = null,array $context = []): bool;

}
