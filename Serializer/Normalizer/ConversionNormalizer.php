<?php

namespace Xe\Xecd\Client\Rates\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Xe\Xecd\Component\Rates\Core\Entity\Conversion;

class ConversionNormalizer extends ObjectNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type == Conversion::class || is_subclass_of($type, Conversion::class);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (is_array($data)) {
            if (array_key_exists('to', $data)) {
                // Inversely map from and to currencies and amounts for many to one requests.
                list($data['from'], $data['quotecurrency'], $data['mid'], $data['amount']) = [
                    $data['quotecurrency'],
                    $data['to'],
                    $data['amount'],
                    $data['mid'],
                ];
                unset($data['to']);
            }

            if (array_key_exists('inverse', $data)) {
                $data['inverse'] = array_merge($data, [
                    'from' => $data['quotecurrency'],
                    'quotecurrency' => $data['from'],
                    'amount' => $data['mid'],
                    'mid' => $data['amount'],
                ]);

                // Prevent infinite recursion.
                unset($data['inverse']['inverse']);

                $data['inverse'] = $this->serializer->denormalize($data['inverse'], Conversion::class, $format, $context);
            }
        }

        return parent::denormalize($data, $class, $format, $context);
    }
}
