function chartLeadingListeningTestResult(d1, d2, d3, d4, d5, d6) {
    $('#leading-listening-test-result').highcharts({
        chart: {
            type: 'column',
            marginTop: 5,
            marginLeft: 130,
            marginRight: 25,
            marginBottom: 0,
            backgroundColor: '#F2f2f2'
        },
        title: {
            style: {display: 'none'},
            text: ''
        },
        legend: {
            enabled: false
        },
        credits: {
            enabled: false
        },
        xAxis: {
            labels: {enabled: false},
            gridLineColor: 'transparent'
        },
        yAxis: {
            min: 0,
            max: 1700,
            title: {
                style: {
                    display: 'none'
                },
                text: ''
            },
            labels: {enabled: false},
            gridLineColor: 'transparent'
        },
        plotOptions: {
            column: {
                pointPadding: 0,
                borderWidth: 0,
                groupPadding: 0
            },
            series: {
                pointWidth: 16
            }
        },
        tooltip: {
            formatter: function () {
                return '<span style="color: ' + this.series.color + ';">● </span>' + this.y + ' スコア';
            }
        },
        series: [
            {
                name: 'スコア/満点スコア',
                data: [d1, d2, d3],
                pointPlacement: 0.32,
                color: "#898989"
            },
            {
                name: '団体平均スコア',
                data: [d4, d5, d6],
                color: "#f79646"
            }
        ]
    });
}
function chartSectoralaveragePercentage(d1, d2, d3, d4, d5, d6) {
    $('#sector-average-percentage').highcharts({
        chart: {
            type: 'column',
            marginTop: 23,
            marginLeft: 125,
            marginRight: 45,
            marginBottom: 0,
            backgroundColor: '#F2f2f2'
        },
        title: {
            style: {display: 'none'},
            text: ''
        },
        legend: {
            enabled: false
        },
        xAxis: {
            categories: ['Apples', 'Oranges', 'Oranges'],
            labels: {enabled: false},
            gridLineWidth: 0,
            tickWidth: 0,
            lineWidth: 0
        },
        yAxis: {
            min: 0,
            max: 100,
            title: {
                style: {display: 'none'},
                text: ''
            },
            labels: {enabled: false},
            gridLineWidth: 0,
            tickWidth: 0,
            lineWidth: 0
        },
        plotOptions: {
            column: {
                pointPadding: 0,
                borderWidth: 0,
                groupPadding: 0
            },
            series: {
                pointWidth: 16
            }
        },
        credits: {
            enabled: false
        },
        tooltip: {
            formatter: function () {
                return '<span style="color: ' + this.series.color + ';">● </span>' + this.y + '%';
            }
        },
        series: [
            {
                name: '団体平均正答率',
                data: [d1, d2, d3],
                pointPlacement: 0.31,
                color: "#898989"
            },
            {
                name: '正答率',
                data: [d4, d5, d6],
                color: "#f79646"
            }
        ]
    });
}