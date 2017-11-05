/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var validator;
var TEST_SITE_EXEMPTION = {    
    loadMainHallUrl: COMMON.baseUrl + 'eiken/load-main-hall',
    loadDistrictInCityUrl: COMMON.baseUrl + 'eiken/load-district-in-city',
    initial: function () {

        TEST_SITE_EXEMPTION.checkExamGrade();
        TEST_SITE_EXEMPTION.checkExemption();
        TEST_SITE_EXEMPTION.onlyNumber('#personalId1,#personalId2', true);
        $('#submitForm').submit(function () {
            if (!$('#submitForm').valid()) {
                return false;
            }
        });

        $('input[name="exemption1"], input[name="exemption2"]').change(function () {
            validator.resetForm();
            TEST_SITE_EXEMPTION.checkExemption();
        });
    },
    initValidator: function () {
        validator = $('#submitForm').validate({
            onkeyup: false,
            onclick: false,
            focusInvalid: false,
            rules: {
                personalId1: {
                    digits: true,
                    maxlength: 7,
                },
                firstTestCity1: {
                    required: true
                },
                firstExamPlace1: {
                    required: true
                },
                secondTestCity1: {
                    required: true
                },
                secondExamPlace1: {
                    required: true
                },
                personalId2: {
                    digits: true,
                    maxlength: 7,
                },
                firstTestCity2: {
                    required: true
                },
                firstExamPlace2: {
                    required: true
                },
                secondTestCity2: {
                    required: true
                },
                secondExamPlace2: {
                    required: true
                },
            },
            onfocusout: function (element) {
            },
            errorPlacement: function (error, element) {
                error.insertAfter(element);
            },
            invalidHandler: function () {
                $(this).find(":input.error:first").focus();
            }
        });
    },
    checkExamGrade: function () {
        var examGrade1 = $('input[name="examGrade1"]').val();
        var examGrade2 = $('input[name="examGrade2"]').val();
        
        if (examGrade1 == 6 || examGrade1 == 7) {
            $('#exemption1_1').attr('disabled', 'disabled').parent().addClass('disabled');
//            $('#exemption1_0').attr('disabled', 'disabled').parent().addClass('disabled');
            $('#secondTestCity1').attr('disabled', 'disabled').addClass('disabled');
            $('#secondExamPlace1').attr('disabled', 'disabled').addClass('disabled');
        }
        if (examGrade2 == 6 || examGrade2 == 7) {
//            $('#exemption2_0').attr('disabled', 'disabled').parent().addClass('disabled');
            $('#exemption2_1').attr('disabled', 'disabled').parent().addClass('disabled');
            $('#secondTestCity2').attr('disabled', 'disabled').addClass('disabled');
            $('#secondExamPlace2').attr('disabled', 'disabled').addClass('disabled');
        }
    },
    checkExemption: function () {
        var exemption1 = $('input[name="exemption1"]:checked').val();
        var exemption2 = $('input[name="exemption2"]:checked').val();

        if (exemption1 == 0) {
            TEST_SITE_EXEMPTION.enableDisableWhenExemptionNo(1);
        } else if (exemption1 == 1) {
            TEST_SITE_EXEMPTION.enableDisableWhenExemptionYes(1);
        }
        if (exemption2 == 0) {
            TEST_SITE_EXEMPTION.enableDisableWhenExemptionNo(2);
        } else if (exemption2 == 1) {
            TEST_SITE_EXEMPTION.enableDisableWhenExemptionYes(2);
        }
    },
    enableDisableWhenExemptionNo: function (index) {
        $('#firstTestCity' + index).removeAttr('disabled', 'disabled').removeClass('disabled');
        $('#firstExamPlace' + index).removeAttr('disabled', 'disabled').removeClass('disabled');
        $('#passedKai' + index).attr('disabled', 'disabled').addClass('disabled');
        $('#passedPlace' + index).attr('disabled', 'disabled').addClass('disabled');
        $('#passedCity' + index).attr('disabled', 'disabled').addClass('disabled');
        $('#personalId' + index).attr('disabled', 'disabled').addClass('disabled');
        $('#secondTestCity' + index).attr('disabled', 'disabled').addClass('disabled');
        $('#secondExamPlace' + index).attr('disabled', 'disabled').addClass('disabled');
    },
    enableDisableWhenExemptionYes: function (index) {
        $('#firstTestCity' + index).attr('disabled', 'disabled').addClass('disabled');
        $('#firstExamPlace' + index).attr('disabled', 'disabled').addClass('disabled');
        $('#passedKai' + index).removeAttr('disabled', 'disabled').removeClass('disabled');
        $('#passedPlace' + index).removeAttr('disabled', 'disabled').removeClass('disabled');
        $('#passedCity' + index).removeAttr('disabled', 'disabled').removeClass('disabled');
        $('#personalId' + index).removeAttr('disabled', 'disabled').removeClass('disabled');
        $('#secondTestCity' + index).removeAttr('disabled', 'disabled').removeClass('disabled');
        $('#secondExamPlace' + index).removeAttr('disabled', 'disabled').removeClass('disabled');
    },    
    ajaxHTML : function(href, data, callbackFunction) {
		$.ajax({
			type : 'POST',
			url : href,
			dataType : 'html',
			data : data, 
			success : function(result) {
				callbackFunction(result);
			},
			error : function() {
				//ERROR_MESSAGE.show(eikAppLevelMess.SystemError);
			}
		});
	},
    loadMainHallAddress: function (cityId, desinationList, eikenLevelId, isFirstTime) {
            TEST_SITE_EXEMPTION.ajaxHTML (TEST_SITE_EXEMPTION.loadMainHallUrl,
                            {cityId:cityId, eikenLevelId: eikenLevelId, isFirstTime: isFirstTime},
                            function (result) {
                                    $('#' + desinationList).html(result);
                            });
	},
    loadDistrictInCity: function (cityId, districtHTML) {           
            $.ajax({
                type : 'POST',
                url : TEST_SITE_EXEMPTION.loadDistrictInCityUrl,
                contentType: "application/json",
                dataType : 'json',
                data : JSON.stringify({ cityId: cityId }),
                success : function (result) {
                    var html = "<option value='' selected='selected'></option>";
                    if (typeof result != 'undefined') {
                            $.each(result,function(i,v) {
                                html = html
                                    + "<option value='"
                                    + v['code']
                                    + "'>"  
                                    + v['name']
                                    + "</option>";
                            });
                    }

                    $('#' + districtHTML).html(html);
                },
                error : function() {
                        //ERROR_MESSAGE.show(eikAppLevelMess.SystemError);
                }
            });                
	},
        onlyNumber: function (selector, number) {
            $(selector).keydown(function (e) {
                var listCode = [46, 8, 9, 27, 13];
                if ($.inArray(e.keyCode, listCode) != -1 ||
                    (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                    (e.keyCode >= 35 && e.keyCode <= 40)) {
                    return;
                }
                if ((e.ctrlKey === true && e.keyCode === 67) || (e.ctrlKey === true && e.keyCode == 86)) {
                    return;
                }
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });
    },
};

// when load page.
$(document).ready(function () {
    TEST_SITE_EXEMPTION.initValidator();
    TEST_SITE_EXEMPTION.initial();
});