var config = {
    FRMPOLICY: "#frm-policy",
    PHONE_NUMBER: "#txtPhoneNumber"
};

var pageSetup = {
    init: function () {
        $('form:first *:input[type!=hidden]:first').focus();
        pageSetup.onlyNumberPhone();
        $("#submitform").click(function () {
            pageSetup.validateFrm();
            $("#frm-policy").submit();
        });
    },
    onlyNumberPhone: function () {
        $(config.PHONE_NUMBER).keydown(function (e) {
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                    (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                    (e.keyCode >= 35 && e.keyCode <= 40)) {
                return;
            }
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    },
    validateFrm: function () {
        //validate email
        jQuery.validator.addMethod("regxEmail", function (value, element) {
            var regex = /^([a-zA-Z])+([a-zA-Z0-9_.+-])*([a-zA-Z0-9])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            var regex2 = /^([a-zA-Z]){1}\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            return this.optional(element) || regex.test(value) || regex2.test(value);
        });
        $(config.FRMPOLICY).validate({
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
$(function () {
    pageSetup.init();
});
