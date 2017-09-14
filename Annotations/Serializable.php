<?php

namespace Xe\Xecd\Client\Rates\Annotations;

use Doctrine\Common\Annotations\Annotation\Target;
use Symfony\Component\Serializer\Serializer;
use Xe\Framework\Client\BaseClient\Annotations\AbstractSerializable;
use Xe\Xecd\Client\Rates\Serializer\XecdRatesSerializer;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Serializable extends AbstractSerializable
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $values, Serializer $serializer = null)
    {
        $serializer = $serializer ?: new XecdRatesSerializer();
        parent::__construct($values, $serializer);
    }
}
