var DETAIL_C = {
    orgNo: orgNo,
    isBegin: true,
    ajaxNumberDetail: COMMON.baseUrl + 'homepage/homepage/ajaxDetailPeopleByTime',
    ajaxDetailExam: COMMON.baseUrl + 'homepage/homepage/ajaxDataDetailTableC',
    exportListExamLink: COMMON.baseUrl + 'homepage/homepage/getExportListAttendPupil',
    tabIndexStart: 20,
    init: function () {
        DETAIL_C.initChart();
        DETAIL_C.initPage();
        $('#_selectY').focus();
        //fix bug safari not change select option value
        $('#_selectY').val(year);
    },
    initPage: function () {
        $('.pagination li a').each(function (index, value) {
            $(value).attr('tabindex', DETAIL_C.tabIndexStart++);
        });
        $('#_columnY1').click(function () {
            DETAIL_C.clickYear1();
        });
        $('#_columnY2').click(function () {
            DETAIL_C.clickYear2();
        });
        $('.select-detail-c').change(function () {

            var value = $(this).val();
            var id = $(this).attr("id");
            var year = $('#_selectY').val();
            var time = $('#_selectT').val();
            DETAIL_C.ajaxNumberDetailClass(year, time);
        });
        $(document).on("click", ".detail-table-c", function () {
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

            DETAIL_C.ajaxDetailExamClass($('#listClassYear li.active').data('detail'), year, time, sum, page, schoolClassification, schoolYearCode, notMap);//[0]orgClassification,[1]orgShoolYear
        });
        $(document).on("click", ".pagination li a", function () {
            var page = $(this).attr('href').substring(6);
            $(this).removeAttr("");
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
            DETAIL_C.ajaxDetailExamClass($('#listClassYear li.active').data('detail'), year, time, sum, page, schoolClassification, schoolYearCode, notMap);
            return false;
        });
        $('#btnExportListExam').keypress(function (e) {
            if (e.which == 13) {
                DETAIL_C.exportListExam();
                return false;
            }
        });

        $(document).on('keyup', '.detail-table-c', function (e) {
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
            $('#chartC').highcharts({
                chart: {
                    type: 'column',
                    backgroundColor: '#f2f2f2',
                    marginTop: 0,
                    marginLeft: 115,
                    marginBottom: 0,
                    marginRight: 0
                },
                title: {
                    text: ' '
                },
                legend: {
                    enabled: false
                },
                credits: {
                    enabled: false
                },
                xAxis: {
                    categories: ['第1回', '第2回', '第3回'],
                    labels: {enabled: false}
                },
                yAxis: {
                    min: 0,
                    max: (maxPeople + (maxPeople * 0.2)),
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
                series: dataGraphJ
            });
        });
    },

    ajaxNumberDetailClass: function (year, time) {
        var myData = 'orgNo=' + DETAIL_C.orgNo + '&year=' + year + '&time=' + time;
        $.ajax({
            type: "POST",
            url: DETAIL_C.ajaxNumberDetail,
            data: myData,
            dataType: 'json',
            success: function (data) {
                $('#listClassYear').empty();
                $('#listClassYear').append(data.content);
                $('.pagination li a').each(function (index, value) {
                    $(value).attr('tabindex', DETAIL_C.tabIndexStart++);
                });
                if (data.status == 1) {
                    $('#btnExportListExam').attr('onclick', 'DETAIL_C.exportListExam()');
                }
                else {
                    $('#btnExportListExam').attr('onclick', 'DETAIL_C.showMSGNoData()');
                }

            },
            error: function () {
            }
        });
    },
    ajaxDetailExamClass: function (schoolYear, year, time, sum, page, schoolClassification, schoolYearCode, notMap) {

        var data = {};
        data['orgNo'] = DETAIL_C.orgNo;
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
            url: DETAIL_C.ajaxDetailExam,
            data: data,
            dataType: 'json',
            success: function (data) {
                $('.tab-content').empty();
                $('.tab-content').append(data.content);
                $('.pagination li a').each(function (index, value) {
                    $(value).attr('tabindex', DETAIL_C.tabIndexStart++);
                });
                if (data.status == 1) {
                    $('#btnExportListExam').attr('onclick', 'DETAIL_C.exportListExam()');
                }
                else {
                    $('#btnExportListExam').attr('onclick', 'DETAIL_C.showMSGNoData()');
                }
            },
            error: function () {
            }
        });
    },
    exportListExam: function () {
        var data = {};
        data.orgNo = DETAIL_C.orgNo;
        data.year = $('#_selectY').val();
        data.kai = $('#_selectT').val();
        data.schoolyear = $('#listClassYear li.active').data('detail');
        data.schoolClassification = $('#listClassYear li.active').data('schoolclassification');
        data.schoolYearCode = $('#listClassYear li.active').data('schoolyearcode');
        data.notMap = $('#listClassYear li.active').data('notmap');
//        update function for : #GNCCNCJDM-304
        if (typeof data.schoolYearCode == 'undefined' && $('#listClassYear li.active').data('detail') == 'other')
        {
                data.schoolYearCode = 'other';
        }

        window.location.href = DETAIL_C.exportListExamLink + "?" + $.param(data);
    },
    showMSGNoData: function () {
        ERROR_MESSAGE.show('エクスポート対象がありません。');
    }

}

$("document").ready(function () {
    DETAIL_C.init();
});

