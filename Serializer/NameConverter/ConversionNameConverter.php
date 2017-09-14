<?php

namespace Xe\Xecd\Client\Rates\Serializer\NameConverter;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class ConversionNameConverter implements NameConverterInterface
{
    const PROPERTY_NAME_NORMALIZE_MAPPING = [
        'fromCurrency' => 'from',
        'toCurrency' => 'quotecurrency',
        'fromAmount' => 'amount',
        'toAmount' => 'mid',
        'date' => 'timestamp',
    ];

    /**
     * {@inheritdoc}
     */
    public function normalize($propertyName)
    {
        $propertyMap = self::PROPERTY_NAME_NORMALIZE_MAPPING;
        $propertyName = isset($propertyMap[$propertyName]) ? $propertyMap[$propertyName] : $propertyName;

        return $propertyName;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($propertyName)
    {
        $propertyMap = array_flip(self::PROPERTY_NAME_NORMALIZE_MAPPING);
        $propertyName = isset($propertyMap[$propertyName]) ? $propertyMap[$propertyName] : $propertyName;

        return $propertyName;
    }
}
