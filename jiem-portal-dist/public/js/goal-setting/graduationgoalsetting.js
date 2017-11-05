/* global ERROR_MESSAGE */
var GRADUATIONGOALSETTING = {
	clearUrl : COMMON.baseUrl + 'goalsetting/graduationgoalsetting/index',
	listSchoolYearUrl : COMMON.baseUrl
			+ 'goalsetting/graduationgoalsetting/listSchoolYear',
	graduationGoalSearchUrl : COMMON.baseUrl
			+ 'goalsetting/graduationgoalsetting/graduationgoalsearch',
	init : function() {
		var isEdit = parseInt($('#editAction').val());
		var isGoalSetting = parseInt($('#rdGoalSettingfrom').val());
		GRADUATIONGOALSETTING.loadListSchoolYear();
		//auto load Graduation time target.
		GRADUATIONGOALSETTING.searchAllAction();
		GRADUATIONGOALSETTING.loadBreadcrumb(isEdit, isGoalSetting);
		$('#GraduationGoalSearchForm .box .box-header').on('click', function() {
			$('#GraduationGoalSearchForm .box-search').toggle();
		});
		setTimeout(function() { document.getElementById('rdGoalSetting').focus(); }, 1000);
		$('form:first *:input[type!=hidden]:first').focus();
		GRADUATIONGOALSETTING.handlerErrorClickedFocusToFieldError();
		GRADUATIONGOALSETTING.handlerTable4(); // TODO remove
	},
	saveEdit : function() {
		var arrayER = new Array();
		if ($('#rdGoalSettingfrom').val() == 0) {
			var idLoadData = '#graduationtargets';
		} else {
			var idLoadData = '#Yearlytargets';
		}
		$(idLoadData + ' tr').not('.noRecord').each(
				function(index, value) {
					var row = parseInt($(this).attr('id'));
					var ekenLevel = GRADUATIONGOALSETTING.trim($(this).find(
							".ekenLevel").val());
					var yearGoal = GRADUATIONGOALSETTING.trim($(this).find(
							".yearGoal").val());
					var idYearGoal = 'yearGoal' + row;
					var idEkenLevel = 'ekenLevel' + row;
					if (yearGoal == '') {
						$(this).find(".yearGoal").addClass('error');
						arrayER.push({
							'id' : idYearGoal,
							'message' : jsMessages.MSG1
						})
					} else if (!$.isNumeric(yearGoal)
							|| GRADUATIONGOALSETTING.isInt(yearGoal) == false) {
						$(this).find(".yearGoal").addClass('error');
						arrayER.push({
							'id' : idYearGoal,
							'message' : jsMessages.MSG00025
						})
					} else if (yearGoal > 100) {
						$(this).find(".yearGoal").addClass('error');
						arrayER.push({
							'id' : idYearGoal,
							'message' : jsMessages.MSG25
						});
					} else if (yearGoal < 1) {
						$(this).find(".yearGoal").addClass('error');
						arrayER.push({
							'id' : idYearGoal,
							'message' : jsMessages.MSG00025
						});
					} else {
						$(this).find(".yearGoal").removeClass('error');
					}
					if (ekenLevel == '' || ekenLevel == 0) {
						$(this).find(".ekenLevel").addClass('error');
						arrayER.push({
							'id' : idEkenLevel,
							'message' : jsMessages.MSG1
						})
					} else {
						$(this).find(".ekenLevel").removeClass('error');
					}
				});
		if (arrayER.length > 0) {
			ERROR_MESSAGE.show(arrayER, function() {
			}, 'inline');
		} else {
			if ($('#rdGoalSettingfrom').val() == 0) {
				$('#updateGraduationGoal').submit();
			} else {
				$('#updateYearGoal').submit();
			}
			ERROR_MESSAGE.clear();
		}
	},
	loadDatatarget : function(data, datapost) {
		var graduationTimeHtml = '';
		var graduationYearHtml = '';
		var editAction = parseInt($('#editAction').val());
		if (data && data.graduationTimeTarget) {//table left
			var isEdit = parseInt(datapost.edit);
			var z = 0;
			$
					.each(
							data.graduationTimeTarget,
							function(i, item) {
								z++;
								graduationTimeHtml += '<tr id="' + z + '">';
								if (datapost.rdGoalSetting == 0) {
									graduationTimeHtml += '<td class="">'
											+ item.year + '</td>';
									graduationTimeHtml += '<input type="hidden" name="yeartagetPass" value="1"/>';
								} else {
									graduationTimeHtml += '<input type="hidden" name="eikenLevelIdMax" value="'
											+ data.idmax + '"/>';
								}
								if (isEdit == 1) {
									var EkenLevel = GRADUATIONGOALSETTING
											.dropDownListEkenlevel(
													data.listEkenLevel,
													item.eikenLevelId, i, z);
									graduationTimeHtml += '<input type="hidden" name="item['
											+ i
											+ '][Year]" id="year'
											+ z
											+ '" value="' + item.year + '"/>';
									graduationTimeHtml += '<input type="hidden" name="item['
											+ i
											+ '][schoolYearId]" id="schoolYearId'
											+ z
											+ '" value="'
											+ item.schoolYearId + '"/>';
									var pass = '<input name="item['
											+ i
											+ '][YearGoal]" maxlength="3" id="yearGoal'
											+ z
											+ '" class="form-control yearGoal " type="text" value="'
											+ item.targetPass.replace("%", "")
											+ '"/>%';

								} else {
									if (item.targetPass) {
										var pass = item.targetPass;
									} else {
										var pass = '';
									}
									var EkenLevel = GRADUATIONGOALSETTING
											.htmlencode(item.levelName);
								}
								if (typeof pass === "undefined") {
									pass = '';
								}
								if (typeof EkenLevel === "undefined") {
									EkenLevel = '';
								}
								graduationTimeHtml += '<td>'
										+ GRADUATIONGOALSETTING
												.htmlencode(item.SchoolYear)
										+ '</td>';
								graduationTimeHtml += '<td class="text-right">'
										+ EkenLevel + '</td>';
								graduationTimeHtml += '<td class="text-center">'
										+ pass + '</td>';
								graduationTimeHtml += '</tr>';
							});
		} else {
			graduationTimeHtml = '<tr class="noRecord"><td colspan="4">' + jsMessages.MSG17
					+ '</td></tr>';
		}
		if (data && data.graduationYear) {//table right
			$.each(data.graduationYear,
					function(i, item) {
						graduationYearHtml += '<tr>';
						if (datapost.rdGoalSetting == 0) {
							graduationYearHtml += '<td class="">' + item.year
									+ '</td>';
						} else {
							graduationYearHtml += '<td>'
									+ GRADUATIONGOALSETTING
											.htmlencode(item.SchoolYear)
									+ '</td>';
						}
						if (item.pass) {
							var passTaget = item.pass;
						} else {
							passTaget = '';
						}
						if (item.targetPass) {
							var targetPass = item.targetPass;
						} else {
							targetPass = '';
						}
						if (typeof passTaget === "undefined") {
							passTaget = '';
						}
						if (typeof targetPass === "undefined") {
							targetPass = '';
						}
						graduationYearHtml += '<td class="text-right">'
								+ GRADUATIONGOALSETTING
										.htmlencode(item.levelName) + '</td>';
						graduationYearHtml += '<td class="text-center">'
								+ targetPass + '</td>';
						if(typeof isHighSchool != "undefined" && isHighSchool){
							graduationYearHtml += '<td class="text-center">'
								+ passTaget + '</td>';
						}
						graduationYearHtml += '</tr>';
					});
		} else {
			graduationYearHtml = '<tr  class="noRecord"><td colspan="4">' + jsMessages.MSG17
					+ '</td></tr>';
		}
		if (datapost.rdGoalSetting == 0) {
			$('#graduationtargets tr').remove();
			$('#graduationtargets').html(graduationTimeHtml);
			$('#Historicgoal tr').remove();
			$('#Historicgoal').html(graduationYearHtml);
		} else {
			var years = parseInt(datapost.ddbYear);
			if (years < 1) {
				var titleleft = '';
				var titleright = '';
			} else {
				var titleleft = years;
				var titleright = years - 1;
			}
			$('#annual-target .pull-left .box-header').html(
					'取得率（目標）：' + titleleft + '年度');
			$('#annual-target .pull-right .box-header').html(
					'取得率（実績）：' + titleright + '年度');
			$('#Yearlytargets tr').remove();
			$('#Yearlytargets').html(graduationTimeHtml);
			$('#HistoricgoalByYear tr').remove();
			$('#HistoricgoalByYear').html(graduationYearHtml);
		}
	},
	loadDataInCitylayout : function(data) {
		var html = '<tr><td colspan="9">検索条件に一致するデータがありません。</td> </tr>';
		if (typeof data != 'undefined' && data != false) {
			$('.showHide').show();
			var html = '';
			var arr = Object.keys(data).map(function(k) {
				return k;
			});
			var yearIndex = arr.sort(function(a, b) {
				return b - a;
			});
			$.each(yearIndex, function(i, key) {
				var item = data[key];
				var nationwidePassRate;
				var cityPassRate;
				if (item.city) {
					var cityPassRate = item.city;
					var cityName = cityPassRate['cityName'];
				} else {
					var cityPassRate = new Array();
					var cityName = '';
				}
				if (item.nation) {
					var nationwidePassRate = item.nation;
				} else {
					var nationwidePassRate = new Array();
				}
				for (t = 1; t < 8; t++) {
					if (cityPassRate[t]) {
						cityPassRate[t] = Math.round(cityPassRate[t]) + '%';
					} else {
						cityPassRate[t] = '-';
					}
					if (nationwidePassRate[t]) {
						nationwidePassRate[t] = Math
								.round(nationwidePassRate[t])
								+ '%';
					} else {
						nationwidePassRate[t] = '-';
					}
				}
				html += '<tr>';
				html += '<td  rowspan="2" >' + key + '</td>';
				html += '<td>' + GRADUATIONGOALSETTING.htmlencode(cityName)
						+ '</td>';
				for (t = 1; t < 8; t++) {
					html += '<td class="text-center">' + cityPassRate[t]
							+ '</td>';
				}
				html += '</tr>';
				html += '<tr>';
				html += '<td>全国</td>';
				for (t = 1; t < 8; t++) {
					html += '<td class="text-center">' + nationwidePassRate[t]
							+ '</td>';
				}
				html += '</tr>';
			});
		} else {
			$('.showHide').hide();
		}
		$('#loadDataIncity tr').remove();
		$('#loadDataIncity').html(html);
	},
	loadBreadcrumb : function(isEdit, isGoalSetting) { 
		if (isGoalSetting == 1 && isEdit == 1) {
			$('.breadcrumbs-current').html('年度目標編集');
			$('#description-page-edit-r2').show();
			$('#description-page-edit-r1').hide();
			$('#description-page-show').hide();
			
			$('#title-description-edit-r2').show();
			$('#title-description-edit-r1').hide();
			$('#show-title-description').hide();
		} else if (isGoalSetting == 1 && isEdit == 0) {
			$('.breadcrumbs-current').html('年度目標詳細');
			$('#description-page-edit-r1').hide();
			$('#description-page-edit-r2').hide();
			$('#description-page-show').show();
			
			$('#title-description-edit-r2').hide();
			$('#title-description-edit-r1').hide();
			$('#show-title-description').show();
		} else if (isGoalSetting == 0 && isEdit == 1) {
			$('.breadcrumbs-current').html('卒業時目標編集');
			$('#graduation-time-target .col-goal-rate').css({
				'width' : 108
			});
			$('#description-page-edit-r1').show();
			$('#description-page-edit-r2').hide();
			$('#description-page-show').hide();
			
			$('#title-description-edit-r1').show();
			$('#title-description-edit-r2').hide();
			$('#show-title-description').hide();
		} else if (isGoalSetting == 0 && isEdit == 0) {
			$('.breadcrumbs-current').html('卒業時目標詳細');
			$('#graduation-time-target .col-goal-rate').css({
				'width' : 80
			});
			$('#description-page-edit-r1').hide();
			$('#description-page-edit-r2').hide();
			$('#description-page-show').show();
			
			$('#title-description-edit-r2').hide();
			$('#title-description-edit-r1').hide();
			$('#show-title-description').show();
			
		}
		
	},
	trim : function(data) {
		if (data) {
			return data.replace(/^\s+|\s+$/gm, '');
		}
		return '';
	},
	isInt : function(n) {
		return n % 1 === 0;
	},
	dropDownListEkenlevel : function(data, selected, key, z) {
		var dropDown = '<select name="item[' + key
				+ '][ekenLevel]" id="ekenLevel' + z
				+ '" class="form-control inset-shadow w-80 ekenLevel">';
		$.each(data, function(i, item) {
			if (selected == i) {
				var select = 'selected';
			} else {
				var select = '';
			}
			dropDown += '<option value="' + i + '" ' + select + '>'
					+ GRADUATIONGOALSETTING.htmlencode(item) + '</option>';
		});
		dropDown += '</select>';
		return dropDown;
	},
	htmlencode : function(str) {
		return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;')
				.replace(/>/g, '&gt;').replace(/"/g, '&quot;')
	},
	loadListSchoolYear : function() {
		var organizationCode = $('#ddbOrganization').val();
		$.ajax({
			type : 'POST',
			url : GRADUATIONGOALSETTING.listSchoolYearUrl,
			dataType : 'json',
			data : {
				'organizationCode' : organizationCode
			},
			success : function(result) {
				var option = '';
				$.each(result, function(i, item) {
					option += '<option value = "' + i + '">' + item
							+ '</option>'
				});
				$('#ddbSchoolYear').html(option);
			},
			error : function() {

			}
		});
	},
	// TODO remove
	handlerTable4 : function() {
		$('.example1').show();
		$('.example2').hide();
		$('#btnDetail').on('click', function() {
			if ($('#rdDisplay1').is(':checked')) {
				$('.example1').show();
				$('.example2').hide();
			} else {
				$('.example1').hide();
				$('.example2').show();
			}
		});
	},
	resetButtonEditState : function() {
		$('#description-page-edit-r1').hide();
		$('#description-page-edit-r2').hide();
		$('#description-page-show').show();
		$('#btn-edit-setting').show();
		$('#btn-cancel-edit').css({
			'display' : 'none'
		});
		$('#btn-save-edit').css({
			'display' : 'none'
		});
	},
	resetButtonSaveEditState : function() {
		$('#btn-edit-setting').hide();
		$('#btn-cancel-edit').removeAttr('style');
		$('#btn-save-edit').removeAttr('style');
	},
	handlerErrorClickedFocusToFieldError : function() {
		// target to input error
		$(".jiem-error").on('click', '.alert li a', function() {
			var idinput = $(this).attr('href');
			setTimeout(function() {
				$(idinput).focus();
			}, 100);
		});
	},
	handlerShowHideTable : function() {
		// when clicked search button call funciton reset button view
		//GRADUATIONGOALSETTING.resetButtonEditState();
		var rdGoalSetting = $('#rdGoalSettingfrom').val();
		if (rdGoalSetting == "") {
			rdGoalSetting = $('#rdGoalSetting').val();
		}
		if (rdGoalSetting == 0) {
			$('div[id*="annual-target"]').hide();
			$('#graduation-time-target-edit').hide();
			$('#graduation-time-target').show();
		} else {
			$('div[id*="graduation-time-target"]').hide();
			$('#annual-target-edit').hide();
			$('#annual-target').show();
		}
	},
	clearAction : function() {
		window.location.href = GRADUATIONGOALSETTING.clearUrl;
	},
	search : function(data, isGoalSetting) {	
		if(isGoalSetting==undefined)
		{
			isGoalSetting = parseInt($('#rdGoalSettingfrom').val());
		}
		
			var isEdit = parseInt($('#editAction').val());
		
		ERROR_MESSAGE.clear();
		$.ajax({
			type : 'POST',
			url : GRADUATIONGOALSETTING.graduationGoalSearchUrl,
			dataType : 'json',
			data : data,
			success : function(result) {
				$('.graduationgoalsetting .table > thead').show();
				GRADUATIONGOALSETTING.loadBreadcrumb(isEdit, isGoalSetting);
				GRADUATIONGOALSETTING.loadDatatarget(result,data);
				if (isEdit == 0) {
					GRADUATIONGOALSETTING.resetButtonEditState();
					GRADUATIONGOALSETTING.loadDataInCitylayout(result.graduationStatistics);
				} else {
					GRADUATIONGOALSETTING.resetButtonSaveEditState();
				}
				COMMON.setTabIndexIdentify("select,input,a.btn");
				GRADUATIONGOALSETTING.handlerShowHideTable();

			},
			error : function(request, status, error) {
			}
		});
	},
	cancelEditAction : function()// action=5
	{
		// set edit action hidden to status no edit.
		$('#editAction').val(0);
		// get data form post
		var data = new Object();
		var data = $('#GraduationGoalSearchForm').serializeObject();
		data.edit = 0;
		data.rdGoalSetting=parseInt($('#rdGoalSettingfrom').val());
		setTimeout(function() { document.getElementById('rdGoalSetting').focus(); }, 1000);
		$('form:first *:input[type!=hidden]:first').focus();
		GRADUATIONGOALSETTING.search(data);

	},
	editModeAction : function()// action=1
	{
		$('#editAction').val(1);
		var data = new Object();
		var data = $('#GraduationGoalAcquisitionRateEditForm')
				.serializeObject();
		data.edit = 1;
		setTimeout(function() { document.getElementById('rdGoalSetting').focus(); }, 1000);
		$('form:first *:input[type!=hidden]:first').focus();
		GRADUATIONGOALSETTING.search(data);

	},
	searchAllAction : function()// action=0
	{
		var data = new Object();
		var data = $('#GraduationGoalSearchForm').serializeObject();
		$('#ddbYearfrom').val(data.ddbYear);
		$('#rdGoalSettingfrom').val(data.rdGoalSetting);
		$('#yearsearch').val(data.ddbYear);
		data.ddbOrganization = $('#ddbOrganization').val();
		data.ddbSchoolYear = $('#ddbSchoolYear').val();
		data.ddbPrefectures = $('#ddbPrefectures').val();
		isGoalSetting = data.rdGoalSetting;
		setTimeout(function() { document.getElementById('rdGoalSetting').focus(); }, 1000);
		$('form:first *:input[type!=hidden]:first').focus();
		GRADUATIONGOALSETTING.search(data, isGoalSetting);
	},
	searchCityGoalAction : function()// action=4
	{
		var data = new Object();
		var data = $('#GraduationGoalNationalSearchForm').serializeObject();
		$.ajax({
			type : 'POST',
			url : GRADUATIONGOALSETTING.graduationGoalSearchUrl,
			dataType : 'json',
			data : data,
			success : function(result) {
				GRADUATIONGOALSETTING.loadDataInCitylayout(result);
			},
			error : function(request, status, error) {
			}
		});
	},

};
var isEditTitle = false;
jQuery.fn.serializeObject = function() {

	$('#btnSearch').click(function() {
		   if($('#rdGoalSetting').is(':checked')) 
		   {
			   $('#show-title-name').html('卒業時目標詳細'); 
		   }	
		   else
		   { 
			   $('#show-title-name').html('年度目標詳細');  
		   }
		   if(isEditTitle)
		   {
			   if($('#rdGoalSetting').is(':checked')) 
			   {			
				   $('#show-title-name').html('卒業時目標編集'); 
			   }
			 else
			   { 
				   $('#show-title-name').html('年度目標編集');  
			   }  
		   }
	});
	
	$('.btn-edit-setting').click(function() {	
		isEditTitle = true;
		 if($('#rdGoalSetting').is(':checked')) 
		   {			
			   $('#show-title-name').html('卒業時目標編集'); 
		   }
		 else
		   { 
			   $('#show-title-name').html('年度目標編集');  
		   }
	});
	
	$('.btn-cancel-edit').click(function() {	
		isEditTitle = false;
		 if($('#rdGoalSetting').is(':checked')) 
		   {			
			   $('#show-title-name').html('卒業時目標詳細'); 
		   }
		 else
		   { 
			   $('#show-title-name').html('年度目標詳細');  
		   }
	});
	
	var arrayData, objectData;
	arrayData = this.serializeArray();
	objectData = {};

	$.each(arrayData, function() {
		var value;

		if (this.value != null) {
			value = this.value;
		} else {
			value = '';
		}

		if (objectData[this.name] != null) {
			if (!objectData[this.name].push) {
				objectData[this.name] = [ objectData[this.name] ];
			}

			objectData[this.name].push(value);
		} else {
			objectData[this.name] = value;
		}
	});
	return objectData;
};
