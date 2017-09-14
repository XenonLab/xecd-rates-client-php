<?php

namespace Xe\Xecd\Client\Rates\Tests\Unit;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Xe\Xecd\Client\Rates\Header;
use Xe\Xecd\Client\Rates\XecdRatesClient;
use Xe\Xecd\Client\Rates\XecdRatesClientAspectKernel;
use Xe\Xecd\Component\Rates\Core\Entity\Conversions;
use Xe\Xecd\Component\Rates\Core\Entity\Currencies;
use Xe\Xecd\Component\Rates\Core\Entity\Currency;
use Xe\Xecd\Component\Rates\Core\Entity\Interval;
use Xe\Xecd\Component\Rates\Core\Entity\LegalEntityInterface;
use Xe\Xecd\Component\Rates\Core\Entity\MonthlyAverageConversion;
use Xe\Xecd\Component\Rates\Core\Entity\OneToManyConversions;

class XecdRatesClientTest extends TestCase
{
    /**
     * @var \GuzzleHttp\Handler\MockHandler
     */
    protected $xecdRatesClientGuzzleMockHandlerStack;

    /**
     * @var \Xe\Xecd\Client\Rates\XecdRatesClient
     */
    protected $xecdRatesClient;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        XecdRatesClientAspectKernel::getInstance()->init();

        $this->xecdRatesClientGuzzleMockHandlerStack = new MockHandler();
        $xecdRatesClientGuzzleHandlerStack = $this->createHandlerStack($this->xecdRatesClientGuzzleMockHandlerStack);

        $this->xecdRatesClient = XecdRatesClient::create(getenv('XECD_RATES_API_ACCOUNT_ID'), getenv('XECD_RATES_API_KEY'), [
            'handler' => $xecdRatesClientGuzzleHandlerStack,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->xecdRatesClient = null;
        $this->xecdRatesClientGuzzleMockHandlerStack = null;
    }

    /**
     * Create the handler stack for Guzzle to use during testing.
     *
     * @param callable $handler
     *
     * @return \GuzzleHttp\HandlerStack
     */
    protected function createHandlerStack(callable $handler)
    {
        return HandlerStack::create($handler);
    }

    /**
     * Assert response contains the required headers.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    protected function assertResponseHeadersExists(ResponseInterface $response)
    {
        $this->assertNotEmpty($response->getHeader(Header::X_RATELIMIT_LIMIT));
        $this->assertNotEmpty($response->getHeader(Header::X_RATELIMIT_LIMIT)[0]);
        $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_NUMERIC, $response->getHeader(Header::X_RATELIMIT_LIMIT)[0]);
        $this->assertNotEmpty($response->getHeader(Header::X_RATELIMIT_REMAINING));
        $this->assertNotEmpty($response->getHeader(Header::X_RATELIMIT_REMAINING)[0]);
        $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_NUMERIC, $response->getHeader(Header::X_RATELIMIT_REMAINING)[0]);
        $this->assertNotEmpty($response->getHeader(Header::X_RATELIMIT_RESET));
        $this->assertNotEmpty($response->getHeader(Header::X_RATELIMIT_RESET)[0]);
        $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_NUMERIC, $response->getHeader(Header::X_RATELIMIT_RESET)[0]);
    }

    /**
     * Assert response has legal properties set.
     *
     * @param \Xe\Xecd\Component\Rates\Core\Entity\LegalEntityInterface $legalEntity
     */
    protected function assertLegalAttributesNotEmpty(LegalEntityInterface $legalEntity)
    {
        $this->assertNotEmpty($legalEntity->getTerms());
        $this->assertNotEmpty($legalEntity->getPrivacy());
    }

    /**
     * Assert conversions response is valid.
     *
     * @param \Xe\Xecd\Component\Rates\Core\Entity\Conversions $conversions
     */
    protected function assertConversionsValid(Conversions $conversions)
    {
        $this->assertLegalAttributesNotEmpty($conversions);
        $this->assertNotEmpty($conversions->getBaseCurrency()->getIso());
        $this->assertNotEmpty($conversions->getAmount());
        $this->assertNotEmpty($conversions->getConversions());

        if (!empty($conversions->getDate())) {
            $this->assertLessThan((new \DateTime()), $conversions->getDate());
        }

        foreach ($conversions->getConversions() as $currency => $currencyConversions) {
            $this->assertNotEmpty($currency);
            $this->assertEquals(3, strlen($currency));
            $this->assertNotEmpty($currencyConversions);

            foreach ($currencyConversions as $timestamp => $conversion) {
                $this->assertNotEmpty($conversion->getDate());
                $this->assertEquals($conversion->getDate()->format(\DateTime::ISO8601), $timestamp);

                if ($conversions instanceof OneToManyConversions) {
                    $this->assertEquals($currency, $conversion->getToCurrency()->getIso());
                } else {
                    $this->assertEquals($currency, $conversion->getFromCurrency()->getIso());
                }

                $this->assertEquals(3, strlen($conversion->getFromCurrency()->getIso()));
                $this->assertEquals(3, strlen($conversion->getToCurrency()->getIso()));
                $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_NUMERIC, $conversion->getFromAmount());
                $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_NUMERIC, $conversion->getToAmount());

                $this->assertNotEmpty($conversion->getInverse());
                $this->assertEquals($conversion->getDate(), $conversion->getInverse()->getDate());
                $this->assertEquals($conversion, $conversion->getInverse()->getInverse());
                $this->assertEquals($conversion->getFromCurrency(), $conversion->getInverse()->getToCurrency());
                $this->assertEquals($conversion->getToCurrency(), $conversion->getInverse()->getFromCurrency());
                $this->assertEquals($conversion->getFromAmount(), $conversion->getInverse()->getToAmount());
                $this->assertEquals($conversion->getToAmount(), $conversion->getInverse()->getFromAmount());

                if (!empty($conversions->getDate())) {
                    if ($conversion instanceof MonthlyAverageConversion) {
                        // Monthly average conversions increment the month number on dates of individual conversions.
                        $this->assertLessThanOrEqual($conversion->getDate(), $conversions->getDate());
                    } else {
                        // All other conversions use the same date as the collection.
                        $this->assertEquals($conversions->getDate(), $conversion->getDate());
                    }
                }
            }
        }
    }

    public function testAccountInfo()
    {
        $response = $this->xecdRatesClient->accountInfo();

        $this->assertResponseHeadersExists($response->getResponse());

        /** @var $accountInfo \Xe\Xecd\Component\Rates\Core\Entity\Account */
        $accountInfo = $response->getBody();

        $this->assertNotEmpty($accountInfo->getId());
        $this->assertNotEmpty($accountInfo->getOrganization());
        $this->assertNotEmpty($accountInfo->getPackage());
        $this->assertNotEmpty($accountInfo->getServiceStartDate());
        $this->assertLessThan((new \DateTime()), $accountInfo->getServiceStartDate());

        if (strpos($accountInfo->getPackage(), 'DAILY') !== false) {
            $this->assertNotEmpty($accountInfo->getPackageLimitDuration());
            $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_NUMERIC, $accountInfo->getPackageLimit());
            $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_NUMERIC, $accountInfo->getPackageLimitRemaining());
            $this->assertNotEmpty($accountInfo->getPackageLimitResetDate());
            $this->assertGreaterThan((new \DateTime()), $accountInfo->getPackageLimitResetDate());
        }
    }

    public function testCurrencies()
    {
        $response = $this->xecdRatesClient->currencies('en', Currencies::wildcard(), true);

        $this->assertResponseHeadersExists($response->getResponse());

        /** @var $currencies \Xe\Xecd\Component\Rates\Core\Entity\Currencies */
        $currencies = $response->getBody();

        $this->assertLegalAttributesNotEmpty($currencies);
        $this->assertNotEmpty($currencies->getCurrencies());

        foreach ($currencies->getCurrencies() as $iso => $currency) {
            $this->assertNotEmpty($iso);
            $this->assertNotEmpty($currency);
            $this->assertEquals($currency->getIso(), $iso);
            $this->assertEquals(3, strlen($currency->getIso()));
            $this->assertNotEmpty($currency->getName());
            $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_BOOL, $currency->isObsolete());

            if ($currency->isObsolete()) {
                $this->assertNotEmpty($currency->getSuccessor());
                $this->assertEquals(3, strlen($currency->getSuccessor()->getIso()));
            }
        }
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function convertDataProvider()
    {
        return [
            'Obsolete' => [true, false],
            'Inverse' => [false, true],
            'Obsolete and Inverse' => [true, true],
            'Neither' => [false, false],
        ];
    }

    /**
     * @dataProvider convertDataProvider
     */
    public function testConvertFrom($obsolete, $inverse)
    {
        $response = $this->xecdRatesClient->convertFrom(new Currency('CAD'), Currencies::wildcard(), 12345.67, $obsolete, $inverse);

        $this->assertResponseHeadersExists($response->getResponse());

        /** @var $conversions \Xe\Xecd\Component\Rates\Core\Entity\OneToManyConversions */
        $conversions = $response->getBody();

        $this->assertConversionsValid($conversions);
        $this->assertEquals('CAD', $conversions->getBaseCurrency()->getIso());
        $this->assertEquals(12345.67, $conversions->getAmount());
        $this->assertNotEmpty($conversions->getDate());
    }

    /**
     * @dataProvider convertDataProvider
     */
    public function testConvertTo($obsolete, $inverse)
    {
        $response = $this->xecdRatesClient->convertTo(new Currency('CAD'), Currencies::wildcard(), 12345.67, $obsolete, $inverse);

        $this->assertResponseHeadersExists($response->getResponse());

        /** @var $conversions \Xe\Xecd\Component\Rates\Core\Entity\ManyToOneConversions */
        $conversions = $response->getBody();

        $this->assertConversionsValid($conversions);
        $this->assertEquals('CAD', $conversions->getBaseCurrency()->getIso());
        $this->assertEquals(12345.67, $conversions->getAmount());
        $this->assertNotEmpty($conversions->getDate());
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function historicRateDataProvider()
    {
        $dateTimeGreaterThan24Hours = new \DateTime();
        $dateTimeGreaterThan24Hours->setDate(1999, 07, 19);
        $dateTimeGreaterThan24Hours->setTime(0, 0, 0, 0);

        $dateTimeLessThan24Hours = new \DateTime();
        $dateTimeLessThan24Hours->setTime($dateTimeLessThan24Hours->format('H'), 0, 0, 0);
        $dateTimeLessThan24Hours->sub(new \DateInterval('PT'.rand(0, 23).'H'));

        return [
            '> 1 Week ago' => [$dateTimeGreaterThan24Hours, 'Y-m-d'],
            '< 24 Hours ago' => [$dateTimeLessThan24Hours, \DateTime::ISO8601],
        ];
    }

    /**
     * @dataProvider historicRateDataProvider
     */
    public function testHistoricRate(\DateTime $dateTime, $format)
    {
        $response = $this->xecdRatesClient->historicRate($dateTime, new Currency('CAD'), Currencies::wildcard(), 12345.67);

        $this->assertResponseHeadersExists($response->getResponse());

        /** @var $conversions \Xe\Xecd\Component\Rates\Core\Entity\OneToManyConversions */
        $conversions = $response->getBody();

        $this->assertConversionsValid($conversions);
        $this->assertEquals('CAD', $conversions->getBaseCurrency()->getIso());
        $this->assertEquals(12345.67, $conversions->getAmount());
        $this->assertNotEmpty($conversions->getDate());
        $this->assertEquals($dateTime->format($format), $conversions->getDate()->format($format));
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function historicRatePeriodDataProvider()
    {
        return array_reduce((new \ReflectionClass(Interval::class))->getConstants(), function (array $data, $interval) {
            $data[$interval] = [$interval];

            return $data;
        }, []);
    }

    /**
     * @dataProvider historicRatePeriodDataProvider
     */
    public function testHistoricRatePeriod($interval)
    {
        $startDateTime = new \DateTime();
        $startDateTime->setDate(2015, 07, 19);
        $startDateTime->setTime(20, 0, 0, 0);
        $endDateTime = new \DateTime();
        $response = $this->xecdRatesClient->historicRatePeriod($startDateTime, $endDateTime, new Currency('CAD'), Currencies::fromArray(['USD', 'JPY', 'GBP']), 12345.67, $interval);

        $this->assertResponseHeadersExists($response->getResponse());

        /** @var $conversions \Xe\Xecd\Component\Rates\Core\Entity\OneToManyConversions */
        $conversions = $response->getBody();

        $this->assertConversionsValid($conversions);
        $this->assertEquals('CAD', $conversions->getBaseCurrency()->getIso());
        $this->assertEquals(12345.67, $conversions->getAmount());
        $this->assertEmpty($conversions->getDate());

        foreach ($conversions->getConversions() as $currency => $currencyConversions) {
            $this->assertEquals($startDateTime, reset($currencyConversions)->getDate());
        }
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function monthlyAverageDataProvider()
    {
        return [
            'year' => [2015, null],
            'month' => [2017, 7],
        ];
    }

    /**
     * @dataProvider monthlyAverageDataProvider
     */
    public function testMonthlyAverageYear($year, $month)
    {
        $response = $this->xecdRatesClient->monthlyAverage($year, $month, new Currency('CAD'), Currencies::wildcard(), 12345.67, true, true);

        $this->assertResponseHeadersExists($response->getResponse());

        /** @var $conversions \Xe\Xecd\Component\Rates\Core\Entity\MonthlyAverageConversions */
        $conversions = $response->getBody();

        $this->assertConversionsValid($conversions);
        $this->assertEquals('CAD', $conversions->getBaseCurrency()->getIso());
        $this->assertEquals(12345.67, $conversions->getAmount());
        $this->assertNotEmpty($conversions->getDate());

        $dateTime = new \DateTime();
        $dateTime->setDate($year, 1, 1);
        $dateTime->setTime(0, 0, 0, 0);
        $this->assertEquals($dateTime, $conversions->getDate());

        foreach ($conversions->getTo() as $currency => $monthlyAverageConversions) {
            foreach ($monthlyAverageConversions as $timestamp => $monthlyAverageConversion) {
                $dateTime->setDate($dateTime->format('Y'), $monthlyAverageConversion->getDate()->format('m'), $dateTime->format('d'));
                $this->assertEquals($dateTime, $monthlyAverageConversion->getDate());
            }
        }
    }
}
