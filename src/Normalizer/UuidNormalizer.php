<?php

namespace App\Normalizer;

use App\Helpers\Uuid;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 *  Converts Uuid to string and string to Uuid Object
 */
class UuidNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): Uuid
    {

        return Uuid::fromString($data);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        return is_string($data) && is_a($type, Uuid::class, true) && Uuid::isValid($data);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function normalize(mixed $object, string $format = null, array $context = []): string
    {
        if($object instanceof Uuid) {
            return $object->__toString();
        }
    }

    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        return $data instanceof Uuid;
    }
}