<?php

namespace Xe\Xecd\Client\Rates\Serializer\Normalizer;

use Xe\Xecd\Component\Rates\Core\Entity\MonthlyAverageConversion;
use Xe\Xecd\Component\Rates\Core\Entity\MonthlyAverageConversions;

class MonthlyAverageConversionsNormalizer extends ConversionsNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type == MonthlyAverageConversions::class || is_subclass_of($type, MonthlyAverageConversions::class);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (is_array($data)) {
            if (array_key_exists('year', $data)) {
                $data['timestamp'] = "{$data['year']}-01-01T00:00:00Z";
            }
        }

        return parent::denormalize($data, $class, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConversionClass()
    {
        return MonthlyAverageConversion::class;
    }
}
