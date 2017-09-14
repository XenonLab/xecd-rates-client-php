<?php

namespace Xe\Xecd\Client\Rates\Serializer\NameConverter;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class XecdRatesExceptionNameConverter implements NameConverterInterface
{
    const PROPERTY_NAME_NORMALIZE_MAPPING = [
        'documentation' => 'documentation_url',
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
