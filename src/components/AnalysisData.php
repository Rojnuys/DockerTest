<?php

use MathPHP\Probability\Distribution\Continuous;

class AnalysisData
{
    static public function getVariationSeries($data)
    {
        $variationSeries = [];

        sort($data);
        $uniqueValueCount = array_count_values($data);

        $i = 0;
        foreach ($uniqueValueCount as $value => $count) {
            $i += $count;

            $variationSeries[] = [
                'value' => round(floatval($value), 4),
                'count' => $count,
                'frequency' => round($count / count($data), 4),
                'ecdf' => round($i / count($data), 4),
            ];
        }

        return $variationSeries;
    }

    static public function getVariationClasses($variationSeries, $countData, $m)
    {
        $h = ($variationSeries[count($variationSeries) - 1]['value'] - $variationSeries[0]['value']) / $m;
        $variationClasses = [];

        $sumCount = 0;
        for ($i = 0; $i < $m; $i++) {
            $min = $variationSeries[0]['value'] + $i * $h;
            $max = $variationSeries[0]['value'] + ($i + 1) * $h;
            $count = 0;

            foreach ($variationSeries as $variationRow) {
                if ($i + 1 == $m) {
                    if ($variationRow['value'] >= $min && $variationRow['value'] <= $max) {
                        $count += $variationRow['count'];
                    }
                } else {
                    if ($variationRow['value'] >= $min && $variationRow['value'] < $max) {
                        $count += $variationRow['count'];
                    }
                }

                if ($variationRow['value'] > $max) {
                    break;
                }
            }

            $sumCount += $count;

            $variationClasses[] = [
                'min' => round($min, 4),
                'max' => round($max, 4),
                'count' => $count,
                'frequency' => round($count / $countData, 4),
                'ecdf' => round($sumCount / $countData, 4),
            ];
        }

        return $variationClasses;
    }

    static public function getBandwidthSilverman($data)
    {
        $avgData = array_sum($data) / count($data);
        $d = [];
        for ($i = 0; $i < count($data); $i++) {
            $d[] = ($data[$i] - $avgData) ** 2;
        }
        $avgD = array_sum($d) / count($d);
        $meanSquareDeviation = sqrt($avgD);
        return $meanSquareDeviation * (0.75 * count($data)) ** (-1/5);
    }

    static private function calculateKdeGause($data, $x, $bandwidth) {
        $n = count($data);
        $sum = 0;

        foreach ($data as $xi) {
            $weight = (1 / ($bandwidth * sqrt(2 * pi()))) * exp(-(($x - $xi) ** 2) / (2 * ($bandwidth ** 2)));
            $sum += $weight;
        }

        return $sum / $n;
    }

    static public function getKernelEstimationOfTheDensity($data, $bandwidth)
    {
        $kernelEstimationOfTheDensity = [];

//        var_dump(min($data), max($data));

        foreach (range(min($data), max($data), (max($data) - min($data)) / 100) as $value) {
            $kernelEstimationOfTheDensity[] = [
                'value' => round($value, 4),
                'result' => self::calculateKdeGause($data, $value, $bandwidth),
            ];
        }

        return $kernelEstimationOfTheDensity;
    }

    static private function getQuantilesOfStudentsDistribution($v)
    {
        $values = [
            1 => 12.7,
            2 => 4.30,
            3 => 3.18,
            4 => 2.78,
            5 => 2.57,
            6 => 2.45,
            7 => 2.36,
            8 => 2.31,
            9 => 2.26,
            10 => 2.23,
            11 => 2.20,
            12 => 2.18,
            13 => 2.16,
            14 => 2.14,
            15 => 2.13,
            16 => 2.12,
            17 => 2.11,
            18 => 2.10,
            19 => 2.09,
            20 => 2.09,
            21 => 2.08,
            22 => 2.07,
            23 => 2.07,
            24 => 2.06,
            25 => 2.06,
            26 => 2.06,
            27 => 2.05,
            28 => 2.05,
            29 => 2.05,
            30 => 2.04,
            31 => 2.04,
            32 => 2.04,
            33 => 2.03,
            34 => 2.03,
            35 => 2.03,
            36 => 2.03,
            37 => 2.03,
            38 => 2.02,
            39 => 2.02,
            40 => 2.02,
            41 => 2.02,
            42 => 2.02,
            43 => 2.02,
            44 => 2.02,
            45 => 2.01,
            46 => 2.01,
            47 => 2.01,
            48 => 2.01,
            49 => 2.01,
            50 => 2.01,
            51 => 2.01,
            52 => 2.01,
            53 => 2.01,
            54 => 2,
            55 => 2,
            56 => 2,
            57 => 2,
            58 => 2,
            59 => 2,
            60 => 2,
            65 => 2,
            70 => 1,99,
            75 => 1,99,
            80 => 1,99,
            90 => 1,99,
            100 => 1,98,
            110 => 1,98,
            120 => 1,98,
        ];

        return isset($values[$v]) ? $values[$v] : 1.96;
    }

    static public function getQuantitativeCharacteristics($data)
    {
        $crts = [];
        sort($data);
        $n = count($data);

        $avg = array_sum($data) / $n;

        $med = $data[round(($n + 1) / 2) - 1];

        $helpSum = [0, 0, 0];
        foreach ($data as $value) {
            $helpSum[0] += (($value - $avg) ** 2);
            $helpSum[1] += (($value - $avg) ** 3);
            $helpSum[2] += (($value - $avg) ** 4);
        }

        $s1 = sqrt($helpSum[0] / $n);
        $s2 = sqrt($helpSum[0] / ($n - 1));

        $w = $s2 / $avg;

        $a1 = $helpSum[1] / ($n * ($s1 ** 3));
        $a2 = sqrt($n * ($n - 1)) / ($n - 2) * $a1;

        $e1 = $helpSum[2] / ($n * ($s1 ** 4)) - 3;
        $e2 = (($n ** 2) - 1) / (($n - 2) * ($n - 3)) * ($e1 + (6 / ($n + 1)));

        $x = 1 / sqrt($e1 + 3);

        $ox = $s1 / sqrt($n);
        $os = $s1 / sqrt(2 * $n);
        $ow = $w * sqrt((1 + 2 * ($w ** 2)) / (2 * $n));
        $oa1 = sqrt((6 * ($n - 2)) / (($n + 1) * ($n + 3)));
        $oa2 = sqrt((6 * $n * ($n - 1)) / (($n - 2) * ($n + 1) * ($n + 3)));
        $oe1 = sqrt((24 * $n * ($n - 2) * ($n - 3)) / (($n + 1) ** 2 * ($n + 3) * ($n + 5)));
        $oe2 = sqrt((24 * $n * (($n - 1) ** 2)) / (($n - 3) * ($n - 2) * ($n + 3) * ($n + 5)));

        $a = 0.05;
        $u = 1.96;
        $v = $n - 1;
        $t = self::getQuantilesOfStudentsDistribution($v);

        $xStart = $avg - $t * $ox;
        $xEnd = $avg + $t * $ox;
        $sStart = $s2 - $t * $os;
        $sEnd = $s2 + $t * $os;
        $wStart = $w - $t * $ow;
        $wEnd = $w + $t * $ow;
        $a1Start = $a1 - $t * $oa1;
        $a1End = $a1 + $t * $oa1;
        $a2Start = $a2 - $t * $oa2;
        $a2End = $a2 + $t * $oa2;
        $e1Start = $e1 - $t * $oe1;
        $e1End = $e1 + $t * $oe1;
        $e2Start = $e2 - $t * $oe2;
        $e2End = $e2 + $t * $oe2;
        $medStart = $data[round($n / 2 - 1.96 * (sqrt($n) / 2)) - 1];
        $medEnd = $data[round($n / 2 + 1 + 1.96 * (sqrt($n) / 2)) - 1];
        $normStart = $avg - $u * $s2;
        $normEnd = $avg + $u * $s2;

        $ta = $a2 / $oa2;
        $te = $e2 / $oe2;

        $crts['avg'] = [round($avg, 2), round($ox, 2), round($xStart, 2), round($xEnd, 2)];
        $crts['med'] = [round($med, 2), '-', round($medStart, 2), round($medEnd, 2)];
        $crts['s'] = [round($s2, 2), round($os, 2), round($sStart, 2), round($sEnd, 2)];
        $crts['a'] = [round($a2, 2), round($oa2, 2), round($a2Start, 2), round($a2End, 2)];
        $crts['e'] = [round($e2, 2), round($oe2, 2), round($e2Start, 2), round($e2End, 2)];
        $crts['min'] = round(min($data), 4);
        $crts['max'] = round(max($data), 4);
        $crts['normal'] = [$normStart, $normEnd];
        $crts['identBaseAE'] = [round($ta, 2), round($te, 2), round($t, 2), abs($ta) <= $t, abs($te) <= $t];

        return $crts;
    }

    static public function getDataForProbabilityPaper($variationSeries)
    {
        foreach ($variationSeries as &$row) {
            $standardNormal = new Continuous\StandardNormal();
            $row['u'] = '' . round($standardNormal->inverse($row['ecdf']), 4);
        }

        return $variationSeries;
    }

    static public function getDataForProbabilityPaperParetto($variationSeries)
    {
        unset($variationSeries[count($variationSeries) - 1]);

        $data = [];
        foreach ($variationSeries as &$row) {
            $tmp = -log($row['value']);
            if (is_nan($tmp))
            {
                continue;
            }
            $data[] = [
                't' => $tmp,
                'z' => log(1 - $row['ecdf'])
            ];
        }

        return $data;
    }

    static public function getParettoParameters($variationSeries)
    {
        $k = $variationSeries[0]['value'];
        $a = 0;
        foreach ($variationSeries as $row)
        {
            $tmp = log($row['value'] / $k);
            if (is_nan($tmp))
            {
                continue;
            }
            $a += $tmp;
        }
        $a = count($variationSeries) / $a;

        $n = count($variationSeries);
        $a2 = (($n - 2) / $n) * $a;
        $k2 = (1 - (1 / ($n - 1) * $a2)) * $k;
        $aD = pow(($n - 2) / $n, 2) * (pow($a, 2) / $n);
        $qa = sqrt($aD);
        $kD = pow($k, 2) * (1 / pow($n - 1, 2));
        $qk = sqrt($kD);
        $u = 1.96;
        $startK = $k2 - $u * $qk;
        $endK = $k2 + $u * $qk;
        $startA = $a - $u * $qa;
        $endA = $a + $u * $qa;

        return [
            'k' => $k,
            'k2' => $k2,
            'qk' => $qk,
            'sk' => $startK,
            'ek' => $endK,
            'a' => $a,
            'a2' => $a2,
            'qa' => $qa,
            'sa' => $startA,
            'ea' => $endA
        ];
    }

    static public function getDataForParettoProbabilityDistribution($variationSeries, $a, $k)
    {
        $data = [];
        foreach ($variationSeries as $row) {
            $data[] = [
                'x' => $row['value'],
                'y' => 1 - pow($k / $row['value'], $a)
            ];
        }

        return $data;
    }

    static public function getDataForParettoDensityProbabilityDistribution($variationSeries, $a, $k)
    {
        $data = [];
        foreach ($variationSeries as $row) {
            $data[] = [
                'x' => $row['value'],
                'y' => ($a / $k) * pow($k / $row['value'], $a + 1)
            ];
        }

        return $data;
    }

    static public function getKolmogorovCriticalValue($a)
    {
        $kolmogorovTable = [
            '0.1' => 1.22,
            '0.05' => 1.36,
            '0.02' => 1.52,
            '0.01' => 1.63,
        ];

        if (array_key_exists($a, $kolmogorovTable)) {
            $criticalValue = $kolmogorovTable[$a];
        } else {
            $criticalValue = 1.63 + ($a - 0.01) * (1.52 - 1.63) / (0.02 - 0.01);
        }

        return $criticalValue;
    }

    static public function getKolmogorovCriterion($variationSeries)
    {
        $parameters = self::getParettoParameters($variationSeries);
        $k = $parameters['k'];
        $a = $parameters['a'];

        $theoreticalDistribution = function($x, $k, $a) {
            if ($x >= $k) {
                return 1 - pow($k / $x, $a);
            } else {
                return 0;
            }
        };

        $n = count($variationSeries);
        $dPlusValues = [];
        $dMinusValues = [];

        for ($i = 0; $i < $n; $i++) {
            $fX = $variationSeries[$i]['ecdf'];
            $dPlusValues[] = abs($fX - $theoreticalDistribution($variationSeries[$i]['value'], $k, $a));
        }

        for ($i = 1; $i < $n; $i++) {
            $fX = $variationSeries[$i - 1]['ecdf'];
            $dMinusValues[] = abs($fX - $theoreticalDistribution($variationSeries[$i]['value'], $k, $a));
        }

        $dValues = $dPlusValues + $dMinusValues;

        $kStatisticZ = sqrt($n) * max($dValues);

        $tmp = 0;
        for ($i = 1; $i <= 6; $i++) {
            $tmp += pow(-1, $i) * exp(-2 * pow($i, 2) * pow($kStatisticZ, 2));
        }

        $kZ = 1 + 2 * $tmp;
        $p = 1 - $kZ;
        $criticalLevel = '0.05';
        $criticalValue = self::getKolmogorovCriticalValue($criticalLevel);

        return ['z' => $kStatisticZ, 'p' => $p, 'a' => $criticalLevel, 'cv' => $criticalValue];
    }
}