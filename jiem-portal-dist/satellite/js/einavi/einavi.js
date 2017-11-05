var EINAVI = {
    save: function (form) {
        $.ajax({
            type: 'POST',
            url: '/einavi/submitregister',
            dataType: 'json',
            data: $(form).serializeObject(),
            success: function (result) {
                // If no result was found
                if (!result)
                    ERROR_MESSAGE.inlineMessage(result.msg);
                else if (!result.status)
                    ERROR_MESSAGE.inlineMessage(result.msg);
                else {
                    //register success				
                    ERROR_MESSAGE.show(result.msg);
                    $(".modal").on('click', '#btnOkModal', function () {
                        $('body').removeAttr('style');                        
                        window.location.href = MSG.addressredirect;
                        //window.location = window.location.protocol + "//" + window.location.hostname;
                    });
                }
            },
            error: function () {
                ERROR_MESSAGE.inlineMessage(MSG.MsgAnError);
                setTimeout(function () {
                    $('#loadingModal').modal('hide');
                }, 2000);
            }
        });
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
        var number = EINAVI.daysInMonth(month, year);
        var days = [];
        for (i = 1; i <= number; i++) {
            days.push(i);
        }
        return days;
    },
    loadDay: function () {
        var year = $('#ddlYear').val();
        var month = $('#ddlMonth').val();        
        if ((year === null || year === '') || (month === null || month === ''))
        {
            $("#ddlMonth").empty();
            $("#ddlDay").empty();
        }
        if (year.length > 0 && (month === null || month === ''))
        {
            $("#ddlMonth").empty();
            var months = $("#ddlMonth");
            months.append($("<option />"));
            for (var i = 1; i <= 12; i++)
            {
                months.append($("<option />").val(i).text(i));
            }
        }
        else if (year.length > 0 && month.length > 0)
        {
        	var currentDay = $("#ddlDay").val();
            $("#ddlDay").empty();
            var options = $("#ddlDay");            
            var data = EINAVI.getDaysInMonth(month, year);
            options.append($("<option />"));
            $.each(data, function () {
                options.append($("<option />").val(this).text(this));
            });
            $("#ddlDay").val(currentDay);
        }
        //update BD thanhnx 27/8/2015  validate if <=15 year old
        var currentDate = new Date();
        var currenYear = currentDate.getFullYear();
        if (currenYear - year <= 15) {
            $("#txtParent").val('');
            $("#parent").slideDown();
        }
        else
        {
            $("#parent").fadeOut('fast');
        }
    },
    validate: function (form) {
        //validate email
        jQuery.validator.addMethod("regxEmail", function (value, element) {
            var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            return this.optional(element) || regex.test(value);
        }, MSG.MsgInvalidEmail);
        //validate password

        jQuery.validator.addMethod("phonenumber", function (value, element) {
            if ($.trim(value).length == 1)
                return $.trim(value).match(/[0-9]/) !== null;
            return $.trim(value).match(/^(\d+-?)+\d+$/) !== null;
        }, MSG.MsgRequiredNumber);

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
        }, MSG.InvalidBirthday);

        var validationMessages = {
            txtPassword: {
                minlength: MSG.MsgInvalidPassword,
                maxlength: MSG.MsgInvalidPassword
            },
            txtPassword2: {
                minlength: MSG.MsgInvalidPassword,
                maxlength: MSG.MsgInvalidPassword,
                equalTo: MSG.MsgPasswordConfirmNotMatch
            },
            txtPostalCode1: {
                minlength: MSG.MsgRequired3,
                maxlength: MSG.MsgRequired3,
                number: MSG.MsgRequiredNumber
            },
            txtPostalCode2: {
                minlength: MSG.MsgRequired4,
                maxlength: MSG.MsgRequired4,
                number: MSG.MsgRequiredNumber
            }
        };
        var checkValidate = $("#" + form).validate({
            onfocusout: false,
            onkeyup: false,
            onclick: false,
            focusInvalid: false,
            rules: EINAVI.validationRules,
            errorPlacement: function (error, element) {
            },
            showErrors: function (errorMap, errorList) {
                this.defaultShowErrors();
                ERROR_MESSAGE.show(errorList, function () {
                }, 'inline');

            },
            submitHandler: function (form) {
                ERROR_MESSAGE.clear();
                EINAVI.save(form);
            },
            messages: validationMessages
            ,
            invalidHandler: function (form, validator) {
                //focus first error
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var firstInvalidElement = $(validator.errorList[0].element);
                    $('html,body').scrollTop(firstInvalidElement.offset().top);
                    firstInvalidElement.focus();

                    //handler custom error radio and check box
                    if (!$("#rdSex1").is(':checked') && !$("#rdSex2").is(':checked'))
                    {
                        $(".rdsex").addClass("rdsex-error");
                    }
                    else
                    {
                        $(".rdsex").removeClass("rdsex-error");
                    }

                    if (!$("#chkAgree").is(':checked'))
                    {
                        $(".chkAgree").addClass("chkerror");
                    }
                    else
                    {
                        $(".chkAgree").removeClass("chkerror");
                    }

                }
            }
        });

    },
    validationRules: {
        txtMailAdd: {
            required: true,
            regxEmail: true,
            maxlength: 256
        },
        txtPassword: {
            required: true,
            minlength: 6,
            maxlength: 32
        },
        txtPassword2: {
            required: true,
            minlength: 6,
            maxlength: 32,
            equalTo: '#txtPassword'
        },
        rdSex: {
            required: true
        },
        txtFirstName: {
            required: true,
            maxlength: 18
        },
        txtLastName: {
            required: true,
            maxlength: 18
        },
        rdSex : {
            required: true
        },
                ddlYear: {
                    required: true
                },
        ddlYear : {
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
        txtPostalCode1: {
            required: true,
            number: true,
            minlength: 3,
            maxlength: 3
        },
        txtPostalCode2: {
            required: true,
            number: true,
            minlength: 4,
            maxlength: 4
        },
        rdReceive: {
            //required : true			
        },
        chkAgree: {
            required: true
        },
        txtParent: {
            required: true
        }
    }
};
function setTabIndexIdentify() {
    var count = 0;
    $("select,input,.btn").each(function () {
        $(this).attr('tabindex', count);
        count++;
    });
}

$(document).ready(function () {
    setTabIndexIdentify();
    //EINAVI.loadDay();
    if ($("#getEinaviId").length) {
        $('#txtMailAdd').focus();
        EINAVI.validate('getEinaviId');
    }

    $(document).on('hidden.bs.modal', '.modal', function () {
        $('body').removeAttr('style');
    });
//    $(document).on('shown.bs.modal', '.modal', function () {
//        $('body').removeAttr('style');
//    });

    //target to input error
    $(".jiem-error").on('click', '.alert li a', function () {
        var idinput = $(this).attr('href');
        setTimeout(function () {
            $(idinput).focus();
        }, 100);
    });
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