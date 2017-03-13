<?php
/**
 * Calc.php -- 财务计算函数，用于计算投资回报率，现值，终值等。公式应用基本等同excel
 *
 * @copyright   Copyright 2009-2015.
 * @author      Xueron
 *
 * @package     Pails
 *
 * @version     $Id: $
 *
 * @link        http://pails.xueron.com
 */

namespace Pails\Utils\Finance;

/**
 * Class Calc
 *
 * @package Dowedo\Core\Utils
 */
class Calc
{
    /**
     * 迭代计算精度
     */
    const IRR_ACCURACY = 0.0000001;

    /**
     * 最高迭代次数
     */
    const MAX_IRR_ITERATION = 200;

    /**
     * 每月天数
     */
    const DAYS_OF_MONTH = 30;

    /**
     * 每年天数
     */
    const DAYS_OF_YEAR = 365;

    /**
     * PV() 是一个财务函数，用于根据固定利率计算贷款或投资的现值。
     * 可以将 PV 与定期付款、固定付款（如按揭或其他贷款）或投资目标的未来值结合使用。
     * PV(rate, nper, pmt, [fv], [type])
     * 请确保指定 rate 和 nper 所用的单位是一致的。
     * 如果贷款为期四年（年利率 12%），每月还一次款，则 rate 应为 12%/12，nper 应为 4*12。 如果对相同贷款每年还一次款，则 rate 应为 12%，nper 应为 4。
     *
     * 公式：
     * rate=0： (pmt*nper) + pv + fv = 0
     * rate!=0: pv * (1+rate)^nper + pmt(1+rate*type)*[((1+rate)^nper -1)/rate] + fv = 0
     *
     * @param float     $rate 必需。 各期利率。
     *                        例如，如果您获得年利率为 10% 的汽车贷款，并且每月还款一次，则每月的利率为 10%/12（即 0.83%）。
     *                        您需要在公式中输入 10%/12（即 0.83%）或 0.0083 作为利率。
     * @param int       $nper 必需。 年金的付款总期数。
     *                        例如，如果您获得为期四年的汽车贷款，每月还款一次，则贷款期数为 4*12（即 48）期。
     *                        您需要在公式中输入 48 作为 nper。
     * @param float     $pmt  必需。 每期的付款金额，在年金周期内不能更改。通常，pmt 包括本金和利息，但不含其他费用或税金。
     *                        例如，对于金额为 ￥100,000、利率为 12% 的四年期汽车贷款，每月付款为 ￥2633.30。
     *                        您需要在公式中输入 -2633.30 作为 pmt。 如果省略 pmt，则必须包括 fv 参数。
     * @param float|int $fv   可选。 未来值，或在最后一次付款后希望得到的现金余额。 如果省略 fv，则假定其值为 0（例如，贷款的未来值是 0）。
     *                        例如，如果要在 18 年中为支付某个特殊项目而储蓄 ￥500,000，则 ￥500,000 就是未来值。
     *                        然后，您可以对利率进行保守的猜测，并确定每月必须储蓄的金额。 如果省略 fv，则必须包括 pmt 参数。
     * @param int       $type 可选。 数字 0 或 1，用以指定各期的付款时间是在期初还是期末。
     *
     * @return float|int
     */
    public static function pv($rate, $nper, $pmt, $fv = 0, $type = 0)
    {
        if ($rate == 0) {
            return 0 - $fv - $pmt * $nper;
        } else {
            return (0 - $fv - ($pmt * (1 + $rate * $type) * ((pow(1 + $rate, $nper) - 1) / $rate) + $fv)) / pow(1 + $rate, $nper);
        }
    }

    /**
     * 用于根据固定付款额和固定利率计算贷款的付款额。
     * PMT 返回的付款包括本金和利息，但不包括税金、准备金，也不包括某些与贷款有关的费用。
     *
     * 请确保指定 rate 和 nper 所用的单位是一致的。
     * 如果要以百分之十二的年利率按月支付一笔四年期的贷款，则 rate 应为 12%/12，nper 应为 4*12。
     * 如果按年支付同一笔贷款，则 rate 使用 12%，nper 使用 4。
     *
     * 公式：
     * rate=0： (pmt*nper) + pv + fv = 0
     * rate!=0: pv * (1+rate)^nper + pmt(1+rate*type)*[((1+rate)^nper -1)/rate] + fv = 0
     *
     * @param $rate
     * @param $nper
     * @param $pv
     * @param int $fv
     * @param int $type
     *
     * @return float
     */
    public static function pmt($rate, $nper, $pv, $fv = 0, $type = 0)
    {
        if ($rate == 0) {
            return (0 - $pv - $fv) / $nper;
        } else {
            return ((0 - $fv - $pv * pow(1 + $rate, $nper)) * $rate) / ((1 + $rate * $type) * (pow(1 + $rate, $nper) - 1));
        }
    }

    /**
     * FV 是一个财务函数，用于根据固定利率计算投资的未来值。 可以将 FV 与定期付款、固定付款或一次付清总额付款结合使用。计算终值
     *
     * @param float $rate 各期利率
     * @param int   $nper 期数
     * @param float $pmt  各期应支付的金额
     * @param int   $pv   可选，现值
     * @param int   $type 可选，期末还是期初，默认0为期末，1为期初
     *
     * @return int
     */
    public static function fv($rate, $nper, $pmt, $pv = 0, $type = 0)
    {
        if ($rate == 0) {
            return 0 - $pv - $pmt * $nper;
        } else {
            return 0 - ($pv * pow(1 + $rate, $nper)) - ($pmt * (1 + $rate * $type) * ((pow(1 + $rate, $nper) - 1) / $rate));
        }
    }

    /**
     * 根据给定的利率以及一组现金流量计算净现值，用法与excel同名函数一致
     * 使用贴现率和一系列未来支出（负值）和收益（正值）来计算一项投资的净现值。
     *
     * NPV 投资开始于 value1 现金流所在日期的前一期，并以列表中最后一笔现金流为结束。
     * NPV 的计算基于未来的现金流。如果第一笔现金流发生在第一期的期初，则第一笔现金必须添加到 NPV 的结果中，而不应包含在值参数中。
     *
     * npv(rate, value1, [value2, ...])
     *
     * @param float $rate   必需。某一期间的贴现率。
     * @param float $values 一组现金流量序列，Value1 是必需的，后续值是可选的。
     *                      Value1, value2, ...在时间上必须具有相等间隔，并且都发生在期末。
     *                      NPV 使用 value1, value2,... 的顺序来说明现金流的顺序。一定要按正确的顺序输入支出值和收益值。
     *
     * @return float|int
     */
    public static function npv($rate, ...$values)
    {
        $npv = 0;
        $t = 1;
        foreach ($values as $value) {
            $pv   = $value / pow(1 + $rate, $t);
            $npv += $pv;
            $t++;
        }

        return $npv;
    }

    /**
     * 根据给定的一组数字计算irr。返回由值中的数字表示的一系列现金流的内部收益率。
     * 这些现金流不必等同，因为它们可能作为年金。
     * 但是，现金流必须定期（如每月或每年）出现。
     * 内部收益率是针对包含付款（负值）和收入（正值）的定期投资收到的利率
     *
     * 函数 IRR 与净现值函数 NPV 密切相关。 IRR 计算的收益率是与 0（零）净现值对应的利率。
     *
     * @param int $values 一组现金流量序列，Values 必须包含至少一个正值和一个负值，以计算返回的内部收益率。
     *
     * @throws \LogicException
     *
     * @return float
     */
    public static function irr(...$values)
    {
        // 二分法求解
        // 区间[0.0000001, 0.9999999]
        // 若在这个区间 xnpv(0.0000001) * xnpv(0.99999999) < 0 成立，则可以用二分法迭代查找使得xnpv无限接近0的值。
        // 从0.1开始找起
        //$args = func_get_args();

        $rateA = 0.0000001;
        $rateB = 0.9999999;
        $rate0 = ($rateA + $rateB) / 2;

        // STEP1. 验证区间成立；
        $npvA = static::npv($rateA, ...$values);
        $npvB = static::npv($rateB, ...$values);
        if ($npvA * $npvB > 0) {
            throw new \LogicException('cannot guess irr');
        }

        // STEP2. 迭代；
        $x = 0;
        while (abs($rateA - $rateB) > static::IRR_ACCURACY && $x < static::MAX_IRR_ITERATION) {
            $npvA = static::npv($rateA, ...$values);
            $npvB = static::npv($rateB, ...$values);
            $rate0 = ($rateA + $rateB) / 2;
            $npv0 = static::npv($rate0, ...$values);
            //echo "x=$x, ratea=$rateA, rateb=$rateB, rate0=$rate0, npva=$npvA, npvb=$npvB, npv0=$npv0\n";
            if (abs($npv0) <= static::IRR_ACCURACY) {
                return $rate0;
            } else {
                if ($npvA * $npv0 < 0) { // rate 在 ratea 和 rate0 之间
                    $rateB = $rate0;
                } else {
                    $rateA = $rate0;
                }
            }
            $x++;
        }
        if ($x >= static::MAX_IRR_ITERATION) {
            throw new \LogicException('max iteration times reached');
        } else {
            return $rate0;
        }
    }

    /**
     * 返回一组现金流的净现值，这些现金流不一定定期发生。若要计算一组定期现金流的净现值，请使用 NPV函数。
     *
     * 该公式与excel的同名公式用法一样。
     *
     * @param float $rate   贴现率(年化)
     * @param array $values 现金流量。一系列按日期对应付款计划的现金流。
     *                      第一次付款是可选的，并且与出现在投资开始阶段发生的成本或付款对应。
     *                      如果初始值是成本或付款，则必须为负值。所有后续的付款根据 365 天/年进行折扣。
     *                      序列值必须包含至少一个正值和一个负值。
     * @param array $dates  对应现金流付款的付款日期计划。
     *                      第一个付款日期表示付款计划的开始阶段，所有其他日期必须晚于此日期，但是它们的顺序可以发生变化。
     *
     * @throws \LogicException
     *
     * @return float|int
     */
    public static function xnpv($rate, $values, $dates)
    {
        $nper = count($values);
        if ($nper != count($dates)) {
            throw new \LogicException('num of values != num of dates');
        }
        $xnpv = 0;
        $date1 = new \DateTime($dates[0]);
        for ($i = 0; $i < $nper; $i++) {
            $datei = new \DateTime($dates[$i]);
            $ddiff = $datei->diff($date1);
            $ndays = $ddiff->format('%a');
            $pv = $values[$i] / pow(1 + $rate, $ndays / static::DAYS_OF_YEAR);
            $xnpv += $pv;
        }

        return $xnpv;
    }

    /**
     * 返回一组不一定定期发生的现金流的内部收益率。 若要计算一组定期现金流的内部收益率，请使用函数 IRR。
     *
     * 值得注意的是xirr返回的直接是年华的收益率
     *
     * @param array $values 必需。 与 dates 中的支付时间相对应的一系列现金流。
     *                      首期支付是可选的，并与投资开始时的成本或支付有关。
     *                      如果第一个值是成本或支付，则它必须是负值。
     *                      所有后续支付都基于 365 天/年贴现。 值系列中必须至少包含一个正值和一个负值。
     * @param array $dates  必需。 与现金流支付相对应的支付日期表。
     *                      日期可按任何顺序排列。 应使用 DATE 函数输入日期，或者将日期作为其他公式或函数的结果输入。
     *                      例如，使用函数 DATE('Y-m-d') 输入 2008 年 5 月 23 日。 如果日期以文本形式输入，则会出现问题 。
     *
     * @throws \LogicException
     *
     * @return float
     */
    public static function xirr($values, $dates)
    {
        // 二分法求解
        // 区间[0.0000001, 0.9999999]
        // 若在这个区间 xnpv(0.0000001) * xnpv(0.99999999) < 0 成立，则可以用二分法迭代查找使得xnpv无限接近0的值。
        // 从0.1开始找起
        $rateA = 0.0000001;
        $rateB = 0.9999999;
        $rate0 = ($rateA + $rateB) / 2;

        $x = 0;
        $npvA = static::xnpv($rateA, $values, $dates);
        $npvB = static::xnpv($rateB, $values, $dates);
        if ($npvA * $npvB > 0) {
            throw new \LogicException('cannot guess irr');
        }
        while (abs($rateA - $rateB) > static::IRR_ACCURACY && $x < static::MAX_IRR_ITERATION) {
            $npvA = static::xnpv($rateA, $values, $dates);
            $npvB = static::xnpv($rateB, $values, $dates);
            $rate0 = ($rateA + $rateB) / 2;
            $npv0 = static::xnpv($rate0, $values, $dates);
            //echo "x=$x, ratea=$rateA, rateb=$rateB, rate0=$rate0, npva=$npvA, npvb=$npvB, npv0=$npv0\n";
            if (abs($npv0) <= static::IRR_ACCURACY) {
                return $rate0;
            } else {
                if ($npvA * $npv0 < 0) { // rate 在 ratea 和 rate0 之间
                    $rateB = $rate0;
                } elseif ($npvB * $npv0 < 0) {
                    $rateA = $rate0;
                } else {
                    // never
                }
            }
            $x++;
        }
        if ($x >= static::MAX_IRR_ITERATION) {
            throw new \LogicException('max iteration times reached');
        }

        return $rate0;
    }

    /**
     * 根据现值、年值以及给定的利率计算净现值，不体现期初有额外天数的情况，均以整期计算
     * 本公式，pv出现在第一期的期初，pmt均发生在各期的期末。
     *
     * @param $rate
     * @param $pv
     * @param $pmt
     * @param $nper
     *
     * @return float
     */
    public static function npv1($rate, $pv, $pmt, $nper)
    {
        $npv = $pv;
        for ($t = 1; $t <= $nper; $t++) {
            $npv += $pmt / pow(1 + $rate, $t);
        }

        return $npv;
    }

    /**
     * 标准irr计算，不体现期初有额外天数的情况，均以整期计算，算法效率最高。
     *
     * 迭代试算，根据现值系数公式计算，Aitken算法
     *
     * @param float $pv    现值
     * @param float $pmt   年值
     * @param int   $nper  期数
     * @param float $guess 起始猜测值
     *
     * @throws \LogicException
     *
     * @return float
     */
    public static function irr1($pv, $pmt, $nper, $guess = 0.1)
    {
        // 初始化计算环境
        $rate0 = $guess;
        $absPv = abs($pv); // 下列公式中PV是正值

        // 开始迭代计算
        $x = 0;
        $npv = static::npv1($rate0, $pv, $pmt, $nper);
        //echo "x=$x, rate=$rate0, npv=$npv\n";
        while (abs($npv) > static::IRR_ACCURACY && $x < static::MAX_IRR_ITERATION) {
            //Aitken 迭代法，高效迭代算法，迭代计算出下一个测试rate
            $rate11 = $pmt * (pow(1 + $rate0, $nper) - 1) / ($absPv * pow(1 + $rate0, $nper));
            $rate12 = $pmt * (pow(1 + $rate11, $nper) - 1) / ($absPv * pow(1 + $rate11, $nper));
            $rate1  = $rate12 - pow($rate12 - $rate11, 2) / ($rate12 - 2 * $rate11 + $rate0);
            $rate0  = $rate1;

            // 代入计算新的npv
            $x++;
            $npv = static::npv1($rate0, $pv, $pmt, $nper);
            //echo "x=$x, rate=$rate0, npv=$npv\n";
        }
        if ($x >= static::MAX_IRR_ITERATION) {
            throw new \LogicException('max iteration times reached');
        }

        return $rate0;
    }

    /**
     * 针对初期有额外天数的情况，计算净现值。days应该是小于30天的一个数字。小于30天则将月利按天折算计算。
     *
     * @param $rate
     * @param $pv
     * @param $pmt
     * @param $nper
     * @param int $days
     *
     * @return mixed
     */
    public static function npv2($rate, $pv, $pmt, $nper, $days = 0)
    {
        // 按照等额分期的期初的现值
        $pv0 = abs(static::pv($rate, $nper, $pmt));
        if ($days == 0) {
            return $pv + $pv0;
        } else {
            return $pv + $pv0 / pow(1 + $rate, $days / static::DAYS_OF_MONTH);
        }
    }

    /**
     * 根据 现值、每期应付额、期数，以及初期的额外天数计算实际利率（收益率）
     *
     * @param float $pv   现值，也就是用户借的金额、或者是投资人出资金额
     * @param float $pmt  每期应还金额、或者每期应收金额
     * @param int   $nper 期数
     * @param int   $days 初期额外的天数
     *
     * @throws \LogicException
     *
     * @return float
     */
    public static function irr2($pv, $pmt, $nper, $days = 0)
    {
        // 二分法求解
        // 区间[0.0000001, 0.9999999]
        // 若在这个区间 xnpv(0.0000001) * xnpv(0.99999999) < 0 成立，则可以用二分法迭代查找使得xnpv无限接近0的值。
        // 从0.1开始找起
        //$args = func_get_args();

        $rateA = 0.0000001;
        $rateB = 0.9999999;
        $rate0 = ($rateA + $rateB) / 2;

        // STEP1. 验证区间成立；
        $npvA = static::npv2($rateA, $pv, $pmt, $nper, $days);
        $npvB = static::npv2($rateB, $pv, $pmt, $nper, $days);
        if ($npvA * $npvB > 0) {
            throw new \LogicException('cannot guess irr');
        }

        // STEP2. 迭代；
        $x = 0;
        while (abs($rateA - $rateB) > static::IRR_ACCURACY && $x < static::MAX_IRR_ITERATION) {
            $npvA = static::npv2($rateA, $pv, $pmt, $nper, $days);
            $npvB = static::npv2($rateB, $pv, $pmt, $nper, $days);
            $rate0 = ($rateA + $rateB) / 2;
            $npv0 = static::npv2($rate0, $pv, $pmt, $nper, $days);
            //echo "x=$x, ratea=$rateA, rateb=$rateB, rate0=$rate0, npva=$npvA, npvb=$npvB, npv0=$npv0\n";
            if (abs($npv0) <= static::IRR_ACCURACY) {
                //echo "rate0=$rate0, its ok, return\n";
                return $rate0;
            } else {
                if ($npvA * $npv0 < 0) { // rate 在 ratea 和 rate0 之间
                    $rateB = $rate0;
                } else {
                    $rateA = $rate0;
                }
            }
            $x++;
        }
        if ($x >= static::MAX_IRR_ITERATION) {
            throw new \LogicException('max iteration times reached');
        }

        return $rate0;
    }

    /**
     * 单利计算应计利息
     *
     * @param float  $principal 本金
     * @param float  $rate      年利率
     * @param string $from      开始日期
     * @param string $to        到期日期
     *
     * @return float
     */
    public static function interest($principal, $rate, $from, $to)
    {
        $fromDate = new \DateTime($from);
        $toDate = new \DateTime($to);
        $diff = $fromDate->diff($toDate);
        $duration = $diff->format('%m,%d'); // 返回持续的月数以及零头的天数
        list($months, $days) = explode(',', $duration);
        $interest = ((($rate / 12) * $months) + (($rate / static::DAYS_OF_YEAR) * $days)) * $principal;

        return $interest;
    }

    /**
     * 根据收益率和天数,计算当前价格. 不考虑利息因素. 精确到4位小数(xx.xx%)
     *
     * @param float $rr
     * @param int   $days
     *
     * @return float
     */
    public static function price($rr, $days)
    {
        $price = 1 / ($rr * $days / static::DAYS_OF_YEAR + 1);

        return round($price, 4);
    }
}
