var PAYMENT_INFO = {
    baseUrl: window.location.protocol + "//" + window.location.host + "/",
    getHalTypeInDantaiUrl: 'eiken/get-hall-type',
    applyEikenConfirm: COMMON.baseUrl + 'satellite',
    saveActionUrl: COMMON.baseUrl + 'eiken/save',
    applyEikenUrl: COMMON.baseUrl + 'eiken/apply-eiken',
    paymentByCreditUrl: COMMON.baseUrl + 'payment-eiken-exam/pay-by-credit',
    paymentByConbiniUrl: COMMON.baseUrl + 'payment-eiken-exam/send-message-to-sqs',
    paymentInformationUrl: COMMON.baseUrl + 'payment-eiken-exam/payment-infomation',
    init: function () {
        $(document).ready(function () {
        });
    },
    payment: function (id, kyu) {
        var kyu = [kyu];
        if (isSupportCredit && isSupportCombini) {
            CONFIRM_PAYMENT_METHOD_MESSAGE.show(messageDeadLine,
                function () {
                    if(msgCreditDeadline){
                        ERROR_MESSAGE.show(msgCreditDeadline,function(){return false;});
                        return false;
                    }
                    window.location.href = window.location.protocol + "//" + window.location.host + '/payment-eiken-exam/pay-by-credit/' + id;
                }, function () {
                    if(msgCombiniDeadline){
                        ERROR_MESSAGE.show(msgCombiniDeadline,function(){return false;});
                        return false;
                    }
                    PAYMENT_INFO.payByCombini(kyu);
                }, function () {

                }, '確認',
                translate.PAY_NOW,
                translate.payByCombini,
                null
            );
        } else if (isSupportCredit) {
            if(msgCreditDeadline){
                ERROR_MESSAGE.show(msgCreditDeadline,function(){return false;});
                return false;
            }
            window.location.href = window.location.protocol + "//" + window.location.host + '/payment-eiken-exam/pay-by-credit/' + id;
        } else if (isSupportCombini) {
            CONFIRM_PAYMENT_METHOD_MESSAGE.show(messageDeadLine,
                function () {
                }, function () {
                    if(msgCombiniDeadline){
                        ERROR_MESSAGE.show(msgCombiniDeadline,function(){return false;});
                        return false;
                    }
                    PAYMENT_INFO.payByCombini(kyu);
                }, function () {
                }, null, isSupportCredit ? translate.PAY_NOW : null, isSupportCombini ? translate.payByCombini : null, null
            );
        } else {
            ERROR_MESSAGE.show('システムエラーが発生しました。システム管理者に連絡してください。');
        }
    },
    payByCombini: function (listKyu) {
        $.ajax({
            type: 'POST',
            url: PAYMENT_INFO.paymentByConbiniUrl,
            data: {listKyu: listKyu},
            dataType: 'json',
            success: function (data) {
                //Todo here when success.
                if (data == 'true') {
                    ALERT_MESSAGE.show(translate.msgWaitReceiptNo,
                        function () {
                            window.location.href = PAYMENT_INFO.paymentInformationUrl;
                        },
                        function () {
                            window.location.href = PAYMENT_INFO.paymentInformationUrl;
                        }
                    );
                } else {
                    ALERT_MESSAGE.show(data,
                        function () {
                            window.location.href = PAYMENT_INFO.paymentInformationUrl;
                        },
                        function () {
                            window.location.href = PAYMENT_INFO.paymentInformationUrl;
                        }
                    );
                }
            }
        });
    }
};