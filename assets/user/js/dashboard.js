function initTotalRevenueChart(selector, route) {
    const el = document.querySelector(selector);
    if (!el) return;

    fetch(route)
        .then(res => res.json())
        .then(chartData => {
            const options = {
                series: chartData.series,
                chart: {
                    height: 300,
                    type: "bar",
                    stacked: true,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: "30%",
                        borderRadius: 8,
                        startingShape: "rounded",
                        endingShape: "rounded",
                        borderRadiusApplication: "around"
                    }
                },
                colors: [config.colors.primary, config.colors.info],
                dataLabels: { enabled: false },
                stroke: {
                    curve: "smooth",
                    width: 6,
                    lineCap: "round",
                    colors: [config.colors.cardColor]
                },
                legend: {
                    show: true,
                    horizontalAlign: "left",
                    position: "top",
                    markers: { size: 4, radius: 12, shape: "circle", strokeWidth: 0 },
                    fontSize: "13px",
                    fontFamily: config.fontFamily,
                    fontWeight: 400,
                    labels: { colors: config.colors.bodyColor },
                    itemMargin: { horizontal: 10 }
                },
                grid: {
                    strokeDashArray: 7,
                    borderColor: config.colors.borderColor,
                    padding: { top: 0, bottom: -8, left: 20, right: 20 }
                },
                fill: { opacity: [1, 1] },
                xaxis: {
                    categories: chartData.categories,
                    labels: {
                        style: {
                            fontSize: "13px",
                            fontFamily: config.fontFamily,
                            colors: config.colors.bodyColor
                        }
                    },
                    axisTicks: { show: false },
                    axisBorder: { show: false }
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: "13px",
                            fontFamily: config.fontFamily,
                            colors: config.colors.bodyColor
                        }
                    }
                },
                responsive: [
                    {
                        breakpoint: 1700,
                        options: { plotOptions: { bar: { borderRadius: 10, columnWidth: "35%" } } }
                    },
                    {
                        breakpoint: 1440,
                        options: { plotOptions: { bar: { borderRadius: 12, columnWidth: "43%" } } }
                    },
                    {
                        breakpoint: 1300,
                        options: { plotOptions: { bar: { borderRadius: 11, columnWidth: "45%" } } }
                    },
                    {
                        breakpoint: 1200,
                        options: { plotOptions: { bar: { borderRadius: 11, columnWidth: "37%" } } }
                    },
                    {
                        breakpoint: 1040,
                        options: { plotOptions: { bar: { borderRadius: 12, columnWidth: "45%" } } }
                    },
                    {
                        breakpoint: 991,
                        options: { plotOptions: { bar: { borderRadius: 12, columnWidth: "33%" } } }
                    },
                    {
                        breakpoint: 768,
                        options: { plotOptions: { bar: { borderRadius: 11, columnWidth: "28%" } } }
                    },
                    {
                        breakpoint: 640,
                        options: { plotOptions: { bar: { borderRadius: 11, columnWidth: "30%" } } }
                    },
                    {
                        breakpoint: 576,
                        options: { plotOptions: { bar: { borderRadius: 10, columnWidth: "38%" } } }
                    },
                    {
                        breakpoint: 440,
                        options: { plotOptions: { bar: { borderRadius: 10, columnWidth: "50%" } } }
                    },
                    {
                        breakpoint: 380,
                        options: { plotOptions: { bar: { borderRadius: 9, columnWidth: "60%" } } }
                    }
                ],
                states: {
                    hover: { filter: { type: "none" } },
                    active: { filter: { type: "none" } }
                }
            };

            new ApexCharts(el, options).render();
        });
}

function initGrowthChart(targetSelector, percentage=62) {
    const el = document.querySelector(targetSelector);
    if (!el) return;

    const options = {
        series: [percentage],
        labels: [Lang.transfer],
        chart: {
            height: 210,
            type: "radialBar"
        },
        plotOptions: {
            radialBar: {
                size: 150,
                offsetY: 10,
                startAngle: -150,
                endAngle: 150,
                hollow: { size: "55%" },
                track: {
                    background: config.colors.cardColor,
                    strokeWidth: "100%"
                },
                dataLabels: {
                    name: {
                        offsetY: 15,
                        color: config.colors.bodyColor,
                        fontSize: "15px",
                        fontWeight: "500",
                        fontFamily: config.fontFamily
                    },
                    value: {
                        offsetY: -25,
                        color: config.colors.headingColor,
                        fontSize: "22px",
                        fontWeight: "500",
                        fontFamily: config.fontFamily
                    }
                }
            }
        },
        colors: [config.colors.primary],
        fill: {
            type: "gradient",
            gradient: {
                shade: "dark",
                shadeIntensity: 0.5,
                gradientToColors: [config.colors.primary],
                inverseColors: true,
                opacityFrom: 1,
                opacityTo: 0.6,
                stops: [30, 70, 100]
            }
        },
        stroke: { dashArray: 5 },
        grid: {
            padding: { top: -35, bottom: -10 }
        },
        states: {
            hover: { filter: { type: "none" } },
            active: { filter: { type: "none" } }
        }
    };

    new ApexCharts(el, options).render();
}

function initRevenueChart(selector, dailyData = []) {
    const el = document.querySelector(selector);
    if (!el) return;

    const options = {
        chart: {
            height: 95,
            type: "bar",
            toolbar: { show: false }
        },
        plotOptions: {
            bar: {
                barHeight: "80%",
                columnWidth: "75%",
                startingShape: "rounded",
                endingShape: "rounded",
                borderRadius: 4,
                distributed: true
            }
        },
        grid: {
            show: false,
            padding: { top: -20, bottom: -12, left: -10, right: 0 }
        },
        colors: [
            config.colors_label.primary,
            config.colors_label.primary,
            config.colors_label.primary,
            config.colors_label.primary,
            config.colors.primary,
            config.colors_label.primary,
            config.colors_label.primary
        ],
        dataLabels: { enabled: false },
        series: [{
            name: Lang.count,
            data: dailyData.length ? dailyData : [0, 0, 0, 0, 0, 0, 0] // fallback
        }],
        legend: { show: false },
        xaxis: {
            categories: ["S", "M", "T", "W", "T", "F", "S"], // Sunday to Saturday
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
                style: {
                    colors: config.colors.text,
                    fontSize: "13px"
                }
            }
        },
        yaxis: {
            labels: { show: false }
        }
    };

    new ApexCharts(el, options).render();
}

function initTransferReportChart(selector,route) {
    const el = document.querySelector(selector);
    if (!el) return;

    fetchWithBlock(route, { meta: { blockId: 'transferChart' } })
        .then(data => {
            const options = {
                chart: {
                    height: 75,
                    width: 240,
                    type: "line",
                    toolbar: { show: false },
                    dropShadow: {
                        enabled: true,
                        top: 10,
                        left: 5,
                        blur: 3,
                        color: config.colors.warning,
                        opacity: 0.15
                    },
                    sparkline: { enabled: true }
                },
                grid: {
                    show: false,
                    padding: { right: 8 }
                },
                colors: [config.colors.warning],
                dataLabels: { enabled: false },
                stroke: {
                    width: 5,
                    curve: "smooth"
                },
                series: [{
                    name: Lang.amount,
                    data: data.data
                }],
                xaxis: {
                    categories: data.labels, // month names here
                    labels: {
                        style: {
                            // colors: config.colors.bodyColor,
                            fontSize: '13px'
                        }
                    },
                    lines: { show: false },
                    axisBorder: { show: false }
                },
                yaxis: { show: false }
            };

            new ApexCharts(el, options).render();
        });
}

function initIncomeChart(selector, chartData) {
    const el = document.querySelector(selector);
    if (!el) return;

    if (el._chart) {
        el._chart.destroy();
    }

    const values = chartData.amounts;
    const maxValue = Math.max(...values) || 10; // fallback 10
    const minValue = Math.min(...values) || 0;
    const roundedMax = Math.ceil(maxValue / 10) * 10;

    const options = {
        series: [{ name : Lang.amount,data: values }],
        chart: {
            height: 250,
            parentHeightOffset: 0,
            parentWidthOffset: 0,
            toolbar: { show: false },
            type: "area"
        },
        dataLabels: { enabled: false },
        stroke: { width: 3, curve: "smooth" },
        legend: { show: false },
        markers: {
            size: 6,
            colors: "transparent",
            strokeColors: "transparent",
            strokeWidth: 4,
            discrete: [{
                fillColor: config.colors.white,
                seriesIndex: 0,
                dataPointIndex: values.length - 1, // last point
                strokeColor: config.colors.primary,
                strokeWidth: 2,
                size: 6,
                radius: 8
            }],
            offsetX: -1,
            hover: { size: 7 }
        },
        colors: [config.colors.primary],
        fill: {
            type: "gradient",
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.3,
                gradientToColors: [config.colors.cardColor],
                opacityTo: 0.3,
                stops: [0, 100]
            }
        },
        grid: {
            borderColor: config.colors.borderColor,
            strokeDashArray: 8,
            padding: { top: -20, bottom: -8, left: 0, right: 8 }
        },
        xaxis: {
            categories: chartData.labels,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
                show: true,
                style: {
                    fontSize: "13px",
                    colors: config.colors.text
                }
            }
        },
        yaxis: {
            tickAmount: 4,
            forceNiceScale: true,
            decimalsInFloat: 0,
            min: 0, // max: roundedMax,
            labels: {
                show: true,
                style: {
                    fontSize: "13px",
                    colors: config.colors.text
                },
                formatter: function (value) {
                    return currencyPosition(value)
                    // return Math.round(value);
                }
            },
        }
    };

    const chart = new ApexCharts(el, options);
    chart.render();
    el._chart = chart;
}

