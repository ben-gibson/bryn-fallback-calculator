<?php

namespace Gibbo\Bryn\Calculator\Fallback\Test;

use Gibbo\Bryn\Calculator\Fallback\FallbackCalculator;
use Gibbo\Bryn\Currency;
use Gibbo\Bryn\Exchange;
use Gibbo\Bryn\ExchangeRate;
use Gibbo\Bryn\ExchangeRateCalculatorException;
use PHPUnit\Framework\TestCase;

/**
 * Calculator tests.
 */
class FallbackCalculatorTest extends TestCase
{

    /**
     * Can the calculator be initialised.
     *
     * @return void
     */
    public function testCanBeInitialised()
    {
        $this->assertInstanceOf(FallbackCalculator::class, $this->getCalculator());
    }

    /**
     * Test the calculator falls back to each of its registered calculators in order until one is successful.
     *
     * @return void
     */
    public function testDoesFallBackOnFailure()
    {
        $exchange = new Exchange(Currency::GBP(), Currency::USD());

        $calculator1 = $this->createMock(FallbackCalculator::class);

        $calculator1->method('getRate')
            ->with($exchange)
            ->willThrowException(new ExchangeRateCalculatorException());

        $calculator2 = $this->createMock(FallbackCalculator::class);

        $calculator2
            ->method('getRate')
            ->with($exchange)
            ->willThrowException(new ExchangeRateCalculatorException());

        $calculator3 = $this->createMock(FallbackCalculator::class);

        $calculator3
            ->method('getRate')
            ->with($exchange)
            ->willReturn(new ExchangeRate($exchange, 1.2567));

        $fallbackCalculator = $this->getCalculator([$calculator1, $calculator2, $calculator3]);

        $this->assertEquals(new ExchangeRate($exchange, 1.2567), $fallbackCalculator->getRate($exchange));
    }

    /**
     * Test the calculator does throw when all calculators fail.
     *
     * @expectedException \Gibbo\Bryn\ExchangeRateCalculatorException
     * @expectedExceptionMessage No calculator could get the exchange rate.
     *
     * @return void
     */
    public function testDoesThrownWhenAllCalculatorsFail()
    {
        $exchange = new Exchange(Currency::GBP(), Currency::USD());

        $calculator1 = $this->createMock(FallbackCalculator::class);

        $calculator1->method('getRate')
            ->with($exchange)
            ->willThrowException(new ExchangeRateCalculatorException());

        $calculator2 = $this->createMock(FallbackCalculator::class);

        $calculator2
            ->method('getRate')
            ->with($exchange)
            ->willThrowException(new ExchangeRateCalculatorException());

        $fallbackCalculator = $this->getCalculator([$calculator1, $calculator2]);

        $fallbackCalculator->getRate($exchange);
    }

    /**
     * Get the calculator under test.
     *
     * @param FallbackCalculator[] $calculators
     *
     * @return FallbackCalculator
     */
    private function getCalculator(array $calculators = []): FallbackCalculator
    {
        $fallbackCalculator = new FallbackCalculator();

        foreach ($calculators as $calculator) {
            $fallbackCalculator->registerCalculator($calculator);
        }

        return $fallbackCalculator;
    }
}