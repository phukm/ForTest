var EIKEN_ORG = {
    baseUrl: window.location.protocol + "//" + window.location.host + "/",
    saveEikenUrl: 'eiken/eikenorg/save',
    getOrgNameUrl: 'eiken/eikenorg/get-org-name',
    applyEikenUrl: 'eiken/eikenorg/index',
    confirmationUrl: 'eiken/eikenorg/confirmation',
    applicationFormUrl: 'eiken/eikenorg/create',
    registrationUrl: 'eiken/eikenorg/registrant-info',
    policyUrl: 'eiken/eikenorg/policy',
    detailUrl: 'eiken/eikenorg/applyeikendetails',
    submitUrl: 'eiken/eikenorg/submit',
    loadExamLocationUrl: '/eiken/eikenorg/load-exam-location',
    savePolicyUrl: 'eiken/eikenorg/save-registrant',
    standardConfirmationUrl: 'eiken/eikenorg/standard-confirmation',
    backToHomepageUrl: 'homepage/homepage',
    fundingStatus: 'funding',
    paymentStatus: 'payment',
    saveStatus: 'eiken/eikenorg/save-status',
    checkPaymentType: 'eiken/eikenorg/check-payment-type',
    saveLog: 'eiken/eikenorg/save-log',
    goToCreateGrade: 'org/orgschoolyear/index',
    callAjax: true,
    init: function () {
        EIKEN_ORG.initRegistrant();
        EIKEN_ORG.initApplication();
        EIKEN_ORG.initStandardConfirmation();
        $("#btnStandardConfirmation").click(function () {
            if (typeof definitionSpecial != 'undefined' && parseInt(definitionSpecial) > 0) {
                CONFIRM_MESSAGE.show(jsMessages.SGHMSG48, function () {
                    window.location.href = EIKEN_ORG.baseUrl + EIKEN_ORG.standardConfirmationUrl;
                });
                return true;
            }
            window.location.href = EIKEN_ORG.baseUrl + EIKEN_ORG.standardConfirmationUrl;
        });
        EIKEN_ORG.renderHTML(1);
    },
    OKStandardConfirmation: function () {
        var nameKanji = $.trim($('#nameKanji').text());
        var manager_name = $.trim($('#manager_name').val());
        if (nameKanji != '' && manager_name != '' && manager_name == nameKanji) {
            $('#manager_name').addClass('error');
            ERROR_MESSAGE.show('申込責任者と実施連帯責任者は別の方を入力してください。', function () {
            }, 'inline');
            return;
        }
        $('#manager_name').removeClass('error');
        if (!$('#sc-manager-name-ck').is(':checked') || !$('#sc-kanjai-name-ck').is(':checked')) {
            ERROR_MESSAGE.show(jsMessages.StandardConfirmChk);
        } else {
            if (typeof (isPayment) != "undefined" && isPayment == 0) {
                if (typeof (invitation) != "undefined" && invitation == 0) {
//                    $("#public-funding-modal").modal('show');
//                    return false;
                }
            }
            $('#registrantInfoForm').submit();
        }
    },
    initStandardConfirmation: function () {
        // disable event enter to sumbit of form
        $('#registrantInfoForm').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });

        // validation
        // Add custom rules for validate half-width font
        // Validate full width
        jQuery.validator.addMethod('fullwidth', function (value, element) {
            return EIKEN_ORG.isFullWidth($.trim(value));
        }, jsMessages.FullWidthFont);

        var validator = $("#registrantInfoForm").validate({
            onfocusout: false,
            onkeyup: false,
            onclick: false,
            rules: {
                manager_name: {
                    required: true,
                    maxlength: 40,
                    fullwidth: true
                }
            },
            messages: {
                manager_name: {
                    maxlength: '{0}文字以内で入力してください'
                }
            },
            errorPlacement: function (error, element) {
            },
            showErrors: function (errorMap, errorList) {
                this.defaultShowErrors();
                ERROR_MESSAGE.show(errorList, function () {
                }, 'inline');
            },
            submitHandler: function (form) {
                ERROR_MESSAGE.clear();
                EIKEN_ORG.OKConfirmation();
            }
        });
    },
    initRegistrant: function () {
        // Add custom rules for validate half-width font
        // Validate mapped email
        jQuery.validator.addMethod('mappedemail', function (value, element) {
            return $('#txtConfirmEmailAddress').val() == $('#txtEmailAddress').val();
        }, jsMessages.MSG80);
        jQuery.validator.addMethod('fullwidth', function (value, element) {
            return EIKEN_ORG.isFullWidth($.trim(value));
        }, jsMessages.FullWidthFont);
        jQuery.validator.addMethod("wrongformat", function (value, element) {
            var regex = /^([a-zA-Z])+([a-zA-Z0-9_.+-])*([a-zA-Z0-9])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            var regex2 = /^([a-zA-Z]){1}\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            return this.optional(element) || regex.test(value) || regex2.test(value);
        }, jsMessages.MSG19_wrong_format);
        //Registration form
        var validator = $("#applicationForm").validate({
            onfocusout: false,
            onkeyup: false,
            onclick: false,
            rules: {
                txtLastName: {
                    required: true,
                    maxlength: 20,
                    fullwidth: true
                },
                txtFirstName: {
                    required: true,
                    maxlength: 20,
                    fullwidth: true
                },
                txtPhoneNumber: {
                    required: true,
                    number: true,
                    maxlength: 20
                },
                txtEmailAddress: {
                    required: true,
                    maxlength: 50,
                    wrongformat: true
                },
                txtConfirmEmailAddress: {
                    required: true,
                    maxlength: 50,
                    mappedemail: true,
                    wrongformat: true
                },
                cityId: {
                    required: true
                },
                districtId: {
                    required: true
                }
            },
            messages: {
                txtLastName: {
                    required: jsMessages.MSG1,
                    maxlength: '{0}文字以内で入力してください'
                },
                txtFirstName: {
                    required: jsMessages.MSG1,
                    maxlength: '{0}文字以内で入力してください'
                },
                txtPhoneNumber: {
                    required: jsMessages.MSG1,
                    number: jsMessages.MSG36
                },
                txtEmailAddress: {
                    required: jsMessages.MSG1,
                },
                txtConfirmEmailAddress: {
                    required: jsMessages.MSG1,
                },
                cityId: {
                    required: jsMessages.MSG1
                },
                districtId: {
                    required: jsMessages.MSG1
                }
            },
            errorPlacement: function (error, element) {
            },
            showErrors: function (errorMap, errorList) {
                this.defaultShowErrors();
                ERROR_MESSAGE.show(errorList, function () {
                }, 'inline');
            }
        });
        //$('#txtFirstName').focus();
    },
    navigatorBack: function () {
        window.location = '/homepage/homepage';
    },
    gotoPolicy: function () {
        window.location = '/eiken/eikenorg/policy';
    },
    agreePolicy: function () {
        if ($('#agree-policy').is(':checked')) {
            EIKEN_ORG.confirmPolicy();
        } else {
            ERROR_MESSAGE.show(jsMessages.MSG50);
        }
    },
    confirmPolicy: function () {
        if ($('#applicationForm').valid())
        {
            ERROR_MESSAGE.clear();
            var data = {
                txtFirstName: $('#txtFirstName').val(),
                txtLastName: $('#txtLastName').val(),
                txtEmailAddress: $('#txtEmailAddress').val(),
                cityId: $('#cityId').val(),
                districtId: $('#districtId').val(),
                txtConfirmEmailAddress: $('#txtConfirmEmailAddress').val()
            };
            $.ajax({
                type: 'POST',
                url: EIKEN_ORG.baseUrl + EIKEN_ORG.savePolicyUrl,
                dataType: 'json',
                data: data,
                success: function (result) {
                    if (result.isSuccess != 1) {
                        ERROR_MESSAGE.show(jsMessages.SentPolicyUnsuccessfully);
                    } else {
                        window.location = EIKEN_ORG.baseUrl + EIKEN_ORG.applicationFormUrl;
                    }
                },
                error: function () {
                    ERROR_MESSAGE.show(jsMessages.SystemError);
                }
            });
        }
    },
    disagreePolicy: function () {
        window.location = '/homepage/homepage';
    },
    agreeRegistration: function (isValid) {
        if (!isValid) {
            //If any required fields are blank, system will show error message “MSG 1”
            if ($.trim($('#txtFirstName').val()) == '' || $.trim($('#txtLastName').val()) == '' || $.trim($('#txtEmailAddress').val()) == '' || $.trim($('#txtPhoneNumber').val()) == '') {
                ERROR_MESSAGE.show(jsMessages.MSG1);
            } else {
                //If the inputted value in [電話番号] (Phone number) is not number, system will show error message: “MSG 36”.
                if (!$.isNumeric($.trim($('#txtPhoneNumber').val()))) {
                    ERROR_MESSAGE.show(jsMessages.MSG36);
                    return;
                }
                //	If the inputted value in [メールアドレス] (Email) is not email format, system will show error message: “MSG 19”.
                if (!EIKEN_ORG.validateEmail($.trim($('#txtEmailAddress').val()))) {
                    ERROR_MESSAGE.show(jsMessages.MSG19_wrong_format);
                    return;
                }
            }
        }
    },
    
//GNCCNCJDM-857
//    showDirection: function () {
//        $('#directionModal').modal('show');
//        $('#directionModal').removeClass('hide');
//    },
//    okDirection: function () {
//        $('#directionModal').modal('hide');
//        $('#directionModal').addClass('hide');
//    },

    //APPLICATION FORM TO CREATE/ EDIT
    initApplication: function () {
        $('#txtExpectApplyNo5').focus();
        //Check valid time
        if (typeof eikenOrgDetail.isValidTime != 'undefined') {
            if (!eikenOrgDetail.isValidTime) {
                ERROR_MESSAGE.show(jsMessages.MSG48, function () {
                    window.location = EIKEN_ORG.baseUrl + EIKEN_ORG.applyEikenUrl;
                });
            }
        }

        EIKEN_ORG.checkShowDetailSection();

        $('.standardHall-expect-number').change(function () {
            EIKEN_ORG.updateDisountPupilToStore(1);
            EIKEN_ORG.checkShowDetailSection();
            EIKEN_ORG.getTotal();
            EIKEN_ORG.visibleStandardSection();
        });

        if ($('#date0').length) {
            //default date0 is unchecked any
            $('#date0').attr('checked', false);
            //$('#location-type-single').attr('checked', false);
        }
        //click on datte0
        $('input[name="date0"]').click(function () {
            EIKEN_ORG.date0Click();
        });
        //locationType click
        $('input[name="locationType"]').click(function () {
            EIKEN_ORG.locationTypeClick();
        });
        //locationType1 change
        $('#locationType1').change(function () {
            EIKEN_ORG.locationType1Change($(this));
        });
        $('input[name=locationType]').change(function () {
            $('input[name=locationType]').removeClass('errorRadio');
        });
        $('input[name=date0]').change(function () {
            $('input[name=date0]').removeClass('errorRadio');
        });
        if (typeof eikenOrgDetail.typeExamDate == 'undefined') {
            $('input[name=date0]').attr('checked', false);
        }
        EIKEN_ORG.getTotal();
        EIKEN_ORG.visibleStandardSection(true);
        $('input[name=date1]').change(function () {
            $('input[name=date1]:first').removeClass('errorRadio');
        });
        $('input[name=date2]').change(function () {
            $('input[name=date2]:first').removeClass('errorRadio');
        });
        $('input[name=date3]').change(function () {
            $('input[name=date3]:first').removeClass('errorRadio');
        });
        $('input[name=date4]').change(function () {
            $('input[name=date4]:first').removeClass('errorRadio');
        });
        $('input[name=date5]').change(function () {
            $('input[name=date5]:first').removeClass('errorRadio');
        });
        EIKEN_ORG.disableStandardInfo();
    },
    //Disabled thông tin hội trường chuẩn, nếu đã submit thông tin lên ukestuke
    disableStandardInfo: function () {
        if (typeof isSentStandardHall != 'undefined') {
            if (isSentStandardHall && eikenOrgDetail.cd > 0) {
                $('#totalcd').attr('disabled', true);
                $('input[name=locationType]').attr('disabled', true);
                $('select[name=locationType1]').attr('disabled', true);
                $('#EikenOrgNo1').attr('disabled', true);
                $('#btnGetOrgName').attr('disabled', true);
                $('#EikenOrgNo2').attr('disabled', true);
                $('input[name=date0]').attr('disabled', true);
                if (typeof eikenOrgDetail.lev5 != 'undefined' && eikenOrgDetail.lev5 > 0) {
                    $('input[name=date5]').attr('disabled', true);
                }
                if (typeof eikenOrgDetail.lev4 != 'undefined' && eikenOrgDetail.lev4 > 0) {
                    $('input[name=date4]').attr('disabled', true);
                }
                if (typeof eikenOrgDetail.lev3 != 'undefined' && eikenOrgDetail.lev3 > 0) {
                    $('input[name=date3]').attr('disabled', true);
                }
                if (typeof eikenOrgDetail.preLev2 != 'undefined' && eikenOrgDetail.preLev2 > 0) {
                    $('input[name=date2]').attr('disabled', true);
                }
                if (typeof eikenOrgDetail.lev2 != 'undefined' && eikenOrgDetail.lev2 > 0) {
                    $('input[name=date1]').attr('disabled', true);
                }
            }
        }
        if (typeof eikenOrgDetail.cd != 'undefined') {
            if (eikenOrgDetail.cd == 0) {
                $('input[name=locationType]').attr('checked', false);
                $('#totalcd').val('');
            }
        }
    },
    getTotal: function () {
        $('#total1').text((parseInt($('#txtMainHallExpectApplyNo1').text()) | 0) + (parseInt($('#txtExpectApplyNo1').val()) | 0));
        $('#total2').text((parseInt($('#txtMainHallExpectApplyNo2').text()) | 0) + (parseInt($('#txtExpectApplyNo2').val()) | 0));
        $('#total3').text((parseInt($('#txtMainHallExpectApplyNo3').text()) | 0) + (parseInt($('#txtExpectApplyNo3').val()) | 0));
        $('#total4').text((parseInt($('#txtMainHallExpectApplyNo4').text()) | 0) + (parseInt($('#txtExpectApplyNo4').val()) | 0));
        $('#total5').text((parseInt($('#txtMainHallExpectApplyNo5').text()) | 0) + (parseInt($('#txtExpectApplyNo5').val()) | 0));
        $('#total6').text((parseInt($('#txtMainHallExpectApplyNo6').text()) | 0));
        $('#total7').text((parseInt($('#txtMainHallExpectApplyNo7').text()) | 0));
        var grandTotal = (parseInt($('#txtMainHallExpectApplyNo1').text()) | 0) + (parseInt($('#txtExpectApplyNo1').val()) | 0)
                + ((parseInt($('#txtMainHallExpectApplyNo2').text()) | 0) + (parseInt($('#txtExpectApplyNo2').val()) | 0))
                + ((parseInt($('#txtMainHallExpectApplyNo3').text()) | 0) + (parseInt($('#txtExpectApplyNo3').val()) | 0))
                + ((parseInt($('#txtMainHallExpectApplyNo4').text()) | 0) + (parseInt($('#txtExpectApplyNo4').val()) | 0))
                + ((parseInt($('#txtMainHallExpectApplyNo5').text()) | 0) + (parseInt($('#txtExpectApplyNo5').val()) | 0))
                + ((parseInt($('#txtMainHallExpectApplyNo6').text()) | 0) + (parseInt($('#txtExpectApplyNo6').val()) | 0))
                + ((parseInt($('#txtMainHallExpectApplyNo7').text()) | 0) + (parseInt($('#txtExpectApplyNo7').val()) | 0));
        $('#grand-total').text(grandTotal);
        var standardExpectation = (parseInt($('#txtExpectApplyNo1').val()) | 0) + (parseInt($('#txtExpectApplyNo2').val()) | 0)
                + (parseInt($('#txtExpectApplyNo3').val()) | 0) + (parseInt($('#txtExpectApplyNo4').val()) | 0)
                + (parseInt($('#txtExpectApplyNo5').val()) | 0);
        $('#grand-total-standard-expectation').text(standardExpectation);
    },
    checkShowDetailSection: function () {
        //check business to show/hide 	金・土の両日にわたり実施（次画面で級別に曜日選択）/ Fri &Sat
        var isSentStandard = (typeof isSentStandardHall != 'undefined' && isSentStandardHall && eikenOrgDetail.cd > 0);
        if (!eikenOrgDetail.isAllowThreeDay) {
            $('.allowAll').remove();
            $('#date3').parent().remove();
//			$('#date1').remove();
//			$('#date11').remove();
            $('#date0-first').parent().remove();
        } else {
//			$('#date0-first').parent().remove();
//			$('#date0-first').remove();
//			$('#date11').parent().remove();
        }

        if (typeof eikenOrgDetail.cd != 'undefined') {
            $('#totalcd').val(eikenOrgDetail.cd);
        }
        if (typeof eikenOrgDetail.typeExamDate != 'undefined') {
            if (eikenOrgDetail.typeExamDate == 4 && $('#date3').length) {
                $('#date1').show();
                $('#date3').attr('checked', true);
            }
            if (eikenOrgDetail.typeExamDate == 1) {
                $('#date0-first').attr('checked', true);
            }

            if (eikenOrgDetail.typeExamDate == 2) {
                $('#date11').attr('checked', true);
            }
            if (eikenOrgDetail.typeExamDate == 3) {
                $('#date2').attr('checked', true);
            }
            //$('#date' + eikenOrgDetail.typeExamDate).attr('checked', true);
        }

        if (typeof eikenOrgDetail.locationType != 'undefined') {
            if (eikenOrgDetail.locationType == 0) {
                $('#location-type-single').attr('checked', true);
                $('.location-type-combination').hide();
            } else {
                $('#location-type-combination').attr('checked', true);
                $('.locationType1').show();
                $('#locationType1 [value=' + eikenOrgDetail.locationType1 + ']').attr('selected', true);
                if (eikenOrgDetail.locationType1 == 2) {
                    $('.EikenOrgNo1').show();
                    $('#EikenOrgNo1').val(eikenOrgDetail.eikenOrgNo1);
                    $('#orgName').text(eikenOrgDetail.eikenOrgNo2);
                    $('#EikenOrgNo123').text(eikenOrgDetail.eikenOrgNo123);
                }
                if (eikenOrgDetail.locationType1 == 1) {
                    $('.EikenOrgNo2').show();
                    $('#EikenOrgNo2').val(eikenOrgDetail.eikenOrgNo2);
                }
            }
        } else {
            $('input[name=locationType]').attr('checked', false);
        }
        var flag = false;
        $('.standardHall-expect-number').each(function () {
            if ($.isNumeric($.trim($(this).val())) && $.trim($(this).val()) > 0) {
                $('#notTheMainHall').show();
                flag = true;

                //kyu 5 - disable date 5 (5級)
                if ($(this).attr('id') == 'txtExpectApplyNo1') {
                    if (typeof eikenOrgDetail.lev5 != 'undefined' && eikenOrgDetail.lev5 > 0 && isSentStandard) {
                        $('input[name=date5]').attr('disabled', true);
                    } else {
                        $('input[name=date5]').attr('disabled', false);
                    }
                    if (typeof $('input[name=date5]:checked').val() == 'undefined' && typeof eikenOrgDetail.dateExamLev5 != 'undefined' && eikenOrgDetail.dateExamLev5 > 0) {
                        $('input[name=date5][value=' + eikenOrgDetail.dateExamLev5 + ']').prop('checked', true);
                    }
                }
                //kyu 4 - disable date 4 (4級)
                if ($(this).attr('id') == 'txtExpectApplyNo2') {
                    if (typeof eikenOrgDetail.lev4 != 'undefined' && eikenOrgDetail.lev4 > 0 && isSentStandard) {
                        $('input[name=date4]').attr('disabled', true);
                    } else {
                        $('input[name=date4]').attr('disabled', false);
                    }
                    if (typeof $('input[name=date4]:checked').val() == 'undefined' && typeof eikenOrgDetail.dateExamLev4 != 'undefined' && eikenOrgDetail.dateExamLev4 > 0) {
                        $('input[name=date4][value=' + eikenOrgDetail.dateExamLev4 + ']').prop('checked', true);
                    }
                }
                //kyu 3- disable date 3 (3級)
                if ($(this).attr('id') == 'txtExpectApplyNo3') {
                    if (typeof eikenOrgDetail.lev3 != 'undefined' && eikenOrgDetail.lev3 > 0 && isSentStandard) {
                        $('input[name=date3]').attr('disabled', true);
                    } else {
                        $('input[name=date3]').attr('disabled', false);
                    }
                    if (typeof $('input[name=date3]:checked').val() == 'undefined' && typeof eikenOrgDetail.dateExamLev3 != 'undefined' && eikenOrgDetail.dateExamLev3 > 0) {
                        $('input[name=date3][value=' + eikenOrgDetail.dateExamLev3 + ']').prop('checked', true);
                    }
                }
                //kyu pre2 - disable date pre2 (準2級)
                if ($(this).attr('id') == 'txtExpectApplyNo4') {
                    if (typeof eikenOrgDetail.preLev2 != 'undefined' && eikenOrgDetail.preLev2 > 0 && isSentStandard) {
                        $('input[name=date2]').attr('disabled', true);
                    } else {
                        $('input[name=date2]').attr('disabled', false);
                    }
                    if (typeof $('input[name=date2]:checked').val() == 'undefined' && typeof eikenOrgDetail.dateExamPreLev2 != 'undefined' && eikenOrgDetail.dateExamPreLev2 > 0) {
                        $('input[name=date2][value=' + eikenOrgDetail.dateExamPreLev2 + ']').prop('checked', true);
                    }
                }
                //kyu 2 - disable date 2 (2級)
                if ($(this).attr('id') == 'txtExpectApplyNo5') {
                    if (typeof eikenOrgDetail.lev2 != 'undefined' && eikenOrgDetail.lev2 > 0 && isSentStandard) {
                        $('input[name=date1]').attr('disabled', true);
                    } else {
                        $('input[name=date1]').attr('disabled', false);
                    }
                    if (typeof $('input[name=date1]:checked').val() == 'undefined' && typeof eikenOrgDetail.dateExamLev2 != 'undefined' && eikenOrgDetail.dateExamLev2 > 0) {
                        $('input[name=date1][value=' + eikenOrgDetail.dateExamLev2 + ']').prop('checked', true);
                    }
                }
                EIKEN_ORG.checkSelectedDateExam();
                //return;
            } else {
                //kyu 5 - disable date 5 (5級)
                if ($(this).attr('id') == 'txtExpectApplyNo1') {
                    $('input[name=date5]').attr('checked', false);
                    $('input[name=date5]').attr('disabled', true);
                    $('input[name=date5]').removeClass('errorRadio');
                }
                //kyu 4 - disable date 4 (4級)
                if ($(this).attr('id') == 'txtExpectApplyNo2') {
                    $('input[name=date4]').attr('checked', false);
                    $('input[name=date4]').attr('disabled', true);
                    $('input[name=date4]').removeClass('errorRadio');
                }
                //kyu 3- disable date 3 (3級)
                if ($(this).attr('id') == 'txtExpectApplyNo3') {
                    $('input[name=date3]').attr('checked', false);
                    $('input[name=date3]').attr('disabled', true);
                    $('input[name=date3]').removeClass('errorRadio');
                }
                //kyu pre2 - disable date pre2 (準2級)
                if ($(this).attr('id') == 'txtExpectApplyNo4') {
                    $('input[name=date2]').attr('checked', false);
                    $('input[name=date2]').attr('disabled', true);
                    $('input[name=date2]').removeClass('errorRadio');
                }
                //kyu 2 - disable date 2 (2級)
                if ($(this).attr('id') == 'txtExpectApplyNo5') {
                    $('input[name=date1]').attr('checked', false);
                    $('input[name=date1]').attr('disabled', true);
                    $('input[name=date1]').removeClass('errorRadio');
                }
            }
        });
    },
    visibleStandardSection: function (isOnReady) {
        var isSentStandard = (typeof isSentStandardHall != 'undefined' && isSentStandardHall && eikenOrgDetail.cd > 0);
        var grandTotalExpected = (parseInt($('#grand-total-standard-expectation').text()) | 0);
        // Update to visible in read mode standard section
        if (!isSentStandard) {
            if ($("#grand-total-standard-expectation").length > 0 && !grandTotalExpected) {
                $('#notTheMainHall input[name=date0]').attr('disabled', true);
                $('#notTheMainHall input[name=date0]').attr('checked', false);
                $('#notTheMainHall input[name=locationType]').attr('disabled', true);
                $('#notTheMainHall input[name=locationType]').attr('checked', false);
                $('#notTheMainHall #totalcd').attr('disabled', true);
                $('#notTheMainHall #totalcd').val('');
                $('#date1').hide();
//				$('input[name=date1]').attr('checked', false);
                $('.locationType1').hide();
                $('#locationType1').val('');
                $('.location-type-combination').hide();
                $('#EikenOrgNo1').val('');
                $('#EikenOrgNo123').val('');
                EIKEN_ORG.isAlreadyReset = true;
            } else if (!isOnReady && EIKEN_ORG.isAlreadyReset) {
                $('#notTheMainHall input[name=date0]').attr('disabled', false);
                $('#notTheMainHall input[name=date0]').attr('checked', false);
                $('#notTheMainHall input[name=locationType]').attr('disabled', false);
                $('#notTheMainHall input[name=locationType]').attr('checked', false);
                $('#notTheMainHall #totalcd').attr('disabled', false);
                $('#notTheMainHall #totalcd').val('');
                $('input[name=date1]').attr('checked', false);
                $('.locationType1').hide();
                $('#date1').hide();
                $('#locationType1').val('');
                $('.location-type-combination').hide();
                $('#EikenOrgNo1').val('');
                $('#EikenOrgNo123').val('');
            }
        }
    },
    isAlreadyReset: false,
    checkSelectedDateExam: function () {
        //checked date
        if (typeof eikenOrgDetail.dateExamLev5 != 'undefined' && eikenOrgDetail.dateExamLev5 == '2') {
            $('input:radio[name="date5"]:last').attr('checked', true);
        } else if (typeof eikenOrgDetail.dateExamLev5 == 'undefined') {
            $('input:radio[name="date5"]').attr('checked', false);
        }
        if (typeof eikenOrgDetail.dateExamLev4 != 'undefined' && eikenOrgDetail.dateExamLev4 == '2') {
            $('input:radio[name="date4"]:last').attr('checked', true);
        } else if (typeof eikenOrgDetail.dateExamLev4 == 'undefined') {
            $('input:radio[name="date4"]').attr('checked', false);
        }
        if (typeof eikenOrgDetail.dateExamLev3 != 'undefined' && eikenOrgDetail.dateExamLev3 == '2') {
            $('input:radio[name="date3"]:last').attr('checked', true);
        } else if (typeof eikenOrgDetail.dateExamLev3 == 'undefined') {
            $('input:radio[name="date3"]').attr('checked', false);
        }
        if (typeof eikenOrgDetail.dateExamPreLev2 != 'undefined' && eikenOrgDetail.dateExamPreLev2 == '2') {
            $('input:radio[name="date2"]:last').attr('checked', true);
        } else if (typeof eikenOrgDetail.dateExamPreLev2 == 'undefined') {
            $('input:radio[name="date2"]').attr('checked', false);
        }
        if (typeof eikenOrgDetail.dateExamLev2 != 'undefined' && eikenOrgDetail.dateExamLev2 == '2') {
            $('input:radio[name="date1"]:last').attr('checked', true);
        } else if (typeof eikenOrgDetail.dateExamLev2 == 'undefined') {
            $('input:radio[name="date1"]').attr('checked', false);
        }
    },
    //End of application form init
    //date 0 click
    date0Click: function () {
        if ($('#date3').is(':checked')) {
            $('#date1').show();
        } else {
            $('#date1').hide();
        }
    },
    locationTypeClick: function () {
        if ($('input[name="locationType"]:last').is(':checked')) {
            $('.locationType1').show();
            EIKEN_ORG.locationType1Change($("#locationType1"));
        } else {
            $('.location-type-combination').hide();
        }
    },
    locationType1Change: function (obj) {

        if (obj.val() == '') {
            $('.EikenOrgNo1').hide();
            $('.EikenOrgNo2').hide();
        }
        //This field will be visible only when value of [受験会場] = “Hội trường chuẩn” and value of [実施区分] = “合同/Combination” and [合同実施区分] = “他団体に合流（子）”
        if (obj.val() == 2) {
            $('.EikenOrgNo1').show();
            $('.EikenOrgNo2').hide();
        }
        //This field will be visible only when value of [受験会場] = “Hội trường chuẩn” and value of [実施区分] = “合同/Combination” and [合同実施区分] = “他団体を吸収（親）”
        if (obj.val() == 1) {
            $('.EikenOrgNo2').show();
            $('.EikenOrgNo1').hide();
        }
    },
    getOrgName: function () {
        if ($('#EikenOrgNo1').val() == '') {
            $('#EikenOrgNo1').addClass('errorHighlight');
            ERROR_MESSAGE.show([{id: 'EikenOrgNo1', message: jsMessages.MSG4}], function () {
            }, 'inline');
            return false;
        }
        $('#EikenOrgNo1').removeClass('errorHighlight');
        $.ajax({
            type: 'POST',
            url: EIKEN_ORG.baseUrl + EIKEN_ORG.getOrgNameUrl,
            dataType: 'json',
            data: {eikenOrgNo: $('#EikenOrgNo1').val()},
            success: function (result) {
                if (result.isEmpty) {
                    ERROR_MESSAGE.show(jsMessages.MSG4, function () {
                    }, 'inline');
                    $('#EikenOrgNo1').addClass('errorHighlight');
                    $('#EikenOrgNo123').text('');
                } else {
                    if(result.eikenOrgName !== null){
                        $('#EikenOrgNo123').text(result.eikenOrgName);   
                    }else{
                        $('#EikenOrgNo123').text(' ');
                    }
                }
            },
            error: function () {
                ERROR_MESSAGE.show(jsMessages.SystemError);
            }
        });
    },
    gotoRegistration: function () {
        window.location = EIKEN_ORG.baseUrl + EIKEN_ORG.policyUrl;
    },
    gotoHomePage: function () {
        window.location = EIKEN_ORG.baseUrl + EIKEN_ORG.backToHomepageUrl;
    },
    backtoPolicy: function () {
        window.location = EIKEN_ORG.baseUrl + EIKEN_ORG.policyUrl;
    },
    cancel: function (eikenScheduleId) {
        window.location = EIKEN_ORG.baseUrl + EIKEN_ORG.detailUrl + '/' + eikenScheduleId;
    },
    saveDraft: function () {
        if (!EIKEN_ORG.checkAllowLessThan10Standard()) {
            ERROR_MESSAGE.show(jsMessages.msgNotAllowLessThan10Standard, function () {
            });
            return false;
        }
        if (!$('#EikenOrgNo2').is(':hidden') || !$('#EikenOrgNo1').is(':hidden')) {
            $('#locationType1').removeClass('errorHighlight');
            if (!$('#EikenOrgNo2').is(':hidden') && $.trim($('#EikenOrgNo2').val()) == '') {
                $('#EikenOrgNo2').addClass('errorHighlight');
                isValid = false;
            }
            if (!$('#EikenOrgNo1').is(':hidden') && $('#EikenOrgNo1').val() == '') {
                $('#EikenOrgNo1').addClass('errorHighlight');
                ERROR_MESSAGE.show([{id: 'EikenOrgNo1', message: jsMessages.MSG1}], function () {
                }, 'inline');
                isValid = false;
            } else {
                $('#EikenOrgNo2').removeClass('errorHighlight');
                $('#EikenOrgNo1').removeClass('errorHighlight');
                $.ajax({
                    type: 'POST',
                    url: EIKEN_ORG.baseUrl + EIKEN_ORG.getOrgNameUrl,
                    dataType: 'json',
                    data: {eikenOrgNo: !$('#EikenOrgNo1').is(':hidden') ? $('#EikenOrgNo1').val() : ''},
                    async: true,
                    success: function (result) {
                        if (result.isEmpty && !$('#EikenOrgNo1').is(':hidden')) {
                            $('#EikenOrgNo1').addClass('errorHighlight');
                            ERROR_MESSAGE.show([{id: 'EikenOrgNo1', message: jsMessages.MSG4}], function () {
                            }, 'inline');
                        } else {
                            if (EIKEN_ORG.checkRequiredField()) {
                                EIKEN_ORG.saveData('draft');
                            } else {
                                //ERROR_MESSAGE.show(jsMessages.MSG1, function(){}, 'inline');
                            }
                        }
                    },
                    error: function () {
                        EIKEN_ORG.callAjax = false;
                        ERROR_MESSAGE.show(jsMessages.SystemError);
                    }
                });
            }
        } else {
            if (EIKEN_ORG.checkRequiredField()) {
                EIKEN_ORG.saveData('draft');
            } else {
                //ERROR_MESSAGE.show(jsMessages.MSG1, function(){}, 'inline');
            }
        }
        return false;
    },
    saveAsDraft: function () {
        var statusRefund = $("#refundStatus").val();
        if (statusRefund == 1) {
            $.ajax({
                type: 'POST',
                url: EIKEN_ORG.baseUrl + EIKEN_ORG.checkPaymentType,
                dataType: 'json',
                data: {},
                success: function (result) {
                    if (result.length !== 0 && result.paymentType == 0) {
                        ERROR_MESSAGE.show(result.message, function () {
                            return false;
                        });
                        $('#refundStatus option[value="1"]').remove();
                        return false;
                    }
                    else {
                        EIKEN_ORG.saveDraft();
                    }
                },
                error: {}
            });
        } else {
            EIKEN_ORG.saveDraft();
        }
    },
    saveToEiken: function () {
        if (!EIKEN_ORG.checkAllowLessThan10Standard()) {
            ERROR_MESSAGE.show(jsMessages.msgNotAllowLessThan10Standard, function () {
            });
            return false;
        }
        if (!$('#EikenOrgNo2').is(':hidden') || !$('#EikenOrgNo1').is(':hidden')) {
            $('#locationType1').removeClass('errorHighlight');
            if (!$('#EikenOrgNo2').is(':hidden') && $.trim($('#EikenOrgNo2').val()) == '') {
                $('#EikenOrgNo2').addClass('errorHighlight');
                isValid = false;
            }
            if (!$('#EikenOrgNo1').is(':hidden') && $('#EikenOrgNo1').val() == '') {
                $('#EikenOrgNo1').addClass('errorHighlight');
                ERROR_MESSAGE.show([{id: 'EikenOrgNo1', message: jsMessages.MSG1}], function () {
                }, 'inline');
                isValid = false;
            }
            if (!$('#refundStatus').is(':hidden') && $('#refundStatus').val() == '') {
                $('#refundStatus').addClass('errorHighlight');
                ERROR_MESSAGE.show([{id: 'refundStatus', message: jsMessages.MSG1}], function () {
                }, 'inline');
                isValid = false;
            } else {
                $('#EikenOrgNo2').removeClass('errorHighlight');
                $('#EikenOrgNo1').removeClass('errorHighlight');
                $.ajax({
                    type: 'POST',
                    url: EIKEN_ORG.baseUrl + EIKEN_ORG.getOrgNameUrl,
                    dataType: 'json',
                    data: {eikenOrgNo: !$('#EikenOrgNo1').is(':hidden') ? $('#EikenOrgNo1').val() : ''},
                    async: true,
                    success: function (result) {
                        if (result.isEmpty && !$('#EikenOrgNo1').is(':hidden')) {
                            $('#EikenOrgNo1').addClass('errorHighlight');
                            ERROR_MESSAGE.show([{id: 'EikenOrgNo1', message: jsMessages.MSG4}], function () {
                            }, 'inline');
                        } else {
                            if (!$('#EikenOrgNo123').is(':hidden')) {
                                if(result.eikenOrgName !== null){
                                   $('#EikenOrgNo123').text(result.eikenOrgName);
                                }else{
                                    $('#EikenOrgNo123').text(' ');
                                }
                                
                            }
                            if (EIKEN_ORG.checkRequiredField()) {
                                EIKEN_ORG.saveData();
                            } else {
                                //ERROR_MESSAGE.show(jsMessages.MSG1, function(){}, 'inline');
                            }
                        }
                    },
                    error: function () {
                        EIKEN_ORG.callAjax = false;
                        ERROR_MESSAGE.show(jsMessages.SystemError);
                    }
                });
            }
        } else {
            if (EIKEN_ORG.checkRequiredField()) {
                if ($('input#ExamDayInviSetting').val() == 0 || typeof $('input[name=date0]:checked').val() == 'undefined') {
                    EIKEN_ORG.saveData();
                } else if ($('input[name=date0]:checked').val() == $('input#ExamDayInviSetting').val()) {
                    EIKEN_ORG.saveData();
                } else {
                    CONFIRM_MESSAGE.show(
                            '受験案内状設定で指定した曜日と異なりますがよろしいですか？',
                            function () {
                                EIKEN_ORG.saveData();
                            },
                            function () {
                            },
                            '確認',
                            'はい',
                            'いいえ'
                            );
                }

            } else {
                //ERROR_MESSAGE.show(jsMessages.MSG1, function(){}, 'inline');
            }
        }
        return false;
    },
    submitToEiken: function () {
        var statusRefund = $("#refundStatus").val();
        if (invitation == 1 && statusRefund == 1) {
            $.ajax({
                type: 'POST',
                url: EIKEN_ORG.baseUrl + EIKEN_ORG.checkPaymentType,
                dataType: 'json',
                data: {},
                success: function (result) {
                    if (result.paymentType == 0) {
                        ERROR_MESSAGE.show(result.message, function () {
                            return false;
                        });
                        $('#refundStatus option[value="1"]').remove();
                        return false;
                    }
                    else {
                        EIKEN_ORG.saveToEiken();
                    }
                },
                error: {}
            });
        } else {
            EIKEN_ORG.saveToEiken();
        }
    },
    //save data draft/submit to Eiken
    saveData: function (type) {
        if (!EIKEN_ORG.checkBlankNoExpectation()) {
            ERROR_MESSAGE.show(jsMessages.MSG44, function () {
            }, 'inline');
            return false;
        }

        if (typeof isSentStandardHall != 'undefined' && typeof eikenOrgDetail.cd != 'undefined') {
            if (isSentStandardHall && !EIKEN_ORG.checkBlankExpectationStandardHall() && eikenOrgDetail.cd > 0) {
                ERROR_MESSAGE.show('合計人数が０以下です。申込み人数を修正してください。', function () {
                }, 'inline');
                return false;
            }
        }

        if (typeof type != 'undefined') {
            //draft data
            var isDraft = 1;
        } else {
            //submit data to Eiken and save to database
            var isDraft = 0;
        }

//        if(typeof(invitation) != "undefined" && invitation == 0){
//            $("#public-funding-modal").modal('show'); 
//            return false;
//        }

        var data = {
            isHallMain: true,
            isDraft: isDraft,
            //expect apply No for Maill Hall - Hoi Truong Chinh
            MainHallExpectApplyNo7: $('#txtMainHallExpectApplyNo1').val(),
            MainHallExpectApplyNo6: $('#txtMainHallExpectApplyNo2').val(),
            MainHallExpectApplyNo5: $('#txtMainHallExpectApplyNo3').val(),
            MainHallExpectApplyNo4: $('#txtMainHallExpectApplyNo4').val(),
            MainHallExpectApplyNo3: $('#txtMainHallExpectApplyNo5').val(),
            MainHallExpectApplyNo2: $('#txtMainHallExpectApplyNo6').val(),
            MainHallExpectApplyNo1: $('#txtMainHallExpectApplyNo7').val(),
            //expect apply No for standard Hall - Hoi Truong Chuan
            ExpectApplyNo7: $('#txtExpectApplyNo1').val(),
            ExpectApplyNo6: $('#txtExpectApplyNo2').val(),
            ExpectApplyNo5: $('#txtExpectApplyNo3').val(),
            ExpectApplyNo4: $('#txtExpectApplyNo4').val(),
            ExpectApplyNo3: $('#txtExpectApplyNo5').val(),
//				ExpectApplyNo6: $('#txtExpectApplyNo6').val(),
//				ExpectApplyNo7: $('#txtExpectApplyNo7').val(),
            date0: !$('input[name=date0]').is(':hidden') ? $('input[name=date0]:checked').val() : 0,
            date1: !$('input[name=date1]').is(':hidden') ? $('input[name=date1]:checked').val() : 0,
            date2: !$('input[name=date2]').is(':hidden') ? $('input[name=date2]:checked').val() : 0,
            date3: !$('input[name=date3]').is(':hidden') ? $('input[name=date3]:checked').val() : 0,
            date4: !$('input[name=date4]').is(':hidden') ? $('input[name=date4]:checked').val() : 0,
            date5: !$('input[name=date5]').is(':hidden') ? $('input[name=date5]:checked').val() : 0,
            totalcd: !$('#totalcd').is(':hidden') ? $('#totalcd').val() : 0,
            locationType: !$('input[name=locationType]').is(':hidden') ? $('input[name=locationType]:checked').val() : null,
            locationType1: !$('#locationType1').is(':hidden') ? $('#locationType1').val() : '',
            EikenOrgNo1: !$('#EikenOrgNo1').is(':hidden') ? $('#EikenOrgNo1').val() : '',
            EikenOrgNo2: !$('#EikenOrgNo2').is(':hidden') ? $('#EikenOrgNo2').val() : (!$('#orgName').is(':hidden') ? $('#orgName').text() : ''),
            EikenOrgNo123: !$('#EikenOrgNo123').is(':hidden') ? $('#EikenOrgNo123').text() : '',
            FirtNameKanji: $('#FirtNameKanji').val(),
            LastNameKanji: $('#LastNameKanji').val(),
            confirmMailAddress: $('#confirmMailAddress').val(),
            cityId: $('#cityId').val(),
            districtId: $('#districtId').val(),
            MailAddress: $('#MailAddress').val(),
            PhoneNumber: $('#PhoneNumber').val(),
            hasRegisterd7: $('#hasRegisterd7').val(),
            hasRegisterd6: $('#hasRegisterd6').val(),
            hasRegisterd5: $('#hasRegisterd5').val(),
            hasRegisterd4: $('#hasRegisterd4').val(),
            hasRegisterd3: $('#hasRegisterd3').val(),
            hasRegisterd2: $('#hasRegisterd2').val(),
            hasRegisterd1: $('#hasRegisterd1').val(),
            refundStatus: $('#refundStatus').val(),
            isOrgDiscount: $("#isOrgDiscount").val(),
            mainPupilDiscountKuy1: $("#mainPupilDiscountKuy1").length ? $("#mainPupilDiscountKuy1").val() : 0,
            mainPupilDiscountKuy2: $("#mainPupilDiscountKuy2").length ? $("#mainPupilDiscountKuy2").val() : 0,
            mainPupilDiscountKuy3: $("#mainPupilDiscountKuy3").length ? $("#mainPupilDiscountKuy3").val() : 0,
            mainPupilDiscountKuy4: $("#mainPupilDiscountKuy4").length ? $("#mainPupilDiscountKuy4").val() : 0,
            mainPupilDiscountKuy5: $("#mainPupilDiscountKuy5").length ? $("#mainPupilDiscountKuy5").val() : 0,
            mainPupilDiscountKuy6: $("#mainPupilDiscountKuy6").length ? $("#mainPupilDiscountKuy6").val() : 0,
            mainPupilDiscountKuy7: $("#mainPupilDiscountKuy7").length != 0 ? $("#mainPupilDiscountKuy7").val() : 0,
            standPupilDiscountKuy3: $("#standPupilDiscountKuy3").length ? $("#standPupilDiscountKuy3").val() : 0,
            standPupilDiscountKuy4: $("#standPupilDiscountKuy4").length ? $("#standPupilDiscountKuy4").val() : 0,
            standPupilDiscountKuy5: $("#standPupilDiscountKuy5").length ? $("#standPupilDiscountKuy5").val() : 0,
            standPupilDiscountKuy6: $("#standPupilDiscountKuy6").length ? $("#standPupilDiscountKuy6").val() : 0,
            standPupilDiscountKuy7: $("#standPupilDiscountKuy7").length ? $("#standPupilDiscountKuy7").val() : 0,
            pupilDiscountStand: $("#renderStand").val()
        };
        $.ajax({
            type: 'POST',
            url: EIKEN_ORG.baseUrl + EIKEN_ORG.saveEikenUrl,
            dataType: 'json',
            data: data,
            success: function (result) {
                if (!result.rfStatus && result.rfStatus != undefined) {
                    $('#refundStatus').addClass('errorHighlight');
                    ERROR_MESSAGE.show([{id: 'refundStatus', message: jsMessages.MSG1}], function () {
                    }, 'inline');
                    isValid = false;
                    return false;
                } else {
                    $('#refundStatus').removeClass('errorHighlight');
                }
                if (!result.isValidTime) {
                    ERROR_MESSAGE.show('<<年度>>年度第<<回>>の申込期間にならないか、もう切れになりました。', function () {
                        window.location = EIKEN_ORG.baseUrl + EIKEN_ORG.applyEikenUrl;
                    });
                    return false;
                }

                //$('#orgName').text(result.eikenOrgName);
                if (isDraft) {
                    window.location = EIKEN_ORG.baseUrl + EIKEN_ORG.applyEikenUrl;
                } else {
                    window.location = EIKEN_ORG.baseUrl + EIKEN_ORG.confirmationUrl;
                }
            },
            error: function () {
                ERROR_MESSAGE.show(jsMessages.SystemError);
            }
        });
    },
    checkBlankNoExpectation: function () {
        return $('#grand-total').text() == '0' ? false : true;
    },
    checkBlankExpectationStandardHall: function () {
        if ($('#txtExpectApplyNo1').val() == 0
                && $('#txtExpectApplyNo2').val() == 0 && $('#txtExpectApplyNo3').val() == 0 && $('#txtExpectApplyNo4').val() == 0 && $('#txtExpectApplyNo5').val() == 0) {
            return false;
        }
        return true;
    },
    checkorgBR72: function () {
        if ($('#orgBR72').val() == 0 && ((parseInt($('#txtMainHallExpectApplyNo7').val()) + parseInt($('#txtMainHallExpectApplyNo6').val()) + parseInt($('#txtMainHallExpectApplyNo5').val()) + parseInt($('#txtMainHallExpectApplyNo4').val()) +
                parseInt($('#txtMainHallExpectApplyNo3').val()) + parseInt($('#txtMainHallExpectApplyNo2').val()) + parseInt($('#txtMainHallExpectApplyNo1').val()) + parseInt($('#txtExpectApplyNo1').val()) +
                +parseInt($('#txtExpectApplyNo2').val()) + parseInt($('#txtExpectApplyNo3').val()) + parseInt($('#txtExpectApplyNo4').val()) + parseInt($('#txtExpectApplyNo5').val())) <= 10)) {
            return false;
        }
        return true;
    },
    checkRequiredField: function () {
        var objError = [];
        var grandTotalExpected = (parseInt($('#grand-total-standard-expectation').text()) | 0);
        if ($('#notTheMainHall').is(':hidden')) {
            return true;
        }
        var isValid = true;

        if (typeof $('input[name=locationType]:checked').val() == 'undefined' && grandTotalExpected > 0) {
            $('input[name=locationType]:first').addClass('errorRadio');
            objError.push({id: "locationType", message: jsMessages.MSG1});
            isValid = false;
        } else {
            $('input[name=locationType]').parent('label').removeClass('errorRadio');
        }

        if (typeof $('input[name=date0]:checked').val() == 'undefined' && grandTotalExpected > 0) {
            $('input[name=date0]:first').addClass('errorRadio');
            objError.push({id: "date0", message: jsMessages.MSG1});
            isValid = false;
        } else {
            $('input[name=date0]:first').parent('label').removeClass('errorHighlight');
        }
        if (!$('#totalcd').is(':hidden') && grandTotalExpected > 0) {
            if ($('#totalcd').val() == '' || $('#totalcd').val() == 0) {
                $('#totalcd').addClass('errorHighlight');
                objError.push({id: "totalcd", message: jsMessages.MSG1});
                isValid = false;
            } else if ($.trim($('#totalcd').val()).length > 2) {
                $('#totalcd').addClass('errorHighlight');
                objError.push({id: "totalcd", message: '2文字以内で入力してください'});
                isValid = false;
            } else if (isNaN($('#totalcd').val())) {
                $('#totalcd').addClass('errorHighlight');
                objError.push({id: "totalcd", message: jsMessages.MSG36});
                isValid = false;
            }
            else if ($.trim($('#totalcd').val()) < 2) {
                $('#totalcd').addClass('errorHighlight');
                objError.push({id: "totalcd", message: jsMessages.MinimumCdSet});
                isValid = false;
            }
            else {
                $('#totalcd').removeClass('errorHighlight');
            }
        }

        var standardExpectedArray = ['txtExpectApplyNo5', 'txtExpectApplyNo4', 'txtExpectApplyNo3', 'txtExpectApplyNo2', 'txtExpectApplyNo1'];
        for (var i = 0; i < 5; i++) {
            // Validate half-width for expected standard hall
            if (!EIKEN_ORG.isHalfwidth('#' + standardExpectedArray[i])) {
                $('#' + standardExpectedArray[i]).addClass('errorHighlight');
                objError.push({id: standardExpectedArray[i], message: jsMessages.HalfWidthFont});
                isValid = false;
            } else {
                $('#' + standardExpectedArray[i]).removeClass('errorHighlight');
            }
        }
        if (!$('#locationType1').is(':hidden')) {
            if ($('#locationType1').val() == '') {
                $('#locationType1').addClass('errorHighlight');
                objError.push({id: "locationType1", message: jsMessages.MSG1});
                isValid = false;
            } else {
                $('#locationType1').removeClass('errorHighlight');
            }
        }

        if (!$('#EikenOrgNo1').is(':hidden')) {
            if ($('#EikenOrgNo1').val() == '') {
                $('#EikenOrgNo1').addClass('errorHighlight');
                objError.push({id: "EikenOrgNo1", message: jsMessages.MSG1});
                isValid = false;
            } else {
                $('#EikenOrgNo1').removeClass('errorHighlight');
            }
        }

        if (!$('#EikenOrgNo2').is(':hidden')) {
            if ($.trim($('#EikenOrgNo2').val()) == '') {
                $('#EikenOrgNo2').addClass('errorHighlight');
                objError.push({id: "EikenOrgNo2", message: jsMessages.MSG1});
                isValid = false;
            } else {
                $('#EikenOrgNo2').removeClass('errorHighlight');
            }
        }
        if (!$('#EikenOrgNo123').is(':hidden')) {
            if ($('#EikenOrgNo123').text() == '') {
                $('#EikenOrgNo123').addClass('errorHighlight');
                objError.push({id: "EikenOrgNo123", message: jsMessages.MSG1});
                isValid = false;
            }

        }
        if (!$('#refundStatus').is(':hidden')) {
            if ($.trim($('#refundStatus').val()) == '') {
                $('#refundStatus').addClass('errorHighlight');
                objError.push({id: "refundStatus", message: jsMessages.MSG1});
                isValid = false;
            } else {
                $('#refundStatus').removeClass('errorHighlight');
            }

        }
        var flag = true;
        if ($('input[name=date5]').length && !$('input[name=date5]').is(':hidden') && !$('input[name=date5]').is(':disabled') && typeof $('input[name=date5]:checked').val() == 'undefined') {
            objError.push({id: "totalcd", message: jsMessages.MSG1});
            flag = false;
            isValid = false;
        }
        if ($('input[name=date4]').length && !$('input[name=date4]').is(':hidden') && !$('input[name=date4]').is(':disabled') && typeof $('input[name=date4]:checked').val() == 'undefined') {
            flag = false;
            isValid = false;
            objError.push({id: "totalcd", message: jsMessages.MSG1});
        }
        if ($('input[name=date3]').length && !$('input[name=date3]').is(':hidden') && !$('input[name=date3]').is(':disabled') && typeof $('input[name=date3]:checked').val() == 'undefined') {
            flag = false;
            isValid = false;
            objError.push({id: "totalcd", message: jsMessages.MSG1});
        }
        if ($('input[name=date2]').length && !$('input[name=date2]').is(':hidden') && !$('input[name=date2]').is(':disabled') && typeof $('input[name=date2]:checked').val() == 'undefined') {
            flag = false;
            isValid = false;
            objError.push({id: "totalcd", message: jsMessages.MSG1});
        }
        if ($('input[name=date1]').length && !$('input[name=date1]').is(':hidden') && !$('input[name=date1]').is(':disabled') && typeof $('input[name=date1]:checked').val() == 'undefined') {
            flag = false;
            isValid = false;
            objError.push({id: "totalcd", message: jsMessages.MSG1});
        }
        if (!flag) {
            if (!$('input[name=date1]').is(':disabled') && typeof $('input[name=date1]:checked').val() == 'undefined') {
                $('input[name=date1]:first').addClass('errorRadio');
            }
            if (!$('input[name=date2]').is(':disabled') && typeof $('input[name=date2]:checked').val() == 'undefined') {
                $('input[name=date2]:first').addClass('errorRadio');
            }
            if (!$('input[name=date3]').is(':disabled') && typeof $('input[name=date3]:checked').val() == 'undefined') {
                $('input[name=date3]:first').addClass('errorRadio');
            }
            if (!$('input[name=date4]').is(':disabled') && typeof $('input[name=date4]:checked').val() == 'undefined') {
                $('input[name=date4]:first').addClass('errorRadio');
            }
            if (!$('input[name=date5]').is(':disabled') && typeof $('input[name=date5]:checked').val() == 'undefined') {
                $('input[name=date5]:first').addClass('errorRadio');
            }
        }
        if (objError != []) {
            ERROR_MESSAGE.show(objError, function () {
            }, 'inline');
        }
        return isValid;
    },
    OKConfirmation: function () {

        if (typeof definitionSpecial != 'undefined' && parseInt(definitionSpecial) > 0) {
            CONFIRM_MESSAGE.show(jsMessages.SGHMSG48, function () {
                EIKEN_ORG.confirmationClone();
            });
            return false;
        }

        if (typeof (isPayment) != "undefined" && isPayment == 0) {
            if (typeof (invitation) != "undefined" && invitation == 0) {
//                $("#public-funding-modal").modal('show');
//                return false;
            }
        }
        EIKEN_ORG.confirmationClone();
        return false;
    },
    confirmationClone: function () {
        $('#submit-button').attr('disabled', true);
        var tocken = '';
        if ($('#tocken').length) {
            tocken = $('#tocken').val();
        }
        $.ajax({
            type: 'POST',
            url: EIKEN_ORG.baseUrl + EIKEN_ORG.submitUrl,
            dataType: 'json',
            data: {managerName: $('#manager_name').val(), tocken: tocken},
            success: function (result) {
                if (typeof result.inValidTocken != 'undefined' && result.inValidTocken) {
                    return;
                }
                var errorMessage = '';
                if (typeof result.isValid != 'undefined') {
                    ERROR_MESSAGE.show('<<年度>>年度第<<回>>の申込期間にならないか、もう切れになりました。', function () {
                        window.location = EIKEN_ORG.baseUrl + EIKEN_ORG.applyEikenUrl;
                    });
                    return;
                }
                // If all is error
                if (result.resultFlag.StandardHallError == '1' && result.resultFlag.MainHallError == '1')
                {
                    ERROR_MESSAGE.show(jsMessages.MSG46, function () {
                        window.location = EIKEN_ORG.baseUrl + EIKEN_ORG.confirmationUrl;
                    });
                }
                else
                {
                    // Standard hall error
                    if (result.resultFlag.StandardHallError == '1') {
                        //errorMessage.push({id: 'StandardHallError', message: jsMessages.MSG72});
                        errorMessage = jsMessages.MSG72;
                    }
                    // Main hall error
                    if (result.resultFlag.MainHallError == '1') {
                        //errorMessage.push({id: 'MainHallError', message: jsMessages.MSG73});
                        errorMessage = jsMessages.MSG73;
                    }
                    // List pupil main hall error
                    if (result.resultFlag.PupilListMainHallError == '1') {
                        //errorMessage.push({id: 'PupilListMainHallError', message: jsMessages.MSG74});
                        errorMessage = result.resultFlag.msg;
                    }
                    // if there is any error
                    if (errorMessage) {
                        ERROR_MESSAGE.show(errorMessage, function () {
                            if(result.resultFlag.PupilListMainHallError != '1'){
                                window.location = EIKEN_ORG.baseUrl + EIKEN_ORG.confirmationUrl;
                            }else{
                                window.location = EIKEN_ORG.baseUrl + EIKEN_ORG.applicationFormUrl;
                            }
                        });
                        
                        setTimeout(function(){ 
                            var height = $("#errorPopupModal .modal-content .modal-body").innerHeight();
                            
                            if(height > 150){
                                $("#errorPopupModal .modal-content .modal-body p").attr('style','max-height: 150px;overflow-y: scroll;display: block;')
                            }
                        }, 300);
                    }
                    // Success
                    else {
                        if (result.resultFlag.SendMailError == '1') {
                            ERROR_MESSAGE.show(jsMessages.SendMailError, function () {
                                window.location = EIKEN_ORG.baseUrl + EIKEN_ORG.applyEikenUrl;
                            });
                        }
                        else {
                            ERROR_MESSAGE.show(jsMessages.MSG47, function () {
                                window.location = EIKEN_ORG.baseUrl + EIKEN_ORG.applyEikenUrl;
                            });
                        }
                    }
                }

            },
            error: function () {
                ERROR_MESSAGE.show(jsMessages.SystemError, function () {
                    window.location = EIKEN_ORG.baseUrl + EIKEN_ORG.applyEikenUrl;
                });
            }
        });
    },
    checkValid: function (msg) {
        if (!dantaiStatus.isValidTime) {
            ERROR_MESSAGE.show(msg, function () {
                window.location = EIKEN_ORG.baseUrl + EIKEN_ORG.applyEikenUrl;
            });
            return;
        }

        if (!dantaiStatus.isValidInvitationSetting) {
            ERROR_MESSAGE.show('There is no valid Invitation setting at the current time. Contact system admin for support.', function () {
                window.location = EIKEN_ORG.baseUrl + EIKEN_ORG.applyEikenUrl;
            });
        } else {
            window.location = EIKEN_ORG.baseUrl + EIKEN_ORG.applyEikenUrl;
        }
    },
    validateEmail: function (email) {
        var re = /^(([^<>()[]\\.,;:\s@\"]+(\.[^<>()[]\\.,;:\s@\"]+)*)|(\".+\"))@(([[0-9]{1,3}\‌​.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    },
    isNumber: function (evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    },
    loadExamLocation: function () {
        var cityId = $('#cityId').val();
        $.ajax({
            type: 'POST',
            url: EIKEN_ORG.loadExamLocationUrl,
            dataType: 'html',
            data: {cityId: cityId},
            success: function (result) {
                $('#districtId').html(result);
            },
            error: function () {
                ERROR_MESSAGE.show(jsMessages.SystemError);
            }
        });
    },
    isFullWidth: function (value) {
        var i, charTarget, transTarget;
        var char_length = ("あ".length);
        // transTarget = obj.value.replace(/[ ]/g,"");
        transTarget = value;
        // obj.value = transTarget;
        if (!transTarget || transTarget.length == 0) {
            return true;
        }
        for (i = 0; i < transTarget.length; i = i + char_length) {
            charTarget = transTarget.charAt(i);
            if ((charTarget >= "!" && charTarget <= "~")
                    || (charTarget >= "｡" && charTarget <= "ﾟ")
                    || charTarget == " ") {
                return false;
            }
        }
        return true;
    },
    isHalfwidth: function (element) {
        value = $.trim($(element).val());
        if (value.length == 0)
            return true;
        var numberOfHalfWidth = value.match(/(?:\xEF\xBD[\xA1-\xBF]|\xEF\xBE[\x80-\x9F])|[\x20-\x7E]/g);
        return numberOfHalfWidth == null ? false : numberOfHalfWidth.length == value.length;
    },
    showpup: function () {
        if ($('#renderStand').length && $('#renderStand').val() != '' && flgHadGradeDiscount == 1) {
            $("#myModal").modal();
        }
        $('#btnShowPop').on('click', function () {
            $columnNumber = $('#detailDiscountStand thead').data('column');
            $rowNumber = $('#detailDiscountStand thead').data('row');
            // set width
            if ($columnNumber != undefined) {
                $columnWidth = 210;
                $with = $columnWidth + $columnNumber * 100;
                $('#myModal .modal-dialog').width($with);
            }
            $('input[name="number"]').on("keypress", function (event) {
                // Allow special chars + arrows
                if (event.which==0 || event.which == 8 || event.which == 9
                        || event.which == 27 || event.which == 13
                        || (event.which == 65 && event.ctrlKey === true)) {
                    return;
                } else {
                    // If it's not a number stop the keypress
                    if (event.which < 48 || event.which > 57) {
                        event.preventDefault();
                    }
                }
            });
            $('input[name="number"]').on("blur", function (event) {
                if (!$.isNumeric($(this).val()) || $(this).val() < 0)
                    $(this).val(0);
                else
                    $(this).val(parseInt($(this).val()));
            });
            $height = (screen.height-$rowNumber*60-400)/2;;
            var delay = 150; //1 second
            // set scroll
            if($rowNumber>6)
            {
                $('#myModal .row .detailDiscountStandDiv').css('overflow-y','scroll');
                $height =(screen.height-700)/2;
            }
            $('#myModal .modal-dialog').css('margin-top',$height);
            setTimeout(function () {
                $('.detailDiscountStandDiv #detailDiscountStand input[name="number"]:first').focus();
                
            }, delay);
            
        });


    },
    showPopupGuide: function (divId) {
        $('#' + divId).modal('show');
    },
    saveFundingAndPaymentStatus: function () {
        var data = 1;

        $.ajax({
            type: 'POST',
            url: EIKEN_ORG.baseUrl + EIKEN_ORG.saveStatus,
            dataType: 'json',
            data: {},
            success: function (data) {
                EIKEN_ORG.confirmationClone();
//                window.location.href = EIKEN_ORG.baseUrl + EIKEN_ORG.applyEikenUrl;
                return false;
            },
            error: function () {

            }
        })
    },
    checkAllowLessThan10Standard: function () {
        var standardExpectation = (parseInt($('#txtExpectApplyNo1').val()) | 0) + (parseInt($('#txtExpectApplyNo2').val()) | 0)
                + (parseInt($('#txtExpectApplyNo3').val()) | 0) + (parseInt($('#txtExpectApplyNo4').val()) | 0)
                + (parseInt($('#txtExpectApplyNo5').val()) | 0);
        var locationType = $('input[name=locationType]:checked').val();
        // show msg when this dantai in dantai list which is rejected allow < 10 ,
        // 実施区分 not select 合同
        // and 申込人数 of 準会場 from 1 to 9
        if (eikenOrgDetail.isRejectLessThan10Standard && locationType != 1 && standardExpectation > 0 && standardExpectation < 10) {
            return false;
        }
        return true;
    },
    renderHTML: function (pageLoad) {
        if ($('#renderStand').length && $('#renderStand').val() != '') {
            var data = $.parseJSON($('#renderStand').val());
            var dataHeader = '';
            var dataContent = '';
            var flgGetHeader = 0;
            var classIsDiscount = ' bg-classdiscount ';
            var columnNumber = 0;
            var rowNumber = 0;
            jQuery.each(data, function (idGrade, row) {
                dataContent += '<tr>';
                dataContent += '<td style="width: 120px;">' + row.gradeName + '</td>';
                rowNumber++;
                jQuery.each(row, function (key, recode) {
                    if (jQuery.isPlainObject(recode)) {
                        jQuery.each(recode, function (header, value) {
                            if (flgGetHeader == 0) {
                                columnNumber++;
                                dataHeader += "<th><span>" + value.kyuName + "</span></th>";
                            }
                            var classHight = '';
                            if (value.isDiscountKyu == 1) {
                                classHight = classIsDiscount;
                            }
                            dataContent += '<td><input id="' + idGrade + '_' + header + '" value="' + value.totalPupil + '" type="text" name="number"  class="danger center-block form-control' + classHight + '"  ></td>';
                        });
                        flgGetHeader = 1;
                    }
                });
                dataContent += '</tr>';
            });
            var html = "<table class='table table-bordered' id='detailDiscountStand'>"+
                    "<thead data-row=" + rowNumber + " data-column=" + columnNumber + ">" +
                        "<tr>" +
                            '<th style="width: 120px;" >学年</th>' 
                            +dataHeader
                        + "</tr>" +
                    "</thead>" + 
                    "</table>"+
                    "<div class='detailDiscountStandDiv'>"+
                        "<table class='table table-bordered detailDiscountStandData' id='detailDiscountStand'>"+
                        dataContent+
                    "</div>" + 
                    "</table>";
            $("#detailDiscountStandTable").html(html);
            if(flgHadGradeDiscount == 0){
                if(pageLoad == 0){
                $('#errorPopupModal').hide();
                ERROR_MESSAGE.show(MSGCreateGrade,
                        function()
                        {
                            window.location = EIKEN_ORG.baseUrl + EIKEN_ORG.goToCreateGrade;
                        });
                }
            }
        }else{
            if(pageLoad == 0){
                $('#errorPopupModal').hide();
            ERROR_MESSAGE.show(MSGCreateGrade,
                    function()
                    {
                        window.location = EIKEN_ORG.baseUrl + EIKEN_ORG.goToCreateGrade;
                    });
            }
        }
    },
    updateDisountPupilToStore: function (reRun) {
        if ($('#renderStand').val() != '') {
            var data = $.parseJSON($('#renderStand').val());

            var totalDiscountKyu1 = 0;
            var totalDiscountKyu2 = 0;
            var totalDiscountKyu3 = 0;
            var totalDiscountKyu4 = 0;
            var totalDiscountKyu5 = 0;
            var totalDiscountKyu6 = 0;
            var totalDiscountKyu7 = 0;

            var txtExpectApplyNo5 = 0;
            var txtExpectApplyNo4 = 0;
            var txtExpectApplyNo3 = 0;
            var txtExpectApplyNo2 = 0;
            var txtExpectApplyNo1 = 0;

            var totalDiscountRowKyu1 = 0;
            var totalDiscountRowKyu2 = 0;
            var totalDiscountRowKyu3 = 0;
            var totalDiscountRowKyu4 = 0;
            var totalDiscountRowKyu5 = 0;
            var totalDiscountRowKyu6 = 0;
            var totalDiscountRowKyu7 = 0;


            jQuery.each(data, function (idGrade, row) {
                jQuery.each(row, function (key, recode) {
                    if (jQuery.isPlainObject(recode)) {
                        jQuery.each(recode, function (header, value) {
                            var keyId = "#" + idGrade + '_' + header;
                            if ($(keyId).length) {
                                if ($(keyId).val().length < 1) {
                                    number = 0;
                                } else {
                                    var number = parseInt($(keyId).val());
                                }
                                if (number < 1) {
                                    number = 0;
                                }

                                data[idGrade][key][header].totalPupil = number;
                                switch (parseInt(header)) {
                                    case 1:
                                        totalDiscountKyu1 += number;
                                        if (data[idGrade][key][header].isDiscountKyu == 1) {
                                            totalDiscountRowKyu1 += number;
                                        }
                                        break;
                                    case 2:
                                        totalDiscountKyu2 += number;
                                        if (data[idGrade][key][header].isDiscountKyu == 1) {
                                            totalDiscountRowKyu2 += number;
                                        }
                                        break;
                                    case 3:
                                        txtExpectApplyNo5 += number;
                                        if (data[idGrade][key][header].isDiscountKyu == 1) {
                                            totalDiscountRowKyu3 += number;
                                        }
                                        break;
                                    case 4:
                                        txtExpectApplyNo4 += number;
                                        if (data[idGrade][key][header].isDiscountKyu == 1) {
                                            totalDiscountRowKyu4 += number;
                                        }
                                        break;
                                    case 5:
                                        txtExpectApplyNo3 += number;
                                        if (data[idGrade][key][header].isDiscountKyu == 1) {
                                            totalDiscountRowKyu5 += number;
                                        }
                                        break;
                                    case 6:
                                        txtExpectApplyNo2 += number;
                                        if (data[idGrade][key][header].isDiscountKyu == 1) {
                                            totalDiscountRowKyu6 += number;
                                        }
                                        break;
                                    case 7:
                                        txtExpectApplyNo1 += number;
                                        if (data[idGrade][key][header].isDiscountKyu == 1) {
                                            totalDiscountRowKyu7 += number;
                                        }
                                        break;
                                    default:
                                        break;
                                }
                            }

                        });
                    }
                });
            });


            txtExpectApplyNo5 += $("#txtExpectApplyNo5").attr('disabled') != 'disabled' ? parseInt($("#txtExpectApplyNo5").val()) < 0 ? 0 : parseInt($("#txtExpectApplyNo5").val()) : 0;
            txtExpectApplyNo4 += $("#txtExpectApplyNo4").attr('disabled') != 'disabled' ? parseInt($("#txtExpectApplyNo4").val()) < 0 ? 0 : parseInt($("#txtExpectApplyNo4").val()) : 0;
            txtExpectApplyNo3 += $("#txtExpectApplyNo3").attr('disabled') != 'disabled' ? parseInt($("#txtExpectApplyNo3").val()) < 0 ? 0 : parseInt($("#txtExpectApplyNo3").val()) : 0;
            txtExpectApplyNo2 += $("#txtExpectApplyNo2").attr('disabled') != 'disabled' ? parseInt($("#txtExpectApplyNo2").val()) < 0 ? 0 : parseInt($("#txtExpectApplyNo2").val()) : 0;
            txtExpectApplyNo1 += $("#txtExpectApplyNo1").attr('disabled') != 'disabled' ? parseInt($("#txtExpectApplyNo1").val()) < 0 ? 0 : parseInt($("#txtExpectApplyNo1").val()) : 0;

            txtExpectApplyNo5 = isNaN(txtExpectApplyNo5) ? 0 : txtExpectApplyNo5;
            txtExpectApplyNo4 = isNaN(txtExpectApplyNo4) ? 0 : txtExpectApplyNo4;
            txtExpectApplyNo3 = isNaN(txtExpectApplyNo3) ? 0 : txtExpectApplyNo3;
            txtExpectApplyNo2 = isNaN(txtExpectApplyNo2) ? 0 : txtExpectApplyNo2;
            txtExpectApplyNo1 = isNaN(txtExpectApplyNo1) ? 0 : txtExpectApplyNo1;


            $("#txtExpectApplyNo5").val(txtExpectApplyNo5);
            $("#txtExpectApplyNo4").val(txtExpectApplyNo4);
            $("#txtExpectApplyNo3").val(txtExpectApplyNo3);
            $("#txtExpectApplyNo2").val(txtExpectApplyNo2);
            $("#txtExpectApplyNo1").val(txtExpectApplyNo1);


            $("#standPupilDiscountKuy1").val(totalDiscountRowKyu1);
            $("#standPupilDiscountKuy2").val(totalDiscountRowKyu2);
            $("#standPupilDiscountKuy3").val(totalDiscountRowKyu3);
            $("#standPupilDiscountKuy4").val(totalDiscountRowKyu4);
            $("#standPupilDiscountKuy5").val(totalDiscountRowKyu5);
            $("#standPupilDiscountKuy6").val(totalDiscountRowKyu6);
            $("#standPupilDiscountKuy7").val(totalDiscountRowKyu7);

            var totalDiscountStand = txtExpectApplyNo5 + txtExpectApplyNo4 + txtExpectApplyNo3 + txtExpectApplyNo2 + txtExpectApplyNo1;
            $("#grand-total-standard-expectation").text(totalDiscountStand);

            if ($('#totalDiscountMain').val() != '') {
                var dataMain = $.parseJSON($('#totalDiscountMain').val());

                jQuery.each(dataMain, function (key, value) {
                    switch (parseInt(key)) {
                        case 1:
                            totalDiscountKyu1 += value;
                            totalDiscountRowKyu1 += value;
                            break;
                        case 2:
                            totalDiscountKyu2 += value;
                            totalDiscountRowKyu2 += value;
                            break;
                        case 3:
                            totalDiscountKyu3 += value;
                            totalDiscountRowKyu3 += value;
                            break;
                        case 4:
                            totalDiscountKyu4 += value;
                            totalDiscountRowKyu4 += value;
                            break;
                        case 5:
                            totalDiscountKyu5 += value;
                            totalDiscountRowKyu5 += value;
                            break
                        case 6:
                            totalDiscountKyu6 += value;
                            totalDiscountRowKyu6 += value;
                            break
                        case 7:
                            totalDiscountKyu7 += value;
                            totalDiscountRowKyu7 += value;
                            break;
                        default:
                            break;
                    }
                });
            }


            $("#totalDiscountKyu1").val(totalDiscountRowKyu1);
            $("#totalDiscountKyu2").val(totalDiscountRowKyu2);
            $("#totalDiscountKyu3").val(totalDiscountRowKyu3);
            $("#totalDiscountKyu4").val(totalDiscountRowKyu4);
            $("#totalDiscountKyu5").val(totalDiscountRowKyu5);
            $("#totalDiscountKyu6").val(totalDiscountRowKyu6);
            $("#totalDiscountKyu7").val(totalDiscountRowKyu7);

            var totalDiscountALL = totalDiscountRowKyu1 + totalDiscountRowKyu2 + totalDiscountRowKyu3 + totalDiscountRowKyu4 + totalDiscountRowKyu5 + totalDiscountRowKyu6 + totalDiscountRowKyu7;
            $("#total-apply-expectation").text(totalDiscountALL);

            EIKEN_ORG.reSetTotalPupil();
            if (reRun == 0) {
                EIKEN_ORG.checkShowDetailSection();
                EIKEN_ORG.getTotal();
                EIKEN_ORG.visibleStandardSection();
            }

            $('#renderStand').val(JSON.stringify(data));
        }
    },
    reSetTotalPupil: function () {
        $('#total1').text((parseInt($('#txtMainHallExpectApplyNo1').text()) | 0) + (parseInt($('#txtExpectApplyNo1').val()) | 0));
        $('#total2').text((parseInt($('#txtMainHallExpectApplyNo2').text()) | 0) + (parseInt($('#txtExpectApplyNo2').val()) | 0));
        $('#total3').text((parseInt($('#txtMainHallExpectApplyNo3').text()) | 0) + (parseInt($('#txtExpectApplyNo3').val()) | 0));
        $('#total4').text((parseInt($('#txtMainHallExpectApplyNo4').text()) | 0) + (parseInt($('#txtExpectApplyNo4').val()) | 0));
        $('#total5').text((parseInt($('#txtMainHallExpectApplyNo5').text()) | 0) + (parseInt($('#txtExpectApplyNo5').val()) | 0));
        $('#total6').text((parseInt($('#txtMainHallExpectApplyNo6').text()) | 0));
        $('#total7').text((parseInt($('#txtMainHallExpectApplyNo7').text()) | 0));
        var grandTotal = (parseInt($('#txtMainHallExpectApplyNo1').text()) | 0) + (parseInt($('#txtExpectApplyNo1').val()) | 0)
                + ((parseInt($('#txtMainHallExpectApplyNo2').text()) | 0) + (parseInt($('#txtExpectApplyNo2').val()) | 0))
                + ((parseInt($('#txtMainHallExpectApplyNo3').text()) | 0) + (parseInt($('#txtExpectApplyNo3').val()) | 0))
                + ((parseInt($('#txtMainHallExpectApplyNo4').text()) | 0) + (parseInt($('#txtExpectApplyNo4').val()) | 0))
                + ((parseInt($('#txtMainHallExpectApplyNo5').text()) | 0) + (parseInt($('#txtExpectApplyNo5').val()) | 0))
                + ((parseInt($('#txtMainHallExpectApplyNo6').text()) | 0) + (parseInt($('#txtExpectApplyNo6').val()) | 0))
                + ((parseInt($('#txtMainHallExpectApplyNo7').text()) | 0) + (parseInt($('#txtExpectApplyNo7').val()) | 0));
        $('#grand-total').text(grandTotal);
    }
};
$(document).ready(function () {
    $('form:first *:input[type!=hidden][disabled!=disabled]:first').focus();
    EIKEN_ORG.init();
    $("#totalcd").keydown(function (e) {
        var key = e.which;  // backspace = 8, delete = 46, arrows = 37,38,39,40

        if ((key >= 37 && key <= 40) || key == 8 || key == 46)
            return;

        return $(this).val().length <= 5;
    });

    if ($('.scrollup').length) {
        $('.scrollup').click(function () {
            $("html, body").animate({
                scrollTop: 0
            }, 600);
            return false;
        });
    }

    if ($('#get-org-info-error').length && $('#get-org-info-error').val() == 1) {
        ERROR_MESSAGE.show(jsMessages.GetOrgInfoError);
        $('#btnOkModal').bind('click', function () {
            if (typeof fncCallBack === 'function') {
                fncCallBack();
            }
            $('#errorPopupModal').modal('hide');

            window.location.href = EIKEN_ORG.baseUrl + EIKEN_ORG.backToHomepageUrl;
        });
    }

    COMMON.showCrossEditMessage('#new-app-eik-level');

    $('#non-use-public-funding').click(function () {
        var fundingStatus = 0;
        $.ajax({
            type: 'POST',
            url: EIKEN_ORG.fundingStatus,
            dataType: 'json',
            data: {fundingStatus: fundingStatus},
            success: function (result) {
                $("#public-funding-modal").modal('hide');
                $("#payment-bill-modal").modal('show');
            },
            error: function () {

            }
        })
    });
    $('#use-public-funding').click(function () {
        var fundingStatus = 1;
        $.ajax({
            type: 'POST',
            url: EIKEN_ORG.fundingStatus,
            dataType: 'json',
            data: {fundingStatus: fundingStatus},
            success: function (result) {
                $("#public-funding-modal").modal('hide');
                $("#payment-bill-modal").modal('show');
            },
            error: function () {

            }
        })
    });
    $('#wo-bill').click(function () {
        var paymentStatus = 0;
        invitation = 1;
        $.ajax({
            type: 'POST',
            url: EIKEN_ORG.paymentStatus,
            dataType: 'json',
            data: {paymentStatus: paymentStatus},
            success: function (result) {
                $("#payment-bill-modal").modal('hide');
                EIKEN_ORG.saveFundingAndPaymentStatus();
//                EIKEN_ORG.confirmationClone();
//                return false;
//                window.location.href = EIKEN_ORG.baseUrl + EIKEN_ORG.applyEikenUrl;
            },
            error: function () {

            }
        })
    });
    $('#w-bill').click(function () {
        var paymentStatus = 1;
        invitation = 1;
        $.ajax({
            type: 'POST',
            url: EIKEN_ORG.paymentStatus,
            dataType: 'json',
            data: {paymentStatus: paymentStatus},
            success: function (result) {
                $("#payment-bill-modal").modal('hide');
                EIKEN_ORG.saveFundingAndPaymentStatus();
//                EIKEN_ORG.confirmationClone();
//                return false;
//                window.location.href = EIKEN_ORG.baseUrl + EIKEN_ORG.applyEikenUrl;
            },
            error: function () {

            }
        })
    })

    $("#btnShowPop").click(
            function () {
                EIKEN_ORG.renderHTML(0);
            }
    );
    $("#updateDiscountPupil").click(
            function () {
                EIKEN_ORG.updateDisountPupilToStore(0);
            }
    );
});

//$( "#clickToShow" ).click(function() {
//	  $( "#directionModal" ).dialog();
//	});
