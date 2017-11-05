var APPLYEIKEN_LOGS = {
	init : function(){
		$('form:first *:input[type!=hidden]:first').focus();
    	$('#btnSearch').click(function() {
            $('#applyeikensearch').submit();
        });
    	$('#btnClear').click(function() {
            $("#organizationNo").val('');
            $("#organizationName").val('');
            $("#action").val('');
            $("#fromDate").val('');
            $("#toDate").val('');
            $('#applyeikensearch').submit();
        });
    	
    	//validate
        jQuery.validator.addMethod("comparedate",
    		function(value, element) {
    		var date2 = $("#toDate").val();
    		date2 = Date.parse(date2);
    		var date1 = $("#fromDate").val();
    		date1 = Date.parse(date1);
    		if(value=="") return true;
    			if (date2 < date1) {
    		        return false ;
    		    }else{
    		    	return true;
    		    }
    		});
    	//  date fomat
    	jQuery.validator.addMethod("dateISO2", function(value, element) {
    		if(value.length !=0){
    			if(/^\d{4}[\/]\d{2}[\/]\d{2}$/.test(value) == false){
    				   return false;
    			}
    		}
    		var array = value.split("/");
    	    var dtDay = parseInt(array[2]);
    	    var dtMonth = parseInt(array[1]);
    	    var dtYear = parseInt(array[0]);

    		if (dtMonth < 1 || dtMonth > 12){
    	        return false;
    		}
    	    else if (dtDay < 1 || dtDay > 31) {
    	        return false;
    	    }
    	    else if ((dtMonth == 4 || dtMonth == 6 || dtMonth == 9 || dtMonth == 11) && dtDay == 31){
    	        return false;
    	    }
    	    else if (dtMonth == 2) {
    	        var isleap = (dtYear % 4 == 0 && (dtYear % 100 != 0 || dtYear % 400 == 0));
    	        if (dtDay > 29 || (dtDay == 29 && !isleap)){
    	            return false;
    	        }
    	    }
    	    return true;
    		});
    	
        var validator = $("#applyeikensearch").validate({
        	onfocusout: false,
            onkeyup: false,
            onclick : false,
            focusInvalid: false,
            rules: {
            	fromDate : {
            		dateISO2: true
            	},
            	toDate : {
            		dateISO2: true,
            		comparedate : true
            	}
            },
            messages: {
            	fromDate: {
            		dateISO2 :jsMessages.MSGdatefomat
            	},
            	toDate: {
            		dateISO2 :jsMessages.MSGdatefomat,
            		comparedate : jsMessages.MSGdatecompare
            	}
            },
            errorPlacement: function(error, element){},
        	showErrors: function(errorMap, errorList) {
        		this.defaultShowErrors();
        		ERROR_MESSAGE.show(errorList, function(){}, 'inline');
            }
        });

        $(function () {
            $('#fromDate').datepicker();
            $('#toDate').datepicker();

        });
        
        $(function(){
            $.datepicker.regional['ja'] = {
                clearText: 'クリア', clearStatus: '日付をクリアします',
                closeText: '閉じる', closeStatus: '変更せずに閉じます',
                prevText: '&#x3c;前', prevStatus: '前月を表示します',
                prevBigText: '&#x3c;&#x3c;', prevBigStatus: '前年を表示します',
                nextText: '次&#x3e;', nextStatus: '翌月を表示します',
                nextBigText: '&#x3e;&#x3e;', nextBigStatus: '翌年を表示します',
                currentText: '今日', currentStatus: '今月を表示します',
                monthNames: ['1月','2月','3月','4月','5月','6月',
                '7月','8月','9月','10月','11月','12月'],
                monthNamesShort: ['1月','2月','3月','4月','5月','6月',
                '7月','8月','9月','10月','11月','12月'],
                monthStatus: '表示する月を変更します', yearStatus: '表示する年を変更します',
                weekHeader: '週', weekStatus: '暦週で第何週目かを表します',
                dayNames: ['日曜日','月曜日','火曜日','水曜日','木曜日','金曜日','土曜日'],
                dayNamesShort: ['日','月','火','水','木','金','土'],
                dayNamesMin: ['日','月','火','水','木','金','土'],
                dayStatus: '週の始まりをDDにします', dateStatus: 'Md日(D)',
                dateFormat: 'yy/mm/dd', firstDay: 0,
                initStatus: '日付を選択します', isRTL: false,
                showMonthAfterYear: true};
            $.datepicker.setDefaults($.datepicker.regional['ja']);
    	});
	},

	showCalendar: function (args){
		$('#'+args).datepicker('show');
    },
	
	isNumber: function (evt) {
	    evt = (evt) ? evt : window.event;
	    var charCode = (evt.which) ? evt.which : evt.keyCode;
	    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
	        return false;
	    }
	    return true;
	},
	
	converttime: function (datetime){
    	var time_date = new Date(datetime).getTime();
    	return time_date;
    },
    
    toggle: function () {
        if (document.getElementById("hidethis").style.display === 'none') {
            document.getElementById("hidethis").style.display = 'table'; // set to table-row instead of an empty string
             } else {
            document.getElementById("hidethis").style.display = '';
        }
    },
}