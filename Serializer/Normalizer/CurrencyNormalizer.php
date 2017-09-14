<?php

namespace Xe\Xecd\Client\Rates\Serializer\Normalizer;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Xe\Xecd\Component\Rates\Core\Entity\Currency;

class CurrencyNormalizer extends ObjectNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && $data instanceof Currency;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$object instanceof Currency) {
            throw new InvalidArgumentException('The object must be an instance of "'.Currency::class.'".');
        }

        return $object->getIso();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type == Currency::class || is_subclass_of($type, Currency::class);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (is_array($data)) {
            if (array_key_exists('superceded_by', $data)) {
                $data['superceded_by'] = $this->serializer->denormalize([
                    'iso' => $data['superceded_by'],
                ], Currency::class, $format, $context);
            }
        } elseif (is_string($data)) {
            $data = [
                'iso' => $data,
            ];
        }

        return parent::denormalize($data, $class, $format, $context);
    }
}
