/**
 * Show pupup error message
 */
var ADD_PUPIL = {
    baseUrl: window.location.protocol + "//" + window.location.host + "/",
    urlAjaxCheckDuplicatePupilNumber: 'pupil/pupil/checkDuplicatePupilNumber',
    urlAjaxCheckDuplicatePupil: 'pupil/pupil/checkDuplicatePupil',
    urlAjaxCheckNameKanji: 'pupil/pupil/checkNameKanji',
    ajaxGetListClass: '/pupil/pupil/ajaxGetListClass',
    converttime: function (datetime) {
        var time_date = new Date(datetime).getTime();
        return time_date;
    },
    toggle: function () {
        if (document.getElementById("hidethis").style.display === 'none') {
            document.getElementById("hidethis").style.display = 'table'; // set to table-row instead of an empty string
        } else {
            document.getElementById("hidethis").style.display = '';
        }
    },
    showCalendar: function (args) {
        if (args == 0) {
            var id = "datetimepicker";
        } else {
            var id = "datetimepicker" + args;
        }

        $('#' + id).datepicker('show');
    }
    ,isFullWidth: function (value) {
        var i, charTarget, transTarget;
        var char_length = ("あ".length);
        // transTarget = obj.value.replace(/[ ]/g,"");
        transTarget = value;
        // obj.value = transTarget;
        if (!transTarget || transTarget.length == 0) {
            return true;
        }
        for (i = 0; i < transTarget.length; i = i + char_length) {
            charTarget = transTarget.charAt(i);
            if ((charTarget >= "!" && charTarget <= "~")
                    || (charTarget >= "｡" && charTarget <= "ﾟ")
                    || charTarget == " ") {
                return false;
            }
        }
        return true;
    },
    showCalendarExEiken: function (args) {
        if (args == 0) {
            var id = "examDateEkien";
        } else {
            var id = "examDateEkien" + args;
        }

        $('#' + id).datepicker('show');
    },
    htmlencode: function htmlencode(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')
    },
    checkDuplicatePupilNumber: function () {
        var pupilId = 0;
        if (typeof ($('#pupilId').val()) != 'undefined') {
            pupilId = $('#pupilId').val();
        }
        $.ajax({
            url: ADD_PUPIL.baseUrl + ADD_PUPIL.urlAjaxCheckDuplicatePupilNumber,
            type: 'POST',
            data: {
                year: $("#year").val(),
                classj: $("#classj").val(),
                orgSchoolYear: $("#orgSchoolYear").val(),
                Number: $("#Number").val(),
                pupilId: pupilId
            },
            success: function (data) {
                var result = $.parseJSON(data);
                if (result.status == 0) {
                    $('#Number').addClass('error');
                    ERROR_MESSAGE.inlineMessage(result.error);
                    return false;
                }
                if($.trim($('#firstNameKana').val()) == "" && $.trim($('#lastNameKana').val()) == ""){
                    CONFIRM_MESSAGE.show(jsMessages.msgEmptyNameKana, function(){
                        ADD_PUPIL.checkDuplicatePupil();
                    });
                    return false;
                }
                ADD_PUPIL.checkDuplicatePupil();
            }
        });
    },
    checkDuplicatePupil: function () {
        var firstNameKanji = '';
        var lastNameKanji = '';
        var firstNameKana = '';
        var lastNameKana = '';
        var birthYear = '';
        var birthMonth = '';
        var birthDate = '';
        var year = '';
        var pupilId ='';
        firstNameKanji = $('#firstNameKanji').val();
        lastNameKanji = $('#lastNameKanji').val();
        firstNameKana = $('#firstNameKana').val();
        lastNameKana = $('#lastNameKana').val();
        birthYear = $('#ddlYear').val();
        birthMonth = $('#ddlMonth').val();
        birthDate = $('#ddlDay').val();
        year = $('#year').val();
        if ($('#pupilId').length){
            pupilId = $('#pupilId').val();
        }
        $.ajax({
            url: ADD_PUPIL.baseUrl + ADD_PUPIL.urlAjaxCheckDuplicatePupil,
            type: 'POST',
            data: {
                firstNameKanji: firstNameKanji,
                lastNameKanji: lastNameKanji,
                firstNameKana: firstNameKana,
                lastNameKana: lastNameKana,
                birthYear: birthYear,
                birthMonth: birthMonth,
                birthDate: birthDate,
                year: year,
                pupilId: pupilId
            },
            success: function (data) {
                var result = $.parseJSON(data);
                var html = '';
                if (result.status === 1) {
                    $(".duplicate-body").html('');
                    $.each(result.data, function (index, value) {
                        if(value.birthday){
                            var birthday = value.birthday.date.substring(0, 10).replace('-', '/').replace('-', '/');
                        }
                        else{
                            var birthday = '';
                        }
                        var gender = '';
                        if(value.gender == 1){
                            gender = '男';
                        }
                        else if(value.gender == 0){
                            gender = '女';
                        }
                        html += '<tr>'+
                                    '<td class="year">' + value.year+ '</td>'+
                                    '<td class="schoolyear">' + ADD_PUPIL.htmlencode(value.schoolYearName)+ '</td>'+
                                    '<td class="class">' + ADD_PUPIL.htmlencode(value.className)+ '</td>'+
                                    '<td class="number">' + ADD_PUPIL.htmlencode(value.number)+ '</td>'+
                                    '<td class="namekanji"> ' + ADD_PUPIL.htmlencode(value.nameKanji)+ '</td>'+
                                    '<td class="namekanna">' + ADD_PUPIL.htmlencode(value.nameKana)+'</td>'+
                                    '<td class="birthday">' + birthday+ '</td>'+
                                    '<td class="gender">' + gender + '</td>'+
                                 '<tr>';
                    });
                    $(".duplicate-body").prepend(html);
                    if(result.data.length > 3){
                        $(".infile-list").addClass('infile-scroll');
                    }
                    $('#duplicate').modal('show');
                } else {
                    $("#form").submit();
                }
            }
        });
    },
    duplicateSubmit: function(){
        $('#form').submit();
    },    
    checkNameKanji: function () {
        $.ajax({
            url: ADD_PUPIL.baseUrl + ADD_PUPIL.urlAjaxCheckNameKanji,
            type: 'POST',
            data: {
                firstNameKanji: $("#firstNameKanji").val(),
                lastNameKanji: $("#lastNameKanji").val()
            },
            success: function (data) {
                var result = $.parseJSON(data);
                if (result.status == 0) {
                    $('#firstNameKanji').addClass('error');
                    $('#lastNameKanji').addClass('error');
                    ERROR_MESSAGE.inlineMessage(result.error);
                } else {
                    ADD_PUPIL.checkDuplicatePupilNumber();
                }
            }
        });
    }
};

$(document).ready(function () {
    $('#datetimepicker').datepicker();
    $('#examDateEkien').datepicker();
    if (typeof String.prototype.trim !== 'function') {
        String.prototype.trim = function () {
            return this.replace(/^\s+|\s+$/g, '');
        }
    }

    var stringkana = /^([゠ァアィイゥウェエォオカガキギクグケゲコゴサザシジスズセゼソゾタダチヂッツヅテデトドナニヌネノハバパヒビピフブプヘベペホボポマミムメモャヤュユョヨラリルレロヮワヰヱヲンヴヵヶヷヸヹヺ・ーヽヾヿ]+)$/;
    var patternKana = /^[ァ-ン|ｧ-ﾝﾞﾟ]+$/;
    var dayselected = 15;
    $('form:first *:input[type!=hidden][disabled!=disabled]:first').focus();
    jQuery.validator.addMethod("checkRequireIbaDate", function (value, element) {
        if ($('#ibaLevel').val() != 0) {
            return (value.length > 0);
        } else {
            return true;
        }
    }, "sai dinh dang ngay thang");   
    jQuery.validator.addMethod("dateISO2", function (value, element) {
        if (value.length != 0) {
            if (/^\d{4}[\/]\d{2}[\/]\d{2}$/.test(value) == false) {
                return false;
            }
        }
        var array = value.split("/");
        var dtDay = parseInt(array[2]);
        var dtMonth = parseInt(array[1]);
        var dtYear = parseInt(array[0]);

        if (dtMonth < 1 || dtMonth > 12) {
            return false;
        }
        else if (dtDay < 1 || dtDay > 31) {
            return false;
        }
        else if ((dtMonth == 4 || dtMonth == 6 || dtMonth == 9 || dtMonth == 11) && dtDay == 31) {
            return false;
        }
        else if (dtMonth == 2) {
            var isleap = (dtYear % 4 == 0 && (dtYear % 100 != 0 || dtYear % 400 == 0));
            if (dtDay > 29 || (dtDay == 29 && !isleap)) {
                return false;
            }
        }
        return true;
    }, "sai dinh dang ngay thang");
    jQuery.validator.addMethod("dateISO3", function (value, element) {
        var array = value.split("/");
        var dtYear = parseInt(array[0]);
        var yearEkien = $('#eikenYear').val();
        if (isNaN(dtYear) || dtYear == '' || dtYear == 'undefined') {
            dtYear = 0;
        }
        if (isNaN(yearEkien) || yearEkien == '' || yearEkien == 'undefined') {
            yearEkien = 0;
        }
        if (dtYear != 0 && parseInt(yearEkien) != parseInt(dtYear)) {
            return false;
        }
        return true;
    }, "sai dinh dang ngay thang");
    jQuery.validator.addMethod("checkKana", function (value, element) {
        var value = $.trim(value);
        return this.optional(element) || patternKana.test(value);
    }, "Positive integer number required");
    jQuery.validator.addMethod("eikenIDNumber", function (value, element) {
        return this.optional(element) || /^\d+[0-9-_]*$/.test(value);
    }, "Positive integer number required");
    jQuery.validator.addMethod("checkPupilNumber", function (value, element) {
        return this.optional(element) || /^\d+[0-9]*$/.test(value);
    }, "Positive integer number required");
    jQuery.validator.addMethod("eikenScoreNumber", function (value, element) {
        value = $.trim(value);
        return this.optional(element) || /^\d+[0-9-_]*$/.test(value);
    }, "Positive integer number required");
    jQuery.validator.addMethod("checkNumberEinaviId", function (value, element) {
        return this.optional(element) || (/^[0-9]{10}$/).test(value);
    }, "Positive integer number required");
    jQuery.validator.addMethod("checkNumberEikenId", function (value, element) {
        return this.optional(element) || (/^[0-9]{11}$/).test(value);
    }, "Positive integer number required");
    jQuery.validator.addMethod("checkEikenPassword", function (value, element) {
        return this.optional(element) || (/^[a-zA-Z0-9]{4,6}$/).test(value);
    }, "Positive integer number required");
    jQuery.validator.addMethod("checkEikenYear", function (value, element) {
        if ($('#eikenLevel').val() == 0) {
            return true;
        } else {
            return (value != 0) ? true : false;
        }
    }, "Positive integer number required");

    jQuery.validator.addMethod("checkEikenKai", function (value, element) {
        if ($('#eikenLevel').val() == 0) {
            return true;
        } else {
            return (value != 0) ? true : false;
        }
    }, "Positive integer number required");
    jQuery.validator.addMethod("birthday", function (value, element) {
        var currentTime = new Date();
        var currentYear = currentTime.getFullYear();
        var currentMonth = currentTime.getMonth() + 1;
        var currentDate = currentTime.getDate();

        var year = $('#ddlYear').val();
        var month = $('#ddlMonth').val();
        var day = $('#ddlDay').val();
        // Case day
        if ($(element).attr('id') == 'ddlDay')
            return !(year >= currentYear && month == currentMonth && day > currentDate);
        else
            return !(year >= currentYear && month > currentMonth);
        return true;
    }, jsMessages.PMSG0094);
    jQuery.validator.addMethod("checkNameKanji", function (value, element) {
        var lengtKanji = $.trim($('#firstNameKanji').val()) + $.trim($('#lastNameKanji').val());
        if (!ADD_PUPIL.isFullWidth(lengtKanji) && lengtKanji.length > 0) {
            return false;
        } else {
            if (/[\u0020]/.test(lengtKanji) == true) {

                return false;
            }

            if (lengtKanji.length > 20) {
                return false;
            } else {
                return true;
            }
        }
        return true;
    }, '');

    var msgMonth = '';
    var msgDay = '';
    jQuery.validator.addMethod("checkRequireBirthDay", function (value, element) {
        var year = $.trim($("#ddlYear").val());
        var month = $.trim($("#ddlMonth").val());
        var day = $.trim($("#ddlDay").val());
        if((year > 0 || month > 0 || day > 0) && $.trim(value) == ''){
            return false;
        }
        return true;
    }, "");
    jQuery.validator.addMethod("checkBirthDay", function (value, element) {
        var year = $("#ddlYear").val();

        if (year == '' || year == null) {
            var year = '';
        }

        var year = year.length;

        var month = $("#ddlMonth").val();
        if (month == '' || month == null) {
            var month = '';
        }
        var month = month.length;

        var day = $("#ddlDay").val();
        if (day == '' || day == null) {
            var day = '';
        }
        var day = day.length;

        if (value == '' || value == null) {
            value = '';
        }
        if (value.length > 0) {
            return true;
        } else {
            if (year > 0 || month > 0 || day > 0) {
                if ($("#ddlMonth").val().length < 1) {
                    msgMonth = jsMessages.birthday;
                    msgDay = '';
                } else {
                    if ($("#ddlDay").val().length < 1) {
                        msgMonth = '';
                        msgDay = jsMessages.birthday;
                    }
                }
                return false;
            }
        }
        return true;

    }, "");
    var validator = $("#form").validate({
        onfocusout: false,
        onkeyup: false,
        onclick: false,
        focusInvalid: false,
        rules: {
            pupilId: {
                eikenIDNumber: true
            },
            Number: {
                required: true,
                checkPupilNumber: true
            },
            year: {
                required: true
            },
            orgSchoolYear: {
                required: true
            },
            classj: {
                required: true
            },
            einaviId: {
                checkNumberEinaviId: true
            },
            eikenId: {
                checkNumberEikenId: true
            },
            eikenPassword: {
                checkEikenPassword: true
            },
            eikenYear: {
                checkEikenYear: true
            },
            kai: {
                checkEikenKai: true
            },
            firstNameKanji: {
                required: true,
                checkNameKanji: true
               // fullwidth:true
            },
            lastNameKanji: {
                required: true,
                checkNameKanji: true
               // fullwidth:true
            },
            firstNameKana: {
                //required: true,
                checkKana: true
            },
            lastNameKana: {
                //required: true,
                checkKana: true
            },
            birthYear: {
                checkRequireBirthDay: true,
                checkBirthDay: true
            },
            birthMonth: {
                checkRequireBirthDay: true,
                checkBirthDay: true,
                birthday: true
            },
            birthDay: {
                checkRequireBirthDay: true,
                checkBirthDay: true,
                birthday: true
            },
            eikenRead: {
                eikenScoreNumber: true
            },
            eikenListen: {
                eikenScoreNumber: true
            },
            eikenWrite: {
                eikenScoreNumber: true
            },
            eikenSpeak: {
                eikenScoreNumber: true
            },
            eikenTotal: {
                eikenScoreNumber: true
            },
            datetime: {
                checkRequireIbaDate: true,
                dateISO2: true
            },
            ibaRead: {
                eikenScoreNumber: true
            },
            ibaListen: {
                eikenScoreNumber: true
            },
            ibaTotal: {
                eikenScoreNumber: true
            }
        },
        messages: {
            pupilId: {
                eikenIDNumber: jsMessages.PMSG0096
            },
            Number: {
                required: jsMessages.MsgPupilNumberError1,
                checkPupilNumber: jsMessages.MsgPupilNumberError2
            },
            year: {
                required: jsMessages.MsgEmptyYear
            },
            orgSchoolYear: {
                required: jsMessages.MsgSchoolYearError1
            },
            classj: {
                required: jsMessages.MsgClassError1
            },
            einaviId: {
                checkNumberEinaviId: jsMessages.MsgEinaviIdError1
            },
            eikenId: {
                checkNumberEikenId: jsMessages.MsgEikenIdError1
            },
            eikenPassword: {
                checkEikenPassword: jsMessages.MsgEikenPasswordError1
            },
            eikenYear: {
                checkEikenYear: jsMessages.MsgEikenYearError1
            },
            kai: {
                checkEikenKai: jsMessages.MsgEikenKaiError1
            },
            birthYear: {
                checkRequireBirthDay: jsMessages.MsgBirthdayError1,
                checkBirthDay: ''
            },
            birthMonth: {
                checkRequireBirthDay: jsMessages.MsgBirthdayError1,
                checkBirthDay: '',
                birthday: jsMessages.MsgBirthdayError4
            },
            birthDay: {
                checkRequireBirthDay: jsMessages.MsgBirthdayError1,
                checkBirthDay: jsMessages.birthday,
                birthday: jsMessages.MsgBirthdayError4
            },
            eikenRead: {
                eikenScoreNumber: jsMessages.MsgEikenScoreReadingError1
            },
            eikenListen: {
                eikenScoreNumber: jsMessages.MsgEikenScoreListeningError1
            },
            eikenWrite: {
                eikenScoreNumber: jsMessages.MsgEikenScoreWritingError1
            },
            eikenSpeak: {
                eikenScoreNumber: jsMessages.MsgEikenScoreSpeakingError1
            },
            eikenTotal: {
                eikenScoreNumber: jsMessages.PMSG0096
            },
            datetime: {
                checkRequireIbaDate: jsMessages.MsgIBADateError1,
                dateISO2: jsMessages.MsgIBADateError2
            },
            ibaRead: {
                eikenScoreNumber: jsMessages.MsgIBAScoreReadingError1
            },
            ibaListen: {
                eikenScoreNumber: jsMessages.MsgIBAScoreListeningError1
            },
            ibaTotal: {
                eikenScoreNumber: jsMessages.PMSG0096
            },
            firstNameKana: {
                //required: jsMessages.MsgFirstnameKanaError1,
                checkKana: jsMessages.MsgFirstnameKanaError2
            },
            lastNameKana: {
                //required: jsMessages.MsgLastnameKanaError1,
                checkKana: jsMessages.MsgLastnameKanaError2
            },
            firstNameKanji: {
                required: jsMessages.MsgFirstnameKanjiError1,
                checkNameKanji: jsMessages.kanjError
            },
            lastNameKanji: {
                required: jsMessages.MsgLastnameKanjiError1,
                checkNameKanji: ''
            }
        },
        onfocusout:function (element) {
        },
                errorPlacement: function (error, element) {
                },
        showErrors: function (errorMap, errorList) {
            
            this.defaultShowErrors();
            for (var key in errorList) {
                if(errorList[key].message == ''){
                    var element = errorList[key].element;
                    $(element).addClass('error');
                    errorList.splice(key, 1);
                }
            }
            if (msgMonth.length > 0) {
                ERROR_MESSAGE.show(errorList, function () {
                }, 'inline', 'ddlMonth', 'ddlDay');
            } else {
                ERROR_MESSAGE.show(errorList, function () {
                }, 'inline');
            }

        }
    });

    $("#submitform").click(function () {
        if ($('#form').valid()) {
            ADD_PUPIL.checkNameKanji();
        }
        //$("#form").submit();
    });
//    $("#btnCancel").click(function () {
//        window.history.back();
//    });
    $('.r9 input').on('change', function () {
        var eikenTotal = 0;
        var read = parseInt($('#eikenRead').val());
        var listen = parseInt($('#eikenListen').val());
        var write = parseInt($('#eikenWrite').val());
        var speak = parseInt($('#eikenSpeak').val());
        if (isNaN(read)) {
            read = 0;
        }
        if (isNaN(listen)) {
            listen = 0;
        }
        if (isNaN(write)) {
            write = 0;
        }
        if (isNaN(speak)) {
            speak = 0;
        }
        eikenTotal = read + listen + write + speak;
        if (isNaN(eikenTotal)) {
            eikenTotal = 0;
        }
        $('#eikenTotal').val(eikenTotal);
    });

    $('.r11 input').on('change', function () {
        var ibaTotal = 0;
        var read = parseInt($('#ibaRead').val());
        var listen = parseInt($('#ibaListen').val());
        if (isNaN(read)) {
            read = 0;
        }
        if (isNaN(listen)) {
            listen = 0;
        }
        ibaTotal = read + listen;
        if (isNaN(ibaTotal)) {
            ibaTotal = 0;
        }
        $('#ibaTotal').val(ibaTotal);
    });
});