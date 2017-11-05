
var INVITATION_SETTING = {
	settingIndexUrl: COMMON.baseUrl + 'invitation/setting/index',
	recommendIndexUrl: COMMON.baseUrl + 'invitation/recommended/index',
	checkExistUrl: COMMON.baseUrl + 'invitation/setting/checkExits',
	invitationLetterUrl: COMMON.baseUrl + 'invitation/generate/invitation-letter',
	generateIndexUrl: COMMON.baseUrl + 'invitation/generate/index',
	settingAddUrl: COMMON.baseUrl + 'invitation/setting/add',
	settingEditUrl: COMMON.baseUrl + 'invitation/setting/edit/',
        loadExpiredPaymentDateUrl: COMMON.baseUrl + 'invitation/setting/load-expired-payment-date',
        hadShowPopup : '',
        isNotShowPopupUrl: COMMON.baseUrl + 'invitation/setting/is-not-show-popup',
        checkedComb : true,
	initital: function () {
		if (typeof $('#function').val() != 'undefined' && $('#function').val() == 'add') {
	        INVITATION_SETTING.getMessage('DoubleEiken', 'DoubleEikenMessage');
	    }else {
	        $('#ExamPlace').val($('input[name="ExamPlace"]').attr('alt'));
	    }
	    var error = $('#error').val();
	    if (error != '') {
	        ERROR_MESSAGE.show([{id: '', message: error}], function () {
	        }, 'inline');
	    }
	   var trans = INVITATION_SETTING.getParameterByName('trans');
	    if(trans == 2){
	    	$('#Payment_4').attr('disabled', 'disabled').parent().addClass('disabled');
	    	$('#PaymentType_0').attr('disabled', 'disabled').removeAttr('checked').parent().addClass('disabled');
	    	$('#PaymentType_1').prop('checked', true);
	    	INVITATION_SETTING.getPaymentType();
	        INVITATION_SETTING.getPersonalPayment();
	    }
	    //[UAT] DANTAI2_1-41 gent letter
	    var status = $('#status').val();
	    if(typeof status != 'undefined' && status == 1){
	        $('input[name="InvitationType"]').not(':checked').attr('disabled', 'disabled').parent().addClass('disabled');
	        $('input[name="ListEikenLevel[]"]').not(':checked').attr('disabled', 'disabled').parent().prev().addClass('disabled');
	        $('#CheckAll').attr('disabled', 'disabled').click(function(){ return false; });
	        $('input[name="ListEikenLevel[]"]').click(function(){ return false; });
	    }
	    
	    COMMON.showCrossEditMessage('#btnsave');
	    $('form:first *:input[type!=hidden]:first').focus();
	  //Check all
	    $("#CheckAll").click(function () {
	        $('input[name="ListEikenLevel[]"]').prop('checked', $(this).prop("checked"));
	    });
	    
	    $('input[name="ListEikenLevel[]"]').click(function () {
	        INVITATION_SETTING.checkAll();
	    });
	    //form invitation setting list
	    $('#year').on('change', function () {
	        INVITATION_SETTING.getEikenSchedule();
	    });
		$('#EikenSchedule').on('change', function () {
			$('#EikenScheduleHidden').val($('#EikenSchedule option:selected').text());
			// show/hide beneficiary when change kai.
			if (currentEikenScheduleId != $('#EikenSchedule').val()) {
				INVITATION_SETTING.hideBeneficiaryBox();
				INVITATION_SETTING.getHallType();
			}else if(isSemiCurrentKai == 1){
				INVITATION_SETTING.showBeneficiaryBox();
				INVITATION_SETTING.getHallType();
			}
		});

	    $('input[name="InvitationType"]').change(function () {
	        INVITATION_SETTING.getInvitationType();
	        INVITATION_SETTING.getHallType();
	        INVITATION_SETTING.getPrintMessage();
	        INVITATION_SETTING.getPaymentType();
	        INVITATION_SETTING.getPersonalPayment();
	    });
       
	    $('input[name="PrintMessage"]').change(function () {
	        INVITATION_SETTING.getPrintMessage();
	    });
	    $('input[name="PaymentType"]').change(function () {
	        INVITATION_SETTING.getPaymentType();
	        INVITATION_SETTING.getPersonalPayment();
	    });
		$('input[name="PersonalPayment[]"]').change(function () {
			if ($(this).val() == 1 && $(this).is(':checked')) {
				INVITATION_SETTING.getPersonalPayment();
			}
			if (!$('#PersonalPayment_1').is(':checked') || $('#PersonalPayment_1').is(':disabled')) {
				$('input[name="Combini[]"]').attr('disabled', 'disabled').parent().addClass('disabled');
			}
	    });
	    $('#calendar').click(function () {
	        if (!$('input[name="Deadline"]').is(':disabled')) {
	            INVITATION_SETTING.calendar();
	        }
	    });
            $('#issueDateCalendar').click(function () {
	        INVITATION_SETTING.issueDateCalendar();
	    });
	    $("#DoubleEiken").change(function () {
	        INVITATION_SETTING.getMessage('DoubleEiken', 'DoubleEikenMessage');
	    });
	    $("#TemplateMsg1").change(function () {
	        INVITATION_SETTING.getMessage('TemplateMsg1', 'Message1');
	    });
	    $("#TemplateMsg2").change(function () {
	        INVITATION_SETTING.getMessage('TemplateMsg2', 'Message2');
	    });
            $('#btnsave').click(function (event) {
                if(!INVITATION_SETTING.checkDulicateEikenSchedule()){
                    return false;
                }
                if (INVITATION_SETTING.validate('save')!= false) {
                    if (INVITATION_SETTING.sghge2kyu()) {
                        CONFIRM_MESSAGE.show(translate.SGHMSG48, function () {
                    		INVITATION_SETTING.btnSave();
                        });
                        return false;
                }
					INVITATION_SETTING.btnSave();
                }
            });
	  //preview template
            $("#btnpreview").click(function () {
                if(!INVITATION_SETTING.checkDulicateEikenSchedule()){
                    return false;
                }
                if (INVITATION_SETTING.validate('preview') != false) {
                    $('#formSetting').attr('target', '_blank').attr('action', '/invitation/setting/preview').submit();
                } 
                return false;
            });
	    // invitation setting list
	    $('#btnSearch').click(function () {
	        if ($('#year').val() == null || $('#year').val() == '' || $('#year').val() == 'undefined') {
	            ERROR_MESSAGE.show([{id: '', message: translate.MSG001}], function () {
	            }, 'inline');
	            return false;
	        }
	        else {
                    if ($('input[name="InvitationType"]:checked').val() != '2')
                    {
                        $('#orgName').val('');
                        $('#principalName').val('');
                        $('#issueDate').val('');
                    } 
	            $('#formSetting').submit();
	        }
	    });
            
            // change puclic funding status
            $('input:radio[name=PublicFunding]').change(
                    function () {
                       if($(this).val() != 0){
                           INVITATION_SETTING.disEnabledPaymentMethod();
                           INVITATION_SETTING.disabledPaymentMethod();
                       }else{
                           INVITATION_SETTING.enabledPaymentMethod();
                       }
                    }
            );
    
            // change payment bill
            $('input:radio[name=PaymentBill]').change(
                    function () {
                       if($(this).val() != 0){
                           INVITATION_SETTING.disEnabledPaymentMethod();
                           INVITATION_SETTING.disabledPaymentMethod();
                       }else{
                           INVITATION_SETTING.enabledPaymentMethod();
                       }
                    }
            );
    
        // check disable PaymentType
        if($('#PublicFunding_1').is(':checked') == true || $('#PaymentBill_1').is(':checked') == true){
            INVITATION_SETTING.disEnabledPaymentMethod();
            INVITATION_SETTING.disabledPaymentMethod();
        }
        
        if(typeof flagPopup == 'undefined' || flagPopup != 1){
            $('input[name="HallType"]').change(function (e) {
                var newValue = $(this).val();
                if(typeof status != 'undefined' && status == 1 && newValue != dbHallType){
                    CONFIRM_MESSAGE.show(
                            translate.msgConfirmChangeTestSiteHallType,
                            function(){
                                INVITATION_SETTING.getHallType();
                            },
                            function(){                              
                                e.preventDefault();
                                INVITATION_SETTING.rollBackTestSite();
                                return false;
                            },
                            null,
                            translate.btnContinueChange);
                }else{
                    INVITATION_SETTING.getHallType();
                }
            });

            $('input[name="PaymentType"]').change(function (e) {
                INVITATION_SETTING.isChangeHallType(e);
            });
            $('input[name="PaymentBill"]').change(function (e) {
                INVITATION_SETTING.isChangeHallTypeWithPub(e);
            });
            $('input[name="PublicFunding"]').change(function (e) {
                INVITATION_SETTING.isChangeHallTypeWithPub(e);
            });
        }
	},
	btnSave: function(){
		if ($('#function').val() == 'add')
		{
			var listInv= INVITATION_SETTING.settingIndexUrl;
			var recommend= INVITATION_SETTING.recommendIndexUrl;
			$.post("/invitation/setting/save", $("#formSetting").find("input,textarea,select").serialize(), function (data) {
				if(data['type']==1)
				{
					CONFIRM_MESSAGE.show(data['message'],function(){window.location.href=recommend},function(){window.location.href=listInv},null,'はい','いいえ');
				}else{
                                   var year = $('#year option:selected').val();
                                   var scheduleId = $('#EikenSchedule option:selected').val();
                                    $.ajax({
                                        type: "POST",
                                        url: INVITATION_SETTING.isNotShowPopupUrl,
                                        data:{
                                                'year' : year,
                                                'scheduleId' : scheduleId
                                        },
                                        success: function (result) {
                                var json = JSON.parse(result);
                                if (json.status === 0) {
                                CONFIRM_MESSAGE.show(data['message'],
                                        function () {
                                            CONFIRM_MESSAGE.show(translate.MSGPopupWaringGradeClassECSetting, function () {
                                                CONFIRM_MESSAGE.show(translate.MSGConfirmGenarateEx,
                                                        function () {
                                                            INVITATION_SETTING.generateInvitation(year, kai);
                                                        },
                                                        function () {
                                                            window.location.href = INVITATION_SETTING.settingEditUrl + data['id'];
                                                        },
                                                        null,
                                                        translate.okConfirmGenarateEx,
                                                        translate.cancelConfirmGenarateEx, 'confirmGenarate');
                                            }, function () {
                                                window.location.href = INVITATION_SETTING.settingIndexUrl;
                                            }, null, null, null, 'ConfirmSubmit');
                                            $('#ConfirmSubmit #btnCancelConfirm-ConfirmSubmit').addClass('btn-red');
                                            $('#ConfirmSubmit #btnAgreeConfirm-ConfirmSubmit').removeClass('btn-red');
                                        },
                                        function () {
                                            window.location.href = listInv;
                                        }, null, 'はい', 'いいえ');

                                } else {
                                CONFIRM_MESSAGE.show(data['message'],
                                        function () {
                                            CONFIRM_MESSAGE.show(translate.MSGConfirmGenarateEx,
                                                    function () {
                                                        INVITATION_SETTING.generateInvitation(year, kai);
                                                    },
                                                    function () {
                                                        window.location.href = INVITATION_SETTING.settingEditUrl + data['id'];
                                                    },
                                                    null,
                                                    translate.okConfirmGenarateEx,
                                                    translate.cancelConfirmGenarateEx, 'confirmGenarate');
                                        },
                                        function () {
                                            window.location.href = listInv;
                                        }, null, 'はい', 'いいえ');

                                return false;
                            }
                        }
                                    }); 
					
				}
			});
			return false;
		}
		else{
                    $('#formSetting').removeAttr('target').attr('action', '/invitation/setting/update').submit();
                }
			
	},
	generateInvitation: function(year, kai){
		var generate = INVITATION_SETTING.invitationLetterUrl;
		var urlRedirect = INVITATION_SETTING.generateIndexUrl;
		$.ajax({
	        type: 'POST',
	        dataType: 'json',
	        url: generate,
	        data: {
	            'year' : year,
	            'kai' : kai
	            },
	            success: function(data){            	
	                // TODO Notice user to check mail
	                if(data.success){
	                    ERROR_MESSAGE.clear();
	                    setTimeout($.proxy(function() {
	                        ALERT_MESSAGE.show(data.messages[0],function(){window.location.href=urlRedirect});
	                    }, this), 500);
	                }
	                else
	                    ERROR_MESSAGE.show(data.messages[0], null, 'inline');
	            },
	            error:function(data){
	                // TODO Notice user to come back later
	            }
	      });
		return false;
	},
	getParameterByName: function(name) {
	    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
	        results = regex.exec(location.search);
	    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	},
	// function change disable/enable
	getInvitationType: function() {
            
	    if ($('input[name="InvitationType"]:checked').val() == '3') { //3- Only Einavi
	        $('input[name="ExamDay"]').attr('disabled', 'disabled').parent().addClass('disabled');
	        $('input[name="ExamPlace"]').attr('disabled', 'disabled');
	        $('input[name="Deadline"]').attr('disabled', 'disabled');
	        $('input[name="PaymentType"]').attr('disabled', 'disabled').parent().addClass('disabled');
	        $('select[name="DoubleEiken"]').attr('disabled', 'disabled');
	        $('textarea[name="DoubleEikenMessage"]').attr('disabled', 'disabled');
	        $('#PrintMessage').attr('disabled', 'disabled');

                $('input[name="PublicFunding"]').attr('disabled', 'disabled').parent().addClass('disabled');
                $('input[name="PaymentBill"]').attr('disabled', 'disabled').parent().addClass('disabled');
                $('input[name="Beneficiary"]').attr('disabled', 'disabled').parent().addClass('disabled');
                $('#orgName').attr('disabled', 'disabled');
                $('#principalName').attr('disabled', 'disabled');
                $('#personalTitle').attr('disabled', 'disabled');
                $('#issueDate').attr('disabled', 'disabled');
	    } else {
                if ($('input[name="InvitationType"]:checked').val() != '2')
                {
                    $('#orgName').attr('disabled', 'disabled');
                    $('#principalName').attr('disabled', 'disabled');
                    $('#personalTitle').attr('disabled', 'disabled');
                    $('#issueDate').attr('disabled', 'disabled');
                }
                else{
                    $('#orgName').removeAttr('disabled', 'disabled');
                    $('#principalName').removeAttr('disabled', 'disabled');
                    $('#personalTitle').removeAttr('disabled', 'disabled');
                    $('#issueDate').removeAttr('disabled', 'disabled');
                }
	        if ($('input[name="InvitationType"]:checked').val() != "4" && $('input[name="InvitationType"]').val() != '' && $('input[name="InvitationType"]').val() != null && $('input[name="InvitationType"]').val() != 'undefined') {
	            $('#PrintMessage').removeAttr('disabled', 'disabled');
	        } else {
	            $('#PrintMessage').attr('disabled', 'disabled');
	        }
	        $('input[name="ExamDay"]').removeAttr('disabled', 'disabled').parent().removeClass('disabled');
	        $('.ExamDayDisabled').prop('checked', false).attr('disabled', 'disabled').parent().addClass('disabled');
                
                 if((typeof(flgSpriceSpecial) !== 'undefined') && flgSpriceSpecial==1){
                    $('#Beneficiary_2').removeAttr('disabled', 'disabled').parent().removeClass('disabled');
                 }else{
                    $('input[name="Beneficiary"]').removeAttr('disabled', 'disabled').parent().removeClass('disabled');
                 }
	        $('input[name="ExamPlace"]').removeAttr('disabled', 'disabled');

	        $('input[name="Deadline"]').removeAttr('disabled', 'disabled');
                // check specalprice
                $('input[name="PaymentType"]').removeAttr('disabled', 'disabled').parent().removeClass('disabled');
                if((typeof(flgSpriceSpecial) !== 'undefined') && flgSpriceSpecial==1)
                    $('#PaymentType_1').attr('disabled', 'disabled').parent().addClass('disabled');
                    
                if($('input[name="PublicFunding"]:checked').val() == 1 || $('input[name="PaymentBill"]:checked').val() == 1 ){
                    $('input[id="PaymentType_0"]').attr('disabled', 'disabled').parent().addClass('disabled');
                }
	        $('select[name="DoubleEiken"]').removeAttr('disabled', 'disabled');
	        $('textarea[name="DoubleEikenMessage"]').removeAttr('disabled', 'disabled');
                
                $('input[name="PublicFunding"]').removeAttr('disabled', 'disabled').parent().removeClass('disabled');
                $('input[name="PaymentBill"]').removeAttr('disabled', 'disabled').parent().removeClass('disabled');
	    }
	},
	getHallType: function() {
	    if ($('input[name="HallType"]:checked').val() == '1') {
	        $('#setting .HallType_main span').addClass('hide');
	        $('input[name="ExamPlace"]').val($('input[name="ExamPlace"]').attr('ref'));
	        $('.HallTypeExamDay').prop('checked', false).attr('disabled', 'disabled').parent().addClass('disabled');
	    } else {
	        $('#setting .HallType_main span').removeClass('hide');
	        $('input[name="ExamPlace"]').val($('input[name="ExamPlace"]').attr('rel'));
	        if ($('input[name="InvitationType"]:checked').val() != '3') {
                    
	            $('.HallTypeExamDay').removeAttr('disabled', 'disabled').parent().removeClass('disabled');
	        }
	        $('.ExamDayDisabled').prop('checked', false).attr('disabled', 'disabled').parent().addClass('disabled');
	    }
	    if($('input[name="ExamDay"]:enabled').length == 1){
	        $('input[name="ExamDay"]:enabled').prop('checked', true);
	    }
	},
	getPrintMessage: function() {
	    if (!$('#PrintMessage').is(':checked') || $('input[name="InvitationType"]:checked').val() == 3 || $('input[name="InvitationType"]:checked').val() == 4) {
	        $('select[name="TemplateMsg1"]').attr('disabled', 'disabled');
	        $('select[name="TemplateMsg2"]').attr('disabled', 'disabled');
	        $('textarea[name="Message1"]').attr('disabled', 'disabled');
	        $('textarea[name="Message2"]').attr('disabled', 'disabled');
	    } else {
	        if ($('#PrintMessage').is(':checked')) {
	            $('select[name="TemplateMsg1"]').removeAttr('disabled', 'disabled');
	            $('textarea[name="Message1"]').removeAttr('disabled', 'disabled');
	            if ($('input[name="InvitationType"]:checked').val() == '2') {
	                $('select[name="TemplateMsg2"]').removeAttr('disabled', 'disabled');
	                $('textarea[name="Message2"]').removeAttr('disabled', 'disabled');
	            } else {
	                $('select[name="TemplateMsg2"]').attr('disabled', 'disabled');
	                $('textarea[name="Message2"]').attr('disabled', 'disabled');
	            }
	        }
	    }
	},
	getPaymentType: function() {
	    if ($('input[name="InvitationType"]:checked').val() == '3') {
	        $('input[name="OrganizationPayment"]').attr('disabled', 'disabled').parent().addClass('disabled');
	        $('input[name="PersonalPayment[]"]').attr('disabled', 'disabled').parent().addClass('disabled');
	    } else {
	        if ($('input[name="PaymentType"]:checked').val() == '0') {
                    if (typeof paymentType != 'undefined' && paymentType!='0') {
                        $('#PersonalPayment_0').prop('checked', true);
                        $('#PersonalPayment_1').prop('checked', true);
                    }
	            $('input[name="PersonalPayment[]"]').removeAttr('disabled', 'disabled').parent().removeClass('disabled');
	            $('input[name="OrganizationPayment"]').attr('disabled', 'disabled').parent().addClass('disabled');
	        }
	        else {
	            $('input[name="OrganizationPayment"]').removeAttr('disabled', 'disabled').parent().removeClass('disabled');
	            $('input[name="PersonalPayment[]"]').attr('disabled', 'disabled').parent().addClass('disabled');
	        }
	    }
	    var trans = INVITATION_SETTING.getParameterByName('trans');
	    if(trans == 2){
	    	$('#Payment_4').attr('disabled', 'disabled').parent().addClass('disabled');
	    	$('#PaymentType_0').attr('disabled', 'disabled').removeAttr('checked').parent().addClass('disabled');
	    	$('#PaymentType_1').prop('checked', true);
	    }
	},
	getPersonalPayment: function () {
            setTimeout(function() {
                if(INVITATION_SETTING.checkedComb == true){
                    if (!$('#PersonalPayment_1').is(':disabled')) {
                            if ($('input[name="Combini[]"]:checked').length == 0 && $('#PersonalPayment_1').is(':checked')) {
                                $('input[name="Combini[]"]').prop('checked', true);
                            }
                            $('input[name="Combini[]"]').removeAttr('disabled', 'disabled').parent().removeClass('disabled');
                    }
                    if (!$('#PersonalPayment_1').is(':checked') || $('#PersonalPayment_1').is(':disabled')) {
                            $('input[name="Combini[]"]').attr('disabled', 'disabled').parent().addClass('disabled');
                    }
                }
            }, 10);
	},
	checkAll: function() {
	    if ($('input[name="ListEikenLevel[]"]').length == $('input[name="ListEikenLevel[]"]:checked').length) {
	        $("#CheckAll").prop('checked', true);
	    }else {
	        $("#CheckAll").prop('checked', false);
	    }
	},
	//function getKai follow year, substitute ajax load.
	getEikenSchedule: function() {
	    var element = $('#year').find('option:selected').attr('ref');
	    var pkai = $('#year').attr('ref');
	    if (typeof element != 'undefined') {
	        element = $.parseJSON(element);
	        var html = '';
	        var selected = '';
	        $.each(element, function (kai, id) {
	            if (id == pkai) {  selected = 'selected'; } else { selected = ''; }
	            html = html + '<option ' + selected + ' value="' + id + '">' + kai + '</option>';
	        });
	        if ($('#type').val() == 'index') html = '<option value=""></option>' + html;
	        if (INVITATION_SETTING.msieversion() && html == '') html = '<option value=""></option>';

	        $('#EikenSchedule').html(html);
	        //phucvv
	        //get val eikenschedule
	        $('#EikenScheduleHidden').val(($('#EikenSchedule option:selected').text()));
	    }
	},
	getMessage: function(msgName, MsgContent) {
	    var element = $('#' + msgName).find('option:selected');
	    $('#' + MsgContent).val(element.attr("rel"));
	},
	//function calendar
	calendar: function() {
	    $('#datetimepicker').datepicker('show');
	},
        issueDateCalendar: function() {
            if ($('input[name="InvitationType"]:checked').val() == '2')
            {
                $('#issueDate').datepicker('show');
            } 
	},
	//Invitation setting form
	checkDate: function(date) {
	    var year = $('#year').val();
	    var EikenSchedule = $('#EikenSchedule').val();
	    var deadline = $('#invDeadline').val();

	    if (typeof deadline != 'undefined') {
	        deadline = $.parseJSON(deadline);
	        if (date <= deadline[year][EikenSchedule]) {
	            return true;
	        } else {
	            return false;
	        }
	    }
	},
	checkDateDeadline: function(date) {
		var year = $('#year').val();
		var EikenSchedule = $('#EikenSchedule').val();
		var deadline = $('#invDeadline').val();

		if (typeof deadline != 'undefined') {
			deadline = $.parseJSON(deadline);
			if (date <= deadline[year][EikenSchedule]) {
				return true;
			} else {
				return false;
			}
		}
	},
	checkExits: function() {
	    var EikenSchedule = $('#EikenSchedule').val();
	    var listInv = INVITATION_SETTING.settingIndexUrl;
	    var recommend = INVITATION_SETTING.recommendIndexUrl;
	    $.ajax({
	        type: "POST",
	        url: INVITATION_SETTING.checkExistUrl,
	        data: $("#formSetting").serialize(),
	        success: function (data) {
	            var k = $("#EikenSchedule option:selected").text();
	            if (!data) {
	                k = translate.MSG030.replace('%u', $('#year').val()).replace('%u', k);
	                ERROR_MESSAGE.show([{id: '', message: k}], null , 'inline');
	                $('#EikenSchedule').addClass('error');
	                return false;
	            } else {
	                if($('#function').val()== 'add'){
                            $.post("/invitation/setting/save", $("#formSetting").find("input,textarea,select").serialize(), function (data) {
	                	if(data['type']==1)
	                	{
                                    CONFIRM_MESSAGE.show(data['message'],function(){window.location.href=recommend},function(){window.location.href=listInv},null,'はい','いいえ');
                                              return false;
	                	}else if(data['type'] == 2){
                                    var kai = $('#EikenSchedule option:selected').val();
                                    var year = $('#year option:selected').val();
                                    $.ajax({
                                        type: "POST",
                                        url: INVITATION_SETTING.isNotShowPopupUrl,
                                        data:{
                                                'year' : year,
                                                'scheduleId' : kai
                                        },
                                        success: function (result) {
                                            var json = JSON.parse(result);
                                            if (json.status === 0) {
                                            CONFIRM_MESSAGE.show(data['message'],
                                                    function () {
                                                        CONFIRM_MESSAGE.show(translate.MSGPopupWaringGradeClassECSetting, function () {
                                                            CONFIRM_MESSAGE.show(translate.MSGConfirmGenarateEx,
                                                                    function () {
                                                                        INVITATION_SETTING.generateInvitation(year, kai);
                                                                    },
                                                                    function () {
                                                                        window.location.href = INVITATION_SETTING.settingEditUrl + data['id'];
                                                                    },
                                                                    null,
                                                                    translate.okConfirmGenarateEx,
                                                                    translate.cancelConfirmGenarateEx, 'confirmGenarate');
                                                        }, function () {
                                                            window.location.href = INVITATION_SETTING.settingIndexUrl;
                                                        }, null, null, null, 'ConfirmSubmit');
                                                        $('#ConfirmSubmit #btnCancelConfirm-ConfirmSubmit').addClass('btn-red');
                                                        $('#ConfirmSubmit #btnAgreeConfirm-ConfirmSubmit').removeClass('btn-red');
                                                    },
                                                    function () {
                                                        window.location.href = listInv;
                                                    }, null, 'はい', 'いいえ');

                                        } else {
                                            CONFIRM_MESSAGE.show(data['message'],
                                                    function () {
                                                        CONFIRM_MESSAGE.show(translate.MSGConfirmGenarateEx,
                                                                function () {
                                                                    INVITATION_SETTING.generateInvitation(year, kai);
                                                                },
                                                                function () {
                                                                    window.location.href = INVITATION_SETTING.settingEditUrl + data['id'];
                                                                },
                                                                null,
                                                                translate.okConfirmGenarateEx,
                                                                translate.cancelConfirmGenarateEx, 'confirmGenarate');
                                                    },
                                                    function () {
                                                        window.location.href = listInv;
                                                    }, null, 'はい', 'いいえ');

                                            return false;
                                        }
                                        }
                                    }); 
                                        return false;
                                    } 
                                    window.location.href = INVITATION_SETTING.settingAddUrl;
	                       });
                            return false;
	            	}
	            	else{
                            $('#formSetting').removeAttr('target').attr('action', '/invitation/setting/update').submit();
                        }
	            		
	            }
	        }
	    });
	},
	sghge2kyu: function () {
		if (typeof definitionSpecial != 'undefined' && $('input[name="HallType"]:checked').val() == 1 && definitionSpecial > 0 && parseInt($('input[name="ListEikenLevel[]"]:checked:eq(-1)').val()) > 2) {
			return true;
		}
		return false;
	},
	validate: function(type) {
	    var message = [];
	    var chooseDeadline = $('#datetimepicker').val();
	    var currentDate = $('#currentDate').val();
            var issueDate = $('#issueDate').val();

            if ($('#year').val() == null || $('#year').val() == '' || $('#EikenSchedule').val() == '' || $('#EikenSchedule').val() == null) {
                message.push({id: 'EikenSchedule', message: translate.MSG001});
            } else {
                if ($('#EikenSchedule').val() == null || $('#EikenSchedule').val() == '') {
                    message.push({id: 'EikenSchedule', message: translate.MSG001});
                }
                if ($('input[name="InvitationType"]:checked').length == 0) {
                    message.push({id: 'InvitationType', message: translate.MSG001});
                }
                if (!($('#issueDate').attr('disabled')) && ($('#issueDate').val() != '') && !INVITATION_SETTING.isDate($('#issueDate').val())) {
                    message.push({id: 'issueDate', message: translate.MSG011});
                }
                else if (!($('#issueDate').attr('disabled')) && ($('#issueDate').val() != '') && !INVITATION_SETTING.checkDate(issueDate)) {
                    message.push({id: 'issueDate', message: translate.R4_MSG11});
                }
                if ($('input[name="HallType"]:checked').length == 0) {
                    message.push({id: 'HallType', message: translate.MSG001});
                }
                if ($('input[name="ListEikenLevel[]"]:checked').length == 0) {
                    message.push({id: 'CheckAll', message: translate.MSG038});
                }
                if ($('input[name="ExamDay"]:disabled').length < 3 && $('input[name="ExamDay"]:checked').length == 0 && $('input[name="ExamDay"]').length !== 2) {
                    message.push({id: 'ExamDay', message: translate.MSG001});
                }else if ($('input[name="ExamDay"]:disabled').length < 2 && $('input[name="ExamDay"]:checked').length == 0 && $('input[name="ExamDay"]').length === 2) {
                    message.push({id: 'ExamDay', message: translate.MSG001});
                }
                if (!$('#ExamPlace').is(':disabled') && ($('#ExamPlace').val() == null || $('#ExamPlace').val() == '')) {
                    message.push({id: 'ExamPlace', message: translate.MSG001});
                }
                if (!$('input[name="Deadline"]').is(':disabled') && ($('input[name="Deadline"]').val() == null || $('input[name="Deadline"]').val() == '')) {
                    message.push({id: 'datetimepicker', message: translate.MSG001});
                }
                else if (!$('input[name="Deadline"]').is(':disabled') && !INVITATION_SETTING.isDate($('input[name="Deadline"]').val())) {
                    message.push({id: 'datetimepicker', message: translate.MSG011});
                }
                else if (!$('input[name="Deadline"]').is(':disabled') && !INVITATION_SETTING.checkDateDeadline(chooseDeadline)) {
                    message.push({id: 'datetimepicker', message: translate.MSG057});
                }
//                if (!$('input[name="PublicFunding"]').is(':disabled') && $('input[name="PublicFunding"]:checked').length == 0) {
//                    message.push({id: 'PublicFunding', message: translate.MSG001});
//                }
//                if (!$('input[name="PaymentBill"]').is(':disabled') && $('input[name="PaymentBill"]:checked').length == 0) {
//                    message.push({id: 'PaymentBill', message: translate.MSG001});
//                }
                if (!$('input[name="PaymentType"]').is(':disabled') && $('input[name="PaymentType"]:checked').length == 0) {
                    message.push({id: 'PaymentType', message: translate.MSG001});
                }
                if (!$('input[name="PersonalPayment[]"]').is(':disabled') && $('input[name="PersonalPayment[]"]:checked').length == 0) {
                    message.push({id: 'PersonalPayment', message: translate.MSG001});
                }
                if (!$('input[name="Combini[]"]').is(':disabled') && $('input[name="Combini[]"]:checked').length == 0) {
                    message.push({id: 'Combini', message: translate.MSG001});
                }
                if (!$('input[name="OrganizationPayment"]').is(':disabled') && $('input[name="OrganizationPayment"]:checked').length == 0) {
                    message.push({id: 'OrganizationPayment', message: translate.MSG001});
                }
                if ($('div#Beneficiary').data('display') == "1" && $('input[name="Beneficiary"]:checked').length == 0) {
                    if($('#Beneficiary_1').attr('disabled') != 'disabled'){
                        if((typeof(flgSpriceSpecial) !== 'undefined') && flgSpriceSpecial==1){
                            message.push({id: 'Beneficiary_2', message: translate.MSG001});
                        }else{
                            message.push({id: 'Beneficiary_1', message: translate.MSG001});
                        }
                    }
                    else if($('#Beneficiary_2').attr('disabled') != 'disabled'){
                        if((typeof(flgSpriceSpecial) !== 'undefined') && flgSpriceSpecial==1){
                            message.push({id: 'Beneficiary_2', message: translate.MSG001});
                        }
                    }
                }
                if (!$('#Message1').is(':disabled') && ($('#Message1').val() == null || $('#Message1').val() == '')) {
                    message.push({id: 'Message1', message: translate.MSG001});
                }
                if (!$('#Message2').is(':disabled') && ($('#Message2').val() == null || $('#Message2').val() == '')) {
                    message.push({id: 'Message2', message: translate.MSG001});
                }
                if (!INVITATION_SETTING.checkDate(currentDate)) {
                    var kai = $("#EikenSchedule option:selected").text();
                    if (kai == '' || kai == null)
                        kai = $("#kai").text();
                    var m29 = translate.MSG029.replace('%u', $('#year').val()).replace('%u', kai);
                    message.push({id: 'EikenSchedule', message: m29});
                }
            }
	    //Output messsage error view.
	    if (message != '') {
	        $('div input').removeClass('error');
	        $('div textarea').removeClass('error');
	        $('div select').removeClass('error');
	        $('div').removeClass('error');
	        $.each(message, function (i, v) {
	            $('#' + v.id).addClass('error');
	        });
	        ERROR_MESSAGE.show(message, null , 'inline');
	        return false;
	    } 
            if ($('#function').val() == 'add' && type != 'preview') {
                if (INVITATION_SETTING.sghge2kyu()) {
                    CONFIRM_MESSAGE.show(translate.SGHMSG48, function () {
                        INVITATION_SETTING.checkExits();
                    });
                    return false;
                }
                INVITATION_SETTING.checkExits();
                return false;
            } 
	},
	//check date yyyy/mm/dd
	isDate: function(txtDate) {
	    var currVal = txtDate;
	    if (currVal == '')
	        return false;

	    var DatePattern = /^(\d{4})(\/|-)(\d{2})(\/|-)(\d{2})$/; //Declare Regex
	    var dateArray = currVal.match(DatePattern); // is format OK

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
	},
	//Check browser ie.
	msieversion: function() {
	    var ua = window.navigator.userAgent;
	    var msie = ua.indexOf("MSIE ");
	    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))
	        return true;
	    else
	        return false;
	},
        //disable payment method
        disabledPaymentMethod : function() {
                $('#PaymentType_0').attr('disabled', 'disabled').parent().addClass('disabled');
                $('#PaymentType_1').prop('checked', true);
        },
        enabledPaymentMethod : function() {
            var trans = INVITATION_SETTING.getParameterByName('trans');
            if($('#PublicFunding_0').is(':checked') == true && $('#PaymentBill_0').is(':checked') == true && trans != 2){
                $('input[name="PaymentType"]').removeAttr('disabled', 'disabled').parent().removeClass('disabled');  
            }
        },
        showPopupForChangePublicfundingStatus : function() {
            var paymentType = $('input[name="PaymentType"]:checked').val();
            if(oldPaymentType != null){
                var paymentType = oldPaymentType;
            }
            var pageLoadPaymentType = $('input[name="PaymentType"]:checked').val();;
            
            var organizationPayment = $('input[name="OrganizationPayment"]:checked').val();
            if(oldOrganizationPayment != null){
                var organizationPayment = oldOrganizationPayment;
            }
            var lastChangePaymentType = $('input[name="PaymentType"]:checked').val();;
            var PersonalPayment_1 = 0;
            if($('#PersonalPayment_1').is(':checked')){
                PersonalPayment_1 = 1;
            }
            var pageloadPersonalPayment_1 = PersonalPayment_1;
            if(oldPersonalPayment_1 != null){
                PersonalPayment_1 = 1;
            }
            $('input[name="HallType"]').change(function (e) {
                if(!INVITATION_SETTING.checkPermissonChangeTestSite($(this).val())){
                    e.preventDefault();
                    INVITATION_SETTING.rollBackTestSite();
                    return false;
                }
	        INVITATION_SETTING.getHallType();
	    });
//            $('input[name="PublicFunding"]').change(
//                function (){
//                    if($('input[name="PaymentType"]:checked').val() == 0 && paymentType == 0){
//                        lastChangePaymentType = '';
//                    }
//                    if(paymentType == 1 && lastChangePaymentType == 0){
//                        lastChangePaymentType = 1;
//                    }
//                }
//            );
//            check PublicFunding
//            $('input[name="PublicFunding"]').click( function(event) {
//                    if(paymentType == 0 && $(this).val() == 1){
//                        if(pageLoadPaymentType == paymentType){
//                            INVITATION_SETTING.isValidPaymentType('PublicFunding_0',event);
//                        }
//                    }
//                }
//            );
//            $('input[name="PaymentBill"]').change(
//                function (){
//                    if($('input[name="PaymentType"]:checked').val() == 0 && paymentType == 0 ){
//                        lastChangePaymentType = '';
//                    }
//                    if(paymentType == 1 && lastChangePaymentType == 0){
//                        lastChangePaymentType = 1;
//                    }
//                }
//            );
//            check PaymentBill
//            $('input[name="PaymentBill"]').click(
//                function (event){
//                    if(paymentType == 0 && $(this).val() == 1){
//                        if(pageLoadPaymentType == paymentType){
//                            INVITATION_SETTING.isValidPaymentType('PaymentBill_0',event);
//                        }
//                    }
//                }
//            );
//            check PaymentType
            $('input[name="PaymentType"]').click(
                function (event){
                    if($(this).val() != paymentType && paymentType == 0){
                        if($(this).val() != lastChangePaymentType){
                            if(pageLoadPaymentType == paymentType){
                                INVITATION_SETTING.showPopupContacEiken();
                                event.preventDefault ? event.preventDefault() : (event.returnValue = false);
                                $('#PaymentType_0').prop('checked', true);
                                INVITATION_SETTING.enabledPaymentStore();
                                lastChangePaymentType = '';
                            }
                        }
                    }else if($(this).val() != paymentType && paymentType == 1 && $(this).val() != lastChangePaymentType){
                        if($(this).val() != lastChangePaymentType && lastChangePaymentType != ''){
                            INVITATION_SETTING.showPopupAllow();
                            lastChangePaymentType = 0;
                        }
                    }else{
                        lastChangePaymentType = $(this).val();
                    }
                     
                }
            );
//            check change teamplate gen letter
            $('input[name="OrganizationPayment"]').click(
                function (){
                    if($(this).val() != organizationPayment ){
                        if(paymentType == 1){
                            INVITATION_SETTING.showPopupAllow();
                        }
                    }
                }
            );
//            check change payment method comi , store
            $('input[name="PersonalPayment[]"]').click(
                function (event){
                    if(paymentType == 0){
                        if(pageLoadPaymentType == paymentType){
                            INVITATION_SETTING.showPopupContacEiken();
                            INVITATION_SETTING.checkedComb = false;
                            //reset conbini checkbox list: uncheck all
                            if(PersonalPayment_1 == 0){
                                $('#PersonalPayment_1').removeAttr('checked');
                                INVITATION_SETTING.getPersonalPayment();
                                $('input[name="Combini[]"]').removeAttr('checked');
                            }
                            event.preventDefault ? event.preventDefault() : (event.returnValue = false);
                        }
                    }
                    if(pageloadPersonalPayment_1 == 1 && pageLoadPaymentType == paymentType){
                        $('input[name="Combini[]"]').removeAttr('disabled', 'disabled').parent().removeClass('disabled');
                    }
                }
            );
//            check change payment method comi , store
            $('input[name="Combini[]"]').click(
                function (event){
                    if(paymentType == 0){
                        if(pageLoadPaymentType == paymentType){
                            INVITATION_SETTING.showPopupContacEiken();
                            event.preventDefault ? event.preventDefault() : (event.returnValue = false);
                        }
                    }
                }
            );
        },
        showPopupAllow : function(){
            ERROR_MESSAGE.show(translate.MSG_Allow,
                function(){});
        },
        showPopupContacEiken : function(){
            ERROR_MESSAGE.show(translate.MSG_Contact,
                function(){});
        },
        disEnabledPaymentMethod : function(){
            $('input[name="OrganizationPayment"]').removeAttr('disabled', 'disabled').parent().removeClass('disabled');
            $('input[name="PersonalPayment[]"]').attr('disabled', 'disabled').parent().addClass('disabled');
            $('input[name="Combini[]"]').attr('disabled', 'disabled').parent().addClass('disabled');
        },
        enabledPaymentStore : function (){
            $('input[name="PersonalPayment[]"]').removeAttr('disabled', 'disabled').parent().removeClass('disabled');
            if($('#PersonalPayment_1').is(':checked')){
                $('input[name="Combini[]"]').removeAttr('disabled', 'disabled').parent().removeClass('disabled');
            }
            $('input[name="OrganizationPayment"]').attr('disabled', 'disabled').parent().addClass('disabled');
        },
        isValidPaymentType : function(id,event){
            INVITATION_SETTING.showPopupContacEiken();
            $('#'+id).prop('checked', true);
            event.preventDefault ? event.preventDefault() : (event.returnValue = false);
            $('#PaymentType_0').prop('checked', true);
            INVITATION_SETTING.enabledPaymentStore();
            $('input[name="PaymentType"]').removeAttr('disabled', 'disabled').parent().removeClass('disabled');
        },
        checkPermissonChangeTestSite: function(newValue){
            if(dbPaymentType == 1 && newValue != dbHallType){
                ERROR_MESSAGE.show(translate.msgAllowChangeTestSite, function(){});
                return true;
            }else if(dbPaymentType == 0 && newValue != dbHallType){
                ERROR_MESSAGE.show(translate.msgNotAllowChangeTestSite, function(){});1
                return false;
            }
            return true;
        },
        checkDulicateEikenSchedule: function () {
            var eikenScheduleId = $('#EikenSchedule').val();
            var paymentType = $('input[name=PaymentType]:checked').val();
            if (paymentType == 0 && statusRefund == 1 && currentEikenScheduleId == eikenScheduleId) {
                ERROR_MESSAGE.show(translate.R4_MSG_when_refund_status_equal_2_warning_collective_payment, function () {
                    return false;
                });
                return false;
            }
            return true;
        },
        rollBackTestSite: function () {
            if (dbHallType == 1) {
                $("#HallType_1").prop('checked', true);
                $("#HallType_0").removeAttr('checked');
            } else if (dbHallType == 0) {
                $("#HallType_1").removeAttr('checked');
                $("#HallType_0").prop('checked', true);
            }
        },
        rollBackPaymentType: function () {
            if (dbPaymentType == 1) {
                $("#PaymentType_1").prop('checked', true);
                $("#PaymentType_0").removeAttr('checked');
                
                $("#PaymentBill_0").prop('checked', true);
                $("#PaymentBill_1").removeAttr('checked');
                
                $("#PublicFunding_0").prop('checked', true);
                $("#PublicFunding_1").removeAttr('checked');
                
                $('#PaymentType_0').removeAttr('disabled', 'disabled').parent().removeClass('disabled');
            } else if (dbPaymentType == 0) {
                $("#PaymentType_1").removeAttr('checked');
                $("#PaymentType_0").prop('checked', true);
                
                $("#PaymentBill_0").prop('checked', true);
                $("#PaymentBill_1").removeAttr('checked');
                
                $("#PublicFunding_0").prop('checked', true);
                $("#PublicFunding_1").removeAttr('checked');
                
                $('#PaymentType_0').removeAttr('disabled', 'disabled').parent().removeClass('disabled');
            }
        },
        isChangeHallType: function (e) {
            var newValue = $('input[name=PaymentType]:checked').val();
            var status = $('#status').val();
            if(typeof status != 'undefined' && status == 1 && newValue != dbPaymentType){
                INVITATION_SETTING.hadShowPopup = 1;
                CONFIRM_MESSAGE.show(
                        translate.msgConfirmChangeTestSite,
                        function(){
                            
                        },
                        function(){
                            INVITATION_SETTING.hadShowPopup = 0;
                            e.preventDefault();
                            INVITATION_SETTING.rollBackPaymentType();
                            return false;
                        },
                        null,
                        translate.btnContinueChange);
            }else{
                INVITATION_SETTING.hadShowPopup = 0;
            }
        },
        isChangeHallTypeWithPub: function (e) {
            var newValue = $('input[name=PaymentType]:checked').val();
            var paymentBill = $('input[name=PaymentBill]:checked').val();
            var publicFunding = $('input[name=PublicFunding]:checked').val();
            var status = $('#status').val();
            if(typeof status != 'undefined' && status == 1){
                if(newValue != dbPaymentType && INVITATION_SETTING.hadShowPopup === 0){
                    INVITATION_SETTING.showConfirmPayment(e);
                }else if(paymentTypeInData == 0 && (paymentBill != 0 || publicFunding != 0) && INVITATION_SETTING.hadShowPopup == 0){
                    if(newValue != dbPaymentType){
                        INVITATION_SETTING.showConfirmPayment(e);
                    }
                }
            }
        },
        showConfirmPayment : function(e){
          INVITATION_SETTING.hadShowPopup = 1;
            CONFIRM_MESSAGE.show(
                    translate.msgConfirmChangeTestSite,
                    function(){

                    },
                    function(){
                        INVITATION_SETTING.hadShowPopup = 0;
                        e.preventDefault();
                        INVITATION_SETTING.rollBackPaymentType();
                        return false;
                    },
                    null,
				translate.btnContinueChange);
		},
	loadExpiredPaymentDate: function (type) {
		if (type == 'add') {
			var kai = $('#EikenSchedule option:selected').text();
		} else {
			var kai = $('#EikenScheduleHidden').val();
		}
		$.ajax({
			method: "POST",
			url: INVITATION_SETTING.loadExpiredPaymentDateUrl,
			dataType: 'json',
			data: {
				year: $('#year').val(),
				kai: kai
			},
			global: false,
			success: function (response) {
				if (response.status == 1) {
					$('#expired_payment_date').html(response.content);
				}
			}
		});
	},
	hideBeneficiaryBox: function () {
		$('div.beneficiary').hide();
		$('div#Beneficiary').attr('data-display', '0');
		$("input[name='Beneficiary']").attr('disabled', 'disabled');
	},
	showBeneficiaryBox: function(){
		$('div.beneficiary').show();
		$('div#Beneficiary').attr('data-display', '1');
		$("input[name='Beneficiary']").removeAttr('disabled');
	}
}
