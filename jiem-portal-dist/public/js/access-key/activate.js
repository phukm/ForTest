var config = {
    FRM_ACCESS_KEY : '#frm-access-key-to-activate',
    URL_ACCESS_KEY_TO_ACTIVATE: COMMON.baseUrl + 'access-key/access-key/activate',
};

var activeAccessKey = {
    init: function() {
        // focus element input first and ignore element input type hidden
        if(forcusBlue != 1){
            $('form:first *:input[type!=hidden]:first').focus();
        }
        
        $("#submitForm").click(function () {
            activeAccessKey.validateFrm();
            $('#validServer').remove();
            $(config.FRM_ACCESS_KEY).submit();
        });
    },
    validateFrm: function () {
        $(config.FRM_ACCESS_KEY).validate({
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
            }
        });
    }
};

$(function() {
    activeAccessKey.init();
});