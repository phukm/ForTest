var CSE_SCORE_TOTAL = {
    baseUrl: window.location.protocol + "//" + window.location.host + "/",
    urlAjaxLoadClass: 'report/report/loadClass',
    init: function () {
        CSE_SCORE_TOTAL.loadClass();
        var selectedVal = "";
        var selected = $(".col-sm-10 input[type='radio']:checked");
        $(".col-sm-10 input[type='radio']").on('change', function () {
            selectedVal = $(this).val();
            if (selectedVal == 'Organization') {
                $('.hiderows1').hide();
                $('.hiderows2').hide();
            }
            else if (selectedVal == 'OrgSchoolYear') {
                $('.hiderows2').hide();
                $('.hiderows1').show();
            } else {
                $('.hiderows1').show();
                $('.hiderows2').show();
            }
        });
        if (selected.length > 0) {
            selectedVal = selected.val();
            if (selectedVal == 'Organization') {
                $('.hiderows1').hide();
                $('.hiderows2').hide();
            }
            else if (selectedVal == 'OrgSchoolYear') {
                $('.hiderows2').hide();
                $('.hiderows1').show();
            } else {
                $('.hiderows1').show();
                $('.hiderows2').show();
            }
        }
        ;

        $('#orgSchoolYearId').change(function () {
            CSE_SCORE_TOTAL.loadClass();
        });


        $("#btnSearch").click(function ()
        {
            var yearFrom = $('#yearFrom').val();
            var yearTo = $('#yearTo').val();
            if (yearFrom > yearTo) {
                $('#yearFrom').addClass('error');
                ERROR_MESSAGE.show([{id: 'yearFrom', message: jMessages.MSG30_FromDate_Greater_ToDate}], null, 'inline');
            } else {
                $('#searchCseScore').submit();
            }

        });

        $('#btnClear').click(function () {
            var date = new Date();
            $("#yearFrom").val(date.getFullYear() - 1);
            $("#yearTo").val(date.getFullYear());
            $("#objectType").val('Organization');
            $("#orgSchoolYearId").val('');
            $("#classIdSelected").val('');
            $("#classId").val('');
            $("#type").val('');
            $("#typeOrganization").prop('checked', true);
            $('#searchCseScore').submit();
        });

        $('#type').change(function () {
            var year = 2015;
            var yFromElement = $('select#yearFrom');
            var yToElement = $('select#yearTo');
            if ($(this).val() === 'IBA') {
                // enable 2015 option for IBA result.
                var option = '<option value="' + year + '" >' + year + '</option>';
                if (yFromElement.find('[value="' + year + '"]').length === 0) {
                    yFromElement.append(option);
                }
                if (yToElement.find('[value="' + year + '"]').length === 0) {
                    yToElement.append(option);
                }
            } else {
                // Remove 2015 option for Eiken and blank option
                var preYFromValue = yFromElement.val();
                var preYToValue = yToElement.val();
                yFromElement.find('[value="' + year + '"]').remove();
                yToElement.find('[value="' + year + '"]').remove();
                /* When change from IBA to other, if 2015 is selected previously, then we need to set selected value to
                smallest value*/
                if(preYFromValue == year){
                    yFromElement.get(0).selectedIndex = yFromElement.children('option').length - 1;
                }
                if(preYToValue == year){
                    yToElement.get(0).selectedIndex = yToElement.children('option').length - 1;
                }
            }
        });

        CSE_SCORE_TOTAL.drawHighchartTotalCseScore(cseAvgScore, cseMinScore, cseMaxScore, cseResultTitle);
        CSE_SCORE_TOTAL.drawHighchartCseScore(cseReadingScore, cseListeningScore, cseSpeakingScore, cseWritingScore, cseResultTitle)

    },
    loadClass: function () {
        var classIdSelected = $('#classIdSelected').val();
        $.ajax({
            type: 'POST',
            url: CSE_SCORE_TOTAL.baseUrl + CSE_SCORE_TOTAL.urlAjaxLoadClass,
            data: {
                orgSchoolYearId: $('#orgSchoolYearId').val(),
                yearFrom: $('#yearFrom').val(),
                yearTo: $('#yearTo').val()
            },
            complete: function (jqXHR, textStatus) {
                $('form:first *:input[type!=hidden][disabled!=disabled]:first').focus();
            },
            success: function (data) {
                var html = '';
                if (data != '') {
                    $.each(data, function (key, val) {
                        var strSelected = '';
                        if (classIdSelected == val.id) {
                            strSelected = 'selected="selected"';
                        }
                        html = html + '<option value="' + val.id + '" ' + strSelected + '>' + val.className + '</option>';
                    });
                } else {
                    html = '<option value=""></option>';
                }
                $("#classId").html(html);
            },
            error: function () {
                ERROR_MESSAGE.show([{id: '', message: 'Error'}], null, 'inline');
                $('#loadingModal').modal('hide');
            }
        });

    },
    drawHighchartTotalCseScore: function (cseAvgScore, cseMinScore, cseMaxScore, cseResultTitle) {
        $('#chart1').highcharts({
            chart: {
                marginTop: 0,
                marginLeft: 0,
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
                categories: cseResultTitle,
                labels: {enabled: false},
                gridLineWidth: 0,
                tickLength: 5,
                tickWidth: 0,
                lineWidth: 0
            },
            yAxis: {
                min: 0,
                max: 3400,
                title: {
                    style: {display: 'none'},
                    text: ''
                },
                labels: {enabled: false},
                gridLineWidth: 0,
                tickLength: 5,
                tickWidth: 0,
                lineWidth: 0
            },
            plotOptions: {
                series: {
                    marker: {
                        symbol: 'circle',
                        radius: 3,
                        lineColor: this.color,
                        lineWidth: 1,
                        fillColor: '#ffffff'
                    }
                }
            },
            tooltip: {
                formatter: function () {
                    return '<span style="color: ' + this.series.color + ';">●</span>' + this.key + this.y;
                }
            },
            series: [
                {
                    name: '平均 - AverageScore',
                    data: cseAvgScore,
                    color: '#ff8107'
                },
                {
                    name: '最低 - LowestScore',
                    data: cseMinScore,
                    color: '#e60012'
                },
                {
                    name: '最高 - HighestScore',
                    data: cseMaxScore,
                    color: '#3556fc'
                }
            ]
        });
    },
    drawHighchartCseScore: function (cseReadingScore, cseListeningScore, cseSpeakingScore, cseWritingScore, cseResultTitle) {
        $('#chart2').highcharts({
            chart: {
                marginTop: 10,
                marginLeft: 0,
                marginBottom: 0,
                backgroundColor: '#F2f2f2',
                events: {
                    load: function () {
                        for (var i = 0; i < this.series.length; i++) {
                            for (var j = 0; j < this.series[i].points.length; j++) {
                                if (this.series[i].points[j].y === -1) {
                                    this.series[i].points[j].remove();
                                    i = 0;
                                    j = 0;
                                }
                            }
                        }
                    }
                }
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
                categories: cseResultTitle,
                labels: {enabled: false},
                gridLineWidth: 0,
                tickLength: 5,
                tickWidth: 0,
                lineWidth: 0
            },
            yAxis: {
                min: 0,
                max: 850,
                title: {
                    style: {display: 'none'},
                    text: ''
                },
                labels: {enabled: false},
                gridLineWidth: 0,
                tickLength: 5,
                tickWidth: 0,
                lineWidth: 0
            },
            plotOptions: {
                series: {
                    marker: {
                        symbol: 'circle',
                        radius: 3,
                        lineColor: this.color,
                        lineWidth: 1,
                        fillColor: '#ffffff'
                    }
                }
            },
            tooltip: {
                formatter: function () {
                    return '<span style="color: ' + this.series.color + ';">●</span>' + this.key + this.y;
                }
            },
            series: [
                {
                    name: 'リーディング  - ReadingScore',
                    data: cseReadingScore,
                    color: '#f61cfe'
                },
                {
                    name: 'リスニング - ListeningScore',
                    data: cseListeningScore,
                    color: '#18904d'
                },
                {
                    name: 'スピーキング - SpeakingScore',
                    data: cseSpeakingScore,
                    color: '#b8f4ff'
                },
                {
                    name: 'ライティング - WritingScore',
                    data: cseWritingScore,
                    color: '#21fe3b'
                }
            ]
        });
    }
};
