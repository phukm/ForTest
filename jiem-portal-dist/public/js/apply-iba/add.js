var APPLY_IBA = {
    frmRegisterIBA: '#registeriba',
    urlSaveDraftAction: COMMON.baseUrl + 'iba/iba/save-draft',
    urlIsRegisterTestDate: COMMON.baseUrl + 'iba/iba/is-register-test-date',
    init: function () {
        $('form:first *:input[type!=hidden]:first').focus();
        $('#datetimepicker').datepicker();
        APPLY_IBA.onlyNumber('#txtZipCode1,#txtZipCode2', false);
        APPLY_IBA.onlyNumber('#txtPeopleA,#txtPeopleB,#txtPeopleC,#txtPeopleD,#txtPeopleE', false);
        APPLY_IBA.onlyNumber('#txtCDA,#txtCDB,#txtCDC,#txtCDD,#txtCDE', false);
        APPLY_IBA.onlyNumber('#rankNo,#questionNo', false);
        APPLY_IBA.onlyNumber('#txtTel,#txtFax', true);
        //duocdd
        $('#select_all').click(function (event) {  //on click
            if (this.checked) { // check select status
                $('.checkbox1').each(function () { //loop through each checkbox
                    this.checked = true;  //select all checkboxes with class "checkbox1"
                });
                $('#cbRankNo').prop('checked',false);
            } else {
                $('.checkbox1').each(function () { //loop through each checkbox
                    this.checked = false; //deselect all checkboxes with class "checkbox1"
                });
            }
            APPLY_IBA.checkInput();
            APPLY_IBA.checkValidOptionApplyToSubmit();
        });
        $('.checkbox1').click(function (event) {  //on click
            var check_checked = true;
            if (this.checked) { // check select status
                $('.checkbox1').each(function () { //loop through each checkbox
                    check_checked = (!$(this).is(':checked')) ? false : true;
                });
                if (check_checked == true) {
                    $('.checkbox_list #select_all').prop('checked', true);
                }
            } else {
                $('.checkbox_list #select_all').prop('checked', false);
            }
        });
        $("#submitform").click(function () {
            $("#back").attr('value', 1);
            var zipCode1 = $('#txtZipCode1').val();
            var zipCode2 = $('#txtZipCode2').val();
            var testDate = $('#datetimepicker').val();
            var idDraft = $('#idDraft').val();
            COMMON.zipcode.load(zipCode1 + zipCode2, function (response) {
                $.ajax({
                    type: 'POST',
                    url: APPLY_IBA.urlIsRegisterTestDate,
                    data: {testDate: testDate, id: idDraft},
                    success: function (resposeTestDate) {
                        if (!response.success) {
                            jQuery.validator.addMethod("maxZipCode", function () {
                                return false;
                            });
                            APPLY_IBA.validateFrmRegister(resposeTestDate);
                            $(APPLY_IBA.frmRegisterIBA).submit();
                        } else {
                            jQuery.validator.addMethod("maxZipCode", function () {
                                return true;
                            });
                            APPLY_IBA.validateFrmRegister(resposeTestDate);
                            $(APPLY_IBA.frmRegisterIBA).submit();
                        }
                    }
                });
            });
        });
        $("#savedap").click(function () {
            $('#registeriba').attr('action', APPLY_IBA.urlSaveDraftAction);
            $('#registeriba')[0].submit();

        });
        //end duoc


        $('div.r19 input[type=radio]').click(function () {
            if ($(this).attr('value') == '0') {
                $('div.r20').hide();
            } else {
                $('div.r20').show();
            }
        });

        $('.checkbox1').click(function () {
            var input = $(this).closest('tr').find('.content-table-input');

            if ($(this).prop('checked') == false) {
                input.attr('disabled', 'disabled');
                input.val('');
            } else {
                input.removeAttr('disabled');
            }
        });

        $('#ddlPurpose').change(function () {
            if ($(this).val() == 'other') {
                $('.r16').show();
            } else {
                $('.r16').hide();
            }
        });
        $(".Sum").change(function () {
            var total = 0;
            $('.Sum').each(function () { //loop through each checkbox
                var val = $(this).val();
                if (val === '') {
                    return;
                }
                total = total + parseInt(val);
            });
            $('#totalPeople').val(total);
            $('.r17 ').find('.totalPeople span').html(total);
        });

        $(".SumCD").change(function () {
            var total = 0;
            $('.SumCD').each(function () { //loop through each checkbox
                var val = $(this).val();
                if (val === '') {
                    return;
                }
                total = total + parseInt(val);
            });
            $('#totalCD').val(total);
            $('.r17 ').find('.totalCD span').html(total);
        });
        APPLY_IBA.checkValidOptionApplyToSubmit();
    },
    isRegisterTestDate: function (responseRegisterTestDate) {
        if (responseRegisterTestDate.success) {
            jQuery.validator.addMethod("isRegisterTestDate", function () {
                return false;
            });
        } else {
            jQuery.validator.addMethod("isRegisterTestDate", function () {
                return true;
            });
        }
    },
    showCalendar: function (args) {
        if (args == 0) {
            var id = "datetimepicker";
        } else {
            var id = "datetimepicker" + args;
        }
        $('#' + id).datepicker('show');
    },
    loadAddressByZipcode: function () {
        var zipCode1 = $('#txtZipCode1').val();
        var zipCode2 = $('#txtZipCode2').val();
        COMMON.zipcode.autoFill(zipCode1 + zipCode2, 'ddlPrefecture', 'txtAdd1', function (res) {
            if (res.success) {
                jQuery.validator.addMethod("maxZipCode", function () {
                    return false;
                });
                $('#txtZipCode1,#txtZipCode2,#ddlPrefecture,#txtAdd1').removeClass('error');
            } else {
                if (zipCode1 === '' && zipCode2 === '') {
                    $('#txtZipCode1').addClass('error');
                    $('#txtZipCode2').addClass('error');
                } else if (zipCode1 === '') {
                    $('#txtZipCode1').addClass('error');
                    $('#txtZipCode2').removeClass('error');
                } else if (zipCode2 === '') {
                    $('#txtZipCode1').removeClass('error');
                    $('#txtZipCode2').addClass('error');
                } else {
                    $('#txtZipCode1').removeClass('error');
                    $('#txtZipCode2').removeClass('error');
                }
            }
        });
    },
    checkInput: function () {
        if ($('div.r19 input[id=option]').prop('checked') == true) {
            $('div.r20').hide();
        } else {
            $('div.r20').show();
        }

        $('.checkbox1').each(function () {
            var input = $(this).closest('tr').find('.content-table-input');

            if ($(this).prop('checked') == false) {
                input.attr('disabled', 'disabled');
                input.val('');
            } else {
                input.removeAttr('disabled');
            }
        });

        if ($('#ddlPurpose').val() == 'other') {
            $('.r16').show();
        } else {
            $('.r16').hide();
        }
    },
    onlyNumber: function (selector, isPhone) {    
        $(selector).keydown(function (e) {
        	var listCode = [46, 8, 9, 27, 13];
            if (isPhone === true)
                listCode.push(189);
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
    checkEmtyImplAndRegex: function (resposeTestDate) {
        var peopleA = parseInt($('#txtPeopleA').val()) ? parseInt($('#txtPeopleA').val()) : 0;
        var peopleB = parseInt($('#txtPeopleB').val()) ? parseInt($('#txtPeopleB').val()) : 0;
        var peopleC = parseInt($('#txtPeopleC').val()) ? parseInt($('#txtPeopleC').val()) : 0;
        var peopleD = parseInt($('#txtPeopleD').val()) ? parseInt($('#txtPeopleD').val()) : 0;
        var peopleE = parseInt($('#txtPeopleE').val()) ? parseInt($('#txtPeopleE').val()) : 0;
        var numberCDA = parseInt($('#txtCDA').val()) ? parseInt($('#txtCDA').val()) : 0;
        var numberCDB = parseInt($('#txtCDB').val()) ? parseInt($('#txtCDB').val()) : 0;
        var numberCDC = parseInt($('#txtCDC').val()) ? parseInt($('#txtCDC').val()) : 0;
        var numberCDD = parseInt($('#txtCDD').val()) ? parseInt($('#txtCDD').val()) : 0;
        var numberCDE = parseInt($('#txtCDE').val()) ? parseInt($('#txtCDE').val()) : 0;
        var questionNo = parseInt($('#questionNo').val()) ? parseInt($('#questionNo').val()) : 0;
        var rankNo = parseInt($('#rankNo').val()) ? parseInt($('#rankNo').val()) : 0;
        var testDate = $('#datetimepicker').val();
        var dateSelect = new Date(testDate);
        var miniDateSelect = dateSelect.getTime();
        var startDate = new Date();
        startDate.setHours(0,0,0,0);
        var miniDateCompare = startDate.getTime() + (14 * 60 * 60 * 24 * 1000);
        jQuery.validator.addMethod("regexHyphenNumber", function (value, element) {
            var regex = /^[0-9+-]+$/;
            return this.optional(element) || regex.test(value);
        });
        jQuery.validator.addMethod("regxEmail", function (value, element) {
            var regex = /^([a-zA-Z])+([a-zA-Z0-9_.+-])*([a-zA-Z0-9])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            var regex2 = /^([a-zA-Z]){1}\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            return this.optional(element) || regex.test(value) || regex2.test(value);
        });

        jQuery.validator.addMethod("checkEmtyImpl", function () {
            return true;
        });
        jQuery.validator.addMethod("emtyOptionMenuQuestionNo", function () {
            return true;
        });
        jQuery.validator.addMethod("emtyOptionMenuRankNo", function () {
            return true;
        });
        jQuery.validator.addMethod("checkDateFormat", function () {
            return true;
        });
        jQuery.validator.addMethod("checkDateFormat", function (value, element) {
            if (value == '')
                return false;

            var DatePattern = /^(\d{4})(\/|-)(\d{2})(\/|-)(\d{2})$/; //Declare Regex
            var dateArray = value.match(DatePattern); // is format OK

            if (dateArray == null)
                return false;

            //Checks for yyy/mm/dd format.
            dtMonth = dateArray[3];
            dtDay = dateArray[5];
            dtYear = dateArray[1];

            if (dtMonth < 1 || dtMonth > 12)
                return false;
            else if (dtDay < 1 || dtDay > 31)
                return false;
            else if ((dtMonth == 4 || dtMonth == 6 || dtMonth == 9 || dtMonth == 11) && dtDay == 31)
                return false;
            else if (dtMonth == 2) {
                var isleap = (dtYear % 4 == 0 && (dtYear % 100 != 0 || dtYear % 400 == 0));
                if (dtDay > 29 || (dtDay == 29 && !isleap))
                    return false;
            }
            return true;
        }, "日付の形式はYYYY/MM/DDとしてください。");
        jQuery.validator.addMethod("compareTestDate", function () {
            if($('#datetimepicker').attr('data-rule-comparetestdate') && miniDateSelect < miniDateCompare){
                return false;
            }else if (testDate != $("#old-test-date").val() && miniDateSelect < miniDateCompare) {
                return false;
            } else {
                return true;
            }
        });
        jQuery.validator.addMethod("checkOppositeOption", function () {
//            if($('#optionMenu6').hasClass('error') || $('#cbRankNo').hasClass('error')){
//                return true;
//            }
            if($('#optionMenu6').is(':checked') && $('#cbRankNo').is(':checked')){
                return false;
            }
            return true;
        });
        APPLY_IBA.isRegisterTestDate(resposeTestDate);
        jQuery.validator.addMethod("checkFullSize", function (value) {
            var lengtKanji = value.replace(/^\s+|\s+$/g, '');
            if (/(?:[a-zA-Z0-9-_\'!@#$%^&*()\uff5F-\uff9F\u0020])/.test(lengtKanji) == true && lengtKanji.length > 0) {
                return false;
            } else {
                if (/[\u0020]/.test(lengtKanji) == true) {

                    return false;
                }
            }
            return true;
        }, "は全角で文字以内で入力してください。");
        if ($('#cbQuestionNo').prop('checked') && questionNo === 0) {
            jQuery.validator.addMethod("emtyOptionMenuQuestionNo", function () {
                return false;
            });
        }
        if ($('#cbRankNo').prop('checked') && rankNo === 0) {
            jQuery.validator.addMethod("emtyOptionMenuRankNo", function () {
                return false;
            });
        }

        if (peopleA == 0 && peopleB == 0 && peopleC == 0 && peopleD == 0 && peopleE == 0 && numberCDA == 0 && numberCDB == 0 && numberCDC == 0 && numberCDD == 0 && numberCDE == 0) {
            jQuery.validator.addMethod("checkEmtyImpl", function () {
                return false;
            });
        } else {
            var check = {
                txtPeopleA: 'txtCDA',
                txtPeopleB: 'txtCDB',
                txtPeopleC: 'txtCDC',
                txtPeopleD: 'txtCDD',
                txtPeopleE: 'txtCDE'
            };
            for (var i in check) {
                var elPeople = parseInt($('#' + i).val()) ? parseInt($('#' + i).val()) : 0;
                var elCD = parseInt($('#' + check[i]).val()) ? parseInt($('#' + check[i]).val()) : 0;

                if (elPeople === 0 && elCD === 0) {
                    continue;
                }
                if (elPeople === 0 && elCD !== 0) {
                    jQuery.validator.addMethod(APPLY_IBA.returnCheckEmtyImpl('#' + i), function (value, element) {
                        return false;
                    });
                } else {
                    jQuery.validator.addMethod(APPLY_IBA.returnCheckEmtyImpl('#' + i), function (value, element) {
                        return true;
                    });
                }
                if (elPeople !== 0 && elCD === 0) {
                    jQuery.validator.addMethod(APPLY_IBA.returnCheckEmtyImpl('#' + check[i]), function (value, element) {
                        return false;
                    });
                } else {
                    jQuery.validator.addMethod(APPLY_IBA.returnCheckEmtyImpl('#' + check[i]), function (value, element) {
                        return true;
                    });
                }
            }

        }
    },
    returnCheckEmtyImpl: function (selector) {
        var data = '';
        switch (selector) {
            case '#txtPeopleA' :
                data = 'checkEmtyImplPeopleA';
                break;
            case '#txtPeopleB' :
                data = 'checkEmtyImplPeopleB';
                break;
            case '#txtPeopleC' :
                data = 'checkEmtyImplPeopleC';
                break;
            case '#txtPeopleD' :
                data = 'checkEmtyImplPeopleD';
                break;
            case '#txtPeopleE' :
                data = 'checkEmtyImplPeopleE';
                break;
            case '#txtCDA' :
                data = 'checkEmtyImplCDA';
                break;
            case '#txtCDB' :
                data = 'checkEmtyImplCDB';
                break;
            case '#txtCDC' :
                data = 'checkEmtyImplCDC';
                break;
            case '#txtCDD' :
                data = 'checkEmtyImplCDD';
                break;
            case '#txtCDE' :
                data = 'checkEmtyImplCDE';
                break;
        }
        return data;
    },
    maxLengthInput: function (id, number) {
        var el = $(id);
        if (el.val().length >= number) {
            el.val(el.val().substring(0, number));
        }
    },
    validateFrmRegister: function (resposeTestDate) {
        APPLY_IBA.checkEmtyImplAndRegex(resposeTestDate);
        $(APPLY_IBA.frmRegisterIBA).validate({
            onfocusout: false,
            onkeyup: false,
            onclick: false,
            focusInvalid: false,
            errorPlacement: function (error, element) {
            },
            showErrors: function (errorMap, errorList) {
                this.defaultShowErrors();
                var aryListMessageError = [];
                var key = 0;
                for (i = 0; i < errorList.length; i++) {
                    if (errorList[i].message != '') {
                        aryListMessageError.push(errorList[i]);

                    }
                    key++;
                }
                ERROR_MESSAGE.show(aryListMessageError, function () {
                }, 'inline');
            }
        });
    },
    checkValidOptionApplyToSubmit: function () {
        if ($('input[name=optionApply]:checked').val() == 1 && $('.checkbox1:input[type=checkbox]:checked').length == 0) {
            $('#submitform').attr('disabled', 'disabled');
        } else
            $('#submitform').removeAttr('disabled');
    }
};
