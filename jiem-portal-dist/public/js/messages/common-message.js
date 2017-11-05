/**
 *Show pupup error message
 **/
$.fn.bootstrapTooltip = $.fn.tooltip;

var ERROR_MESSAGE = {
	show: function(msg, fncCallBack, errorType ,changeFocusField , focusField){
		if(typeof errorType == 'undefined') {
			ERROR_MESSAGE.popupMessage(msg, fncCallBack);
			return;
		}
		ERROR_MESSAGE.inlineMessage(msg, fncCallBack, errorType,changeFocusField , focusField);
	},
	/**
	 * example:
	 *  ERROR_MESSAGE.show([{id:'xxx', message: 'abc'},{id: 'abcdef', message: 'abcdef'}]);
	 *  ERROR_MESSAGE.show('popup message'); 
	 * @param msg string or array object
	 * @param fncCallBack
	 */
	popupMessage: function(msg, fncCallBack, title,btnLabel,btnClass){
            if(!btnLabel)btnLabel='OK';
            if(!btnClass)btnClass='btn-red';
		if(typeof msg != 'undefined' && typeof msg != 'string') {
			messages = '<ul>';
			$.each( msg, function( key, value ) {
				  messages += '<li>' + value.message + '</li>';
			});
			messages += '</ul>';
			msg = messages;
		}
		var 	errorPopup = '<div class="modal fade" id="errorPopupModal" data-backdrop="static" tabindex="-1" role="dialog"';
				errorPopup += 'aria-labelledby="mySmallModalLabel" aria-hidden="true">';
					errorPopup += '<div class="modal-dialog modal-md common-dialog">';
						errorPopup += '<div class="modal-content">';
						if(typeof title != 'undefined'){
							errorPopup += '<div class="modal-header">';
								errorPopup += '<p class="modal-title">' + title + '</p>';
							errorPopup += '</div>';
						}
							
							errorPopup += '<div class="modal-body">';
								errorPopup += '<p>' + msg + '</p>';
							errorPopup += '</div>';
							errorPopup += '<div class="modal-footer">';
									errorPopup += '<button class="btn btn-large-180 '+btnClass+'" id="btnOkModal" >';
									errorPopup += btnLabel;
								errorPopup += '</button>';
							errorPopup += '</div>';
						errorPopup += '</div>';
					errorPopup += '</div>';
			errorPopup += '</div>';
		if($('#errorPopupModal').length){
			$('#errorPopupModal').remove()
		}
		$('body').append(errorPopup);
		$('#errorPopupModal').modal('show');
		$('#btnOkModal').focus();
		$('#btnOkModal').bind('click', function(){
			if(typeof fncCallBack === 'function'){
				fncCallBack();
			}
			$('#errorPopupModal').modal('hide');
		});
		
		$(document).ready(function(){
			setTimeout($.proxy(function() {
				$('#errorPopupModal button:first').focus();
			}, this), 500);
		});

        $(document).ready(function () {
            setTimeout($.proxy(function () {
                $('#errorPopupModal button:first').focus();
            }, this), 500);
        });

    },
    /**
     * Display Inline error message Top-left
     * @param msg
     * @param fncCallBack
     * @param errorType in ['success', 'info', 'warning', 'danger']
     */
    inlineMessage: function (msg, fncCallBack, errorType, changeFocusField, focusField) {

        var errorMsg = '';
        $errorFlag = false;
        var messages = '<ul><li>' + msg + '</li></ul>';
        if (typeof msg != 'undefined' && typeof msg != 'string') {
            messages = '<ul>';
            var id = '';
            $.each(msg, function (key, value) {
                $errorFlag = true;
                if (typeof value.element != 'undefined') {
                    if (typeof value.element.id != 'undefined') {
                        id = value.element.id;
                    }
                } else {
                    id = value.id;
                }
                
                if (typeof value.message != 'undefined') {
                    messages += '<li><a onclick="forcusField(this)" href="#' + id + '">' + value.message + '</a></li>';
                } else {
                    messages += '<li><a onclick="forcusField(this)" href="#' + id + '">' + value + '</a></li>';
                }
                if (id == focusField) {
                    id = changeFocusField;
                }
            });
            messages += '</ul>';
        } else {
            $errorFlag = true;
        }
        switch (errorType) {
            case 'success':
                errorMsg = '<div class="alert" role="alert">' + messages + '</div>';
                break;
            case 'info':
                errorMsg = '<div class="alert" role="alert">' + messages + '</div>';
                break;
            case 'warning':
                errorMsg = '<div class="alert" role="alert">' + messages + '</div>';
                break;
            case 'danger':
                errorMsg = '<div class="alert" role="alert">' + messages + '</div>';
                break;
            case 'inline':
                errorMsg = '<div class="alert" role="alert">' + messages + '</div>';
                break;
            default:
                errorMsg = '<div class="alert" role="alert">' + messages + '</div>';
                break;
        }
        if ($errorFlag) {
            $('.jiem-error').text('');
            $('.jiem-error').css('display', 'block').append(errorMsg);
            var scroll_pos = (0);
            $('html, body').animate({scrollTop: (scroll_pos)}, '2000');
        }
    },
    clear: function () {
        $('.jiem-error').css('display', 'none')
        $('.jiem-error').text('');
    }
};

var CONFIRM_MESSAGE = {
    show: function (msg, fncOKCallBack, fncKOCallBack, title, nameOK, nameCancel, idMoDal) {
        if (typeof title === 'undefined' || title === null)
            title = "確認";
        if (typeof nameOK === 'undefined' || nameOK === null)
            nameOK = "OK";
        if (typeof nameCancel === 'undefined' || nameCancel === null)
            nameCancel = "キャンセル";
        if (typeof idMoDal === 'undefined' || idMoDal === null)
            idMoDal = "confirmPopupModal";
        
        var idClass ='';
        if(idMoDal !== "confirmPopupModal"){
            idClass = '-' + idMoDal;
        }
        var confirmPopup = '<div class="modal fade" data-backdrop="static" id="' + idMoDal + '" tabindex="-1" role="dialog"';
        confirmPopup += 'aria-labelledby="mySmallModalLabel" aria-hidden="true">';
        confirmPopup += '<div class="modal-dialog modal-md common-dialog">';
        confirmPopup += '<div class="modal-content">';
        confirmPopup += '<div class="modal-header">';
        confirmPopup += '<p class="modal-title">';
        confirmPopup += title;
        confirmPopup += '</p>';
        confirmPopup += '</div>';
        confirmPopup += '<div class="modal-body">';
        confirmPopup += '<p>' + msg + '</p>';
        confirmPopup += '</div>';
        confirmPopup += '<div class="modal-footer">';
        //confirmPopup += '<button class="btn btn-large-180" id="btnCancelModel">キャンセル</button>';
        confirmPopup += '<button class="btn btn-large-180" id="btnCancelConfirm' + idClass + '">';
        confirmPopup += nameCancel;
        confirmPopup += '</button>';
        confirmPopup += '<button class="btn btn-large-180 btn-red" id="btnAgreeConfirm' + idClass + '" >';
        confirmPopup += nameOK;
        confirmPopup += '</button>';
        confirmPopup += '</div>';
        confirmPopup += '</div>';
        confirmPopup += '</div>';
        confirmPopup += '</div>';
        if ($('#' + idMoDal).length) {
            $('#' + idMoDal).remove();
        }
        $('body').append(confirmPopup);
        $('#' + idMoDal).modal('show');
        $('#btnAgreeConfirm' + idClass).bind('click', function () {
            if (typeof fncOKCallBack === 'function') {
                fncOKCallBack();
            }
            $('#' + idMoDal).modal('hide');
        });
        $('#btnCancelConfirm' + idClass).bind('click', function () {
            if (typeof fncKOCallBack === 'function') {
                fncKOCallBack();
            }
            $('#' + idMoDal).modal('hide');
        });
        $(document).ready(function () {
            setTimeout($.proxy(function () {
                $('#' + idMoDal + ' button:first').focus();
            }, this), 500);
        });
    }
};



var ALERT_MESSAGE = {
		show: function(msg, fncOKCallBack, fncKOCallBack,title,icon){
			if(typeof title=== 'undefined'||title===null)title="確認";
			if(typeof icon=== 'undefined'||icon===null){icon="";}else{ icon='<i class="'+icon+'"></i>' }
			var 	confirmPopup = '<div class="modal fade"  data-backdrop="static"  id="confirmPopupModal" tabindex="-1" role="dialog"';
					confirmPopup += 'aria-labelledby="mySmallModalLabel" aria-hidden="true">';
						confirmPopup += '<div class="modal-dialog modal-md common-dialog">';
							confirmPopup += '<div class="modal-content">';
								confirmPopup += '<div class="modal-header">';
									confirmPopup += '<p class="modal-title">'+title+'</p>';
								confirmPopup += '</div>';
								confirmPopup += '<div class="modal-body">';
									confirmPopup += '<p>' + msg + '</p>';
								confirmPopup += '</div>';
								confirmPopup += '<div class="modal-footer">';
									//confirmPopup += '<button class="btn btn-large-180" id="btnCancelModel">キャンセル</button>';
										//confirmPopup += '<button class="btn btn-large-180" id="btnCancelConfirm">キャンセル</button>';
										confirmPopup += '<button class="btn btn-large-180 btn-red" id="btnAgreeConfirm" >'+icon;
										confirmPopup += 'OK';
									confirmPopup += '</button>';
								confirmPopup += '</div>';
							confirmPopup += '</div>';
						confirmPopup += '</div>';
				confirmPopup += '</div>';
			if($('#confirmPopupModal').length){
				$('#confirmPopupModal').remove()
			}
			$('body').append(confirmPopup);
			$('#confirmPopupModal').modal('show');
			$('#btnAgreeConfirm').bind('click', function(){
				if(typeof fncOKCallBack === 'function'){
					fncOKCallBack();
				}
				$('#confirmPopupModal').modal('hide');
			});
			$('#btnCancelConfirm').bind('click', function(){
				if(typeof fncKOCallBack === 'function'){
					fncKOCallBack();
				}
				$('#confirmPopupModal').modal('hide');
			});
			$(document).ready(function(){
				setTimeout($.proxy(function() {
					$('#confirmPopupModal button:first').focus();
				}, this), 500);
			});
		}
};
function forcusField(linkObj) {
    var objId = $(linkObj).attr('href');
    setTimeout($.proxy(function () {
        $(objId).focus();
    }, this), 500);
    $(objId).focus();
}
var MENU_THREEBUTTON_MESSAGE = {
		show: function(hasInvitationSetting) {
			var title="確認";
			var nameOK="OK（設定済）";
			var nameBtn1 = "これから設定する";
			var nameCancel="キャンセル";
			var confirmPopup = '<div class="modal fade" data-backdrop="static" id="confirmPopupModal" tabindex="-1" role="dialog"';
					confirmPopup += 'aria-labelledby="mySmallModalLabel" aria-hidden="true">';
						confirmPopup += '<div class="modal-dialog modal-md common-dialog">';
							confirmPopup += '<div class="modal-content">';
								confirmPopup += '<div class="modal-header">';
									confirmPopup += '<p class="modal-title">';
									confirmPopup +=title;
									confirmPopup +='</p>';
								confirmPopup += '</div>';
								confirmPopup += '<div class="modal-body">';
									confirmPopup += '<p>マスタ（学年、クラス、生徒情報など）は設定されていますか。</p>';
								confirmPopup += '</div>';
								confirmPopup += '<div class="modal-footer">';
									//confirmPopup += '<button class="btn btn-large-180" id="btnCancelModel">キャンセル</button>';
										confirmPopup += '<button class="btn btn-large" id="btnCancelConfirm" style="float: left;">';
										confirmPopup +=nameCancel;
										confirmPopup +='</button>';
										confirmPopup += '<button class="btn btn-large btn-primary" id="btn1Confirm" >';
										confirmPopup += nameBtn1;
									confirmPopup += '</button>';
										confirmPopup += '<button class="btn btn-large btn-red" id="btnAgreeConfirm" >';
										confirmPopup += nameOK;
									confirmPopup += '</button>';
								confirmPopup += '</div>';
							confirmPopup += '</div>';
						confirmPopup += '</div>';
				confirmPopup += '</div>';
			if($('#confirmPopupModal').length){
				$('#confirmPopupModal').remove()
			}
			$('body').append(confirmPopup);
			$('#confirmPopupModal').modal('show');
			$('#btnAgreeConfirm').bind('click', function(){
				if (hasInvitationSetting == 1)
					window.location.href = '/invitation/setting/index';
				else
					window.location.href = '/invitation/setting/add';
				$('#confirmPopupModal').modal('hide');
			});
			$('#btnCancelConfirm').bind('click', function(){
				$('#confirmPopupModal').modal('hide');
			});
			$('#btn1Confirm').bind('click', function(){
				window.location.href = '/org/orgschoolyear/add';
				$('#confirmPopupModal').modal('hide');
			});
			 $(document).ready(function () {
	            setTimeout($.proxy(function () {
	                $('#confirmPopupModal button:first').focus();
	            }, this), 500);
	        });
		}
};