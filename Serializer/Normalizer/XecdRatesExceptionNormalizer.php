<?php

namespace Xe\Xecd\Client\Rates\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Xe\Xecd\Client\Rates\Exception\XecdRatesException;

class XecdRatesExceptionNormalizer extends ObjectNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type == XecdRatesException::class || is_subclass_of($type, XecdRatesException::class);
    }
}
