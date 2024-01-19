<?php

require_once ROOT . '/components/AnalysisData.php';

class IndexController
{
    public function indexAction()
    {
        $pageTitle = 'Main page';
        if (isset($_SESSION['data'])) {
            $data = $_SESSION['data'];

            $abnormal = !empty($_POST['abnormal']) ? $_POST['abnormal'] : 0;

            if ($abnormal) {
                $crts = AnalysisData::getQuantitativeCharacteristics($data);
                $normalMin = $crts['normal'][0];
                $normalMax = $crts['normal'][1];
                foreach ($data as $key => $value) {
                    if ($value < $normalMin || $value > $normalMax) {
                        unset($data[$key]);
                    }
                }
                $data = array_values($data);
                $_SESSION['data'] = $data;
            }

            $variationSeries = AnalysisData::getVariationSeries($data);
            $jsonVariationSeries = json_encode($variationSeries);

            $m = !empty($_GET['m']) ? $_GET['m'] : round(1 + 3.32 * log10(count($data)));
            $variationClasses = AnalysisData::getVariationClasses($variationSeries, count($data), $m);
            $jsonVariationClasses = json_encode($variationClasses);

            $b = !empty($_GET['b']) ? $_GET['b'] : AnalysisData::getBandwidthSilverman($data);
            $kernelEstimationOfTheDensity = AnalysisData::getKernelEstimationOfTheDensity($data, $b);
            $jsonKernelEstimationOfTheDensity = json_encode($kernelEstimationOfTheDensity);

            $crts = AnalysisData::getQuantitativeCharacteristics($data);

            sort($data);
            $crts['normal'][0] = round($crts['normal'][0], 4);
            $crts['normal'][1] = round($crts['normal'][1], 4);
            $abnormal = ['normal' => $crts['normal'], 'sortedData' => $data];
            $jsonAbnormal = json_encode($abnormal);

            $probabilityPaperData = AnalysisData::getDataForProbabilityPaper($variationSeries);
            $jsonProbabilityPaperData = json_encode($probabilityPaperData);

            $parettoGraphics = !empty($_POST['parettoGraphics']) ? $_POST['parettoGraphics'] : 0;

            $parameterEstimateParetto = AnalysisData::getParettoParameters($variationSeries);

            if ($parettoGraphics) {
                $parettoProbabilityDistributionData = AnalysisData::getDataForParettoProbabilityDistribution($variationSeries, $parameterEstimateParetto['a'], $parameterEstimateParetto['k']);
                $jsonParettoProbabilityDistributionData = json_encode($parettoProbabilityDistributionData);
                $parettoDensityProbabilityDistributionData = AnalysisData::getDataForParettoDensityProbabilityDistribution($variationSeries, $parameterEstimateParetto['a'], $parameterEstimateParetto['k']);
                $jsonParettoDensityProbabilityDistributionData = json_encode($parettoDensityProbabilityDistributionData);
            }

            $kalmagorovData = AnalysisData::getKolmogorovCriterion($variationSeries);

            $probabilityPaperParettoData = AnalysisData::getDataForProbabilityPaperParetto($variationSeries);
            $jsonProbabilityPaperParettoData = json_encode($probabilityPaperParettoData);
        }
        $viewPath = ROOT . '/views/index/index.php';
        include_once $viewPath;
    }
}