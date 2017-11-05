/*
 * @author : Huy Manh (ManhNH5)
 */
var PAYMENT_EIKEN_EXAM = {
    paymentConfirm: COMMON.baseUrl + 'payment-eiken-exam/payment-confirm',
    initital: function () {
        PAYMENT_EIKEN_EXAM.onlyNumber('#card-number,#card-cvv,#card-year,#card-month', true);
        $('#btnNext').click(function () {
            if (PAYMENT_EIKEN_EXAM.validate()) {
                PAYMENT_EIKEN_EXAM.submit();
            }
        });
        $('#card-first-name').focus();
    },
    validate: function () {
        var stringKana = /^([゠ァアィイゥウェエォオカガキギクグケゲコゴサザシジスズセゼソゾタダチヂッツヅテデトドナニヌネノハバパヒビピフブプヘベペホボポマミムメモャヤュユョヨラリルレロヮワヰヱヲンヴヵヶヷヸヹヺ・ーヽヾヿ]+)$/;
        var halfSize = /[a-zA-Z0-9-_\'!@#$%^&*()\uff5F-\uff9F\u0020]/;
        var cardFirstName = $('input[name="cardFirstName"]').val();
        var cardLastName = $('input[name="cardLastName"]').val();
        var cardNumber = $('input[name="cardNumber"]').val();
        var cardCvv = $('input[name="cardCvv"]').val();
        var cardMonth = $('input[name="cardMonth"]').val();
        var cardYear = $('input[name="cardYear"]').val();
        var chooseKyu = $('input[name="chooseKyu[]"]:checked');
        var date = new Date();
        var message = [];

        cardFirstName = $.trim(cardFirstName);
        cardLastName = $.trim(cardLastName);

        if (cardYear.length == 2) {
            cardYear = date.getFullYear().toString().substr(0, 2) + cardYear;
        }
        if (cardFirstName.length < 1) {
            message.push({id: 'card-first-name', message: translate.MSG1});
        }
        else if (halfSize.test(cardFirstName)) {
            message.push({id: 'card-first-name', message: translate.MSG22});
        }
        else if (!stringKana.test(cardFirstName)) {
            message.push({id: 'card-first-name', message: translate.MSG23b});
        }
        if (cardLastName.length < 1) {
            message.push({id: 'card-last-name', message: translate.MSG1});
        }
        else if (halfSize.test(cardLastName)) {
            message.push({id: 'card-last-name', message: translate.MSG22});
        }
        else if (!stringKana.test(cardLastName)) {
            message.push({id: 'card-last-name', message: translate.MSG23b});
        }
        if (cardNumber.length < 1) {
            message.push({id: 'card-number', message: translate.MSG1});
        }
        else if (cardNumber.indexOf('-') != -1) {
            message.push({id: 'card-number', message: translate.MSG2});
        }
        else if (!$.isNumeric(cardNumber)) {
            message.push({id: 'card-number', message: translate.MSG3});
        }
        else if (cardNumber.length > 16) {
            message.push({id: 'card-number', message: translate.MSG0});
        }
        if (cardMonth.length < 1) {
            message.push({id: 'card-month', message: translate.MSG1});
        }
        else if (cardMonth.length > 2 || parseInt(cardMonth) > 12 || parseInt(cardMonth) < 1) {
            message.push({id: 'card-month', message: translate.MSG27});
        }
        else if (!PAYMENT_EIKEN_EXAM.invalidMonth(cardMonth)) {
            message.push({id: 'card-month', message: translate.MSG27});
        }
        if (cardYear.length < 1) {
            message.push({id: 'card-year', message: translate.MSG1});
        }
        else if (cardYear.length != 4 || ((parseInt(cardYear) < date.getFullYear()) && parseInt(cardYear) > date.getFullYear() + 5)) {
            message.push({id: 'card-year', message: translate.MSG4});
        }
        else if (!PAYMENT_EIKEN_EXAM.invalidYear(cardYear)) {
            message.push({id: 'card-year', message: translate.MSG4});
        }
        else if (parseInt(cardMonth) > 0 && !PAYMENT_EIKEN_EXAM.checkCurrentDate(cardMonth, cardYear)) {
            message.push({id: 'card-month', message: translate.MSG4});
        }
        if (cardCvv.length < 1 || cardCvv.length > 4 || !$.isNumeric(cardCvv)) {
            message.push({id: 'card-cvv', message: translate.MSG1});
        }
//       
        if (message != '') {
            $('div input').removeClass('error');
            $('div textarea').removeClass('error');
            $('div select').removeClass('error');
            $('div').removeClass('error');
            $.each(message, function (i, v) {
                $('#' + v.id).addClass('error');
            });
            ERROR_MESSAGE.show(message, null, 'inline');
            return false;
        }

        return true;
    },
    onlyNumber: function (selector, number) {
        $(selector).keydown(function (e) {
            var listCode = [46, 8, 9, 27, 13];
            if ($.inArray(e.keyCode, listCode) !== -1 ||
                (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                (e.keyCode >= 35 && e.keyCode <= 40)) {
                return;
            }
            if ((e.ctrlKey === true && e.keyCode === 67) || (e.ctrlKey === true && e.keyCode === 86)) {
                return;
            }
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    },
    checkCurrentDate: function (month, year) {
        var date = new Date();
        if (year == date.getFullYear() && month <= date.getMonth()) {
            return false;
        }
        return true;
    },
    invalidMonth: function (month) {
        var date = new Date();
        if (parseInt(month) > 0 && parseInt(month) < 13 && $.isNumeric(month)) {
            return true;
        }
        return false;
    },
    invalidYear: function (year) {
        var date = new Date();
        if (parseInt(year) >= date.getFullYear() && $.isNumeric(year)) {
            return true;
        }
        return false;
    },
    submit: function () {
        $('#paymentEikenExam').attr('action', PAYMENT_EIKEN_EXAM.paymentConfirm).submit();
    }
}