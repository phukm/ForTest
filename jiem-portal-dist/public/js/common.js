var COMMON = {
	baseUrl : window.location.protocol + "//" + window.location.host + "/",
	checkValidApplyEikenUrl : 'eiken/eikenorg/check-valid-apply-eiken',
	applyEikenUrl: 'eiken/eikenorg/index',
	policyApplyEikenUrl: 'eiken/eikenorg/policy',
	init: function() {
		if($('#search-box').length) {
			$('#search-box').on('hidden.bs.collapse', function (e) { //event when #search-box is collapsed
				$('.search-header').find('i').removeClass('search-chevron-down');
				$('.search-header').find('i').addClass('search-chevron-right');
				$('#searchVisible').val(0);
			})
			$('#search-box').on('shown.bs.collapse', function (e) {
				$('.search-header').find('i').removeClass('search-chevron-right');
				$('.search-header').find('i').addClass('search-chevron-down');
				$('#searchVisible').val(1);
			})
		}
		if($('.header-sort').length) {
			$('.header-sort').find('th').click(function(){
				if($(this).attr('data-sort')){
					$('#sortKey').val($(this).attr('data-sort'));
					var currentOrder = $('#sortOrder').val();
					if($('#sortOrder').val() == 'asc') {
						$('#sortOrder').val('desc');
					} else {
						$('#sortOrder').val('asc');
					}
					if($('#search-box').is(':hidden')){
						$('#searchVisible').val(0);
					} else {
						$('#searchVisible').val(1);
					}
					$('form').submit();
				}
			});
		}
                if(window.location.href.indexOf('/login') < 0 && window.location.href.indexOf('/user') < 0){
                    COMMON.showPopupComfirmAutoMappingEiken();
                    COMMON.showPopupComfirmAutoMappingIBA();
                };
	},
        showPopupComfirmAutoMappingEiken: function(){
            var jsonurl = location.protocol + '//' + location.hostname + '/history/eiken/show-popup-comfirm-auto-mapping';
            $.ajax({
                type: 'GET',
                url: jsonurl,
                data: {},
                dataType: 'json',
                success:function(data){
                        if(data.success)
                        {
                            if(data.data.year){
                                CONFIRM_MESSAGE.show(data.messages.msg, function () {
                                    window.open(location.protocol+ '//' + location.hostname + '/history/eiken/eiken-mapping-result/year/'+data.data.year+'/kai/'+data.data.kai,'_blank');  
                                }, function(){
                                     false;
                                },'','<i class="arrow-circle-right-w"></i>次へ');
                            }
                            else{
                                ERROR_MESSAGE.show(data.messages.msg, function () {false;});
                            }
                        }else{
                            setTimeout(COMMON.showPopupComfirmAutoMappingEiken,30000);
                        }
                    },
                error:function(){  
                },
                global: false
            });
        },
         showPopupComfirmAutoMappingIBA: function(){
            var jsonurl = location.protocol + '//' + location.hostname + '/history/iba/show-popup-comfirm-auto-mapping';
            $.ajax({
                type: 'GET',
                url: jsonurl,
                data: {},
                dataType: 'json',
                success:function(data){
                        if(data.success)
                        {
                            if(data.data.year){
                                CONFIRM_MESSAGE.show(data.messages.msg, function () {
                                    window.open(location.protocol+ '//' + location.hostname + '/history/eiken/exam-result');
                                }, function(){
                                    false;
                                },'','結果を見る','後で見る');
                            }
                            else{
                                ERROR_MESSAGE.show(data.messages.msg, function () {false;});
                            }
                        }else{
                            setTimeout(COMMON.showPopupComfirmAutoMappingIBA,30000);
                        }
                    },
                error:function(){  
                },
                global: false
            });
        },
	showCrossEditMessage: function(buttonOK) {
		if(typeof jsMessages != 'undefined' && jsMessages.conflictWarning && jsMessages.conflictType == 'edit')
			CONFIRM_MESSAGE.show(jsMessages.conflictWarning, function () {
				$(buttonOK).click();
			}, function(){
				window.location.reload();
			});
		if(typeof jsMessages != 'undefined' && jsMessages.conflictWarning && jsMessages.conflictType == 'delete')
			ERROR_MESSAGE.show(jsMessages.conflictWarning);
	},
	checkValidApplyEikenDate: function(isPersonal) {
                if(typeof isPersonal == 'undefined'){
                    isPersonal = 0;
                }
		$.ajax({
			type : 'POST',
			url : COMMON.baseUrl + COMMON.checkValidApplyEikenUrl,
			dataType : 'json',
			data : {isPersonal:isPersonal}, 
			success : function(result) {
				//$('#orgName').text(result.eikenOrgName);
				if(result.isValid) {
					//goto Apply Eiken Org page
					//You are already to register to the Eiken exam. Please check the list of exam to view or update the application information
					if(result.isRegistered) {
						window.location =  COMMON.baseUrl + COMMON.applyEikenUrl;
						//window.location =  COMMON.baseUrl + COMMON.applyEikenUrl;
					}else {
						window.location =  COMMON.baseUrl + COMMON.policyApplyEikenUrl;
					}
				}else {
					window.location =  COMMON.baseUrl + COMMON.applyEikenUrl;
				}
			},
			error : function() {
				ERROR_MESSAGE.show('システムエラーが発生しました。システム管理者に連絡してください。');
			}
		});
	},
    setTabIndexIdentify: function (selector) {
        //for each auto set tabindex by selector
        //eg: "select,input,.btn"
        var count = 0;
        $(selector).each(function () {
            $(this).attr('tabindex', count);
            count++;
        });
    },
    numberStartWithZero: function (val) {
        //if input is 1 -> 9, add zero first
        if (val) {
            var number = parseInt(val, 10);
            if (number < 10)
            {
                return '0' + number;
            }
            else {
                return number;
            }
            return '';
        }
    },
    escapeHtml: function(str){
    	var d = $('<div></div>');
    	d.text(str);
    	return d.html();
    },
    initLayout: function () {
    	$('#btnCancelModel').click(function () {
            $("#btnlogout").click();
        });
        $('#btnLogoutModal').click(function () {
            window.location.href = '/logout';
        });
        $('#btnUserProfile').click(function () {
            window.location.href = '/profile';
        });
        var path = window.location.pathname;
        //Module History - clear sessionStorage.
        if (path.indexOf('/history/eiken/') == -1 || path.indexOf('history/eiken/exam-result') != -1) {
            sessionStorage.removeItem('sessionStorageEikenResult');
        }
        if (path.indexOf('/history/iba/') == -1 || path.indexOf('history/eiken/exam-result') != -1) {
            sessionStorage.removeItem('sessionStorageIbaResult');
        }
        if (path.indexOf('/org/orgschoolyear') != -1 || path.indexOf('/org/class') != -1 || path.indexOf('/pupil/pupil') != -1 || path.indexOf('/pupil/import-pupil') != -1
        		|| path.indexOf('/org/importmasterdata/index') != -1 || path.indexOf('/org/org/undetermined') != -1 || path.indexOf('/org/org/index') != -1 || path.indexOf('/org/user') != -1 || path.indexOf('/invitation/standard') != -1 || path.indexOf('org/org/show') != -1 || path.indexOf('org/org/index') != -1 || path.indexOf('logs/apply-eiken/index') != -1 || path.indexOf('logs/activity/index') != -1) {
            $('.menu-large').removeClass('active-page');  
            $('.n7').addClass('active-page');
        }
        else if (path.indexOf('/eiken/eikenorg/index') != -1 || path.indexOf('/eiken/eikenorg/applyeikendetails') != -1 || path.indexOf('/history/iba/pupil-achievement') != -1
        		|| path.indexOf('/history/eiken/pupil-achievement') != -1 || path.indexOf('/history/iba/iba-history-pupil') != -1
        		|| (path.indexOf('/history/iba') != -1 && path.indexOf('/history/iba/empty-name-kana') == -1)) {
            $('.menu-large').removeClass('active-page');
            $('.n4').addClass('active-page');
        } else if (path.indexOf('/invitation/setting') != -1 || path.indexOf('/invitation/generate') != -1 || path.indexOf('/invitation/recommended') != -1
                || path.indexOf('/eiken/eikenorg/navigator') != -1 || path.indexOf('/eiken/eikenorg/policy') != -1 || path.indexOf('/eiken/payment') != -1
                || path.indexOf('/eiken/eikenorg') != -1 || path.indexOf('/eiken/eikenpupil') != -1
                || path.indexOf('/eiken/eikenorg/confirmation') != -1 || path.indexOf('/eiken/eikenid') != -1 
                || path.indexOf('/iba/iba/policy') != -1 || path.indexOf('/iba/iba/show') != -1 || path.indexOf('/iba/iba/add') != -1
                || path.indexOf('/eiken/exemption/list') != -1) {
            $('.menu-large').removeClass('active-page');
            $('.n3').addClass('active-page');
        } else if (path.indexOf('/history/eiken/exam-result') != -1 || path.indexOf('/history/eiken/confirm-exam-result') != -1
                 || path.indexOf('/history/eiken/eiken-history-pupil') != -1 || path.indexOf('/history/eiken/personal-achievement') != -1
                 || path.indexOf('/org/org/paymentRefundStatus') != -1
                 || path.indexOf('/history/eiken/exam-history-list') != -1
                 || path.indexOf('/history/eiken/eiken-mapping-result') != -1
                 || path.indexOf('/history/eiken/eiken-confirm-result') != -1
		) {
            $('.menu-large').removeClass('active-page');
            $('.n4').addClass('active-page');
        } else if ('/' == path) {
            $('.menu-large').removeClass('active-page');
            $('.n1').addClass('active-page');
        } else if (path.indexOf('/goalsetting/studygear') != -1) {
            $('.menu-large').removeClass('active-page');
            $('.n5').addClass('active-page');
        } else if (path.indexOf('/homepage/homepage/detailc') != -1 || path.indexOf('/homepage/homepage/detailb1') != -1
                ||path.indexOf('/homepage/homepage/detailb2') != -1 || path.indexOf('/homepage/homepage/achieve-goal') != -1
                ||path.indexOf('report/report/csescoretotal') != -1) {
            $('.menu-large').removeClass('active-page');
            $('.n6').addClass('active-page');
        } else if (path.indexOf('/goalsetting/graduationgoalsetting') != -1 || path.indexOf('/goalsetting/eikenscheduleinquiry') != -1) {
            $('.menu-large').removeClass('active-page');
            $('.n2').addClass('active-page');
        }
        $(function() {
            $("ul.dropdown-menu").on("click", "[data-keepOpenOnClick]", function(e) {
                e.stopPropagation();
            });

        });
        $('.dropdown-toggle').click(function(e) {
  		  e.preventDefault();
  		  setTimeout($.proxy(function() {
  		    if ('ontouchstart' in document.documentElement) {
  		      $(this).siblings('.dropdown-backdrop').off().remove();
  		    }
  		  }, this), 0);
  		});
        $(function () {
    	   $('[data-toggle="tooltip"]').bootstrapTooltip();
    	   $('.bootstrap-tooltip').bootstrapTooltip({title:"英検および英検IBAの申込を行います。", trigger:"hover"});
    	});
    }
    ,getCurrentYear:function()
    {
     var curDate = new Date();
     var curMonth=curDate.getMonth()+1;
     if(curMonth>=4)
     {
         return curDate.getFullYear();
     }
     else
     {
         return curDate.getFullYear()-1;
     }
    }
};

COMMON.zipcode = {
    load:function(zipcode,callback){
        if(typeof callback !== 'function'){
            callback = function(){};
        }
        $.ajax({
            type: 'POST',
            url: COMMON.baseUrl + 'zipcode',
            dataType: 'json',
            data: {zipcode: zipcode},
            success: callback,
            error: function () {
                ERROR_MESSAGE.show('システムエラーが発生しました。システム管理者に連絡してください。');
            }

        });
    },
    autoFill:function(zipcode,idSelectCity,idAddress,callback){
        if(typeof callback !== 'function'){
            callback = function(){};
        }
        COMMON.zipcode.load(zipcode,function(response){
            if(response.success){
                
                if(response.data.length >1){
                    COMMON.zipcode.showOption(response.data,idSelectCity,idAddress);
                }else{
                    var resData = response.data[0];
                    var select = $('#'+idSelectCity);
                    var optionVal = select.find('option').filter(function(){
                        return $(this).text() === resData.cityName;
                    }).val();
                    select.val(optionVal);
                    $('#'+idAddress).val(resData.districtName + resData.address);
                }
            }else{
                ERROR_MESSAGE.show(response.messages.join('\n'));
            }
            callback(response);
        });
    },
    showOption : function(listData,idSelectCity,idAddress){
        var ul = document.createElement('ul');
        ul.className = 'selectZipcode';
        
        for(var i in listData){
            var data = listData[i];
            var li = '<li '
                    +'data-cityname="'+ data.cityName +'" '
                    +'data-idselectcity="'+idSelectCity+'" '
                    +'data-idaddress="'+idAddress+'" '
                    +'onClick="COMMON.zipcode.fillDataByOption(this)" '
                    +'>'
                    +data.districtName + data.address
                    +'</li>';
            ul.innerHTML +=li;
        }
        ERROR_MESSAGE.popupMessage(ul.outerHTML,function(){},'郵便番号','キャンセル',' ');
    },
    fillDataByOption: function(el){
        var select = $('#'+$(el).attr('data-idselectcity'));
        var cityName = $(el).attr('data-cityname');
        var idAddress = $(el).attr('data-idaddress');
        
        var optionVal = select.find('option').filter(function(){
            return $(this).text() === cityName;
        }).val();
        select.val(optionVal);
        
        $('#'+idAddress).val($(el).html());
        $('#errorPopupModal').modal('hide');
    }
};

$( document ).ajaxStart(function() {
	$('#loadingModal').modal('show');
});
$( document ).ajaxSuccess(function() {
	$('#loadingModal').modal('hide');
	$('body').removeClass('modal-open');
//	$('.modal-backdrop').remove();
});


$( document ).ajaxError(function( event, jqxhr, settings ) {
	var baseUrl = window.location.protocol + "//" + window.location.host + "/";
	if(jqxhr.status == 401){
		window.location.href = baseUrl + 'access-denied';
	}else if(jqxhr.status == 403){
		window.location.href = baseUrl + 'logout';
	}else if(jqxhr.status == 405){
		window.location.href = baseUrl + 'inactivated';
	}else if(jqxhr.status == 406){
		window.location.href = baseUrl + 'login';
	}
});
// add translate for jquery ui calendar
$(function() {
	if(typeof $.fn.datepicker !== "undefined"){
	    $.datepicker.regional['ja'] = {
	        clearText : 'クリア',
	        clearStatus : '日付をクリアします',
	        closeText : '閉じる',
	        closeStatus : '変更せずに閉じます',
	        prevText : '&#x3c;前',
	        prevStatus : '前月を表示します',
	        prevBigText : '&#x3c;&#x3c;',
	        prevBigStatus : '前年を表示します',
	        nextText : '次&#x3e;',
	        nextStatus : '翌月を表示します',
	        nextBigText : '&#x3e;&#x3e;',
	        nextBigStatus : '翌年を表示します',
	        currentText : '今日',
	        currentStatus : '今月を表示します',
	        monthNames : [ '1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月',
	                '10月', '11月', '12月' ],
	        monthNamesShort : [ '1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月',
	                '9月', '10月', '11月', '12月' ],
	        monthStatus : '表示する月を変更します',
	        yearStatus : '表示する年を変更します',
	        weekHeader : '週',
	        weekStatus : '暦週で第何週目かを表します',
	        dayNames : [ '日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日' ],
	        dayNamesShort : [ '日', '月', '火', '水', '木', '金', '土' ],
	        dayNamesMin : [ '日', '月', '火', '水', '木', '金', '土' ],
	        dayStatus : '週の始まりをDDにします',
	        dateStatus : 'Md日(D)',
	        dateFormat : 'yy/mm/dd',
	        firstDay : 0,
	        initStatus : '日付を選択します',
	        isRTL : false,
	        showMonthAfterYear : true
	    };
	    $.datepicker.setDefaults($.datepicker.regional['ja']);
	}
});

function removeCharacterExceptNumber(str) {
	return str.replace(/\D/g, '');
}

function htmlencode(str) {
	return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')
}
if (!Object.keys) {
	  Object.keys = function(obj) {
	    var keys = [];

	    for (var i in obj) {
	      if (obj.hasOwnProperty(i)) {
	        keys.push(i);
	      }
	    }

	    return keys;
	  };
	} 
	(function(fn){
		    if (!fn.map) fn.map=function(f){var r=[];for(var i=0;i<this.length;i++)r.push(f(this[i]));return r}
		    if (!fn.filter) fn.filter=function(f){var r=[];for(var i=0;i<this.length;i++)if(f(this[i]))r.push(this[i]);return r}
		})(Array.prototype); 
$(document).ready(function(){
	COMMON.init();
});
