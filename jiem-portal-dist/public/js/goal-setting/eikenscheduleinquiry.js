var SCHEDULEINQUIRY = {
    urlGetSchedules: COMMON.baseUrl + 'goalsetting/eikenscheduleinquiry/get-eiken-schedules',
    urlGetHolidays: COMMON.baseUrl + 'goalsetting/eikenscheduleinquiry/get-eiken-schedule-holidays',
    strIconStarOrange1: '<div class="icon-c-day icon-event-eiken icon-star-orange" data-id="icon-star-orange" data-round2="1"></div>',
    strIconStarOrange2: '<div class="icon-c-day icon-event-eiken icon-star-orange" data-id="icon-star-orange" data-round2="2"></div>',
    strIconStarOrangeO: '<div class="icon-c-day icon-event-eiken icon-star-orange-o" data-id="icon-star-orange-o"></div>',
    strIconCircleOrange: '<div class="icon-c-day icon-event-eiken icon-circle-orange" data-id="icon-circle-orange"></div>',
    strIconCircleOrangeO: '<div class="icon-c-day icon-event-eiken icon-circle-orange-o" data-id="icon-circle-orange-o"></div>',
    strBodyPopover: '<table class="tbl-popover"><tr><td rowspan="2" id="day-selected"></td><td id="eiken-event"></td></tr><tr><td id="eikeniba-event"></td></tr></table>',
    events: [],
    isOtherYear: false,
    idCalendar: '#calendar',
    schedulesData: [],
    triggerPopover: 'hover',
    textRound2A: '二次試験実施日（A日程）',
    textRound2B: '二次試験実施日（B日程）',
    init: function () {
        SCHEDULEINQUIRY.detectDevice();
        SCHEDULEINQUIRY.hackIe8();
        SCHEDULEINQUIRY.initCalendar();
        SCHEDULEINQUIRY.handlerMonthClicked();
        SCHEDULEINQUIRY.handlerButtonNextPrevInYearViewClicked();
        SCHEDULEINQUIRY.initPopoverDayHover();
        SCHEDULEINQUIRY.handlerEikenCheckBox();
        var currentYear = $(SCHEDULEINQUIRY.idCalendar).fullCalendar('getDate').getFullYear();
        SCHEDULEINQUIRY.getHolidaysOfYear(currentYear);
        //set label other year        
        $('.other-year').text($(SCHEDULEINQUIRY.idCalendar).fullCalendar('getDate').getFullYear() + 1);
        COMMON.setTabIndexIdentify("select,input,.btn"); //auto set tab index
        $('#chk-eiken').prop('checked', true); //default checked eiken checkbox
    },
    initCalendar: function () {
        var currentYear = new Date().getFullYear();
        $(SCHEDULEINQUIRY.idCalendar).fullCalendar({
            header: {
                left: '', //prev,next,today
                center: '', //title
                right: '' //year,month,agendaWeek,agendaDay
            },
            viewDisplay: function (view) {
                if (view.name == 'month')
                    $('.btn-back').show();
                else
                    $('.btn-back').hide();
                SCHEDULEINQUIRY.updateTextOtherYearInYearView();
                $('.breadcrumbs-current').text(view.name === 'month' ? '月間スケジュール' : '年間スケジュール');
            },
            editable: false,
            eventSources: [
                {
                    url: SCHEDULEINQUIRY.urlGetSchedules,
                    data: {year: currentYear},
                    beforeSend: function (jqXHR, settings) {
                        var tempYear = parseInt($('#temp-year').val(), 10);
                        if (tempYear) {
                            settings.url = settings.url.replace(currentYear, tempYear);
                        }
                        if (SCHEDULEINQUIRY.isOtherYear) {
                            settings.url = settings.url.replace(currentYear + 1, tempYear - 1);
                        }

                    },
                    success: function (data) {
                        SCHEDULEINQUIRY.schedulesData = data;
                        //save state of checkbox                        
                        //call func append all icons to calendar
                        SCHEDULEINQUIRY.appendAllIconToCalendar(data);
                        $('#temp-year').val($(SCHEDULEINQUIRY.idCalendar).fullCalendar('getDate').getFullYear());
                    },
                    complete: function () {
                        SCHEDULEINQUIRY.addPopover();
                        SCHEDULEINQUIRY.addClassEventColorYearView(); //add class show event firsttime                                                
                        $('.modal-backdrop').remove();
                    }
                }
            ]
        });
    },
    handlerMonthClicked: function () {
        $(document).on('click touchstart', '.fc-year-monthly-header', function () {
            $('td').find('.fc-event-inner').remove();
            //get all event of month clicked
            var monthSelected = $(this).attr('data-id');
            var calendar = $(SCHEDULEINQUIRY.idCalendar);
            var month = $(this).attr('data-id');
            var currentYear = $(SCHEDULEINQUIRY.idCalendar).fullCalendar('getDate').getFullYear();
            calendar.fullCalendar('changeView', 'month');
            if ($(this).hasClass('o-year'))
            {
                //go year + 1
                calendar.fullCalendar('gotoDate', currentYear + 1, month - 1);
                SCHEDULEINQUIRY.isOtherYear = true;
            }
            else
            {
                calendar.fullCalendar('gotoDate', currentYear, month - 1);
                SCHEDULEINQUIRY.isOtherYear = false;
            }
            var currentYear = $(SCHEDULEINQUIRY.idCalendar).fullCalendar('getDate').getFullYear();
            if (SCHEDULEINQUIRY.isOtherYear)
            {
                SCHEDULEINQUIRY.getAllEventInYearViewOfMonth(currentYear - 1, monthSelected);
                SCHEDULEINQUIRY.getHolidaysOfYear(currentYear - 1); //append holiday
            }
            else
            {
                SCHEDULEINQUIRY.getAllEventInYearViewOfMonth(currentYear, monthSelected);
                SCHEDULEINQUIRY.getHolidaysOfYear(currentYear); //append holiday   
            }
        });
    },
    updateTextOtherYearInYearView: function () {
        $('.other-year').text($(SCHEDULEINQUIRY.idCalendar).fullCalendar('getDate').getFullYear() + 1);
    },
    handlerButtonNextPrevInYearViewClicked: function () {
        $(document).on('click', '#prev-cl,#next-cl', function () {
            if ($(this).attr('id') === 'prev-cl')
            {
                if ($(SCHEDULEINQUIRY.idCalendar).fullCalendar('getView').name === 'year')
                {
                    $('#temp-year').val(parseInt($('#temp-year').val()) + (-1));
                    $(SCHEDULEINQUIRY.idCalendar).fullCalendar('prevYear');
                }
                else
                {
                    $('td').find('.fc-event-inner').remove();
                    $(SCHEDULEINQUIRY.idCalendar).fullCalendar('prev');
                    $(SCHEDULEINQUIRY.idCalendar).fullCalendar('refresh');
                }
            }
            if ($(this).attr('id') === 'next-cl')
            {
                if ($(SCHEDULEINQUIRY.idCalendar).fullCalendar('getView').name === 'year')
                {
                    $('#temp-year').val(parseInt($('#temp-year').val()) + (1));
                    $(SCHEDULEINQUIRY.idCalendar).fullCalendar('nextYear');
                }
                else
                {
                    $('td').find('.fc-event-inner').remove();
                    $(SCHEDULEINQUIRY.idCalendar).fullCalendar('next');
                    $(SCHEDULEINQUIRY.idCalendar).fullCalendar('refresh');
                }
            }
            SCHEDULEINQUIRY.handlerShowHideEventInMonthView();
            SCHEDULEINQUIRY.initPopoverDayHover(); //reinit popover
            var currentYear = $(SCHEDULEINQUIRY.idCalendar).fullCalendar('getDate').getFullYear();
            SCHEDULEINQUIRY.getHolidaysOfYear(currentYear); //append holiday   
        });
    },
    handlerButtonBack: function () {
        $(SCHEDULEINQUIRY.idCalendar).fullCalendar('changeView', 'year');
        var currentYear = $(SCHEDULEINQUIRY.idCalendar).fullCalendar('getDate').getFullYear();
        if (SCHEDULEINQUIRY.isOtherYear) {
            $(SCHEDULEINQUIRY.idCalendar).fullCalendar('gotoDate', currentYear - 1);
            SCHEDULEINQUIRY.getHolidaysOfYear(currentYear - 1);
        }
        else
        {
            SCHEDULEINQUIRY.getHolidaysOfYear(currentYear);
        }
        SCHEDULEINQUIRY.updateTextOtherYearInYearView();
    },
    highlightDaySelected: function (day) {
        $(".fc-view-month").find(".fc-day-number:contains('" + day + "')").filter(function () {
            var flag = $(this).parents('td').hasClass('fc-other-month');
            if (!flag && parseInt($(this).text(), 10) === day) {
                $(this).css({'border': '2px solid red'});
            }
        });
    },
    initPopoverDayHover: function () {
        //remove all popup            
        $(document).on('touchstart', '[data-toggle="popover"]', function () {
            if ($('#chk-eiken').is(':checked')) {
                $(this).popover({
                    trigger: SCHEDULEINQUIRY.triggerPopover,
                    html: true,
                    title: '',
                    placement: 'top',
                    content: SCHEDULEINQUIRY.strBodyPopover
                });
                $(this).popover('show');
            }
        });
        SCHEDULEINQUIRY.handlerPopoverShow();
    },
    handlerPopoverShow: function () {
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            $(document).on('show.bs.popover', function (e) {
                $('[data-toggle="popover"]').not(e.target).popover("destroy");
            });
        }
        $(document).on('shown.bs.popover', function (e) {
            setTimeout(function () {
                var data = $("#day-selected").parents('td.fc-widget-content').attr('data-id');
                var dataround ='';
                if (data)
                {
                    var arrayDate = data.split('-');
                    var month = arrayDate[1].substring(0, 2);
                    var day = arrayDate[2].substring(0, 2);
                    var strMd = parseInt(month, 10) + "/" + parseInt(day, 10);
                    var weekname = $('#day-selected').parents('td.fc-widget-content').attr('week-name');
                    $("#day-selected").html("<span style='font-size: 14px'>" + strMd + "</span><p>(" + weekname + ")</p>");
                    var lineYellow = '<div class="icon-line-radius-popover"></div>';
                    var textOfIconLine = $(".icon-line-radius-orange").parents(".icon-calendar-full").text();
                    var strDes = textOfIconLine + ': ' + $('#day-selected').parents('td.fc-widget-content').attr('date-range');
                    //bind text event
                    var iconOfDayHover = $('#day-selected').parents('td.fc-widget-content').find('.icon-c-day').clone().removeClass('icon-c-day').addClass('icon-c-popover');

                    var selectorTitle = iconOfDayHover.attr('data-id');
                    var title = $('.tbl-calendar-control .' + selectorTitle).parent('div').find('span').text();

                    var icon = iconOfDayHover.prop('outerHTML') ? iconOfDayHover.prop('outerHTML') : lineYellow;
                    
                    if(icon.search('data-round2="1"') > -1){
                        title = SCHEDULEINQUIRY.textRound2A;
                    }
                    if(icon.search('data-round2="2"') > -1){
                        title = SCHEDULEINQUIRY.textRound2B;
                    }
                    
                    //check has line yellow                
                    var str = title !== "" ? title + ': ' + strMd + '(' + weekname + ')' + '<br />' : strDes;
                    var tempkai = $('#day-selected').parents('td.fc-widget-content').attr('data-kai');
                    var tempyear = $('#day-selected').parents('td.fc-widget-content').attr('data-year');
                    //if have icon will show line + icon 
                    $('td#eiken-event').html('').html(icon + '<strong>英検</strong><br />' + tempyear + '年度第' + tempkai + '回<br />' + str);
                }
            }, 150);
        });
    },
    addPopover: function () {
        setTimeout(function () {
            if ($('#chk-eiken').is(':checked')) {
                $('[data-toggle="popover"]').popover({
                    trigger: SCHEDULEINQUIRY.triggerPopover,
                    html: true,
                    title: '',
                    placement: 'top',
                    content: SCHEDULEINQUIRY.strBodyPopover
                });
            }
        }, 200);
    },
    removePopover: function () {
        //remove all popup
        $('[data-toggle="popover"]').each(function () {
            $(this).popover('destroy');
        });
    },
    handlerEikenCheckBox: function () {
        $('#chk-eiken').on('change', function () {
            if ($('#chk-eiken').is(':checked'))
            {
                SCHEDULEINQUIRY.addPopover();
                SCHEDULEINQUIRY.addClassEventColorYearView();
                SCHEDULEINQUIRY.appendAllIconToCalendar(SCHEDULEINQUIRY.schedulesData); //restore icon
                SCHEDULEINQUIRY.handlerShowHideEventInMonthView();
                $('tr[class*="fc-week"] .icon-calendar-full').show();
            }
            else {
                SCHEDULEINQUIRY.removePopover(); //restore popover
                //remove color event of year
                $('.fc-year-have-event').each(function () {
                    $(this).removeClass('fc-year-have-event-color');
                });
                //remove color event of month
                $('.fc-event-skin').each(function () {
                    $(this).removeClass('fc-event-skin-color');
                });
                //clear all event
                $('.icon-event-eiken').remove();
                $('tr[class*="fc-week"] .icon-calendar-full').hide();
            }
        });
    },
    getHolidaysOfYear: function (currentYear) {
        $.ajax({
            url: SCHEDULEINQUIRY.urlGetHolidays,
            data: {year: currentYear},
            dataType: 'json',
            beforeSend: function (xhr) {
                $('.fc-view-month .fc-day-number.holiday').removeClass('holiday'); //remove month view if exist
            },
            success: function (data) {
                SCHEDULEINQUIRY.addHolidaysToCalendar(data);
            }
        });
    },
    addHolidaysToCalendar: function (data) {
        if (data && data.length > 0) {
            for (var i = 0; i < data.length; i++) {
                if (data[i].dayOff.date)
                {
                    var strDate = data[i].dayOff.date.substring(0, 10);
                    $('.fc-day-' + strDate).find('.fc-day-number').addClass('holiday');
                    $('.fc-day-id-' + strDate).addClass('holiday'); //month view
                }
            }
        }
    },
    getAllEventInYearViewOfMonth: function (year, month) {
        var view = $(SCHEDULEINQUIRY.idCalendar).fullCalendar('getView');
        if (view.name === 'month')
        {
            $.ajax({
                url: SCHEDULEINQUIRY.urlGetSchedules,
                dataType: 'json',
                data: {year: year},
                beforeSend: function () {
                    $('.fc-widget-content .icon-calendar-full').remove();
                },
                success: function (data) {
                    for (var i = 0; i < data.length; i++) {
                        SCHEDULEINQUIRY.appendLineEventToMonth(data[i].start, data[i].end, month);
                        if (data[i].friDate)
                        {
                            SCHEDULEINQUIRY.appendIconToMonth('icon-star-orange-o', data[i].friDate, month);
                        }
                        if (data[i].satDate)
                        {
                            SCHEDULEINQUIRY.appendIconToMonth('icon-star-orange-o', data[i].satDate, month);
                        }
                        if (data[i].sunDate)
                        {
                            SCHEDULEINQUIRY.appendIconToMonth('icon-star-orange-o', data[i].sunDate, month);
                        }
                        if (data[i].round2Day1ExamDate)
                        {
                            SCHEDULEINQUIRY.appendIconToMonth('icon-star-orange', data[i].round2Day1ExamDate, month);
                        }
                        if (data[i].round2Day2ExamDate)
                        {
                            SCHEDULEINQUIRY.appendIconToMonth('icon-star-orange', data[i].round2Day2ExamDate, month);
                        }
                        if (data[i].day1stTestResult)
                        {
                            SCHEDULEINQUIRY.appendIconToMonth('icon-circle-orange-o', data[i].day1stTestResult, month);
                        }
                        if (data[i].day2ndTestResult)
                        {
                            SCHEDULEINQUIRY.appendIconToMonth('icon-circle-orange', data[i].day2ndTestResult, month);
                        }
                    }
                },
                complete: function (data) {
                    SCHEDULEINQUIRY.addClassEventColorMonthView();
                }
            });
        }
    },
    handlerShowHideEventInMonthView: function () {
        var date = $(SCHEDULEINQUIRY.idCalendar).fullCalendar('getDate');
        var year = date.getFullYear();
        var month = date.getMonth() + 1;
        SCHEDULEINQUIRY.getAllEventInYearViewOfMonth(year, month);
    },
    appendIconToMonth: function (event, date, month) {
        if ($('#chk-eiken').is(':checked')) {
//            var arrDate = date.split('-');
            var icon = $('.' + event).parents('.icon-calendar-full')[0].outerHTML;
            $('.fc-day-id-' + date).parent('div').find('.fc-day-content').html('').append(icon);
        }
    },
    formatDFullYear: function (date) {
        return date.getFullYear() + "-" + COMMON.numberStartWithZero(date.getMonth() + 1) + "-" + COMMON.numberStartWithZero(date.getDate());
    },
    appendLineEventToMonth: function (s, e, month) {
        var temps = s.replace(/-/g, '/');
        var tempe = e.replace(/-/g, '/');
        var start = new Date(temps);
        var end = new Date(tempe);
        if ((start && start instanceof Date) && (end && end instanceof Date)) {
            var line = '<div class="fc-event-inner fc-event-skin"><span class="fc-event-title"></span></div>';
            while (start <= end) {
                var startMonth = (start.getMonth() + 1);
                if (startMonth >= (parseInt(month, 10) - 1) && startMonth <= (parseInt(month, 10) + 1))
                {
                    var filterSelector = '.fc-day-id-' + SCHEDULEINQUIRY.formatDFullYear(start);
                    if ($(filterSelector).parents('td').find('.fc-event-inner').length <= 0)
                    {
                        $(filterSelector).parents('td').css({'position': 'relative'}).append(line);
                    }
                }
                start = new Date(start.setDate(
                        start.getDate() + 1
                        ));
            }
        }
    },
    appendIcon: function (day, icon, kai, year) {
        if (day) {
            var day_selector = '.fc-day-' + day;
            $(day_selector).find('.fc-day-number').append(icon);
            //add class popover                    
            $(day_selector).find('.fc-day-number').attr('data-toggle', 'popover');
            $(day_selector).attr('data-kai', kai);
            $(day_selector).attr('data-year', year);
        }
    },
    appendAllIconToCalendar: function (data) {
        //load eiken
        if ($('#chk-eiken').is(':checked')) {
            //clear all event
            $('.icon-event-eiken').remove();
            for (var i = 0; i < data.length; i++)
            {
                var kai = data[i].kai;
                var year = data[i].year;
                //eiken round 1                                
                SCHEDULEINQUIRY.appendIcon(data[i].friDate, SCHEDULEINQUIRY.strIconStarOrangeO, kai, year); //FRI
                SCHEDULEINQUIRY.appendIcon(data[i].satDate, SCHEDULEINQUIRY.strIconStarOrangeO, kai, year); //SAT
                SCHEDULEINQUIRY.appendIcon(data[i].sunDate, SCHEDULEINQUIRY.strIconStarOrangeO, kai, year); //SUN
                //eiken round 2
                SCHEDULEINQUIRY.appendIcon(data[i].round2Day1ExamDate, SCHEDULEINQUIRY.strIconStarOrange1, kai, year);
                SCHEDULEINQUIRY.appendIcon(data[i].round2Day2ExamDate, SCHEDULEINQUIRY.strIconStarOrange2, kai, year);
                SCHEDULEINQUIRY.appendIcon(data[i].day1stTestResult, SCHEDULEINQUIRY.strIconCircleOrangeO, kai, year); //DAY1RESULT
                SCHEDULEINQUIRY.appendIcon(data[i].day2ndTestResult, SCHEDULEINQUIRY.strIconCircleOrange, kai, year); //DAY2RESULT
            }
        }
    },
    addClassEventColorYearView: function () {
        if ($("#chk-eiken").is(':checked'))
        {
            //add class color for year
            $('.fc-year-have-event').each(function () {
                //add class popover                    
                $(this).find('.fc-day-number').attr('data-toggle', 'popover');
                $(this).addClass('fc-year-have-event-color');
            });
        }
    },
    addClassEventColorMonthView: function () {
        if ($("#chk-eiken").is(':checked'))
        {
            //add class color for month
            $('.fc-event-skin').each(function () {
                $(this).addClass('fc-event-skin-color');
            });
        }
    },
    hackIe8: function () {
        if (navigator.appVersion.indexOf("MSIE 8.") !== -1)
        {
            $("#prev-cl,#next-cl").css({'height': '30px'});
            $("#prev-cl").html('<strong><</strong>');
            $("#next-cl").html('<strong>></strong>');
        }
    },
    detectDevice: function () {
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            SCHEDULEINQUIRY.triggerPopover = 'manual';
        }
        else
        {
            SCHEDULEINQUIRY.triggerPopover = 'hover';
        }
    }
};
