var HISTORY_STUDY = {
	
	urlGetListClass : COMMON.baseUrl + 'goalsetting/studygear/ajaxGetListClass',
	init : function() {
		
		// VALIDATE COMPARE DATEFROM AND DATETO
		jQuery.validator.addMethod("comparedate", function(value, element) {
			var date1 = $("#fromDate").val();
			date1 = Date.parse(date1);
			var date2 = $("#toDate").val();
			date2 = Date.parse(date2);
			if (value == "")
				return true;
			if (date2 < date1) {
				return false;
			} else {
				return true;
			}
		}, '日付（から）と日付（まで）の前後関係がが正しくありません');

		// VALIDATE COMPARE INPUT DATE AND CURRENT DATE
		jQuery.validator.addMethod("comparecurrentdate", function(value,element) {
			 var today = new Date();
			 var currentDate = today.getDate();
			 var currentMonth = today.getMonth()+1; //January is 0!
				
			 var currentYear = today.getFullYear();
			 if(currentDate<10){
				 currentDate='0'+currentDate
			 }
			 if(currentMonth<10){
				 currentMonth='0'+currentMonth
			 }
			var current = currentYear+''+currentMonth+''+currentDate;
			date = element.value;
			date = removeCharacterExceptNumber(date);
			date = parseInt(date);
			if (value == "")
				return true;
			if (current <= date) {
				return false;
			} else {
				return true;
			}
		}, '日付は昨日以前を指定してください');

		// VALIDATE DATEISO
		jQuery.validator.addMethod("dateISO",function(value, element) {
			if (value.length != 0) {
				if (/^\d{4}[\/]\d{1,2}[\/]\d{1,2}$/.test(value) == false) {
					return false;
				}
			}
			var array = value.split("/");
			var dtDay = parseInt(array[2]);
			var dtMonth = parseInt(array[1]);
			var dtYear = parseInt(array[0]);

			if (dtMonth < 1 || dtMonth > 12) {
				return false;
			} else if (dtDay < 1 || dtDay > 31) {
				return false;
			} else if ((dtMonth == 4 || dtMonth == 6
					|| dtMonth == 9 || dtMonth == 11)
					&& dtDay == 31) {
				return false;
			} else if (dtMonth == 2) {
				var isleap = (dtYear % 4 == 0 && (dtYear % 100 != 0 || dtYear % 400 == 0));
				if (dtDay > 29 || (dtDay == 29 && !isleap)) {
					return false;
				}
			}
			return true;
		}, "日付の形式はYYYY/MM/DDとしてください。");

		// VALIDATE FORM
		var validator = $("#frmStudyGear").validate({
			onfocusout: false,
		    onkeyup: false,
		    onclick : false,
		    focusInvalid: false,
			rules : {
				fromDate : {
					required: true,
					dateISO : true,
					comparecurrentdate : true
				},
				toDate : {
					required: true,
					dateISO : true,
					comparecurrentdate : true,
					comparedate : true
				}
			},
			errorPlacement : function(error, element) {
			},
			showErrors : function(errorMap, errorList) {
				this.defaultShowErrors();
				ERROR_MESSAGE.show(errorList, function() {
				}, 'inline');
			}
		});

		var date = new Date();
		$('#ddlSchoolYear').focus();
		$("#fromDate").datepicker();
		$("#toDate").datepicker();

		$("#btnSearch").on('click', function() {
			$("#frmStudyGear").submit();
		});
		
		$("#ddlSchoolYear").on('change', function() {
			var schoolYear = $("#ddlSchoolYear").val();
			HISTORY_STUDY.getListClass(schoolYear);
		});
		
		//dataTables_paginate 
		var a=$('#hasRow');
	 	if(a.size()==0){
			$('#tblExam').DataTable({
				"sScrollX": "100%",
		        "sScrollXInner": "110%",
		        "bScrollCollapse": true,
		        "pagingType": "full_numbers",
		        "pageLength": 20,
		        "bFilter" : false,
		        "bLengthChange": false,
		        "bInfo":false,
		        "oLanguage": {
		        	"oPaginate": {
		                "sFirst": "<<",
		                "sPrevious": "<",
		                "sNext": ">",
		                "sLast": ">>"
		            	}
		        	},
	        	"fnDrawCallback":function(){
	            	var element=document.getElementById('tblExam_paginate');
	        	 	if ( $('#tblExam_paginate .paginate_button').size()>5) {
	        			 element.parentElement.style.display = "block";
	        	     	} else {
	        	    	 	element.parentElement.style.display = "none";
	        	     	}
	        		}
	    		});
	 	}
	 	if(/chrom(e|ium)/.test(navigator.userAgent.toLowerCase())){
	 			$('td.boder-top-none').css({'margin-top': '1px'});
	 	}
	},
	
	//GET LIST CLASS BY YEAR AND SCHOOL YEAR
	getListClass : function(schoolYear) {
		$.ajax({
			type : 'POST',
			url : HISTORY_STUDY.urlGetListClass,
			data : {
				schoolyear : schoolYear
			},
			dataType : "json",
			success : function(data) {
				var html = "<option value='' selected='selected'></option>";
				if (typeof data.classj != 'undefined') {
					$.each(data.classj, function(i, v) {
						html = html + "<option value='" + v['id'] + "'>"
								+ htmlencode(v['className']) + "</option>";
					});
				}
				$('#ddlClass').html(html);
			},
			error : function() {
			}
		});
	},
	clearForm: function() { 
	    $('#ddlSchoolYear').val('');
	    $('#ddlClass').val('');
	    $('#eikenGrade').val('');
	    $('#toDate').datepicker('setDate',  new Date(new Date().setDate(new Date().getDate()-1)));
	    $('#fromDate').datepicker('setDate',  new Date(new Date().setDate(new Date().getDate()-7)));
	    $("#frmStudyGear").submit();
	},
	showCalendar: function(name){
		$('#'+name).datepicker('show');
    }
}

