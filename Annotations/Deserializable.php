<?php

namespace Xe\Xecd\Client\Rates\Annotations;

use Doctrine\Common\Annotations\Annotation\Target;
use Symfony\Component\Serializer\Serializer;
use Xe\Framework\Client\BaseClient\Annotations\AbstractDeserializable;
use Xe\Xecd\Client\Rates\Exception\XecdRatesException;
use Xe\Xecd\Client\Rates\Serializer\XecdRatesSerializer;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Deserializable extends AbstractDeserializable
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $values, Serializer $serializer = null)
    {
        $values['exception'] = isset($values['exception']) ? $values['exception'] : XecdRatesException::class;
        $serializer = $serializer ?: new XecdRatesSerializer();

        parent::__construct($values, $serializer);
    }
}
