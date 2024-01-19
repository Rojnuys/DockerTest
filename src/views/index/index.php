<?php require_once ROOT . '/views/layouts/header.php'; ?>
<?php if (!isset($_SESSION['data'])) { ?>
    <h2 class="pt-5">Upload data to continue ...</h2>
<?php } else { ?>
    <h2 class="pt-5">Варіаційний ряд</h2>
    <div class="scroll-container">
        <table class="table table-dark table-striped">
            <tr class="scroll-container-head">
                <th>#</th>
                <th>Value</th>
                <th>Frequency</th>
                <th>Relative frequency</th>
                <th>ECDF</th>
            </tr>
            <?php $i = 0; foreach ($variationSeries as $variationRow) { $i++; ?>
                <tr>
                    <td><?= $i ?></td>
                    <td><?= $variationRow['value'] ?></td>
                    <td><?= $variationRow['count'] ?></td>
                    <td><?= $variationRow['frequency'] ?></td>
                    <td><?= $variationRow['ecdf'] ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>

    <h2 class="pt-5">Варіаційний ряд розбитий на (<?= $m ?>) класів</h2>
    <div class="scroll-container">
        <table class="table table-dark table-striped">
            <tr class="scroll-container-head">
                <th>#</th>
                <th>Class</th>
                <th>Frequency</th>
                <th>Relative frequency</th>
                <th>ECDF</th>
            </tr>
            <?php $i = 0; foreach ($variationClasses as $variationRow) { $i++; ?>
                <tr>
                    <td><?= $i ?></td>
                    <td>[ <?= $variationRow['min'] ?> ; <?= $variationRow['max'] ?>
                        <?php if($i == count($variationClasses)) { ?>
                            ]
                        <?php } else { ?>
                            )
                        <?php } ?></td>
                    <td><?= $variationRow['count'] ?></td>
                    <td><?= $variationRow['frequency'] ?></td>
                    <td><?= $variationRow['ecdf'] ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
    <form action="/" method="get" class="mb-5 mt-3">
        <div class="mb-3">
            <label for="m" class="form-label">Count of classes:</label>
            <input class="form-control" type="text" name="m" id="m" placeholder="Enter count of classes:" value="<?= count($variationClasses) ?>">
        </div>
        <div>
            <label for="b" class="form-label">Bandwidth:</label>
            <input class="form-control" type="text" name="b" id="b" placeholder="Enter bandwidth:" value="<?= isset($_GET['b']) ? $_GET['b'] : '' ?>">
        </div>
        <input class="btn btn-dark mt-3" type="submit" value="Update">
    </form>

    <div style="display: none" id="jsonVariationClasses"><?= $jsonVariationClasses ?></div>
    <div style="display: none" id="jsonKernelEstimationOfTheDensity"><?= $jsonKernelEstimationOfTheDensity ?></div>
    <div style="display: none" id="jsonVariationSeries"><?= $jsonVariationSeries ?></div>

    <div class="graphic-container">
        <svg id="histogram"></svg>
        <svg id="histogram2"></svg>
    </div>

    <h2 class="mt-5">Незсунені кількісні характеристики показника</h2>
    <div class="scroll-container">
        <table class="table table-dark table-striped">
            <tr>
                <th>Характеристика</th>
                <th>Оцінка</th>
                <th>Середньоквадратичне
                    відхилення оцінки</th>
                <th>95% довірчий інтервал
                    для характеристики</th>
            </tr>
            <tr>
                <td>Середнє арифметичне</td>
                <td><?= $crts['avg'][0] ?></td>
                <td><?= $crts['avg'][1] ?></td>
                <td>[<?= $crts['avg'][2] ?> ; <?= $crts['avg'][3] ?>]</td>
            </tr>
            <tr>
                <td>Медіана</td>
                <td><?= $crts['med'][0] ?></td>
                <td><?= $crts['med'][1] ?></td>
                <td>[<?= $crts['med'][2] ?> ; <?= $crts['med'][3] ?>]</td>
            </tr>
            <tr>
                <td>Середньоквадратичне відхилення</td>
                <td><?= $crts['s'][0] ?></td>
                <td><?= $crts['s'][1] ?></td>
                <td>[<?= $crts['s'][2] ?> ; <?= $crts['s'][3] ?>]</td>
            </tr>
            <tr>
                <td>Коефіцієнт асиметрії</td>
                <td><?= $crts['a'][0] ?></td>
                <td><?= $crts['a'][1] ?></td>
                <td>[<?= $crts['a'][2] ?> ; <?= $crts['a'][3] ?>]</td>
            </tr>
            <tr>
                <td>Коефіцієнт ексцесу</td>
                <td><?= $crts['e'][0] ?></td>
                <td><?= $crts['e'][1] ?></td>
                <td>[<?= $crts['e'][2] ?> ; <?= $crts['e'][3] ?>]</td>
            </tr>
            <tr>
                <td>Мінімум</td>
                <td><?= $crts['min'] ?></td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td>Максимум</td>
                <td><?= $crts['max'] ?></td>
                <td>-</td>
                <td>-</td>
            </tr>
        </table>
    </div>

    <h2 class="mt-5">Аномалії</h2>
    <table class="table table-dark table-striped mb-5">
        <tr>
            <td>Діапазон значень без аномалій</td>
            <td><?= '[' . $crts['normal'][0] . ' ; ' . $crts['normal'][1] . ']' ?></td>
        </tr>
    </table>

    <div style="display: none" id="jsonAbnormal"><?= $jsonAbnormal ?></div>

    <div class="graphic-container">
        <svg id="histogram3"></svg>
    </div>
    <form action="/" method="post" class="mt-5">
        <input type="hidden" name="abnormal" value="1">
        <input type="submit" value="Remove abnormal values" class="btn btn-dark">
    </form>

    <h2 class="mt-5">Ідентифікувати нормальний розподіл</h2>
    <h4 class="mt-5">Ідентифікація на основі коефіцієнтів асиметрії та ексцесу </h4>
    <table class="table table-dark table-striped mb-5">
        <tr>
            <td>t<sub>A</sub> = <?= $crts['identBaseAE'][0] ?></td>
            <td>t<sub>1-a/2,v</sub> = <?= $crts['identBaseAE'][2] ?></td>
            <td>|t<sub>A</sub>| &lt;= t<sub>1-a/2,v</sub> = <?= $crts['identBaseAE'][3] ? 'Так' : 'Hi'  ?></td>
        </tr>
        <tr>
            <td>t<sub>E</sub> = <?= $crts['identBaseAE'][1] ?></td>
            <td>t<sub>1-a/2,v</sub> = <?= $crts['identBaseAE'][2] ?></td>
            <td>|t<sub>E</sub>| &lt;= t<sub>1-a/2,v</sub> = <?= $crts['identBaseAE'][4] ? 'Так' : 'Hi'  ?></td>
        </tr>
        <tr>
            <td colspan="3">
                <?= $crts['identBaseAE'][3] && $crts['identBaseAE'][4]
                    ? 'Ідентифіковано нормальний розподіл' : 'Нормальний розподіл не ідентифіковано'  ?>
            </td>
        </tr>
    </table>

    <h4 class="mt-5">Ідентифікація нормального розподілу на основі ймовірнісного паперу</h4>
    <div style="display: none" id="jsonProbabilityPaperData"><?= $jsonProbabilityPaperData ?></div>

    <div class="graphic-container mb-5">
        <svg id="histogram4"></svg>
    </div>

    <h4 class="mt-5">Ідентифікація розподілу Паретто на основі ймовірнісного паперу</h4>
    <div style="display: none" id="jsonProbabilityPaperParettoData"><?= $jsonProbabilityPaperParettoData ?></div>
    <div style="display: none" id="jsonParettoProbabilityDistributionData"><?= isset($jsonParettoProbabilityDistributionData) ? $jsonParettoProbabilityDistributionData : "" ?></div>
    <div style="display: none" id="jsonParettoDensityProbabilityDistributionData"><?= isset($jsonParettoDensityProbabilityDistributionData) ? $jsonParettoDensityProbabilityDistributionData : "" ?></div>

    <div class="graphic-container mb-5">
        <svg id="histogram5"></svg>
    </div>

    <h4 class="mt-5">Оцінки параметрів розподілу Паретто методом максимальної правдоподібності</h4>
    <div class="scroll-container">
        <table class="table table-dark table-striped mb-5">
            <tr>
                <td>Параметр</td>
                <td>Значення оцінки</td>
                <td>
                    Середньоквадратичне
                    відхилення оцінки
                </td>
                <td>
                    95% довірчий інтервал
                    для параметра
                </td>
            </tr>
            <tr>
                <td>k</td>
                <td><?= round($parameterEstimateParetto['k2'], 4) ?></td>
                <td><?= round($parameterEstimateParetto['qk'], 4) ?></td>
                <td>[<?= round($parameterEstimateParetto['sk'], 4) . ';' . round($parameterEstimateParetto['ek'], 4) ?>]</td>
            </tr>
            <tr>
                <td>a</td>
                <td><?= round($parameterEstimateParetto['a'], 4) ?></td>
                <td><?= round($parameterEstimateParetto['qa'], 4) ?></td>
                <td>[<?= round($parameterEstimateParetto['sa'], 4) . ';' . round($parameterEstimateParetto['ea'], 4) ?>]</td>
            </tr>
        </table>
    </div>
    <form action="/" method="post">
        <input type="hidden" name="parettoGraphics" value="1">
        <input type="submit" value="Додати до графіків" class="btn btn-dark">
    </form>

    <h4 class="mt-5">Перевірити вірогідність відновленого розподілу (Колмогоров)</h4>
    <table class="table table-dark table-striped mb-5">
        <tr>
            <td>Статистика критерія (z)</td>
            <td><?= round($kalmagorovData['z'], 4) ?></td>
        </tr>
        <tr>
            <td>p</td>
            <td><?= round($kalmagorovData['p'], 4) ?></td>
        </tr>
        <tr>
            <td>Критичне значення</td>
            <td><?= round($kalmagorovData['cv'], 4) ?></td>
        </tr>
        <tr>
            <td colspan="2">
                <?= $kalmagorovData['p'] >= $kalmagorovData['a']
                    ? 'Вірогідність відновленого розподілу' : 'Невірогідність відновленого розподілу'  ?>
            </td>
        </tr>
    </table>
<?php } ?>
<?php require_once ROOT . '/views/layouts/footer.php'; ?>