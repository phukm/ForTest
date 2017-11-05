var DETAIL_B1 = {
    orgNo: dataJsonB.orgNo,
    isBegin: true,
    ajaxDetailClassB: COMMON.baseUrl + 'homepage/homepage/ajaxDetailClassB',
    ajaxTableByClassB: COMMON.baseUrl + 'homepage/homepage/ajaxTableByClassB',
    exportListExamLink: COMMON.baseUrl + 'homepage/homepage/getExportListAttendPupil',
    tabIndexStart: 20,
    init: function () {
        DETAIL_B1.initChart();
        DETAIL_B1.initPage();
        DETAIL_B1.loadPage();
    },

    loadPage: function () {
        $('#_selectY').focus();
        $('#_selectY').val(dataJsonB.year);
    },

    initPage: function () {
        $('.pagination li a').each(function (index, value) {
            $(value).attr('tabindex', DETAIL_B1.tabIndexStart++);
        });
        $('.detail-b-type-check').click(function () {
            var type = 0;
            if ($(this).hasClass('detail-b-type-table')) type = $(this).attr("id");
            DETAIL_B1.displayChart(type);
        });

        $('.select-detail-b').change(function () {
            var value = $(this).val();
            var id = $(this).attr("id");
            var year = $('#_selectY').val();
            var time = $('#_selectT').val();
            DETAIL_B1.ajaxDetailClass(year, time);
        });
        $(document).on("click", ".detail-table-b", function () {
            var year = $('#_selectY').val();
            var time = $('#_selectT').val();

            var sum = $(this).data('count');
            var page = 1;

            var schoolClassification = $('#listClassYear li.active').data('schoolclassification');
            var schoolYearCode = $('#listClassYear li.active').data('schoolyearcode');
            var notMap = $('#listClassYear li.active').data('notmap');
//            update function for : #GNCCNCJDM-304
            if (typeof schoolYearCode == 'undefined' && $('#listClassYear li.active').data('detail') == 'other'){
                schoolYearCode = 'other';
            }

            DETAIL_B1.ajaxTableByClass($(this).data('detail'), year, time, sum, page, schoolClassification, schoolYearCode, notMap);//[0]orgClassification,[1]orgShoolYear
        });
        $(document).on("click", ".pagination li a", function () {
            var page = $(this).attr('href').substring(6);
            $(this).removeAttr("");
            if (page == "pre" || page == "next") {
                return false;
            }
            var year = $('#_selectY').val();
            var time = $('#_selectT').val();

            var schoolClassification = $('#listClassYear li.active').data('schoolclassification');
            var schoolYearCode = $('#listClassYear li.active').data('schoolyearcode');
            var notMap = $('#listClassYear li.active').data('notmap');
//            update function for : #GNCCNCJDM-304
            if (typeof schoolYearCode == 'undefined' && $('#listClassYear li.active').data('detail') == 'other'){
                schoolYearCode = 'other';
            }
            

            var sum = $('#listClassYear li.active').data('count');
            DETAIL_B1.ajaxTableByClass($('#listClassYear li.active').data('detail'), year, time, sum, page, schoolClassification, schoolYearCode, notMap);
            return false;
        });
        $('#btnExportListExam').keypress(function (e) {
            if (e.which == 13) {
                DETAIL_B1.exportListExam();
                return false;
            }
        });

        $(document).on('keyup', '.detail-table-b', function (e) {
            if (e.keyCode === 13) { // 13 is enter key
                $(this).find('a').trigger('click');
            }
        });

    },
    /*
     * Draw highcharts
     * DucNA17
     */
    initChart: function () {
        $(function () {
            $('#chartB3').highcharts({
                chart: {
                    backgroundColor: '#f2f2f2',
                    marginTop: 0,
                    marginLeft: 115,
                    marginBottom: 0,
                    marginRight: 0
                },
                title: {
                    text: 'Combination chart',
                    style: {display: 'none'}
                },
                legend: {
                    enabled: false
                },
                credits: {
                    enabled: false
                },
                xAxis: {
                    categories: ['1級', '準1級', '2級', '準2級', '3級', '4級', '5級'],
                    labels: {enabled: false}
                },
                yAxis: {
                    min: 0,
                    max: maxPeople,
                    title: {
                        style: {display: 'none'},
                        text: ''
                    },
                    labels: {enabled: false},
                    gridLineColor: 'transparent'
                },
                plotOptions: {
                    column: {
                        grouping: true,
                        borderWidth: 0
                    },
                    series: {
                        borderWidth: 0,
                        dataLabels: {
                            enabled: true,
                            allowOverlap: true,
                            align: 'center',
                            formatter: function () {
                                if (this.y != 0) {
                                    return this.y;
                                } else {
                                    return null;
                                }
                            }
                        }
                    }
                },
                series: [{
                    type: 'column',
                    name: '本校-' + dataGraphJ.Y0[1]['year'],
                    data: [parseInt(dataGraphJ.Y0[1]["totalPassed"]),
                        parseInt(dataGraphJ.Y0[2]["totalPassed"]),
                        parseInt(dataGraphJ.Y0[3]["totalPassed"]),
                        parseInt(dataGraphJ.Y0[4]["totalPassed"]),
                        parseInt(dataGraphJ.Y0[5]["totalPassed"]),
                        parseInt(dataGraphJ.Y0[6]["totalPassed"]),
                        parseInt(dataGraphJ.Y0[7]["totalPassed"])
                    ],
                    color: '#9dcff2',
                    pointWidth: 16,
                    pointPadding: 0,
                    pointPlacement: -0.06
                },

                    {
                        type: 'column',
                        name: '本校-' + dataGraphJ.Y1[1]['year'],
                        data: [parseInt(dataGraphJ.Y1[1]["totalPassed"]),
                            parseInt(dataGraphJ.Y1[2]["totalPassed"]),
                            parseInt(dataGraphJ.Y1[3]["totalPassed"]),
                            parseInt(dataGraphJ.Y1[4]["totalPassed"]),
                            parseInt(dataGraphJ.Y1[5]["totalPassed"]),
                            parseInt(dataGraphJ.Y1[6]["totalPassed"]),
                            parseInt(dataGraphJ.Y1[7]["totalPassed"])
                        ],
                        color: '#0077cb',
                        pointWidth: 16,
                        pointPadding: 0,
                        pointPlacement: 0
                    },
                    {
                        type: 'column',
                        name: '本校-' + dataGraphJ.Y2[1]['year'],
                        data: [parseInt(dataGraphJ.Y2[1]["totalPassed"]),
                            parseInt(dataGraphJ.Y2[2]["totalPassed"]),
                            parseInt(dataGraphJ.Y2[3]["totalPassed"]),
                            parseInt(dataGraphJ.Y2[4]["totalPassed"]),
                            parseInt(dataGraphJ.Y2[5]["totalPassed"]),
                            parseInt(dataGraphJ.Y2[6]["totalPassed"]),
                            parseInt(dataGraphJ.Y2[7]["totalPassed"])
                        ],
                        color: '#005897',
                        pointWidth: 16,
                        pointPadding: 0,
                        pointPlacement: 0.06
                    }]
            });
        });
    },
    ajaxDetailClass: function (year, time) {
        var myData = 'orgNo=' + DETAIL_B1.orgNo + '&year=' + year + '&time=' + time;
        $.ajax({
            type: "POST",
            url: DETAIL_B1.ajaxDetailClassB,
            data: myData,
            dataType: 'json',
            success: function (data) {
                $('#listClassYear').empty();
                $('#listClassYear').append(data.content);
                $('.pagination li a').each(function (index, value) {
                    $(value).attr('tabindex', DETAIL_B1.tabIndexStart++);
                });
                if (data.status == 1) {
                    $('#btnExportListExam').attr('onclick', 'DETAIL_B1.exportListExam()');
                }
                else {
                    $('#btnExportListExam').attr('onclick', 'DETAIL_B1.showMSGNoData()');
                }

            },
            error: function () {
            }
        });
    },
    ajaxTableByClass: function (schoolYear, year, time, sum, page, schoolClassification, schoolYearCode, notMap) {
        var data = {};
        data['orgNo'] = DETAIL_B1.orgNo;
        data['year'] = year;
        data['schoolYearName'] = schoolYear;
        data['time'] = time;
        data['sum'] = sum;
        data['page'] = page;
        data['schoolClassification'] = schoolClassification;
        data['schoolYearCode'] = schoolYearCode;
        data['notMap'] = notMap;
        $.ajax({
            type: "POST",
            url: DETAIL_B1.ajaxTableByClassB,
            data: data,
            dataType: 'html',
            success: function (data) {
                $('#detailTable').empty();
                $('#detailTable').append(data);
                $('.pagination li a').each(function (index, value) {
                    $(value).attr('tabindex', DETAIL_B1.tabIndexStart++);
                });

            },
            error: function () {
            }
        });
    },
    exportListExam: function () {
        var data = {};
        data.orgNo = DETAIL_B1.orgNo;
        data.year = $('#_selectY').val();
        data.kai = $('#_selectT').val();
        data.schoolyear = $('#listClassYear li.active').data('detail');
        data.typeDetail = 'B';

        data.schoolClassification = $('#listClassYear li.active').data('schoolclassification');
        data.schoolYearCode = $('#listClassYear li.active').data('schoolyearcode');
        data.notMap = $('#listClassYear li.active').data('notmap');
//        update function for : #GNCCNCJDM-304
        if (typeof data.schoolYearCode == 'undefined' && $('#listClassYear li.active').data('detail') == 'other')
        {
                data.schoolYearCode = 'other';
        }
            
        window.location.href = DETAIL_B1.exportListExamLink + "?" + $.param(data);
    },
    showMSGNoData: function () {
        ERROR_MESSAGE.show('エクスポート対象がありません。');
    }

}


$("document").ready(function () {
    DETAIL_B1.init();
});

