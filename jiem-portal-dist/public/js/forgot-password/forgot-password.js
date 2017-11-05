var forgotPassword = {
    init: function () {
        $("#submitform").click(function () {
            $('#validServer').remove();
            forgotPassword.validateFrm();
            var checkEmail = forgotPassword.validateEmail();
            if (!checkEmail) {
                $('#txtEmail').addClass('error');
                return false;
            }
            var option = $('input[name="radioOption"]:checked').length;
            $('#radioOption').removeClass('error').addClass('errorRadio');
            if (option > 0) {
                $('#radioOption').removeClass('errorRadio');
            }
        });
    },
    validateFrm: function () {
        $('#frmForgot').validate({
            onfocusout: false,
            onkeyup: false,
            onclick: false,
            focusInvalid: false,
            errorPlacement: function (error, element) {
            },
            showErrors: function (errorMap, errorList) {
                this.defaultShowErrors();
                ERROR_MESSAGE.show(errorList, function () {
                }, 'inline');
                var option = $('input[name="radioOption"]:checked').length;
                $('#radioOption').removeClass('error').addClass('errorRadio');
                if (option > 0) {
                    $('#radioOption').removeClass('errorRadio');
                }
            }
        });
    },
    validateEmail: function () {
        var message = [];
        var email = $('#txtEmail').val();
        var option = $('input[name="radioOption"]:checked').length;
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        if (typeof email != 'undefined' && email != '' && !regex.test(email)) {
            if (option < 1) {
                message.push({id: 'radioOption', message: MSG1});
                $('#radioOption').removeClass('error').addClass('errorRadio');
            } else {
                $('#radioOption').removeClass('error').removeClass('errorRadio');
            }
            message.push({id: 'txtEmail', message: Msg_Error_Vaild_Email});
            if (message) {
                ERROR_MESSAGE.show(message, null, 'inline');
            }

            return false;
        }
        return true;
    }
};

$(document).ready(function () {
    forgotPassword.init();
});