/**
 * 
 */
var eikenPupil = {
	baseUrl : window.location.protocol + "//" + window.location.host + "/",
	deleteEikUrl : 'eiken/eikenpupil/destroy',
	listEikUrl : 'eiken/eikenpupil/index',
	loadMainHallUrl : 'eiken/eikenpupil/load-main-hall',
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
				ERROR_MESSAGE.show(eikAppLevelMess.SystemError);
			}
		});
	},
	deleteEikPupil : function (eikenLevelId) {
		var selectedItems = $('.checkbox1:checked');
		if (!selectedItems.length) {
			ERROR_MESSAGE.show(eikAppLevelMess.MSG15);
		}
		else {
			CONFIRM_MESSAGE.show(eikAppLevelMess.MSG16, function () {
				var data = '';
				selectedItems.each (function () {
					data += $(this).attr('id') + '|';
				});
				eikenPupil.ajaxHTML (eikenPupil.baseUrl + eikenPupil.deleteEikUrl, {selectedItems:data}, function () {
					window.location.href = eikenPupil.baseUrl + eikenPupil.listEikUrl + '/' + eikenLevelId;
				})
			});
		}
	},
	deleteSingleEikPupil : function (id, eikenLevelId) {
		if (!id) {
			ERROR_MESSAGE.show(eikAppLevelMess.MSG15);
		}
		else {
			CONFIRM_MESSAGE.show(eikAppLevelMess.MSG16, function () {
				eikenPupil.ajaxHTML (eikenPupil.baseUrl + eikenPupil.deleteEikUrl, {selectedItems:id, eikenLevelId: eikenLevelId}, function () {
					window.location.href = eikenPupil.baseUrl + eikenPupil.listEikUrl + '/' + eikenLevelId;
				})
			});
		}
	},
	showHideSection : function () {
		if ($('input:radio:checked[disabled!=disabled]').val() == 1) {
			$('.request-free-at-first-time').removeClass('hide');
			
			$('.first-time-writing select').attr('disabled', true);
			$('.first-time-writing select').val('');
			$('.first-time-writing .required').addClass('hide');
			
			$('.second-time-interview select').attr('disabled', false);
			$('.second-time-interview .required').removeClass('hide');
		}
		else {
			$('.request-free-at-first-time').addClass('hide');
			$('.first-time-writing select').attr('disabled', false);
			$('.first-time-writing .required').removeClass('hide');
			
			$('.second-time-interview select').attr('disabled', true);
			$('.second-time-interview select').val('');
			$('.second-time-interview .required').addClass('hide');
		}
	}, 
	loadMainHallAddress: function (cityId, desinationList) {
		eikenPupil.ajaxHTML (eikenPupil.baseUrl + eikenPupil.loadMainHallUrl, 
				{cityId:cityId, eikenLevelId: $('#eikenLevelId').val(), isFirstTime: desinationList == 'mainHallAddressId1'? 1:0}, 
				function (result) {
					$('#' + desinationList).html(result);
				});
	}

}
$(document).ready(function () {
	$('.btn.btn-higher.width68').focus();
	if ($('form#form-appeik-level').length) {
		eikenPupil.showHideSection();		
		$('input:radio').click (function () {
			eikenPupil.showHideSection();
		});
		jQuery.validator.addMethod("digit7", function(value, element) {
	 	  if($.trim(value).length == 7 || $.trim(value).length == 0){
	 		  return true;
	 	  }
	 	  return false;
	 	}, '7'+eikAppLevelMess.MSG70);
		jQuery.validator.addMethod("digit4", function(value, element) {
		 	  if($.trim(value).length == 4 || $.trim(value).length == 0){
		 		  return true;
		 	  }
		 	  return false;
		 	}, '4'+eikAppLevelMess.MSG70);
		// Add custom rules for validate half-width font
		jQuery.validator.addMethod("halfwidth", function(value, element) {
			value = $.trim(value);
			if (value.length == 0)
				return true;
			var numberOfHalfWidth = value.match(/(?:\xEF\xBD[\xA1-\xBF]|\xEF\xBE[\x80-\x9F])|[\x20-\x7E]/g);
			return numberOfHalfWidth == null? false : numberOfHalfWidth.length == value.length;
	 	}, 
	 	eikAppLevelMess.HalfWidthFont);
		jQuery.validator.addMethod("number", function(value, element) {
			return (this.optional(element) || /^[0-9]/.test($.trim(value))) && value >= 0;
	 	}, eikAppLevelMess.MSG34);
		// Validate
		var checkValidate = $("#form-appeik-level").validate({
			onfocusout: false,
			onkeyup: false,
			onclick: false,
			 rules: {
				 cityId1 : {
					 required: true
				 },
				 mainHallAddressId1 : {
					 required: true
				  },
				 cityId2 : {
					 required: true
				 },
				 mainHallAddressId2: {
					 required: true
				 },
				 areaNumber1 : {
					 halfwidth: true,
					 number: true,
					 digit4: true
				 },
				 areaPersonal1 : {
					 halfwidth: true,
					 number: true,
					 digit7: true
				 }
			 },
			 errorPlacement: function(error, element){
			 },
			 showErrors: function(errorMap, errorList) {
				this.defaultShowErrors();
				ERROR_MESSAGE.show(errorList, function(){}, 'inline');
			 },  
			 ignore: ".hide select, .hide input, select[disabled=disabled]",
			 submitHandler: function(form) {
				ERROR_MESSAGE.clear();
				 // check valid eikenLevel
				if ($('#validEikenLevel').length && $('#validEikenLevel').val() == '') {
					ERROR_MESSAGE.show(eikAppLevelMess.InvalidKyu, function() {
					});
				}
				else {
					form.submit();
				}
			 }
			
		});
	}
	
	COMMON.showCrossEditMessage('#new-app-eik-level');
	
});