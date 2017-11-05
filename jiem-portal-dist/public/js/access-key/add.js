var configAddAccessKey = {
    FRM_Add: '#frm-create-first-user',
    URL_LOGIN: COMMON.baseUrl+'login',
};

var addAccessKey = {
    init: function() {
        if(forcusBlue != 1){
            $('form:first *:input[type!=hidden]:first').focus();
        }
//        trim for ie 8 
        if(typeof String.prototype.trim !== 'function') {
            String.prototype.trim = function() {
              return this.replace(/^\s+|\s+$/g, ''); 
            }
          }
        $('#confirmEimail').bind("cut paste", function (e) {
            e.preventDefault();
        });
        $("#submitform").click(function () {
            if($("#checkBoxPolicy").is(':checked')){
                $('#validServer').remove();
                addAccessKey.validateFrm();
               $("#frm-create-first-user").submit();
            }else{
                var msgCheckBox = $('#checkBoxPolicy').data('msg-required');
                if(msgCheckBox){
                    ERROR_MESSAGE.show(msgCheckBox);
                }
            }
            
        });
        if (status == 1) {
            ALERT_MESSAGE.show(msgReturnToLoginPage, function () {
                window.location.href= configAddAccessKey.URL_LOGIN;
            });
            $('#confirmPopupModal').on('hidden.bs.modal', function () {
                setTimeout(function(){
                    window.location.href= configAddAccessKey.URL_LOGIN;
                },100);
            });
        }
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
        jQuery.validator.addMethod("regxEmail", function (value, element) {
            var regex = /^([a-zA-Z])+([a-zA-Z0-9_.+-])*([a-zA-Z0-9])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            var regex2 = /^([a-zA-Z]){1}\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            return this.optional(element) || regex.test(value) || regex2.test(value);
        });
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

        jQuery.validator.addMethod("checkPwdSameUser", function (value) {
            var userId = $('#userId').val();
            if(value == userId){
                return false;
            }
            
            return true;
        });
        $(configAddAccessKey.FRM_Add).validate({
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