<?php

namespace Xe\Xecd\Client\Rates\Serializer\Normalizer;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Xe\Xecd\Component\Rates\Core\Entity\Currencies;
use Xe\Xecd\Component\Rates\Core\Entity\Currency;

class CurrenciesNormalizer extends ObjectNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && $data instanceof Currencies;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$object instanceof Currencies) {
            throw new InvalidArgumentException('The object must be an instance of "'.Currencies::class.'".');
        }

        return implode(',', array_reduce($object->getCurrencies(), function ($currencies, Currency $currency) {
            $currencies[] = $currency->getIso();

            return $currencies;
        }, []));
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type == Currencies::class || is_subclass_of($type, Currencies::class);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (is_array($data)) {
            if (array_key_exists('currencies', $data)) {
                $data['currencies'] = $this->serializer->denormalize($data['currencies'], Currency::class.'[]', $format, $context);

                // Index currencies by iso.
                $data['currencies'] = array_reduce($data['currencies'], function ($currencies, Currency $currency) {
                    $currencies[$currency->getIso()] = $currency;

                    return $currencies;
                }, []);
            }
        }

        return parent::denormalize($data, $class, $format, $context);
    }
}
