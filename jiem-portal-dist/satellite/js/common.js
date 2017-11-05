//todo debug log error.
var logevent;
var logjqxhr;
var logsetting;
$(document).ajaxError(function (event, jqxhr, setting) {
    logevent = event;
    logjqxhr = jqxhr;
    logsetting = setting;
    var baseUrl = window.location.protocol + "//" + window.location.host + "/";
    if (jqxhr.status == 406) {
        window.location.href = baseUrl + 'login';
    } else {
        ERROR_MESSAGE.show('システムエラーが発生しました。システム管理者に連絡してください。', function () {
            window.location.href = baseUrl;
        });
    }
});
var COMMON = {
	baseUrl : window.location.protocol + "//" + window.location.host + "/",
	checkValidApplyEikenUrl : 'eiken/eikenorg/check-valid-apply-eiken',
	applyEikenUrl: 'eiken/eikenorg/index',
	policyApplyEikenUrl: 'eiken/eikenorg/policy',
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
	checkValidApplyEikenDate: function() {
		$.ajax({
			type : 'POST',
			url : COMMON.baseUrl + COMMON.checkValidApplyEikenUrl,
			dataType : 'json',
			data : {}, 
			success : function(result) {
				//$('#orgName').text(result.eikenOrgName);
				if(result.isValid) {
					//goto Apply Eiken Org page
					//You are already to register to the Eiken exam. Please check the list of exam to view or update the application information
					if(result.isRegistered) {
						ERROR_MESSAGE.show('英検検定に申込完了しました。閲覧・更新するために英検検定一覧を確認してください。', function(){
							window.location =  COMMON.baseUrl + COMMON.applyEikenUrl;
						});
						//window.location =  COMMON.baseUrl + COMMON.applyEikenUrl;
					}else {
						window.location =  COMMON.baseUrl + COMMON.policyApplyEikenUrl;
					}
				}else {
					ERROR_MESSAGE.show('The application time is not available at the moment. Please check the list of exam in this year and find out the next time to apply to Eiken exam.', function(){
						window.location =  COMMON.baseUrl + COMMON.applyEikenUrl;
					});
				}
			},
			error : function() {
				ERROR_MESSAGE.show('System error, please contact support team!!!');
			}
		});
	}
};
$( document ).ajaxStart(function() {
	$('#loadingModal').modal('show');
});
$( document ).ajaxSuccess(function() {
	$('#loadingModal').modal('hide');
});
COMMON.zipcode = {
    load:function(zipcode,callback){
        if(typeof callback !== 'function'){
            callback = function(){};
        }
        $.ajax({
            type : 'POST',
            url : COMMON.baseUrl + 'eiken/zipcode',
            dataType : "json",
            contentType: "application/json",
            data : JSON.stringify({zipcode:zipcode}),
            success :callback
        });
    },
    autoFill:function(zipcode,idSelectCity, idDicstrict, idAddress, callback){
        if(typeof callback !== 'function'){
            callback = function(){};
        }
        COMMON.zipcode.load(zipcode,function(response){
            if(response.success){                  
                if(response.data.length >1){                    
                    COMMON.zipcode.showOption(response.data,idSelectCity, idDicstrict, idAddress);
                }else{
                    var resData = response.data[0];
                    var select = $('#'+idSelectCity);
                    var optionVal = select.find('option').filter(function(){
                        return $(this).text() === resData.cityName;
                    }).val();
                    select.val(optionVal);
                    $('#'+idAddress).val(resData.address);
                    $('#'+idDicstrict).val(resData.districtName);
                }
            }else{
                ERROR_MESSAGE.show(response.messages.join('\n'));
            }
            callback(response);
        });
    },
    showOption : function(listData,idSelectCity, idDicstrict, idAddress){
        var ul = document.createElement('ul');
        ul.className = 'selectZipcode';
        
        for(var i in listData){
            var data = listData[i];
            var li = '<li '
                    +'data-cityname="'+ data.cityName +'" '
                    +'data-address="'+ data.address +'" '
                    +'data-dicstrict="'+ data.districtName +'" '
                    +'data-idselectcity="'+idSelectCity+'" '
                    +'data-iddicstrict="'+idDicstrict+'" '
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
        var address = $(el).attr('data-address');
        var dicstrict = $(el).attr('data-dicstrict');
        var idAddress = $(el).attr('data-idaddress');
        var idDicstrict = $(el).attr('data-iddicstrict');
        
        var optionVal = select.find('option').filter(function(){
            return $(this).text() === cityName;
        }).val();
        select.val(optionVal);
        
        $('#'+idAddress).val(address);
        $('#'+idDicstrict).val(dicstrict);
        $('#errorPopupModal').modal('hide');
    }
};