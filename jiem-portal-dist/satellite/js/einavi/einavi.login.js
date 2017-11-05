var EINAVILOGIN = {
    save: function (form) {
        $.ajax({
            type: 'POST',
            url: '/einavi/submitlogin',
            dataType: 'json',
            data: $(form).serializeObject(),
            success: function (result) {
                // If no result was found
                if (!result)
                    ERROR_MESSAGE.inlineMessage(result.msg);
                else if (!result.status)
                    ERROR_MESSAGE.inlineMessage(result.msg);
                else {
                    // login
                    ERROR_MESSAGE.show(result.msg);
                    $(".modal").on('click', '#btnOkModal', function () {
                        $('body').removeAttr('style');
                        window.location.href = MSG.addressredirect;
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
    validate: function (form) {
        // validate email
        var validationMessages = {
            // txtMai : {
            // rangelength : MSG.MSG60
            // }
        };
        var checkValidate = $("#" + form)
                .validate(
                        {
                            onfocusout: false,
                            onkeyup: false,
                            onclick: false,
                            focusInvalid: false,
                            rules: EINAVILOGIN.validationRules,
                            errorPlacement: function (error, element) {
                            },
                            showErrors: function (errorMap, errorList) {
                                this.defaultShowErrors();
                                ERROR_MESSAGE.show(errorList, function () {
                                }, 'inline');

                            },
                            submitHandler: function (form) {
                                ERROR_MESSAGE.clear();
                                EINAVILOGIN.save(form);
                            },
                            messages: validationMessages,
                            invalidHandler: function (form, validator) {
                                // focus first error
                                var errors = validator.numberOfInvalids();
                                if (errors) {
                                    var firstInvalidElement = $(validator.errorList[0].element);
                                    firstInvalidElement.focus();
                                }
                            }
                        });

    },
    validationRules: {
        txtMailAdd: {
            required: true
        },
        txtPassword: {
            required: true
        }
    }
};
$(document).ready(function () {
    if ($("#loginEinavi").length) {
        $('#txtMailAdd').focus();
        EINAVILOGIN.validate('loginEinavi');
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
        setTimeout(function(){$(idinput).focus();},100);
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