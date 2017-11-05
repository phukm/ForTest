var EIKEN_EXAM_HISTORY_LIST = {
    urlLoadSchoolYear: COMMON.baseUrl + 'history/eiken/load-school-year',
    urlLoadClass: COMMON.baseUrl + 'history/eiken/load-class',
    urlViewDetailEikenHistory: COMMON.baseUrl + 'history/eiken/get-data-eiken',
    urlViewDetailIBAHistory: COMMON.baseUrl + 'history/iba/get-data-iba',
    ajaxGetListClass: COMMON.baseUrl + 'history/eiken/ajax-get-list-class',
    init : function() {
        $('#year').focus();
        $('#btnClear').click(function() {
        	$("#year option[value='" + $("#currentYear").text() + "']").prop('selected', true);
            $("#orgSchoolYear").val('');
            $("#classj").val('');
            $("#name").val('');
            $('#historylist').submit();
        });
        $('#btnSearch').click(function() {
            $('#historylist').submit();
        });       // selected only value
        $(".box-body #classj option").each(function(){
                if($(this).val() === valueClassj)
                $(this).attr('selected','selected');
        });
        $(".box-body #orgSchoolYear option").each(function(){
                if($(this).val() === valueOrgSchoolYear)
                $(this).attr('selected','selected');
        });
    },
    
    loadOrgSchoolYear : function() {
		var request_year = $("#year").val();
		var request_schoolyear = $("#orgSchoolYear").val();
		$.ajax({
			type : 'POST',
			url : EIKEN_EXAM_HISTORY_LIST.ajaxGetListClass,
			data : {
				year : request_year,
				schoolyear : request_schoolyear
			},
			dataType : "json",
			success : function(data) {
				$("#classj").html("");
				$("#classj").prepend(EIKEN_EXAM_HISTORY_LIST.loadclass(data.classj));
				$("#classj").prepend('<option value selected ></option>');
			},
			error : function() {
			}
		});
	},
	loadYear : function() {
		var request_year = $("#year").val();
		var request_schoolyear = $("#orgSchoolYear").val();
		if (request_schoolyear != "" || request_schoolyear != null) {
			$.ajax({
				type : 'POST',
				url : EIKEN_EXAM_HISTORY_LIST.ajaxGetListClass,
				data : {
					year : request_year,
					schoolyear : request_schoolyear
				},
				dataType : "json",
				success : function(data) {
					$("#classj").html("");
					$("#classj").prepend(EIKEN_EXAM_HISTORY_LIST.loadclass(data.classj));
					$("#classj").prepend('<option value selected ></option>');
				},
				error : function() {
				}
			});
		}
	},
	loadclass : function(data) {
		var $html = '';
		if (data) {
			$.each(data, function(index, value) {
				$html += '<option value="' + value.className + '">' + EIKEN_EXAM_HISTORY_LIST.htmlencode(value.className)
						+ '</option>';
			});
		}
		return $html;
	},
	htmlencode : function(str) {
		return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;')
				.replace(/>/g, '&gt;').replace(/"/g, '&quot;')
	},
    setClassName : function() {
    	$("#className").val($('#searchClass option:selected').text());
    },
    viewEikenHistory : function(id, pupilId, schoolYear, className, pupilNumber, name) {
    	var params = "?type=eiken"+"&id="+id+"&pupilId="+pupilId+"&schoolYear="+schoolYear+"&className="+className+
        "&pupilNumber="+pupilNumber+"&name="+name;
        window.location = EIKEN_EXAM_HISTORY_LIST.urlViewDetailEikenHistory + params;
    },
    viewIBAHistory : function(id, pupilId, schoolYear, className, pupilNumber, name) {
        var params = "?type=iba"+"&id="+id+"&pupilId="+pupilId+"&schoolYear="+schoolYear+"&className="+className+
        "&pupilNumber="+pupilNumber+"&name="+name;
        window.location = EIKEN_EXAM_HISTORY_LIST.urlViewDetailIBAHistory + params;
    }
}