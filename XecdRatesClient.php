<?php

namespace Xe\Xecd\Client\Rates;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Xe\Framework\Client\BaseClient\AbstractClient;
use Xe\Xecd\Client\Rates\Annotations\Deserializable;
use Xe\Xecd\Client\Rates\Annotations\Serializable;
use Xe\Xecd\Component\Rates\Core\Entity\Account;
use Xe\Xecd\Component\Rates\Core\Entity\Currencies;
use Xe\Xecd\Component\Rates\Core\Entity\Currency;
use Xe\Xecd\Component\Rates\Core\Entity\Interval;
use Xe\Xecd\Component\Rates\Core\Entity\ManyToOneConversions;
use Xe\Xecd\Component\Rates\Core\Entity\MonthlyAverageConversions;
use Xe\Xecd\Component\Rates\Core\Entity\OneToManyConversions;

class XecdRatesClient extends AbstractClient
{
    /**
     * @var Request
     */
    protected $accountInfoRequest;

    /**
     * @var Request
     */
    protected $currenciesRequest;

    /**
     * @var Request
     */
    protected $convertFromRequest;

    /**
     * @var Request
     */
    protected $convertToRequest;

    /**
     * @var Request
     */
    protected $historicRateRequest;

    /**
     * @var Request
     */
    protected $historicRatePeriodRequest;

    /**
     * @var Request
     */
    protected $monthlyAverageRequest;

    public function __construct(ClientInterface $client)
    {
        parent::__construct($client);

        $this->accountInfoRequest = new Request('GET', 'account_info');
        $this->currenciesRequest = new Request('GET', 'currencies');
        $this->convertFromRequest = new Request('GET', 'convert_from');
        $this->convertToRequest = new Request('GET', 'convert_to');
        $this->historicRateRequest = new Request('GET', 'historic_rate');
        $this->historicRatePeriodRequest = new Request('GET', 'historic_rate/period');
        $this->monthlyAverageRequest = new Request('GET', 'monthly_average');
    }

    /**
     * Factory method.
     *
     * @param string $apiAccountId Your account id
     * @param string $apiKey       Your API key
     * @param array  $options      Guzzle request options
     *
     * @return \Xe\Xecd\Client\Rates\XecdRatesClient
     */
    public static function create($apiAccountId, $apiKey, array $options = [])
    {
        $options['handler'] = isset($options['handler']) ? $options['handler'] : HandlerStack::create();
        $options['handler']->unshift(Middleware::mapRequest());

        $client = new Client(array_merge([
            RequestOptions::TIMEOUT => 15,
            RequestOptions::CONNECT_TIMEOUT => 15,
            'base_uri' => 'https://xecdapi.xe.com',
            RequestOptions::AUTH => [
                $apiAccountId,
                $apiKey,
            ],
        ], $options));

        return new static($client);
    }

    /**
     * {@inheritdoc}
     *
     * @Serializable
     */
    protected function send(RequestInterface $request, array $options = [])
    {
        return parent::send($request, $options);
    }

    /**
     * Request account info associated with your api key and secret.
     *
     * @param array $options Guzzle request options
     *
     * @return \Xe\Framework\Client\BaseClient\Psr7\DeserializedResponse
     *
     * @Deserializable(type=Account::class)
     */
    public function accountInfo(array $options = [])
    {
        return $this->send($this->accountInfoRequest, $options);
    }

    /**
     * Request currency information.
     *
     * @param string     $language ISO 639-1 language code specifying the language to request currency information in
     * @param Currencies $iso      ISO 4217 currency codes to request currency information for
     * @param bool       $obsolete true to request obsolete currencies, false otherwise
     * @param array      $options  Guzzle request options
     *
     * @return \Xe\Framework\Client\BaseClient\Psr7\DeserializedResponse
     *
     * @Deserializable(type=Currencies::class)
     */
    public function currencies($language = 'en', Currencies $iso = null, $obsolete = false, array $options = [])
    {
        return $this->send($this->currenciesRequest, array_merge($options, [
            RequestOptions::QUERY => [
                'language' => $language,
                'iso' => $iso ?: Currencies::wildcard(),
                'obsolete' => $obsolete ? 'true' : 'false',
            ],
        ]));
    }

    /**
     * Convert from a single currency to multiple currencies.
     *
     * @param Currency   $from     ISO 4217 currency code to convert from
     * @param Currencies $to       ISO 4217 currency codes to convert to
     * @param int        $amount   Amount to convert
     * @param bool       $obsolete true to request rates for obsolete currencies, false otherwise
     * @param bool       $inverse  true to request inverse rates as well, false otherwise
     * @param array      $options  Guzzle request options
     *
     * @return \Xe\Framework\Client\BaseClient\Psr7\DeserializedResponse
     *
     * @Deserializable(type=OneToManyConversions::class)
     */
    public function convertFrom(Currency $from = null, Currencies $to = null, $amount = 1, $obsolete = false, $inverse = false, array $options = [])
    {
        return $this->send($this->convertFromRequest, array_merge($options, [
            RequestOptions::QUERY => [
                'from' => $from ?: new Currency('USD'),
                'to' => $to ?: Currencies::wildcard(),
                'amount' => $amount,
                'obsolete' => $obsolete ? 'true' : 'false',
                'inverse' => $inverse ? 'true' : 'false',
            ],
        ]));
    }

    /**
     * Convert to a single currency from multiple currencies.
     *
     * @param Currency   $to       ISO 4217 currency code to convert to
     * @param Currencies $from     ISO 4217 currency codes to convert from
     * @param int        $amount   Amount to convert
     * @param bool       $obsolete true to request rates for obsolete currencies, false otherwise
     * @param bool       $inverse  true to request inverse rates as well, false otherwise
     * @param array      $options  Guzzle request options
     *
     * @return \Xe\Framework\Client\BaseClient\Psr7\DeserializedResponse
     *
     * @Deserializable(type=ManyToOneConversions::class)
     */
    public function convertTo(Currency $to = null, Currencies $from = null, $amount = 1, $obsolete = false, $inverse = false, array $options = [])
    {
        return $this->send($this->convertToRequest, array_merge($options, [
            RequestOptions::QUERY => [
                'to' => $to ?: new Currency('USD'),
                'from' => $from ?: Currencies::wildcard(),
                'amount' => $amount,
                'obsolete' => $obsolete ? 'true' : 'false',
                'inverse' => $inverse ? 'true' : 'false',
            ],
        ]));
    }

    /**
     * Request historic rates from a single currency to multiple currencies for a specific date and time.
     *
     * @param \DateTime  $dateTime Date and time to request rates for. The time portion is only applicable to LIVE packages and for the last 24 hours
     * @param Currency   $from     ISO 4217 currency code to convert from
     * @param Currencies $to       ISO 4217 currency codes to convert to
     * @param int        $amount   Amount to convert
     * @param bool       $obsolete true to request rates for obsolete currencies, false otherwise
     * @param bool       $inverse  true to request inverse rates as well, false otherwise
     * @param array      $options  Guzzle request options
     *
     * @return \Xe\Framework\Client\BaseClient\Psr7\DeserializedResponse
     *
     * @Deserializable(type=OneToManyConversions::class)
     */
    public function historicRate(\DateTime $dateTime, Currency $from = null, Currencies $to = null, $amount = 1, $obsolete = false, $inverse = false, array $options = [])
    {
        return $this->send($this->historicRateRequest, array_merge($options, [
            RequestOptions::QUERY => [
                'date' => $dateTime->format('Y-m-d'),
                'time' => $dateTime->format('H:i'),
                'from' => $from ?: new Currency('USD'),
                'to' => $to ?: Currencies::wildcard(),
                'amount' => $amount,
                'obsolete' => $obsolete ? 'true' : 'false',
                'inverse' => $inverse ? 'true' : 'false',
            ],
        ]));
    }

    /**
     * Request historic rates from a single currency to multiple currencies for a time period.
     *
     * @param \DateTime|null $startDateTime Date and time to start requesting rates for. The time portion is only applicable to LIVE packages. Defaults to the current date and time
     * @param \DateTime|null $endDateTime   Date and time to end requesting rates for. The time portion is only applicable to LIVE packages. Defaults to the current date and time
     * @param Currency       $from          ISO 4217 currency code to convert from
     * @param Currencies     $to            ISO 4217 currency codes to convert to
     * @param int            $amount        Amount to convert
     * @param string         $interval      Either "daily" or "live". Only applicable to LIVE packages
     * @param bool           $obsolete      true to request rates for obsolete currencies, false otherwise
     * @param bool           $inverse       true to request inverse rates as well, false otherwise
     * @param int            $page          Page number of results to return
     * @param int            $perPage       Number of results per page. The maximum results per page is 100
     * @param array          $options       Guzzle request options
     *
     * @return \Xe\Framework\Client\BaseClient\Psr7\DeserializedResponse
     *
     * @Deserializable(type=OneToManyConversions::class)
     */
    public function historicRatePeriod(\DateTime $startDateTime = null, \DateTime $endDateTime = null, Currency $from = null, Currencies $to = null, $amount = 1, $interval = Interval::DAILY, $obsolete = false, $inverse = false, $page = 1, $perPage = 30, array $options = [])
    {
        return $this->send($this->historicRatePeriodRequest, array_merge($options, [
            RequestOptions::QUERY => [
                'start_timestamp' => isset($startDateTime) ? $startDateTime->format(\DateTime::ISO8601) : null,
                'end_timestamp' => isset($endDateTime) ? $endDateTime->format(\DateTime::ISO8601) : null,
                'from' => $from ?: new Currency('USD'),
                'to' => $to ?: Currencies::fromArray('USD'),
                'amount' => $amount,
                'interval' => $interval,
                'obsolete' => $obsolete ? 'true' : 'false',
                'inverse' => $inverse ? 'true' : 'false',
                'page' => $page,
                'per_page' => $perPage,
            ],
        ]));
    }

    /**
     * Request monthly averages from a single currency to multiple currencies.
     *
     * @param int|null   $year     Year to request averages for. Defaults to the current year
     * @param int|null   $month    Month to request averages for. Defaults to all months
     * @param Currency   $from     ISO 4217 currency code to convert from
     * @param Currencies $to       ISO 4217 currency codes to convert to
     * @param int        $amount   Amount to convert
     * @param bool       $obsolete true to request rates for obsolete currencies, false otherwise
     * @param bool       $inverse  true to request inverse rates as well, false otherwise
     * @param array      $options  Guzzle request options
     *
     * @return \Xe\Framework\Client\BaseClient\Psr7\DeserializedResponse
     *
     * @Deserializable(type=MonthlyAverageConversions::class)
     */
    public function monthlyAverage($year = null, $month = null, Currency $from = null, Currencies $to = null, $amount = 1, $obsolete = false, $inverse = false, array $options = [])
    {
        return $this->send($this->monthlyAverageRequest, array_merge($options, [
            RequestOptions::QUERY => [
                'from' => $from ?: new Currency('USD'),
                'to' => $to ?: Currencies::wildcard(),
                'amount' => $amount,
                'year' => $year,
                'month' => $month,
                'obsolete' => $obsolete ? 'true' : 'false',
                'inverse' => $inverse ? 'true' : 'false',
            ],
        ]));
    }
}
