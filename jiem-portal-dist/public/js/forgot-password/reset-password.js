var configResettPassword = {
    FRM_Add: '#frmResetPassword'
};

var resetPassword = {
    init: function() {

        $("#submitform").click(function () {
            $('#validServer').remove();
            resetPassword.validateFrm();
        });
    },
    validateFrm: function () {
        jQuery.validator.addMethod("characterUser", function (value, element) {
            value = value.trim();
            if( /^[a-zA-Z0-9-_\uFF61-\uFFDC\uFFE8-\uFFEE]*$/.test(value) && /^[a-zA-Z]{1}/.test(value)){
                if (value.length > 3 && value.length < 32) {
                    return true;
                }
            }
            return false;
        }, "");

        jQuery.validator.addMethod("fieldLength", function (value) {
            value = value.trim();
            if(value.length >= 32 || value.length < 6){
                return false;
            }
            return true;
        });
        jQuery.validator.addMethod("halfSize", function (value) {
            var regex = /^[a-zA-Z0-9-_!@#$%^&*()\uFF61-\uFFDC\uFFE8-\uFFEE]*$/;
            
            if(value.length > 0 && regex.test(value)){
                if (value.length > 5 && value.length < 32) {
                    if(/(:?[a-z])/.test(value) && /(:?[A-Z])/.test(value) && /(:?[0-9])/.test(value)){  
                        return true;
                    }
                }
            }
            return false;
        });
        
        $(configResettPassword.FRM_Add).validate({
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
            },
            submitHandler: function (form) {
                form.submit();
            }
        });
    }
};

$(document).ready(function() {
    resetPassword.init();
});