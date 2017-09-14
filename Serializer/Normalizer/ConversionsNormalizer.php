<?php

namespace Xe\Xecd\Client\Rates\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Xe\Xecd\Component\Rates\Core\Entity\Conversion;
use Xe\Xecd\Component\Rates\Core\Entity\Conversions;
use Xe\Xecd\Component\Rates\Core\Entity\Currency;
use Xe\Xecd\Component\Rates\Core\Entity\OneToManyConversions;

class ConversionsNormalizer extends ObjectNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type == Conversions::class || is_subclass_of($type, Conversions::class);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (is_array($data)) {
            $conversionsProperty = $class == OneToManyConversions::class || is_subclass_of($class, OneToManyConversions::class) ? 'to' : 'from';
            $baseCurrencyProperty = $class == OneToManyConversions::class || is_subclass_of($class, OneToManyConversions::class) ? 'from' : 'to';

            if (array_key_exists($conversionsProperty, $data)) {
                if (array_keys($data[$conversionsProperty]) !== range(0, count($data[$conversionsProperty]) - 1)) {
                    // Conversions are indexed by currency.
                    foreach ($data[$conversionsProperty] as $currency => $currencyConversions) {
                        // Normalize and flatten to a numerically indexed array.
                        $data[$conversionsProperty] = array_merge($data[$conversionsProperty], $this->normalizeConversions($currencyConversions, $data, ['quotecurrency' => $currency]));
                        unset($data[$conversionsProperty][$currency]);
                    }
                } else {
                    // Conversions are numerically indexed.
                    $data[$conversionsProperty] = $this->normalizeConversions($data[$conversionsProperty], $data);
                }

                $data[$conversionsProperty] = $this->serializer->denormalize($data[$conversionsProperty], $this->getConversionClass().'[]', $format, $context);

                // Index conversions by currency and timestamp.
                $data[$conversionsProperty] = array_reduce($data[$conversionsProperty], function ($conversions, Conversion $conversion) use ($class) {
                    $currency = $class == OneToManyConversions::class || is_subclass_of($class, OneToManyConversions::class) ? $conversion->getToCurrency()->getIso() : $conversion->getFromCurrency()->getIso();
                    $conversions[$currency][$conversion->getDate()->format(\DateTime::ISO8601)] = $conversion;

                    return $conversions;
                }, []);
            }

            if (array_key_exists($baseCurrencyProperty, $data)) {
                $data[$baseCurrencyProperty] = $this->serializer->denormalize($data[$baseCurrencyProperty], Currency::class, $format, $context);
            }

            if (array_key_exists('timestamp', $data)) {
                $data['timestamp'] = $this->serializer->denormalize($data['timestamp'], \DateTime::class, $format, $context);
            }
        }

        return parent::denormalize($data, $class, $format, $context);
    }

    /**
     * Merges additional properties into each conversion.
     *
     * @param array $conversions Original conversions array
     * @param array $parent      Parent of conversions that contains global properties to all conversions
     * @param array $extra       Additional attributes to inject into each conversion
     *
     * @return array
     */
    protected function normalizeConversions($conversions, $parent, $extra = [])
    {
        foreach ($conversions as $key => $conversion) {
            // Merge properties from parent into child.
            $conversions[$key] = array_merge(array_filter($parent, function ($value) {
                return !is_array($value);
            }), $conversion, $extra);
        }

        return $conversions;
    }

    /**
     * @return string
     */
    protected function getConversionClass()
    {
        return Conversion::class;
    }
}
