var configIndexAccessKey = {
    FRM_ACCESS_KEY: '#frm-access-key',
    URL_ACCESS_KEY_IS_USE: COMMON.baseUrl + 'access-key/access-key/is-use-access-key',
    URL_ACCESS_KEY_DELETE_USE: COMMON.baseUrl + 'access-key/access-key/delete-user',
};

var indexAccessKey = {
    init: function() {
        // focus element input first and ignore element input type hidden
        if(forcusBlue != 1){
            $('form:first *:input[type!=hidden]:first').focus();
        }
        
        $("#submitForm").click(function () {
            $('#validServer').remove();
            indexAccessKey.validateFrm();
            indexAccessKey.isUseAccessKey();
        });
    },
    validateFrm: function () {
        $(configIndexAccessKey.FRM_ACCESS_KEY).validate({
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
    },
    isUseAccessKey: function(){
        var orgNo = $('#organizationNo').val();
        var accessKey = $('#accessKey').val();
        $.ajax({
            method: "POST",
            url: configIndexAccessKey.URL_ACCESS_KEY_IS_USE,
            dataType: 'json',
            data: {orgNo: orgNo,accessKey : accessKey},
            success: function (response) {
                if(response.status == 1){
                    CONFIRM_MESSAGE.show(response.MSG, function () {
                        indexAccessKey.deleteUser();
                    });
                }
                if(response.status == 0){
                     $(configIndexAccessKey.FRM_ACCESS_KEY).submit();
                }
               
                
            }
        });
    },
    deleteUser: function(){
        var orgNo = $('#organizationNo').val();
        $.ajax({
            method: "POST",
            url: configIndexAccessKey.URL_ACCESS_KEY_DELETE_USE,
            dataType: 'json',
            data: {orgNo: orgNo},
            success: function (response) {
                $(configIndexAccessKey.FRM_ACCESS_KEY).submit();
            }
        });
    }
};

$(function() {
    indexAccessKey.init();
});