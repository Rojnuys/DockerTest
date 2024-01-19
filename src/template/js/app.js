const variationClassesElement = document.getElementById('jsonVariationClasses');
const kernelEstimationOfTheDensityElement = document.getElementById('jsonKernelEstimationOfTheDensity');
const variationSeriesElement = document.getElementById('jsonVariationSeries');
const abnormalElement = document.getElementById('jsonAbnormal');
const probabilityPaperDataElement = document.getElementById('jsonProbabilityPaperData');
const probabilityPaperParettoDataElement = document.getElementById('jsonProbabilityPaperParettoData');
const parettoProbabilityDistributionDataElement = document.getElementById('jsonParettoProbabilityDistributionData');
const parettoDensityProbabilityDistributionDataElement = document.getElementById('jsonParettoDensityProbabilityDistributionData');

window.addEventListener('load', createCharts);
window.addEventListener('resize', createCharts);

function createCharts() {
    const windowWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
    let graphicContainerWidth = 0;
    if (variationClassesElement || kernelEstimationOfTheDensityElement || variationSeriesElement || abnormalElement) {
        graphicContainerWidth = document.querySelector('.graphic-container').clientWidth;
    }

    // Розміри SVG-елемента і графіків
    let width = windowWidth < 1000 ? graphicContainerWidth : graphicContainerWidth  / 2;
    let height = 400;
    const margin = {top: 20, right: 30, bottom: 30, left: 40};

    if (variationClassesElement && kernelEstimationOfTheDensityElement) {
        const variationClasses = JSON.parse(variationClassesElement.innerText);
        const kernelEstimationOfTheDensity = JSON.parse(kernelEstimationOfTheDensityElement.innerText);

        // Дані для гістограми (початкова координата x, ширина, висота кожного стовпця)
        const data = variationClasses.map(vC => ({
            x: vC.min,
            width: vC.max - vC.min,
            height: vC.frequency,
        }));

        // Дані для лінійного графіку (x, y)
        const lineData = kernelEstimationOfTheDensity.map(ked => ({
            x: ked.value,
            y: ked.result,
        }));

        // Видалити всі попередні графіки
        d3.select('#histogram').selectAll('*').remove();

        // Створіть SVG-елемент
        const svg = d3.select('#histogram')
            .attr('width', width)
            .attr('height', height);

        // Масштаби для осей x і y
        const xScale = d3.scaleLinear()
            .domain([d3.min(data, d => d.x), d3.max(data, d => d.x + d.width)])
            .range([margin.left, width - margin.right]);

        const yScale = d3.scaleLinear()
            .domain([0, d3.max(data, d => d.height)])
            .nice()
            .range([height - margin.bottom, margin.top]);

        const lineXScale = d3.scaleLinear()
            .domain([d3.min(data, d => d.x), d3.max(lineData, d => d.x)])
            .range([margin.left, width - margin.right]);

        const lineYScale = d3.scaleLinear()
            .domain([0, d3.max(lineData, d => d.y)])
            .nice()
            .range([height - margin.bottom, margin.top]);

        // Побудуйте стовпці гістограми
        svg.selectAll('rect')
            .data(data)
            .enter()
            .append('rect')
            .attr('x', d => xScale(d.x))
            .attr('y', d => yScale(d.height))
            .attr('width', d => xScale(d.x + d.width) - xScale(d.x)) // Вираховуємо правильну ширину
            .attr('height', d => height - margin.bottom - yScale(d.height))
            .attr('fill', 'steelblue');

        // Побудуйте лінійний графік
        const line = d3.line()
            .x(d => lineXScale(d.x))
            .y(d => lineYScale(d.y));

        svg.append('path')
            .datum(lineData)
            .attr('class', 'line')
            .attr('fill', 'none')
            .attr('stroke', 'red') // Колір лінії
            .attr('stroke-width', 2) // Ширина лінії
            .attr('d', line);

        if (parettoDensityProbabilityDistributionDataElement.innerText !== "") {
            const parettoDensityProbabilityDistributionData = JSON.parse(parettoDensityProbabilityDistributionDataElement.innerText);
            const parettoData = parettoDensityProbabilityDistributionData.map(item => ({
                x: item.x,
                y: item.y,
            }));

            const lineXScale = d3.scaleLinear()
                .domain([d3.min(data, d => d.x), d3.max(parettoData, d => d.x)])
                .range([margin.left, width - margin.right]);

            const lineYScale = d3.scaleLinear()
                .domain([0, d3.max(parettoData, d => d.y)])
                .nice()
                .range([height - margin.bottom, margin.top]);

            const line = d3.line()
                .x(d => lineXScale(d.x))
                .y(d => lineYScale(d.y));

            svg.append('path')
                .datum(parettoData)
                .attr('class', 'line')
                .attr('fill', 'none')
                .attr('stroke', 'blueviolet') // Колір лінії
                .attr('stroke-width', 2) // Ширина лінії
                .attr('d', line);
        }

        // Додайте вісі
        svg.append('g')
            .attr('class', 'x-axis')
            .attr('transform', `translate(0,${height - margin.bottom})`)
            .call(d3.axisBottom(xScale));

        svg.append('g')
            .attr('class', 'y-axis')
            .attr('transform', `translate(${margin.left},0)`)
            .call(d3.axisLeft(yScale));

        svg.append("text")
            .attr("class", "chart-title")
            .attr("x", width / 2)
            .attr("y", margin.top)
            .style("text-anchor", "middle")
            .text('Classes histogram | KDE');

        svg.append("text")
            .attr("x", width / 2)
            .attr("y", height)
            .style("text-anchor", "middle")
            .text("X")
            .attr("class", "x-axis-label");

        svg.append("text")
            .attr("transform", "rotate(-90)")
            .attr("x", -height / 2)
            .attr("y", 8)
            .style("text-anchor", "middle")
            .text("Relative frequency")
            .attr("class", "x-axis-label");
    }

    if (variationSeriesElement) {
        const variationSeries = JSON.parse(variationSeriesElement.innerText);

        // Дані для гістограми (x - значення, у - розрахунок функції ecdf)
        const ecdfData = variationSeries.map(vS => ({
            x: vS.value,
            y: vS.ecdf,
        }));

        width = width - margin.left - margin.right;
        height = height - margin.top - margin.bottom;

        // Видалити всі попередні графіки
        d3.select('#histogram2').selectAll('*').remove();

        // Створіть SVG-елемент
        const svg = d3.select('#histogram2')
            .attr('width', width + margin.left + margin.right)
            .attr('height', height + margin.top + margin.bottom)
            .append('g')
            .attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

        //Масштаби для осей x і y
        const x = d3.scaleLinear()
            .domain([d3.min(ecdfData, function (d) { return d.x; }), d3.max(ecdfData, function (d) { return d.x; })])
            .range([0, width]);

        const y = d3.scaleLinear()
            .domain([0, 1])
            .range([height, 0]);

        // Створіть стрілкову функцію для побудови стрілок
        function createArrow(svg, x1, x2, y) {
            var arrowHeight = 8; // Висота стрілки

            svg.append('line')
                .attr('class', 'arrow-line')
                .attr('x1', x1)
                .attr('y1', y)
                .attr('x2', x2)
                .attr('y2', y)
                .attr('stroke', 'steelblue')
                .attr('stroke-width', 2);

            // Трикутник на кінці стрілки
            var triangle = [
                [x2, y - arrowHeight / 2],
                [x2, y + arrowHeight / 2],
                [x2 + arrowHeight / 2, y]
            ];

            svg.append('polygon')
                .attr('class', 'arrow-triangle')
                .attr('points', triangle.map(function (point) { return point.join(','); }).join(' '))
                .attr('fill', 'steelblue');
        }

        // Побудуйте стрілки для кожної точки на графіку
        ecdfData.forEach(function (d, i) {
            if (i === 0) {
                createArrow(svg, 0, x(d.x), y(d.y));
            } else {
                createArrow(svg, x(ecdfData[i - 1].x), x(d.x), y(d.y));
            }
        });

        if (parettoProbabilityDistributionDataElement.innerText !== "") {
            const parettoProbabilityDistributionData = JSON.parse(parettoProbabilityDistributionDataElement.innerText);
            const parettoData = parettoProbabilityDistributionData.map(item => ({
                x: item.x,
                y: item.y,
            }));

            // const lineXScale = d3.scaleLinear()
            //     .domain([d3.min(ecdfData, d => d.x), d3.max(parettoData, d => d.x)])
            //     .range([margin.left, width - margin.right]);

            const xMin = Math.min(d3.min(ecdfData, d => d.x), d3.min(parettoData, d => d.x));
            const xMax = Math.max(d3.max(ecdfData, d => d.x), d3.max(parettoData, d => d.x));

            const lineXScale = d3.scaleLinear()
                .domain([xMin, xMax])
                .range([0, width]);

            const lineYScale = d3.scaleLinear()
                .domain([0, 1])
                .range([height, 0]);

            const line = d3.line()
                .x(d => lineXScale(d.x))
                .y(d => lineYScale(d.y));

            svg.append('path')
                .datum(parettoData)
                .attr('class', 'line')
                .attr('fill', 'none')
                .attr('stroke', 'blueviolet') // Колір лінії
                .attr('stroke-width', 2) // Ширина лінії
                .attr('d', line);
        }

        // Додайте осі
        svg.append('g')
            .attr('class', 'x-axis')
            .attr('transform', `translate(0,${height})`)
            .call(d3.axisBottom(x))
            .selectAll('text')
            .attr('class', 'axis-label');

        svg.append('g')
            .attr('class', 'y-axis')
            .call(d3.axisLeft(y));

        svg.append("text")
            .attr("class", "chart-title")
            .attr("x", width / 2)
            .attr("y", 0)
            .style("text-anchor", "middle")
            .text('Variation Series ECDF');

        svg.append("text")
            .attr("x", width / 2)
            .attr("y", height + margin.top + 10)
            .style("text-anchor", "middle")
            .text("X")
            .attr("class", "x-axis-label");

        svg.append("text")
            .attr("transform", "rotate(-90)")
            .attr("x", -height / 2)
            .attr("y", -margin.left / 2 - 5)
            .style("text-anchor", "middle")
            .text("ecdf")
            .attr("class", "x-axis-label");
    }

    if (abnormalElement) {
        const abnormal = JSON.parse(abnormalElement.innerText);

        // Дані для гістограми (x - значення, у - розрахунок функції ecdf)
        const data = abnormal.sortedData.map((data, index) => ({
            x: index + 1,
            y: parseFloat(data),
        }));

        // Видалити всі попередні графіки
        d3.select('#histogram3').selectAll('*').remove();

        width = graphicContainerWidth;

        // Створіть SVG-елемент
        const svg = d3.select('#histogram3')
            .attr('width', width)
            .attr('height', height)

        //Масштаби для осей x і y
        const x = d3.scaleLinear()
            .domain([d3.min(data, function (d) { return d.x; }), d3.max(data, function (d) { return d.x; })])
            .range([margin.left, width - margin.right]);

        const y = d3.scaleLinear()
            .domain([d3.min([...data, {y: abnormal.normal[0]}], function (d) { return d.y; }), d3.max([...data, {y: abnormal.normal[1]}], function (d) { return d.y; })])
            .range([height - margin.bottom, margin.top]);

        // Побудуйте прямоугольники гістограми
        svg.selectAll('rect')
            .data(data)
            .enter()
            .append('rect')
            .attr('x', d => x(d.x))
            .attr('y', d => y(d.y))
            .attr('width', 0)
            .attr('height', d => height - margin.bottom - y(d.y))

        // Додайте точки поверх графіку гістограми
        svg.selectAll('circle')
            .data(data)
            .enter()
            .append('circle')
            .attr('cx', d => x(d.x))
            .attr('cy', d => y(d.y))
            .attr('r', 3)
            .style('fill', 'steelblue');

        // Додайте першу обмежувальну лінію (min normal values)
        svg.append('line')
            .attr('x1', margin.left)
            .attr('y1', y(abnormal.normal[0]))
            .attr('x2', width - margin.right)
            .attr('y2', y(abnormal.normal[0]))
            .attr('stroke-width', 2)
            .style('stroke', 'red');

        // Додайте другу обмежувальну лінію (max normal values)
        svg.append('line')
            .attr('x1', margin.left)
            .attr('y1', y(abnormal.normal[1]))
            .attr('x2', width - margin.right)
            .attr('y2', y(abnormal.normal[1]))
            .attr('stroke-width', 2)
            .style('stroke', 'red');

        // Додайте осі
        svg.append('g')
            .attr('class', 'x-axis')
            .attr('transform', `translate(0,${height - margin.bottom})`)
            .call(d3.axisBottom(x))

        svg.append('g')
            .attr('class', 'y-axis')
            .attr('transform', `translate(${margin.left},0)`)
            .call(d3.axisLeft(y));

        svg.append("text")
            .attr("class", "chart-title")
            .attr("x", width / 2)
            .attr("y", margin.top)
            .style("text-anchor", "middle")
            .text('Значення з границями аномалій');

        svg.append("text")
            .attr("x", width / 2)
            .attr("y", height)
            .style("text-anchor", "middle")
            .text("I")
            .attr("class", "x-axis-label");

        svg.append("text")
            .attr("transform", "rotate(-90)")
            .attr("x", -height / 2)
            .attr("y", 20)
            .style("text-anchor", "middle")
            .text("X")
            .attr("class", "x-axis-label");
    }

    if (probabilityPaperDataElement) {
        const probabilityPaperData = JSON.parse(probabilityPaperDataElement.innerText);
        probabilityPaperData.pop();

        // Дані для гістограми (x - значення, у - квантиль )
        const data = probabilityPaperData.map(item => ({
            x: item.value,
            y: parseFloat(item.u),
        }));

        // Видалити всі попередні графіки
        d3.select('#histogram4').selectAll('*').remove();

        width = graphicContainerWidth;

        // Створіть SVG-елемент
        const svg = d3.select('#histogram4')
            .attr('width', width)
            .attr('height', height)

        //Масштаби для осей x і y
        const x = d3.scaleLinear()
            .domain([d3.min(data, function (d) { return d.x; }), d3.max(data, function (d) { return d.x; })])
            .range([margin.left, width - margin.right]);

        const y = d3.scaleLinear()
            .domain([d3.min(data, function (d) { return d.y; }), d3.max(data, function (d) { return d.y; })])
            .range([height - margin.bottom, margin.top]);

        // Побудуйте прямоугольники гістограми
        svg.selectAll('rect')
            .data(data)
            .enter()
            .append('rect')
            .attr('x', d => x(d.x))
            .attr('y', d => y(d.y))
            .attr('width', 0)
            .attr('height', d => height - margin.bottom - d.y)

        // Додайте точки поверх графіку гістограми
        svg.selectAll('circle')
            .data(data)
            .enter()
            .append('circle')
            .attr('cx', d => x(d.x))
            .attr('cy', d => y(d.y))
            .attr('r', 3)
            .style('fill', 'steelblue');

        // Додайте лінію
        svg.append('line')
            .attr('x1', x(d3.min(data, function (d) { return d.x; })))
            .attr('y1', y(d3.min(data, function (d) { return d.y; })))
            .attr('x2', x(d3.max(data, function (d) { return d.x; })))
            .attr('y2', y(d3.max(data, function (d) { return d.y; })))
            .attr('stroke-width', 2)
            .style('stroke', 'red');

        // Додайте осі
        svg.append('g')
            .attr('class', 'x-axis')
            .attr('transform', `translate(0,${height - margin.bottom})`)
            .call(d3.axisBottom(x))

        svg.append('g')
            .attr('class', 'y-axis')
            .attr('transform', `translate(${margin.left},0)`)
            .call(d3.axisLeft(y));

        svg.append("text")
            .attr("x", width / 2)
            .attr("y", height)
            .style("text-anchor", "middle")
            .text("X")
            .attr("class", "x-axis-label");

        svg.append("text")
            .attr("transform", "rotate(-90)")
            .attr("x", -height / 2)
            .attr("y", 10)
            .style("text-anchor", "middle")
            .text("UFn(x)")
            .attr("class", "x-axis-label");
    }

    if (probabilityPaperParettoDataElement) {
        const probabilityPaperParettoData = JSON.parse(probabilityPaperParettoDataElement.innerText);
        probabilityPaperParettoData.pop();

        // Дані для гістограми (x - значення, у - квантиль )
        const data = probabilityPaperParettoData.map(item => ({
            x: item.t,
            y: item.z,
        }));

        // Видалити всі попередні графіки
        d3.select('#histogram5').selectAll('*').remove();

        width = graphicContainerWidth;

        // Створіть SVG-елемент
        const svg = d3.select('#histogram5')
            .attr('width', width)
            .attr('height', height)

        //Масштаби для осей x і y
        const x = d3.scaleLinear()
            .domain([d3.min(data, function (d) { return d.x; }), d3.max(data, function (d) { return d.x; })])
            .range([margin.left, width - margin.right]);

        const y = d3.scaleLinear()
            .domain([d3.min(data, function (d) { return d.y; }), d3.max(data, function (d) { return d.y; })])
            .range([height - margin.bottom, margin.top]);

        // Побудуйте прямоугольники гістограми
        svg.selectAll('rect')
            .data(data)
            .enter()
            .append('rect')
            .attr('x', d => x(d.x))
            .attr('y', d => y(d.y))
            .attr('width', 0)
            .attr('height', d => height - margin.bottom - d.y)

        // Додайте точки поверх графіку гістограми
        svg.selectAll('circle')
            .data(data)
            .enter()
            .append('circle')
            .attr('cx', d => x(d.x))
            .attr('cy', d => y(d.y))
            .attr('r', 3)
            .style('fill', 'steelblue');

        // Додайте лінію
        // svg.append('line')
        //     .attr('x1', x(d3.min(data, function (d) { return d.x; })))
        //     .attr('y1', y(d3.min(data, function (d) { return d.y; })))
        //     .attr('x2', x(d3.max(data, function (d) { return d.x; })))
        //     .attr('y2', y(d3.max(data, function (d) { return d.y; })))
        //     .attr('stroke-width', 2)
        //     .style('stroke', 'red');

        // Додайте осі
        svg.append('g')
            .attr('class', 'x-axis')
            .attr('transform', `translate(0,${height - margin.bottom})`)
            .call(d3.axisBottom(x))

        svg.append('g')
            .attr('class', 'y-axis')
            .attr('transform', `translate(${margin.left},0)`)
            .call(d3.axisLeft(y));

        svg.append("text")
            .attr("x", width / 2)
            .attr("y", height)
            .style("text-anchor", "middle")
            .text("t = -ln(x)")
            .attr("class", "x-axis-label");

        svg.append("text")
            .attr("transform", "rotate(-90)")
            .attr("x", -height / 2)
            .attr("y", 10)
            .style("text-anchor", "middle")
            .text("z = ln(1 - F(x))")
            .attr("class", "x-axis-label");
    }
}