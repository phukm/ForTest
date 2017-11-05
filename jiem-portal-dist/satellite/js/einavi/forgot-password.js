var FORGOT_PASSWORD = {
    requestChangePasswordAction: COMMON.baseUrl + 'einavi/request-change-password',
    init: function () {
        $('#email').focus();
        $('#year').change(function () {
            FORGOT_PASSWORD.loadDay();
        });
        $('#month').change(function () {
            FORGOT_PASSWORD.loadDay();
        });
        $('#btnSend').click(function () {
            if (FORGOT_PASSWORD.validate()) {
                FORGOT_PASSWORD.requestChangePassword();
            }
        });
    },
    loadDay: function () {
        var year = $('#year').val();
        var month = $('#month').val();
        var day = new Date(parseInt(year), parseInt(month), 0).getDate();
        var html = '<option value=""></option>';
        for (var i = 1; i <= day; i++) {
            html = html + '<option value="' + (i < 10 ? '0' + i : i) + '">' + (i < 10 ? '0' + i : i) + '</option>'
        }
        $('#day').html(html);
    },
    requestChangePassword: function () {
        var email = $('#email').val();
        var birthday = String($('#year').val()) + String($('#month').val()) + String($('#day').val());
        $.ajax({
            type: 'POST',
            url: FORGOT_PASSWORD.requestChangePasswordAction,
            data: {
                email: email,
                birthday: birthday
            },
            dataType: "json",
            beforeSend: function () {
                $('#btnSend').addClass('disabled').attr('disabled');
                $('#btnCancel').addClass('disabled');
            },
            error: function () {
                ERROR_MESSAGE.show(messages.errorTechnicalIssue, function () {
                    $('#loadingModal').modal('hide');
                });
            },
            complete: function () {
                $('#btnSend').removeClass('disabled').removeAttrs('disabled');
                $('#btnCancel').removeClass('disabled');
            },
            success: function (data) {
                if (!data) {
                    ERROR_MESSAGE.show(messages.errorTechnicalIssue);
                    return false;
                }
                else if (data.auth_result == true) {
                    ERROR_MESSAGE.show(email + messages.passwordIsCompleted, function () {
                        $('#month').val("");
                        $('#day').val("");
                        window.location.href = COMMON.baseUrl;
                    });
                    return false;
                }
                else if (data.auth_result == 'BKE_0008') {
                    ERROR_MESSAGE.show(messages.userDontNotExist);
                    return false;
                }
                ERROR_MESSAGE.show(messages.emailNotExistInEinavi);
                return false;
            },
        });
    },
    validate: function () {
        var message = [];
        var email = $('#email').val();
        var year = $('#year').val();
        var month = $('#month').val();
        var day = $('#day').val();
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        if (typeof email == 'undefined' || email == null || email == '') {
            message.push({id: 'email', message: messages.MSG1});
        }
        else if (!regex.test(email)) {
            message.push({id: 'email', message: messages.emailRegex});
        }
        if (typeof year == 'undefined' || year == null || year == '') {
            message.push({id: 'year', message: messages.MSG1});
        }
        if (typeof month == 'undefined' || month == null || month == '') {
            message.push({id: 'month', message: messages.MSG1});
        }
        if (typeof day == 'undefined' || day == null || day == '') {
            message.push({id: 'day', message: messages.MSG1});
        }
        if (message != '') {
            $('div input').removeClass('error');
            $('div select').removeClass('error');
            $.each(message, function (i, v) {
                $('#' + v.id).addClass('error');
            });
            ERROR_MESSAGE.show(message, null, 'inline');

            return false;
        }
        $('.jiem-error').hide();
        $('div input').removeClass('error');
        $('div select').removeClass('error');

        return true;
    }
}