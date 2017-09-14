<?php

namespace Xe\Xecd\Client\Rates\Serializer\NameConverter;

class MonthlyAverageConversionNameConverter extends ConversionNameConverter
{
    const PROPERTY_NAME_NORMALIZE_MAPPING = [
        'days' => 'daysInMonth',
    ];

    /**
     * {@inheritdoc}
     */
    public function normalize($propertyName)
    {
        $propertyMap = self::PROPERTY_NAME_NORMALIZE_MAPPING;
        $propertyName = isset($propertyMap[$propertyName]) ? $propertyMap[$propertyName] : $propertyName;

        return parent::normalize($propertyName);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($propertyName)
    {
        $propertyMap = array_flip(self::PROPERTY_NAME_NORMALIZE_MAPPING);
        $propertyName = isset($propertyMap[$propertyName]) ? $propertyMap[$propertyName] : $propertyName;

        return parent::denormalize($propertyName);
    }
}
