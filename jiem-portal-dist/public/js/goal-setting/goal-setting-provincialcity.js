//CUSTOM BY THANHNX6
//http://jsfiddle.net/xakx65np/16/

$(function () {
    InitChart();
    // handlerColumnClicked();
});
// @TODO USE?
// function handlerColumnClicked() {
// $(".chart-legend li").on('click', function() {
// if ($(this).attr('data-click') == 'yes') {
// $(this).attr('data-click', 'no');
// $(this).removeAttr('style');
// } else {
// $(this).attr('data-click', 'yes');
// $(this).css({
// 'color' : 'silver'
// });
// }
// });
// }

function InitChart() {
    var pointwidth = 15;
    var pointPadding = 0.01;
    $('#chart')
            .highcharts(
                    {
                        chart: {
                            backgroundColor: '#f2f2f2',
                            height: 200,
                            marginLeft: 0,
                            //marginRight: 10,
                            marginBottom: 0,
                            type: 'column',
                            //width: 898,      
                            events: {
                                load: function (event) {
                                    $("text:contains('Highcharts.com')")
                                            .remove();
                                    // set color and trigger to custom legend
                                    var selector1 = ".highcharts-legend-item:nth-child(1)";
                                    var selector2 = ".highcharts-legend-item:nth-child(2)";
                                    var selector3 = ".highcharts-legend-item:nth-child(3)";

                                    var color1 = $(selector1).find('rect')
                                            .attr('fill');
                                    var color2 = $(selector2).find('rect')
                                            .attr('fill');
                                    var color3 = $(selector3).find('rect')
                                            .attr('fill');

                                    // set color
                                    $(
                                            ".chart-legend li:nth-child(1) .rectangle")
                                            .css({
                                                "background-color": color1
                                            });
                                    $(
                                            ".chart-legend li:nth-child(2) .rectangle")
                                            .css({
                                                "background-color": color2
                                            });
                                    $(
                                            ".chart-legend li:nth-child(3) .rectangle")
                                            .css({
                                                "background-color": color3
                                            });

                                    // bind trigger event
//									$(".chart-legend li:nth-child(1)").on(
//											'click', function() {
//												$(selector1).trigger('click');
//											});
//									$(".chart-legend li:nth-child(2)").on(
//											'click', function() {
//												$(selector2).trigger('click');
//											});
//									$(".chart-legend li:nth-child(3)").on(
//											'click', function() {
//												$(selector3).trigger('click');
//											});
                                }
                            }
                        },
                        title: {
                            text: ''
                        },
                        subtitle: {
                            text: ''
                        },
                        legend: {
                            align: 'right',
                            verticalAlign: 'top',
                            floating: true,
                            x: 400,
                            y: -50,
                            symbolWidth: 10,
                            enabled: true
                        },
                        xAxis: {
                            categories: ['0', '1', '2', '3', '4', '5', '6',
                                '7', ],
                            labels: {
                                enabled: false
                            },
                            tickWidth: 0,
                            lineColor: '#333'
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: ''
                            },
                            labels: {
                                enabled: false
                            },
                            tickWidth: 0,
                            gridLineWidth: 0
                        },
                        tooltip: {
                            headerFormat: '<span style="font-size:10px"></span><table>',
                            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>'
                                    + '<td style="padding:0"><b>{point.y:.0f}</b></td></tr>',
                            footerFormat: '</table>',
                            shared: true,
                            useHTML: true
                        },
                        plotOptions: {
                            column: {
                                pointPadding: 0.1,
                                groupPadding: 0.1,
                                pointRange: 0.5
                                //pointWidth: 0.1
                            }
                        },
                        exporting: {
                            enabled: false
                        },
                        series: [
                            {
                                name: '自校',
                                data: [0,
                                    Number(pupilPassRate[1]),
                                    Number(pupilPassRate[2]),
                                    Number(pupilPassRate[3]),
                                    Number(pupilPassRate[4]),
                                    Number(pupilPassRate[5]),
                                    Number(pupilPassRate[6]),
                                    Number(pupilPassRate[7])],
                                color: '#9dcff2',
                                pointWidth: pointwidth
                            },
                            {
                                name: String(cityName),
                                data: [0,
                                    Number(cityPassRate[1]),
                                    Number(cityPassRate[2]),
                                    Number(cityPassRate[3]),
                                    Number(cityPassRate[4]),
                                    Number(cityPassRate[5]),
                                    Number(cityPassRate[6]),
                                    Number(cityPassRate[7])],
                                color: '#0077cb',
                                pointWidth: pointwidth        
                            },
                            {
                                name: '全国',
                                data: [0, Number(nationwidePassRate[1]),
                                    Number(nationwidePassRate[2]),
                                    Number(nationwidePassRate[3]),
                                    Number(nationwidePassRate[4]),
                                    Number(nationwidePassRate[5]),
                                    Number(nationwidePassRate[6]),
                                    Number(nationwidePassRate[7])],
                                color: '#005897',
                                pointWidth: pointwidth
                            }]
                    });
}
