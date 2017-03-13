<?php
/**
 * This file is part of the Supreme Shop Middleware.
 *
 * @author    Supreme NewMedia GmbH <entwicklung@supreme.de>
 * @copyright 2012-2014 Supreme NewMedia GmbH
 * @license   MIT License
 *
 * @package   Finance
 */

namespace Pails\Utils\Finance;

/**
 * Money with an amount and a Currency.
 */
class Money
{
    /**
     * Scale used for all calculations.
     *
     * @var int bcscale argument
     */
    const BCSCALE = 5;

    /**
     * Round up (or take the ceiling, or round towards plus infinity).
     *
     * @var string round up
     */
    const ROUND_UP = 'ROUND_UP';

    /**
     * Round down (or take the floor, or round towards minus infinity).
     *
     * @var string round down
     */
    const ROUND_DOWN = 'ROUND_DOWN';

    /**
     * Round towards zero (or truncate, or round away from infinity).
     *
     * @var string round towards zero
     */
    const ROUND_TOWARDS_ZERO = 'ROUND_TOWARDS_ZERO';

    /**
     * Round away from zero (or round towards infinity).
     *
     * @var string round away from zero
     */
    const ROUND_AWAY_FROM_ZERO = 'ROUND_AWAY_FROM_ZERO';

    /**
     * If the fraction of the amount is exactly 0.5, then return the amount + 0.5.
     *
     * @var string round half up
     */
    const ROUND_HALF_UP = 'ROUND_HALF_UP';

    /**
     * If the fraction of the amount is exactly 0.5, then return the amount - 0.5.
     *
     * @var string round half down
     */
    const ROUND_HALF_DOWN = 'ROUND_HALF_DOWN';

    /**
     * If the fraction of the amount is exactly 0.5, then return the amount - 0.5 if the amount is positive, and return
     * the amount + 0.5 if the amount is negative.
     *
     * @var string round haf towards zero
     */
    const ROUND_HALF_TOWARDS_ZERO = 'ROUND_HALF_TOWARDS_ZERO';

    /**
     * If the fraction of the amount is exactly 0.5, then return the amount + 0.5 if the amount is positive, and return
     * the amount - 0.5 if the amount is negative.
     *
     * @var string round half away from zero
     */
    const ROUND_HALF_AWAY_FROM_ZERO = 'ROUND_HALF_AWAY_FROM_ZERO';

    /**
     * If the fraction of the amount is 0.5, then return the even integer nearest to the amount.
     *
     * @var string round half to even
     */
    const ROUND_HALF_TO_EVEN = 'ROUND_HALF_TO_EVEN';

    /**
     * If the fraction of the amount is 0.5, then return the odd integer nearest to the amount.
     *
     * @var string round half to odd
     */
    const ROUND_HALF_TO_ODD = 'ROUND_HALF_TO_ODD';

    // Aliases

    /**
     * Round up (or take the ceiling, or round towards plus infinity).
     *
     * @var string round up
     */
    const ROUND_CEILING = self::ROUND_UP;

    /**
     * Round up (or take the ceiling, or round towards plus infinity).
     *
     * @var string round up
     */
    const ROUND_TOWARDS_PLUS_INFINITY = self::ROUND_UP;

    /**
     * Round down (or take the floor, or round towards minus infinity).
     *
     * @var string round down
     */
    const ROUND_FLOOR = self::ROUND_DOWN;

    /**
     * Round down (or take the floor, or round towards minus infinity).
     *
     * @var string round down
     */
    const ROUND_TOWARDS_MINUS_INFINITY = self::ROUND_DOWN;

    /**
     * Round towards zero (or truncate, or round away from infinity).
     *
     * @var string round towards zero
     */
    const ROUND_TRUNCATE = self::ROUND_TOWARDS_ZERO;

    /**
     * Round towards zero (or truncate, or round away from infinity).
     *
     * @var string round towards zero
     */
    const ROUND_AWAY_FROM_INFINITY = self::ROUND_TOWARDS_ZERO;

    /**
     * Round away from zero (or round towards infinity).
     *
     * @var string round away from zero
     */
    const ROUND_TOWARDS_INFINITY = self::ROUND_AWAY_FROM_ZERO;

    /**
     * If the fraction of the amount is exactly 0.5, then return the amount + 0.5.
     *
     * @var string round half up
     */
    const ROUND_HALF_TOWARDS_PLUS_INFINITY = self::ROUND_HALF_UP;

    /**
     * If the fraction of the amount is exactly 0.5, then return the amount - 0.5.
     *
     * @var string round half down
     */
    const ROUND_HALF_TOWARDS_MINUS_INFINITY = self::ROUND_HALF_DOWN;

    /**
     * If the fraction of the amount is exactly 0.5, then return the amount - 0.5 if the amount is positive, and return
     * the amount + 0.5 if the amount is negative.
     *
     * @var string round half towards zero
     */
    const ROUND_HALF_AWAY_FROM_INFINITY = self::ROUND_HALF_TOWARDS_ZERO;

    /**
     * If the fraction of the amount is exactly 0.5, then return the amount + 0.5 if the amount is positive, and return
     * the amount - 0.5 if the amount is negative.
     *
     * @var string round half away from zero
     */
    const ROUND_HALF_TOWARDS_INFINITY = self::ROUND_HALF_AWAY_FROM_ZERO;

    /**
     * If the fraction of the amount is 0.5, then return the even integer nearest to the amount.
     *
     * @var string round half to even
     */
    const ROUND_UNBIASED = self::ROUND_HALF_TO_EVEN;

    /**
     * If the fraction of the amount is 0.5, then return the even integer nearest to the amount.
     *
     * @var string
     */
    const ROUND_CONVERGENT = self::ROUND_HALF_TO_EVEN;

    /**
     * If the fraction of the amount is 0.5, then return the even integer nearest to the amount.
     *
     * @var string
     */
    const ROUND_STATISTICIANS = self::ROUND_HALF_TO_EVEN;

    /**
     * If the fraction of the amount is 0.5, then return the even integer nearest to the amount.
     *
     * @var string
     */
    const ROUND_DUTCH = self::ROUND_HALF_TO_EVEN;

    /**
     * If the fraction of the amount is 0.5, then return the even integer nearest to the amount.
     *
     * @var string
     */
    const ROUND_GAUSSIAN = self::ROUND_HALF_TO_EVEN;

    /**
     * If the fraction of the amount is 0.5, then return the even integer nearest to the amount.
     *
     * @var string
     */
    const ROUND_ODD_EVEN = self::ROUND_HALF_TO_EVEN;

    /**
     * If the fraction of the amount is 0.5, then return the even integer nearest to the amount.
     *
     * @var string
     */
    const ROUND_BANKERS = self::ROUND_HALF_TO_EVEN;

    /**
     * The Amount.
     *
     * @var string bcmath representation of the amount
     */
    private $amount;

    /**
     * the Currency.
     *
     * @var Currency based on ISO Code
     */
    private $currency;

    /**
     * Creates a new instance.
     *
     * @param string   $amount   bcmath representation of the amount
     * @param Currency $currency based on ISO Code
     */
    private function __construct($amount, Currency $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * Round towards zero (or truncate, or round away from infinity).
     *
     * @param int $decimalDigits the digits to truncate to, null for currency default
     *
     * @return Money
     */
    public function truncate($decimalDigits = 0)
    {
        return $this->round($decimalDigits, self::ROUND_TRUNCATE);
    }

    /**
     * Round up (or take the ceiling, or round towards plus infinity).
     *
     * @param int $decimalDigits the digits to ceil to, null for currency default
     *
     * @return Money
     */
    public function ceil($decimalDigits = 0)
    {
        return $this->round($decimalDigits, self::ROUND_CEILING);
    }

    /**
     * Round down (or take the floor, or round towards minus infinity).
     *
     * @param int $decimalDigits the digits to floor to, null for currency default
     *
     * @return Money
     */
    public function floor($decimalDigits = 0)
    {
        return $this->round($decimalDigits, self::ROUND_FLOOR);
    }

    /**
     * Round to a given number of decimal digits, using a given mode.
     *
     * @param int    $decimalDigits the digits to truncate to, null for currency default
     * @param string $mode          the rounding mode to use, see class constants
     *
     * @return Money
     */
    public function round($decimalDigits = 0, $mode = self::ROUND_HALF_AWAY_FROM_ZERO)
    {
        if (null === $decimalDigits) {
            $decimalDigits = $this->currency->getDecimalDigits();
        }

        $factor = bcpow('10', $decimalDigits, self::BCSCALE);
        $amount = bcmul($this->amount, $factor, self::BCSCALE);

        switch ($mode) {
            case self::ROUND_UP:
                $result = self::roundUp($amount);
                break;
            case self::ROUND_DOWN:
                $result = self::roundDown($amount);
                break;
            case self::ROUND_TOWARDS_ZERO:
                $result = self::roundTowardsZero($amount);
                break;
            case self::ROUND_AWAY_FROM_ZERO:
                $result = self::roundAwayFromZero($amount);
                break;
            case self::ROUND_HALF_UP:
                $result = self::roundHalfUp($amount);
                break;
            case self::ROUND_HALF_DOWN:
                $result = self::roundHalfDown($amount);
                break;
            case self::ROUND_HALF_TOWARDS_ZERO:
                $result = self::roundHalfTowardsZero($amount);
                break;
            case self::ROUND_HALF_AWAY_FROM_ZERO:
                $result = self::roundHalfAwayFromZero($amount);
                break;
            case self::ROUND_HALF_TO_EVEN:
                $result = self::roundHalfToEven($amount);
                break;
            case self::ROUND_HALF_TO_ODD:
                $result = self::roundHalfToOdd($amount);
                break;
            default:
                throw new \InvalidArgumentException('Unknown rounding mode \'' . $mode . '\'');
        }

        $result = bcdiv($result, $factor, self::BCSCALE);

        return self::valueOf($result, $this->currency);
    }

    /**
     * Remove trailing zeros up to and including the decimal point.
     *
     * @param string $amount               the amount to process
     * @param int    $minimumDecimalDigits the number of decimal digits that has to remain under all circumstances
     *
     * @return string
     */
    private static function discardDecimalDigitsZero($amount, $minimumDecimalDigits = 0)
    {
        if (false !== strpos($amount, '.')) {
            while ('0' == substr($amount, -1)) {
                $amount = substr($amount, 0, -1);
            }

            if ('.' == substr($amount, -1)) {
                $amount = substr($amount, 0, -1);
            }
        }

        if ($minimumDecimalDigits > 0) {
            if (false === strpos($amount, '.')) {
                $amount .= '.';
            }

            $currentDecimalDigits = strlen(substr($amount, strpos($amount, '.') + 1));
            $minimumDecimalDigits -= $currentDecimalDigits;

            while ($minimumDecimalDigits > 0) {
                $amount .= '0';
                $minimumDecimalDigits--;
            }
        }

        return $amount;
    }

    /**
     * Round up (or take the ceiling, or round towards plus infinity).
     *
     * @param string $amount the amount to round
     *
     * @return string
     */
    private static function roundUp($amount)
    {
        $result = self::roundTowardsZero($amount);

        if (1 == bccomp($amount, '0', self::BCSCALE) && 0 != bccomp($amount, $result, self::BCSCALE)) {
            $result = bcadd($result, '1', self::BCSCALE);
        }

        return $result;
    }

    /**
     * Round down (or take the floor, or round towards minus infinity).
     *
     * @param string $amount the amount to round
     *
     * @return string
     */
    private static function roundDown($amount)
    {
        $result = self::roundTowardsZero($amount);

        if (-1 == bccomp($amount, '0', self::BCSCALE) && 0 != bccomp($amount, $result, self::BCSCALE)) {
            $result = bcsub($result, '1', self::BCSCALE);
        }

        return $result;
    }

    /**
     * Round towards zero (or truncate, or round away from infinity).
     *
     * @param string $amount the amount to round
     *
     * @return string
     */
    private static function roundTowardsZero($amount)
    {
        $result = self::discardDecimalDigitsZero($amount);

        $point = strpos($result, '.');

        if (false !== $point) {
            $result = substr($result, 0, $point);
        }

        return $result;
    }

    /**
     * Round away from zero (or round towards infinity).
     *
     * @param string $amount the amount to round
     *
     * @return string
     */
    private static function roundAwayFromZero($amount)
    {
        $result = self::roundTowardsZero($amount);

        if (0 != bccomp($amount, $result, self::BCSCALE)) {
            if (1 == bccomp($amount, '0', self::BCSCALE)) {
                $result = bcadd($result, '1', self::BCSCALE);
            } elseif (-1 == bccomp($amount, '0', self::BCSCALE)) {
                $result = bcsub($result, '1', self::BCSCALE);
            }
        }

        return $result;
    }

    /**
     * Round towards the nearest integer, but return null if there's a tie, so specialized methods can break it
     * differently.
     *
     * @param string $amount the amount to round
     *
     * @return string|null
     */
    private static function roundToNearestIntegerIgnoringTies($amount)
    {
        $relevantDigit = substr(self::roundTowardsZero(bcmul($amount, '10', self::BCSCALE)), -1);

        $result = null;

        switch ($relevantDigit) {
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
                $result = self::roundTowardsZero($amount);
                break;
            case '5':
                $result = null; // handled by tie-breaking rules
                break;
            case '6':
            case '7':
            case '8':
            case '9':
                $result = self::roundAwayFromZero($amount);
                break;
        }

        return $result;
    }

    /**
     * If the fraction of the amount is exactly 0.5, then return the amount + 0.5.
     *
     * @param string $amount the amount to round
     *
     * @return string
     */
    private static function roundHalfUp($amount)
    {
        $result = self::roundToNearestIntegerIgnoringTies($amount);

        if (null == $result) {
            $result = self::roundToNearestIntegerIgnoringTies(bcadd($amount, '0.1', self::BCSCALE));
        }

        return $result;
    }

    /**
     * If the fraction of the amount is exactly 0.5, then return the amount - 0.5.
     *
     * @param string $amount the amount to round
     *
     * @return string
     */
    private static function roundHalfDown($amount)
    {
        $result = self::roundToNearestIntegerIgnoringTies($amount);

        if (null == $result) {
            $result = self::roundToNearestIntegerIgnoringTies(bcsub($amount, '0.1', self::BCSCALE));
        }

        return $result;
    }

    /**
     * If the fraction of the amount is exactly 0.5, then return the amount - 0.5 if the amount is positive, and return
     * the amount + 0.5 if the amount is negative.
     *
     * @param string $amount the amount to round
     *
     * @return string
     */
    private static function roundHalfTowardsZero($amount)
    {
        if (0 <= bccomp($amount, '0', self::BCSCALE)) {
            $result = self::roundHalfDown($amount);
        } else {
            $result = self::roundHalfUp($amount);
        }

        return $result;
    }

    /**
     * If the fraction of the amount is exactly 0.5, then return the amount + 0.5 if the amount is positive, and return
     * the amount - 0.5 if the amount is negative.
     *
     * @param string $amount the amount to round
     *
     * @return string
     */
    private static function roundHalfAwayFromZero($amount)
    {
        if (0 <= bccomp($amount, '0', self::BCSCALE)) {
            $result = self::roundHalfUp($amount);
        } else {
            $result = self::roundHalfDown($amount);
        }

        return $result;
    }

    /**
     * If the fraction of the amount is 0.5, then return the even integer nearest to the amount.
     *
     * @param string $amount the amount to round
     *
     * @return string
     */
    private static function roundHalfToEven($amount)
    {
        $result = self::roundToNearestIntegerIgnoringTies($amount);

        if (null == $result) {
            $truncated = self::roundHalfTowardsZero($amount);

            if (0 == bcmod($truncated, '2')) { // Even
                $result = $truncated;
            } else {
                $result = self::roundHalfAwayFromZero($amount);
            }
        }

        return $result;
    }

    /**
     * If the fraction of the amount is 0.5, then return the odd integer nearest to the amount.
     *
     * @param string $amount the amount to round
     *
     * @return string
     */
    private static function roundHalfToOdd($amount)
    {
        $result = self::roundToNearestIntegerIgnoringTies($amount);

        if (null == $result) {
            $truncated = self::roundHalfTowardsZero($amount);

            if (0 != bcmod($truncated, '2')) { // Odd
                $result = $truncated;
            } else {
                $result = self::roundHalfAwayFromZero($amount);
            }
        }

        return $result;
    }

    /**
     * Creates a new instance for the given amount and Currency.
     *
     * @param string   $amount   bcmath representation of the amount
     * @param Currency $currency based on ISO Code
     *
     * @return Money
     */
    public static function valueOf($amount, Currency $currency)
    {
        return new Money($amount, $currency);
    }

    /**
     * Creates a new instance with zero amount and Currency None or optional.
     *
     * @param Currency $currency an optional currency to use
     *
     * @return Money
     */
    public static function zero(Currency $currency = null)
    {
        if (null === $currency) {
            $currency = Currency::valueOf(Currency::NONE);
        }

        return self::valueOf('0', $currency);
    }

    /**
     * Creates a new instance with amount zero or optional and Currency None.
     *
     * @param string $amount an optional amount to use
     *
     * @return Money
     */
    public static function noCurrency($amount = null)
    {
        if (null === $amount) {
            $amount = '0';
        }

        return self::valueOf(strval($amount), Currency::valueOf(Currency::NONE));
    }

    /**
     * Gets the amount.
     *
     * @return string bcmath representation of the amount
     */
    public function getAmount()
    {
        return self::discardDecimalDigitsZero($this->amount, $this->getCurrency()->getDecimalDigits());
    }

    /**
     * Gets the amount multiplied to match the smallest possible denomination (based on the decimal digits of the
     * Currency), e.g. 1.234 EUR (with 2 decimal Digits to the EUR) would be returned as '123.4' (Euro Cents).
     *
     * @return string bcmath representation of the amount in the smallest possible denomination
     */
    public function getAmountInSmallestDenomination()
    {
        return self::discardDecimalDigitsZero(
            bcmul(
                $this->amount,
                bcpow(
                    '10',
                    $this->currency->getDecimalDigits(),
                    self::BCSCALE
                ),
                self::BCSCALE
            )
        );
    }

    /**
     * Gets the currency.
     *
     * @return Currency based on ISO Code
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Returns a suitable string representation.
     *
     * @see __toString()
     *
     * @return string the amount and currency
     */
    public function __toString()
    {
        return $this->getCurrency() . ' ' . $this->format();
    }

    public function format($decimals = 2, $dec_point = '.', $thousands_sep = ',')
    {
        return number_format($this->getAmount(), $decimals, $dec_point, $thousands_sep);
    }

    /**
     * Is the current currency 'NONE'?
     *
     * @return bool
     */
    public function isCurrencyNone()
    {
        return (Currency::NONE == $this->currency->getCode());
    }

    /**
     * Adds the given money to this one (immutable) and returns the result.
     *
     * @param Money $money the money to add
     *
     * @throws \LogicException if the Currencies do not match
     *
     * @return Money
     */
    public function add(Money $money)
    {
        if (!$this->currency->equals($money->getCurrency())) {
            throw new \LogicException($this . ' does not match ' . $money);
        }

        $amount = bcadd($this->amount, $money->getAmount(), self::BCSCALE);

        return self::valueOf($amount, $this->currency);
    }

    /**
     * Divides this money (immutable) by the given factor and returns the result.
     *
     * @param mixed $factor the factor to divide by
     *
     * @throws \InvalidArgumentException if the $factor is not numeric
     *
     * @return Money
     */
    public function divide($factor)
    {
        if (!is_numeric($factor)) {
            throw new \InvalidArgumentException('Factor must be numeric');
        }

        $amount = bcdiv($this->amount, strval($factor), self::BCSCALE);

        return self::valueOf($amount, $this->currency);
    }

    public function ratio(Money $money)
    {
        return bcdiv($this->amount, $money->getAmount(), self::BCSCALE);
    }

    /**
     * Are these to money equal?
     *
     * @param Money $money the money to camplare with
     *
     * @return bool true if both amounts and currency are equal, false otherwise
     */
    public function equals(Money $money)
    {
        if (!$this->currency->equals($money->getCurrency())) {
            return false;
        }

        return (0 == $this->compare($money));
    }

    /**
     * Calculates the modulus of this money.
     *
     * @param mixed $modulus the modulus to apply
     *
     * @throws \InvalidArgumentException if the $modulus is not numeric
     *
     * @return Money
     */
    public function modulus($modulus)
    {
        if (!is_numeric($modulus)) {
            throw new \InvalidArgumentException('Modulus must be numeric');
        }

        $amount = bcmod($this->amount, strval($modulus));

        return self::valueOf($amount, $this->currency);
    }

    /**
     * Multiplies this money (immutable) with the given factor and returns the result.
     *
     * @param mixed $factor the factor to multiply with
     *
     * @throws \InvalidArgumentException if the $factor is not numeric
     *
     * @return Money
     */
    public function multiply($factor)
    {
        if (!is_numeric($factor)) {
            throw new \InvalidArgumentException('Factor must be numeric');
        }

        $amount = bcmul($this->amount, strval($factor), self::BCSCALE);

        return self::valueOf($amount, $this->currency);
    }

    /**
     * Raises this money (immutable) to the given power (bcmath).
     *
     * @param mixed $power the power to raise to
     *
     * @throws \InvalidArgumentException if the $power is not numeric
     *
     * @return Money
     */
    public function power($power)
    {
        if (!is_numeric($power)) {
            throw new \InvalidArgumentException('Power must be numeric');
        }

        $amount = bcpow($this->amount, strval($power), self::BCSCALE);

        return self::valueOf($amount, $this->currency);
    }

    /**
     * Shortcut for $this->power($power)->modulus($modulus)
     *
     * @param mixed $power   the power to raise to
     * @param mixed $modulus the modulus to apply
     *
     * @throws \InvalidArgumentException if the $power is not numeric
     * @throws \InvalidArgumentException if the $modulus is not numeric
     *
     * @return Money
     */
    public function powerModulus($power, $modulus)
    {
        if (!is_numeric($power)) {
            throw new \InvalidArgumentException('Power must be numeric');
        }

        if (!is_numeric($modulus)) {
            throw new \InvalidArgumentException('Modulus must be numeric');
        }

        // bcscale leads to strange results with modulus operations (which is
        // why bcmod doesn't even have the parameter...)
        $amount = bcpowmod($this->amount, strval($power), strval($modulus), 0);

        return self::valueOf($amount, $this->currency);
    }

    /**
     * Calculates the square root of this money.
     *
     * @return Money
     */
    public function squareRoot()
    {
        $amount = bcsqrt($this->amount, self::BCSCALE);

        return self::valueOf($amount, $this->currency);
    }

    /**
     * Subtratcs the given money from this one (immutable) and returns the result.
     *
     * @param Money $money the money to subtract
     *
     * @throws \LogicException if the Currencies do not match
     *
     * @return Money
     */
    public function subtract(Money $money)
    {
        if (!$this->currency->equals($money->getCurrency())) {
            throw new \LogicException($this . ' does not match ' . $money);
        }

        $amount = bcsub($this->amount, $money->getAmount(), self::BCSCALE);

        return self::valueOf($amount, $this->currency);
    }

    //////////////
    //////////////

    public static function create($amount, $currency = Currency::CODE_CNY)
    {
        if (is_string($currency)) {
            $currency = Currency::valueOf($currency);
        }

        return static::valueOf($amount ?: 0, $currency);
    }

    /**
     * 返回一个格式化的金额,标准两位小数
     *
     * @param $amount
     *
     * @return string
     */
    public static function normalize($amount)
    {
        return self::create($amount)->round(2)->getAmount();
    }

    /**
     * Compares this moneys amount with the given ones and returns 0 if they are equal, 1 if this amount is larger than
     * the given ones, -1 otherwise. This method explicitly disregards the Currency!
     *
     * @param Money $money the money to compare with
     *
     * @return int
     */
    public function compare(Money $money)
    {
        return bccomp($this->getAmount(), $money->getAmount(), 2);
    }

    public function lessThan(Money $money)
    {
        return $this->compare($money) == -1;
    }

    public function greaterThan(Money $money)
    {
        return $this->compare($money) == 1;
    }

    public function max(Money $money)
    {
        return $this->lessThan($money) ? $money : $this;
    }

    public function min(Money $money)
    {
        return $this->lessThan($money) ? $this : $money;
    }

    public function isZero()
    {
        return (0 == bccomp('0', $this->getAmount(), 2));
    }

    public function getAmountInCents()
    {
        return bcmul($this->amount, '100', 0);
    }

    public function isNegative()
    {
        return $this->lessThan(Money::create(0));
    }

    public function isPositive()
    {
        return $this->greaterThan(Money::create(0));
    }

    public function lessThanOrEqual(Money $money)
    {
        return $this->lessThan($money) || $this->equals($money);
    }
}
