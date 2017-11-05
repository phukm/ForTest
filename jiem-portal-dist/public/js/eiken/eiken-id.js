var EIKEN_ID = {
    save: function (form) {
        $.ajax({
            type: 'POST',
            url: '/eiken/eikenid/save',
            dataType: 'json',
            data: $(form).serializeObject(),
            success: function (result) {
                // If no result was found
                if (!result)
                    ERROR_MESSAGE.show(eikAppLevelMess.NoResultFound);
                else if (!result.eikenId || result.eikenId == '00')
                    ERROR_MESSAGE.show(eikAppLevelMess.ApiSystemError);
                else if (result.eikenId == '01')
                    ERROR_MESSAGE.show(eikAppLevelMess.PassMisMatch);
                else if (result.isCrossOrgEikenId != 0) {
                    ERROR_MESSAGE.show(eikAppLevelMess.CrossOrgEikenId);
                }
                else if (result.isValidEikenLevel != 1) {
                    ERROR_MESSAGE.show(eikAppLevelMess.InvalidKyu);
                }
                else {
                    if (result.action == 'reference')
                        window.location.href = '/eiken/eikenpupil/create';
                    else {
                        ERROR_MESSAGE.show(eikAppLevelMess.MSG52, function () {
                            window.location.href = '/eiken/eikenpupil/create';
                        });
                    }
                }

            },
            error: function () {
                ERROR_MESSAGE.show(eikAppLevelMess.SystemError);
            }
        });
    },
    showpupil: function (loadFirstTime) {
        if ($('#ddlJobCode').val() == 1) {
            $('#dvShowPupil').show();
            $('#dvShowSchoolCode').show();
        }
        else {
            $('#dvShowPupil').hide();
            $('#dvShowSchoolCode').hide();
        }
    },
    daysInMonth: function (month, year) {
        return new Date(year, month, 0).getDate();
    },
    /**
      * @param {int} The month number, 0 based
      * @param {int} The year, not zero based, required to account for leap years
      * @return {Date[]} List with date objects for each day of the month
      */
    getDaysInMonth: function (month, year) {
        var number = EIKEN_ID.daysInMonth(month, year);
        var days = [];
        for (i = 1; i <= number; i++)
        { 
            days.push(i);
        }
            return days;
    },
    loadDay: function () {
        var year = $('#ddlYear').val();
        var month = $('#ddlMonth').val();
        var currentDay = $("#ddlDay").val();
        $("#ddlDay").empty();
        var options = $("#ddlDay");
        var data = EIKEN_ID.getDaysInMonth(month, year);
        options.prepend("<option value='' selected='selected'></option>");
        $.each(data, function () {
            options.append($("<option />").val(this).text(this));
        });
        $("#ddlDay").val(currentDay);
    },
    loadClass: function (ddlClass) {
        var id = $('#ddlSchoolYear').val();
        var jsonurl = '/eiken/eikenid/getclass?schoolyear=' + id;
        $.ajax({
            type: 'GET',
            url: jsonurl,
            data: {},
            success: function (data) {
                $("#ddlClass").empty();
                $("#ddlClass").prepend("<option value='' selected='selected'></option>");
                var options = $("#ddlClass");
                $.each(data, function () {
                    options.append($("<option />").val(this.id).text(this.className));
                });
                if (ddlClass)
                    $('#ddlClass').val(ddlClass);
            },
            error: function () {
            }
        });
    },
    loadSchoolName: function () {
        var schoolCode = $('#ddlSchoolCode').val();
        if (schoolCode == null || schoolCode == '') {
            schoolCode = 0;
        }
        var schoolCode = parseInt(schoolCode);
        var arrSchoolName = " ,大学,短大 ,高専,高校,中学,小学,専修各種学校,大学院";
        arrSchoolName = arrSchoolName.split(',');
    },
    validate: function (form) {
        jQuery.validator.addMethod("total1", function (value, element) {
            var data1 = $.trim($("#txtPostalCode1").val()).length;
            if (data1 == 3) {
                return true;
            }
            return false;
        }, '3' + eikAppLevelMess.MSG70);
        jQuery.validator.addMethod("total2", function (value, element) {
            var data2 = $.trim($("#txtPostalCode2").val()).length;
            if (data2 == 4) {
                return true;
            }
            return false;
        }, '4' + eikAppLevelMess.MSG70);
        jQuery.validator.addMethod("number2", function (value, element) {
            return this.optional(element) || /^[0-9]/.test(value);
        }, eikAppLevelMess.MSG34);
        jQuery.validator.addMethod("number", function (value, element) {
            return value >= 0;
        }, eikAppLevelMess.MSG34);
        jQuery.validator.addMethod("phonenumber", function (value, element) {
            if ($.trim(value).length == 1)
                return $.trim(value).match(/[0-9]/) !== null;
            if ($.trim(value).indexOf("-") == -1)
            	return false;
            return $.trim(value).match(/^(\d+-?)+\d+$/) !== null;
        }, eikAppLevelMess.MSG34);

        // Validate birthday <= current day
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
        }, eikAppLevelMess.InvalidBirthday);
        // Validate full width
        jQuery.validator.addMethod('fullwidth', function (value, element) {
            return EIKEN_ID.isFullWidth($.trim(value));
        }, eikAppLevelMess.FullWidthFont);

        // Validate kanakana font
        jQuery.validator.addMethod('katakana', function (value, element) {
            return EIKEN_ID.isKataKana($.trim(value));
        }, eikAppLevelMess.MSG_Kana_Error);
        
        // Validate halfsize font
        jQuery.validator.addMethod('halfsize', function (value, element) {
            return EIKEN_ID.isHalfsize($.trim(value));
        }, eikAppLevelMess.MSG60);
        // Custom messages
        var validationMessages = {
            txtEikenPassword: {
                rangelength: eikAppLevelMess.MSG60
            },
            txtPass: {
                rangelength: eikAppLevelMess.MSG60
            }
        };
        var checkValidate = $("#" + form).validate({
            onfocusout: false,
            onkeyup: false,
            onclick: false,
            focusInvalid: false,
            rules: EIKEN_ID.validationRules,
            errorPlacement: function (error, element) {
            },
            showErrors: function (errorMap, errorList) {
                this.defaultShowErrors();
                // Remove duplicate postal code error message
                var postalCodeErrors = [];
                var removeKey = null;
                $.each(errorList, function (key, value) {
                    if (typeof value.element != 'undefined') {
                        if (typeof value.element.id != 'undefined') {
                            id = value.element.id;
                        }
                    } else {
                        id = value.id;
                    }
                    if (value.message == eikAppLevelMess.MSG28 && (id == 'txtPostalCode1' || id == 'txtPostalCode2')) {
                        postalCodeErrors.push({key: key});
                        removeKey = key;
                    }
                });
                if (postalCodeErrors.length >= 2) {
                    errorList.splice(removeKey, 1);
                }
                ERROR_MESSAGE.show(errorList, function () {
                }, 'inline');
            },
            submitHandler: function (form) {
                ERROR_MESSAGE.clear();
                if ($.trim($('#txtAreaCode').val()) == '')
                	CONFIRM_MESSAGE.show(eikAppLevelMess.EikIdConfirmWithoutAreaCode, function () {
                		EIKEN_ID.save(form);
        			});
                else
                	EIKEN_ID.save(form);
            },
            messages: validationMessages,
            highlight: function (element) {
            	if ($(element).attr('id') == 'rdGender') $('input[name=rdGender]').addClass('errorRadio');
            	$(element).addClass('error');
            },
            unhighlight: function (element) {
            	if ($(element).attr('id') == 'rdGender') $('input[name=rdGender]').removeClass('error errorRadio');
            	$(element).removeClass('error');
            }
        });
    },
    getPersonalInfoApi: function () {
        // Clear all rules except eikenId and pass
        $.each(EIKEN_ID.validationRules, function (index, value) {
            if (index != 'txtEikenId' && index != 'txtPass' && $('#' + index).length)
                $('#' + index).rules('remove');
        });
        if ($('#refeikenid').valid())
        {
            ERROR_MESSAGE.clear();
            $.ajax({
                type: 'POST',
                url: '/eiken/eikenid/load-reference-api',
                dataType: 'json',
                data: {eikenId: $('#txtEikenId').val(), pass: $('#txtPass').val()},
                success: function (result) {
                    // If no result was found
                    if (!result)
                        ERROR_MESSAGE.show(eikAppLevelMess.MSG51);
                    else if (result.kekka == '00')
                        ERROR_MESSAGE.show(eikAppLevelMess.MSG51);
                    else if (result.kekka == '01')
                        ERROR_MESSAGE.show(eikAppLevelMess.MSG51);
                    else if (result.kekka == '02')
                        ERROR_MESSAGE.show(eikAppLevelMess.MSG51);
                    else {
                        EIKEN_ID.bindData(result);
                    }
                },
                error: function () {
                    ERROR_MESSAGE.show(eikAppLevelMess.SystemError);
                }
            });
        }
        // Readd all rules
        $.each(EIKEN_ID.validationRules, function (index, value) {
            if (index != 'txtEikenId' && index != 'txtPass' && $('#' + index).length)
                $('#' + index).rules('add', value);
        });
    },
    bindData: function (result) {
        // show info form
        $('.ref-result.hide').removeClass('hide');

        $.each(result, function (index, value) {
            $('#' + index).val(value);
            if (index == 'rdGender') {
                if (value == '1')
                    $('input:radio[name=rdGender][value=1]').attr('checked', 'checked');
                else
                    $('input:radio[name=rdGender][value=2]').attr('checked', 'checked');
            }
            if (index == 'ddlJobCode') {
                if (value == '1') {
                    $('#dvShowPupil').show();
                    $('#dvShowSchoolCode').show();
                }
                else {
                    $('#dvShowPupil').hide();
                    $('#dvShowSchoolCode').hide();
                }
            }
        });
        EIKEN_ID.loadSchoolName();
        // Store referred eikenId and password for avoiding modify before submit
        $('#hidden-eiken-id').val($.trim($('#txtEikenId').val()));
        $('#hidden-eiken-pass').val($.trim($('#txtPass').val()));
    },
    handleKeyPress: function (e) {
        var key = e.keyCode || e.which;
        if (key == 13) {
            EIKEN_ID.getPersonalInfoApi();
            e.preventDefault();
        }
    },
    validationRules: {
        txtEikenId: {
            required: true
        },
        txtPass: {
            required: true,
            halfsize: true,
            rangelength: [4, 6]
        },
        txtFirtNameKanji: {
            required: true,
            fullwidth: true,
            maxlength: 36
        },
        rdGender: {
            required: true
        },
        txtLastNameKanji: {
            required: true,
            fullwidth: true,
            maxlength: 36
        },
        txtFirtNameKana: {
            required: true,
            katakana: true,
            maxlength: 36
        },
        txtLastNameKana: {
            required: true,
            katakana: true,
            maxlength: 36
        },
        txtPostalCode1: {
            required: true,
            total1: true,
            number: true,
            number2: true
        },
        txtPostalCode2: {
            required: true,
            total2: true,
            number: true,
            number2: true
        },
        txtTelCode1: {
            required: true,
            phonenumber: true
        },
        txtVillage: {
            required: true,
            fullwidth: true
        },
        txtCity: {
            required: true
        },
        txtBuilding: {
            fullwidth: true
        },
        txtMailAddress: {
            email: true
        },
        txtEikenPassword: {
            required: true,
            halfsize: true,
            rangelength: [4, 6]
        },
        txtArea: {
            required: true,
            fullwidth: true
        },
        txtAreaCode: {
            fullwidth: true
        },
        ddlJobCode: {
            required: true
        },
        ddlSchoolCode: {
            required: true
        },
        ddlYear: {
            required: true
        },
        ddlMonth: {
            required: true,
            birthday: true
        },
        ddlDay: {
            required: true,
            birthday: true
        },
        ddlSchoolYear: {
            required: true,
            maxlength: 1
        },
        ddlClass: {
            required: true,
            maxlength: 2
        }
    },
    isFullWidth: function (value) {
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
    isKataKana: function (value) {
        return (/^[\u30A0-\u30FF\uFF5F-\uFF9F\s]*$/).test(value);
    },
    isHalfsize: function (value) {
        return (/^[a-zA-Z0-9]*$/).test(value);
        //return (/^[a-zA-Z0-9-_\uFF61-\uFFDC\uFFE8-\uFFEE]*$/).test(value);
    }

};
$(document).ready(function () {

    EIKEN_ID.loadSchoolName();
    EIKEN_ID.showpupil(1);
    EIKEN_ID.loadDay();
    if ($('#day-of-birth').length)
        $('#ddlDay').val($('#day-of-birth').val());
    if ($("#getneweikenid").length) {
        $('#txtFirtNameKanji').focus();
        EIKEN_ID.validate('getneweikenid');
    }
    if ($("#refeikenid").length) {
        $('#txtEikenId').focus();
        EIKEN_ID.validate('refeikenid');
    }
});
jQuery.fn.serializeObject = function () {
    var arrayData, objectData;
    arrayData = this.serializeArray();
    objectData = {};

    $.each(arrayData, function () {
        var value;

        if (this.value != null) {
            value = this.value;
        } else {
            value = '';
        }

        if (objectData[this.name] != null) {
            if (!objectData[this.name].push) {
                objectData[this.name] = [objectData[this.name]];
            }

            objectData[this.name].push(value);
        } else {
            objectData[this.name] = value;
        }
    });
    return objectData;
};