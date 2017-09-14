<?php

namespace Xe\Xecd\Client\Rates\Serializer;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Serializer;
use Xe\Framework\Client\BaseClient\Serializer\Encoder\QueryEncoder;
use Xe\Xecd\Client\Rates\Serializer\NameConverter\AccountNameConverter;
use Xe\Xecd\Client\Rates\Serializer\NameConverter\ConversionNameConverter;
use Xe\Xecd\Client\Rates\Serializer\NameConverter\ConversionsNameConverter;
use Xe\Xecd\Client\Rates\Serializer\NameConverter\CurrencyNameConverter;
use Xe\Xecd\Client\Rates\Serializer\NameConverter\MonthlyAverageConversionNameConverter;
use Xe\Xecd\Client\Rates\Serializer\NameConverter\XecdRatesExceptionNameConverter;
use Xe\Xecd\Client\Rates\Serializer\Normalizer\AccountNormalizer;
use Xe\Xecd\Client\Rates\Serializer\Normalizer\ConversionNormalizer;
use Xe\Xecd\Client\Rates\Serializer\Normalizer\ConversionsNormalizer;
use Xe\Xecd\Client\Rates\Serializer\Normalizer\CurrenciesNormalizer;
use Xe\Xecd\Client\Rates\Serializer\Normalizer\CurrencyNormalizer;
use Xe\Xecd\Client\Rates\Serializer\Normalizer\MonthlyAverageConversionNormalizer;
use Xe\Xecd\Client\Rates\Serializer\Normalizer\MonthlyAverageConversionsNormalizer;
use Xe\Xecd\Client\Rates\Serializer\Normalizer\XecdRatesExceptionNormalizer;

class XecdRatesSerializer extends Serializer
{
    public function __construct(array $normalizers = [], array $encoders = [])
    {
        parent::__construct(array_merge($normalizers, [
            new CurrencyNormalizer(null, new CurrencyNameConverter()),
            new CurrenciesNormalizer(),
            new AccountNormalizer(null, new AccountNameConverter()),
            new MonthlyAverageConversionsNormalizer(null, new ConversionsNameConverter()),
            new MonthlyAverageConversionNormalizer(null, new MonthlyAverageConversionNameConverter()),
            new ConversionNormalizer(null, new ConversionNameConverter()),
            new ConversionsNormalizer(null, new ConversionsNameConverter()),
            new XecdRatesExceptionNormalizer(null, new XecdRatesExceptionNameConverter()),
            new DateTimeNormalizer(),
            new ArrayDenormalizer(),
        ]), array_merge($encoders, [
            new JsonEncoder(),
            new QueryEncoder(),
        ]));
    }
}
