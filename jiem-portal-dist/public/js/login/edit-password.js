var EDIT_PASSWORD = {
    baseUrl: window.location.protocol + "//" + window.location.host + "/",
    urlAjaxChangePassword: 'basicConstruction/uac/ajaxChangePassword',
    urlAjaxChangePasswordFirst: 'basicConstruction/uac/ajaxChangePasswordFirst',
    urlPolicy: 'policy-first-login',
    urlProfile: 'profile',
    Msg_Change_Pass_Success: 'パスワードが変更されました。',
    ajaxChangePassword: function (redirectUrl) {
        $.ajax({
            url: EDIT_PASSWORD.baseUrl + EDIT_PASSWORD.urlAjaxChangePassword,
            type: 'POST',
            data: {
                oldPassword: $("#oldPassword").val(),
                newPassword: $("#newPassword").val(),
                confirmNewPassword: $("#confirmNewPassword").val()
            },
            success: function (data) {
                $("#show_error_content").html('');
                $(".form-control").removeClass('error');
                var result = $.parseJSON(data);
                if (result.status == 0) {
                    $("#show_error").show();
                    $('#oldPassword').removeAttr('autofocus');
                    var input_error;
                    for (input_error in result.error) {

                        $("#show_error_content").append('<li style="color:red;cursor: pointer;" onclick="$(\'#' + input_error + '\').focus();">' + result.error[input_error] + '</li>');
                        if (input_error == "all") {
                            $(".form-control").each(function () {
                                if (this.value == "") {
                                    $(this).addClass("error");
                                }
                            });
                        } else {
                            $('#' + input_error).addClass("error");
                        }

                    }
                } else {
                    $("#show_error").hide();
                    ALERT_MESSAGE.show(EDIT_PASSWORD.Msg_Change_Pass_Success,
                            function ()
                            {
                                if (result.agreePolicy == 0) {
                                    window.location.href = EDIT_PASSWORD.baseUrl + EDIT_PASSWORD.urlPolicy;
                                } else {
                                    window.location.href = redirectUrl;
                                }
                            });
                }
            }
        });
    },
    ajaxChangePasswordFirst: function () {
        $.ajax({
            url: EDIT_PASSWORD.baseUrl + EDIT_PASSWORD.urlAjaxChangePasswordFirst,
            type: 'POST',
            data: {
                txtUserID: $("#txtUserID").val(),
                txtFistname: $("#txtFistname").val(),
                txtlastname: $("#txtlastname").val(),
                txtEmailAddress: $("#txtEmailAddress").val(),
                oldPassword: $("#oldPassword").val(),
                newPassword: $("#newPassword").val(),
                confirmNewPassword: $("#confirmNewPassword").val()
            },
            success: function (data) {

                $("#show_error_content").html('');
                $(".form-control").removeClass('error');
                var result = $.parseJSON(data);
                if (result.status == 0) {
                    $("#show_error").show();
                    $('#txtUserID').removeAttr('autofocus');
                    var input_error;
                    for (input_error in result.error) {
                        $("#show_error_content").show().append('<li style="color:red;cursor: pointer;" onclick="$(\'#' + input_error + '\').focus();">' + result.error[input_error] + '</li>');
                        if (input_error == "all") {
                            $(".form-control").each(function () {
                                if (this.value == "") {
                                    $(this).addClass("error");
                                }
                            });
                        } else {
                            $('#' + input_error).addClass("error");
                        }

                    }
                } else {
                    $("#show_error").hide();
                    ALERT_MESSAGE.show(EDIT_PASSWORD.Msg_Change_Pass_Success,
                            function ()
                            {
                                if (result.agreePolicy == 0) {
                                    window.location.href = EDIT_PASSWORD.baseUrl + EDIT_PASSWORD.urlPolicy;
                                } else {
                                    window.location.href = EDIT_PASSWORD.baseUrl + EDIT_PASSWORD.urlProfile;
                                }
                            });
                }
            }
        });
    }
}