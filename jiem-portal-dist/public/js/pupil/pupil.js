/**
 * Show pupup error message
 */
var lIST_PUPIL = {
    baseUrl : window.location.protocol + "//" + window.location.host + "/",
    ajaxGetListClass: '/pupil/pupil/ajaxGetListClass',
    ajaxGetListClassName: '/pupil/pupil/ajaxGetListClassName',
    ajaxCheckHadApplyOrPaid: 'pupil/pupil/check-pupil-had-apply-eiken-to-delete',
    ajaxCheckDeletePupil: 'pupil/pupil/cannot-delete',
    submitformpupil: function () {
        $('#pupilmanager').submit();
        sessionStorage.setItem('cookieExport', '');
    },
    clearSearch: function () {
        $("#year").val(COMMON.getCurrentYear());
        $("#orgSchoolYear").val('');
        $("#classj").val('');
        $("#name").val('');
        sessionStorage.setItem('cookieExport', '');
        $('#pupilmanager').submit();
    },
    deletepupils: function () {
        var check;
        check = $(".checkbox1").is(":checked");
        if (check) {
            CONFIRM_MESSAGE.show('選択したアイテムを削除します。よろしいですか？', function () {
                $('#delpupil').submit();
            })
        } else {
            ERROR_MESSAGE.show('削除対象項目を選択してください。');
        }
    },
    loadOrgSchoolYear: function () {
        var request_year = $("#year").val();
        var request_schoolyear = $("#orgSchoolYear").val();
        $.ajax({
            type: 'POST',
            url: lIST_PUPIL.ajaxGetListClass,
            beforeSend: function () {
                $("#loadingIcon").show();
                $("#classj").prop("disabled", true);
            },
            complete: function () {
                $("#loadingIcon").hide();
                $("#classj").prop("disabled", false);
            },
            global: false,
            data: {
                year: request_year,
                schoolyear: request_schoolyear
            },
            dataType: "json",
            success: function (data) {
                $("#classj").html("");
                $("#classj").prepend(lIST_PUPIL.loadclass(data.classj));
                $("#classj").prepend('<option value selected ></option>');
            },
            error: function () {
            }
        });
    },
    loadYear: function () {
        var request_year = $("#year").val();
        var request_schoolyear = $("#orgSchoolYear").val();
        if (request_year != "") {
            $("#orgSchoolYear").removeAttr("disabled", "disabled");
            $("#classj").removeAttr("disabled", "disabled");
            if(request_schoolyear != ""){
                $.ajax({
                    beforeSend: function () {
                        $("#loadingIcon").show();
                        $("#classj").prop("disabled", true);
                    },
                    complete: function () {
                        $("#loadingIcon").hide();
                        $("#classj").prop("disabled", false);
                    },
                    global: false,
                    type: 'POST',
                    url: lIST_PUPIL.ajaxGetListClass,
                    data: {
                        year: request_year,
                        schoolyear: request_schoolyear
                    },
                    dataType: "json",
                    success: function (data) {
                        $("#classj").html("");
                        $("#classj").prepend(lIST_PUPIL.loadclass(data.classj));
                        $("#classj").prepend('<option value selected ></option>');
                    },
                    error: function () {
                    }
                });
            }
        }
        else{
            $("#orgSchoolYear").attr("disabled", "disabled");
            $("#classj").attr("disabled", "disabled");
        }
    },
    loadclass: function (data) {
        var $html = '';
        if (data) {
            $.each(data, function (index, value) {
                $html += '<option value="' + value.id + '">' + lIST_PUPIL.htmlencode(value.className)
                        + '</option>';
            });
        }
        return $html;
    },
    loadSearchOrgSchoolYear: function () {
        var request_year = $("#year").val();
        var request_schoolyear = $("#orgSchoolYear").val();
        $.ajax({
            beforeSend: function () {
                $("#loadingIcon").show();
                $("#classj").prop("disabled", true);
            },
            complete: function () {
                $("#loadingIcon").hide();
                $("#classj").prop("disabled", false);
            },
            global: false,
            type: 'POST',
            url: lIST_PUPIL.ajaxGetListClassName,
            data: {
                year: request_year,
                schoolyear: request_schoolyear
            },
            dataType: "json",
            success: function (data) {
                $("#classj").html("");
                $("#classj").prepend(lIST_PUPIL.loadSearchClass(data.classj));
                $("#classj").prepend('<option value selected ></option>');
            },
            error: function () {
            }
        });
    },
    loadSearchYear: function () {
        var request_year = $("#year").val();
        var request_schoolyear = $("#orgSchoolYear").val();
        if (request_schoolyear != "" || request_schoolyear != null) {
            $.ajax({
                beforeSend: function () {
                    if(request_schoolyear != ""){
                        $("#loadingIcon").show();
                        $("#classj").prop("disabled", true);
                    }
                },
                complete: function () {
                    $("#loadingIcon").hide();
                    $("#classj").prop("disabled", false);
                },
                global: false,
                type: 'POST',
                url: lIST_PUPIL.ajaxGetListClassName,
                data: {
                    year: request_year,
                    schoolyear: request_schoolyear
                },
                dataType: "json",
                success: function (data) {
                    $("#classj").html("");
                    $("#classj").prepend(lIST_PUPIL.loadSearchClass(data.classj));
                    $("#classj").prepend('<option value selected ></option>');
                },
                error: function () {
                }
            });
        }
    },
    loadSearchClass: function (data) {
        var $html = '';
        if (data) {
            $.each(data, function (index, value) {
                $html += '<option value="' + lIST_PUPIL.htmlencode(value.className) + '">' + lIST_PUPIL.htmlencode(value.className)
                        + '</option>';
            });
        }
        return $html;
    },
    daysInMonth: function (month, year) {
        return new Date(year, month, 0).getDate();
    },
    /**
     *  *
     * 
     * @param {int}
     *            The month number, 0 based  *
     * @param {int}
     *            The year, not zero based, required to account for leap years  *
     * @return {Date[]} List with date objects for each day of the month  
     */
    getDaysInMonth: function (month, year) {
        var number = lIST_PUPIL.daysInMonth(month, year);
        var days = [];
        days.push('');
        for (i = 1; i <= number; i++) {
            days.push(i);
        }
        return days;
    },
    getMonthInYear: function (year) {
        var months = [];
        months.push('');
        for (i = 1; i <= 12; i++) {
            months.push(i);
        }
        return months;
    },
    loadDay: function () {
        var currentDay = $("#ddlDay").val();
        var year = $('#ddlYear').val();
        var month = $('#ddlMonth').val();
        if (month < 1 || year < 1) {
            $("#ddlDay").empty();
        }
        var options = $("#ddlDay");
        var data = lIST_PUPIL.getDaysInMonth(month, year);
        if (month > 0) {
            $("#ddlDay").empty();
            $.each(data, function () {
                options.append($("<option />").val(this).text(this));
            });
        }
        $("#ddlDay").val(currentDay);
    },
    loadMonth: function () {
        var year = $('#ddlYear').val();
        var month = $('#ddlMonth').val();
        if (year < 1) {
            $("#ddlMonth").empty();
            $("#ddlDay").empty();
        }
        var options = $("#ddlMonth");
        var data = lIST_PUPIL.getMonthInYear(year);
        if (year > 0 && (month === null || month === '' || month === '0')) {
            $("#ddlMonth").empty();
            $.each(data, function () {
                options.append($("<option />").val(this).text(this));
            });
        }
        lIST_PUPIL.loadDay();
    },
    htmlencode: function (str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
    },
    exPortFile: function () {
        $("#delpupil").find('input').removeAttr("checked");
        var Itemdelete = sessionStorage.getItem('cookieExport');
        if (Itemdelete) {
            $("#exportItem").val(Itemdelete);
        }

        $('#export').submit();
        sessionStorage.clear();
        $("#exportItem").val('');
    },
    deletepupil: function () {
        var Itemdelete = sessionStorage.getItem('cookieExport');
        if (Itemdelete && Itemdelete != '[]') {
            $.ajax({
                type: 'POST',
                url:  lIST_PUPIL.baseUrl + lIST_PUPIL.ajaxCheckDeletePupil,
                data: {pupilIds: Itemdelete},
                success: function (data) {
                    if(data.status == 3){
                        ERROR_MESSAGE.show(jsMessages.CanNotDeletePupil);
                    }else{
                         CONFIRM_MESSAGE.show('選択したアイテムを削除します。よろしいですか？', function () {
                            lIST_PUPIL.checkPupilHadApplyOrPaid(Itemdelete, function() {
                                $("#exportItem").val(Itemdelete);
                                sessionStorage.clear();
                                $('#export').attr('action', '/pupil/pupil/delete').submit();
                            });
                        });
                    }
                }
            });
             
        } else {
            ERROR_MESSAGE.show('削除対象項目を選択してください。');
        }
    },
    checkPupilHadApplyOrPaid: function (pupilId, deleteFunction){
        $.ajax({
            type: 'POST',
            url:  lIST_PUPIL.baseUrl + lIST_PUPIL.ajaxCheckHadApplyOrPaid,
            contentType: "application/json",
            dataType: 'json',
            data: JSON.stringify({pupilId: pupilId}),
            success: function (data) {
                if(!data.success){
                    ALERT_MESSAGE.show(data.messages);
                }else{
                    deleteFunction();
                }
            },
        });
    }
};
$(document).ready(function () {
    COMMON.showCrossEditMessage('#submitform');
    $('form:first *:input[type!=hidden][disabled!=disabled]:first').focus();
    $('#select_all').click(function (event) {
        if (this.checked) {
            $('.checkbox1').each(function () {
                this.checked = true;
            });
        } else {
            $('.checkbox1').each(function () {
                this.checked = false;
            });
        }
    });
    $('.checkbox1').click(function (event) {
        var check_checked = true;
        if (this.checked) {
            $('.checkbox1').each(function () {
                if ($(this).is(':checked')) {

                } else {
                    check_checked = false;
                }
            });
            if (check_checked == true) {
                $('.table-striped .checkbox_list #select_all').prop('checked', true);
            }
        } else {
            $('.table-striped .checkbox_list #select_all').prop('checked', false);
        }
    });
    // index
    if (sessionStorage.getItem('cookieExport')) {
        var allitem = sessionStorage.getItem('cookieExport');
        allitem = allitem.split(',');
        $.each(allitem,
                function (index, value) {
                    $('#delpupil #' + value).attr('checked',
                            'checked');
                });
        if ($('.checkbox1').length > 0
                && $('.checkbox1').length == $('.checkbox1:checked').length)
            $('#select_all').attr('checked', 'checked');
    }

//		$("#eikenYear").on('change', function() {
//			var request_year = $("#eikenYear").val();
//			if(request_year !="" || request_year!=null){
//				$.ajax({
//		              type: 'POST',
//		              url : location.protocol + '//' + location.hostname + '/pupil/pupil/ajaxGetListKai',
//		              data: {eikenYear:request_year},
//		              dataType:"json",
//		              success:function(data){
//		            	  $("#kai").html("");
//		            	  $("#kai").prepend(lIST_PUPIL.loadkai(data.kai));
//		            	  $("#kai").prepend('<option value selected ></option>');
//		              },
//		              error:function(){
//		              }
//		            });
//				}
//		});
    $("#delpupil input[type=checkbox]").on('change', function () {
        if ($(this).attr('id') == 'select_all') {
            if (!$(this).is(':checked')) {
                $('#delpupil input').each(function () {
                    $(this).prop('checked', false);
                })
            } else
                $('#delpupil input').each(function () {
                    $(this).prop('checked', true);
                })
        }

        var pupilList = sessionStorage.getItem('cookieExport');
        if (!pupilList) {
            pupilList = new Array();
        }
        else {
            pupilList = pupilList.split(',');
        }
        $("#delpupil input[type=checkbox]").each(function () {
            var idPupil = $(this).val();
            var found = $.inArray('' + idPupil, pupilList);
            if (this.checked) {
                if (found < 0 && $.isNumeric(idPupil)) {
                    pupilList.push(idPupil);
                }
            } else {
                if (found >= 0)
                    pupilList.splice(found, 1);
            }
        });
        sessionStorage.setItem('cookieExport', pupilList.join());
    });

});