<?php

namespace Xe\Xecd\Client\Rates\Serializer\Normalizer;

use Xe\Xecd\Component\Rates\Core\Entity\MonthlyAverageConversion;

class MonthlyAverageConversionNormalizer extends ConversionNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type == MonthlyAverageConversion::class || is_subclass_of($type, MonthlyAverageConversion::class);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (is_array($data)) {
            if (array_key_exists('year', $data) && array_key_exists('month', $data)) {
                $data['timestamp'] = "{$data['year']}-{$data['month']}-01T00:00:00Z";
            }

            if (array_key_exists('monthlyAverage', $data)) {
                $data['mid'] = $data['monthlyAverage'];
                unset($data['monthlyAverage']);
            }

            if (array_key_exists('monthlyAverageInverse', $data)) {
                $data['inverse'] = $data['monthlyAverageInverse'];
                unset($data['monthlyAverageInverse']);
            }
        }

        return parent::denormalize($data, $class, $format, $context);
    }
}
