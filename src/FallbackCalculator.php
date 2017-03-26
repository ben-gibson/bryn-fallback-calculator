<?php

namespace Gibbo\Bryn\Calculator\Fallback;

use Gibbo\Bryn\Exchange;
use Gibbo\Bryn\ExchangeRate;
use Gibbo\Bryn\ExchangeRateCalculator;
use Gibbo\Bryn\ExchangeRateCalculatorException;

/**
 * Defers to registered calculators in priority order until an exchange rate is returned or they all fail.
 */
class FallbackCalculator implements ExchangeRateCalculator
{
    /** @var ExchangeRateCalculator[] */
    private $calculators = [];

    /**
     * Register a calculator.
     *
     * @param ExchangeRateCalculator $calculator
     *
     * @return void
     */
    public function registerCalculator(ExchangeRateCalculator $calculator)
    {
        $this->calculators[] = $calculator;
    }


    /**
     * @inheritdoc
     */
    public function getRate(Exchange $exchange): ExchangeRate
    {
        foreach ($this->calculators as $calculator) {
            try {
                return $calculator->getRate($exchange);
            } catch (ExchangeRateCalculatorException $exception) {}
        }

        throw new ExchangeRateCalculatorException("No calculator could get the exchange rate.");
    }
}
