<?php

namespace Xe\Xecd\Client\Rates\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Xe\Xecd\Component\Rates\Core\Entity\Account;

class AccountNormalizer extends ObjectNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type == Account::class || is_subclass_of($type, Account::class);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (is_array($data)) {
            if (array_key_exists('service_start_timestamp', $data)) {
                $data['service_start_timestamp'] = $this->serializer->denormalize($data['service_start_timestamp'], \DateTime::class, $format, $context);
            }

            if (array_key_exists('package_limit_reset', $data)) {
                $data['package_limit_reset'] = $this->serializer->denormalize($data['package_limit_reset'], \DateTime::class, $format, $context);
            }
        }

        return parent::denormalize($data, $class, $format, $context);
    }
}
