var EIKEN_EXAM_RESULT = {
    urlMappingIbaResult : COMMON.baseUrl + 'history/iba/mapping-data',
    urlMappingEikenResult : COMMON.baseUrl + 'history/eiken/mapping-data',
    urlMappingEikenError: COMMON.baseUrl + 'history/eiken/mapping-error',
    urlMappingIbaError: COMMON.baseUrl + 'history/iba/mapping-error',
    urlMappingEikenSuccess: COMMON.baseUrl + 'history/eiken/mapping-success',
    urlGetKaiAction : COMMON.baseUrl + 'history/eiken/get-kai',
    urlCheckKekkaEiken: COMMON.baseUrl + 'history/eiken/check-kekka-value',
    urlCheckKekkaIba: COMMON.baseUrl + 'history/iba/check-kekka-value',
    urlSaveEikenResult : COMMON.baseUrl + 'history/eiken/save-eiken-exam-result-only',
    urlSaveIBAResult : COMMON.baseUrl + 'history/iba/save-iba-exam-result-only',
    urlListEikenMappingResult : COMMON.baseUrl + 'history/eiken/eiken-mapping-result',
    urlListIbaMappingResult : COMMON.baseUrl + 'history/iba/iba-mapping-result',
    mappingStatus: false,
    init : function() {
        $('#examType').focus();
        if($('#startDate').length){
            $('#startDate').datepicker();
            $('#endDate').datepicker();
        }
        $('#divSeachForm input').bind('keypress', function(e) {
            if (e.keyCode == 13) {
                $('#searcheikenexam').submit();
            }
        });
        $('#btnClear').click(function() {
        	$("#year option[value='" + $("#pastYear").text() + "']").prop('selected', true);
            $("#endDate").val('');
            $("#startDate").val('');
            $("#examType").val('');
            $("#kai").val('');
            $("#year").val('');
            $('#searcheikenexam').submit();
        });
        $('#btnSearch').click(function() {
            $('#searcheikenexam').submit();
        });
        EIKEN_EXAM_RESULT.showKai(true);

        // validator date from & date to
        jQuery.validator.addMethod("comparedate", function(value, element) {
            var date1 = $("#startDate").val();
            date1 = Date.parse(date1);
            var date2 = $("#endDate").val();
            date2 = Date.parse(date2);
            if (value == "")
                return true;
            if (date2 < date1) {
                return false;
            } else {
                return true;
            }
        }, '日付（から）と日付（まで）の前後関係がが正しくありません。');
        
        jQuery.validator.addMethod("dateISO", function(value, element) {
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
            } else if ((dtMonth == 4 || dtMonth == 6 || dtMonth == 9 || dtMonth == 11)
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
        
        var validator = $("#searcheikenexam").validate({
            onfocusout : false,
            onkeyup : false,
            onclick : false,
            rules : {
                startDate : {
                    dateISO : true
                },
                endDate : {
                    dateISO : true,
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
    },
    processBar:function(examName, year, kai, mappingStatus){
        var progressBar = '<div class="row"><div class="col-lg-12 row"><div class="progress"><div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="background: #e70012;"></div></div><div class="col-lg-12"><span class="pull-left" style="color: #e70012;">0%</span><span class="pull-right"  style="color: #e70012;">100%</span></div></div>';
        EIKEN_EXAM_RESULT.creatPopup('<p>お待ちください。。。</p> ' + progressBar,'英検検定結果がマッピング中です。',0);
        EIKEN_EXAM_RESULT.runProcessBar(examName, year, kai, mappingStatus);
    },
    creatPopup: function(msg,title,rate){
        var popup='';
        popup += '<div class="modal fade" id="processBar">';
        popup += '<div class="modal-dialog">';
        popup += '<div class="modal-content">';
        popup += '<div class="modal-header">';
        popup += '<h4 class="modal-title">'+title+'</h4>';
        popup += '</div>';//end modal-header
        popup += '<div class="modal-body">';
        popup += ' <p>'+msg+'</p>';
        popup += ' <p id="mapping-rate" style="text-align: center;font-weight: bold; color: #e70012;">'+rate+'%</p>';
        
        popup += '</div>';//end modal-body
        popup += '</div>';//end modal-content
        popup += '</div>';//end modal-dialog
        popup += '</div>';//end modal
        if($('#processBar').length){
            $('#processBar').remove()
        }
        $('body').append(popup);
        $('#processBar').modal('show');
    },
    runProcessBar: function(examName, year, kai, mappingStatus){
        EIKEN_EXAM_RESULT.progress(examName, year, kai, mappingStatus);
    },
    progress: function(examName, year, kai, mappingStatus){
        if(typeof mappingStatus == 'undefined'){
            mappingStatus = 'inprogress';
        }
        var val = parseInt($('#mapping-rate').text()) || 0;
        if(typeof examName != 'undefined'){
            $('#examNameCurrentSelect').text(examName);
            $('#examYearCurrentSelect').text(year);
            $('#examKaiCurrentSelect').text(kai);
        }else {
            $('#mappingStatus').text(mappingStatus);
        }
        $('#mapping-rate').text(val+10+'%');
        $('.progress-bar').css('width', val + 10 + '%');

        if ( val < 90 ) {
            if(EIKEN_EXAM_RESULT.mappingStatus || mappingStatus == 'finished') {
                setTimeout( EIKEN_EXAM_RESULT.progress, 100 );
                return;
            }
            setTimeout( EIKEN_EXAM_RESULT.progress, 2000 );
        } else {
            EIKEN_EXAM_RESULT.closeProcessBar();
            var year = $('#examYearCurrentSelect').text();
            var kai = $('#examKaiCurrentSelect').text();
            if ($('#examNameCurrentSelect').text() != EXAM_EIKEN) {
                window.location.href = EIKEN_EXAM_RESULT.urlMappingIbaResult + '/year/' + year;
            } else {
                window.location.href = EIKEN_EXAM_RESULT.urlMappingEikenError + '/year/' + year + '/kai/' + kai;
            }
        }
    },
    closeProcessBar: function(){
        $('#processBar').modal('hide');
    },
    mappingButtonClick : function() {
        // CONFIRM_MESSAGE.show();
    },
    showKai : function(flag) {
        var checkIba = $("#examType").val();
        if (checkIba == EXAM_IBA) {
            $("#kai").attr('disabled', 'disabled');
            $("#kai").val('');
        } else {
            $("#kai").removeAttr('disabled');
        }
    },
    loadKai : function() {
        var year = $('#year').val();
        if (year != null) {
            var checkIba = $("#examType").val();
            var jsonurl = EIKEN_EXAM_RESULT.urlGetKaiAction + "?year=" + year;
            if (checkIba != "IBA") {
                $.ajax({
                    type : 'GET',
                    url : jsonurl,
                    data : {},
                    global : false,
                    success : function(data) {
                    	$("#kai").empty();
                        $("#kai").prepend("<option value='' selected='selected'></option>");
                        var options = $("#kai");
                        $.each(data, function() {
                            options.append($("<option />").val(this.kai).text(this.kai));
                        });
                    },
                    error : function() {
                    }
                });
            }
        }
    },
    checkKekkaValue : function(year, kai, examId, statusMapping, totalImport, jisshiId, examType) { // examId <=> applyEikenOrgId or applyIbaOrgId
    	// jisshiId and examType  is mandatory in IBA
    	if(!kai && (!jisshiId || !examType)){
		   ERROR_MESSAGE.show('The jisshiId or examType is not found in the apply IBA ORG Table.', function () {
           })
    		return;
    	}
        var statusMapping = typeof statusMapping != 'undefined' ? statusMapping : 0;
        var totalImport = typeof totalImport != 'undefined' ? totalImport : 0;
        if (kai) {
            var urlCheckKekka = $('#linkCheckKekka_' + year + "_" + kai).text();
            var urlConfirmExamScreen = $('#linkConfirmExamScreen_' + year + "_" + kai).text();
        } else {
            var urlCheckKekka = $('#linkCheckKekka_' + year).text();
            var urlConfirmExamScreen = $('#linkConfirmExamScreen_' + year).text();
        }
        if(totalImport == 2 || statusMapping == 2){
            ERROR_MESSAGE.show('試験結果取込処理を実行しています。完了するまでお待ちください。', function () {
                window.location.reload(true);
            });
            return;
        }
        if (totalImport == 1) {
            CONFIRM_MESSAGE.show(translator.msgReImport, function() {
                EIKEN_EXAM_RESULT.checkDatafromAPI(year, kai, examId, statusMapping, urlCheckKekka, urlConfirmExamScreen, jisshiId, examType);
           });
        } else {
            EIKEN_EXAM_RESULT.checkDatafromAPI(year, kai, examId, statusMapping, urlCheckKekka, urlConfirmExamScreen, jisshiId, examType);
        }
    },
    checkDatafromAPI: function(year, kai, examId, statusMapping, urlCheckKekka, urlConfirmExamScreen, jisshiId, examType) {
        $.ajax({
            type : 'GET',
            url : urlCheckKekka,
            data : {
                year : year,
                kai : kai,
                examId: examId,
                statusMapping: statusMapping,
                jisshiId: jisshiId,
                examType: examType
            },
            success : function(data) {
                if (data == '10') {//co data tra ve tu API Ukestuke
                    //redirect to confirmation screen
                    window.location.href = urlConfirmExamScreen;
                } else if (data == '01') {
                    ALERT_MESSAGE.show($('#msg15').text(), function(){
                        window.location.reload(true);
                    });
                } else if (data == '00') {
                    ALERT_MESSAGE.show($('#msg16').text(), function(){
                        window.location.reload(true);
                    });
                } else if (data == '02') {
                    ALERT_MESSAGE.show($('#msg24').text(), function(){
//                        window.location.reload(true);
                    });
                }
            },
            error : function() {}
        });
    },
    mappingAction : function(exam_type) {
        EIKEN_MAPPING_RESULT.processBar();
    },
    showCalendar : function(name) {
        $('#' + name).datepicker('show');
    },
    errorMapping: function(){
        ERROR_MESSAGE.show('試験結果取込処理を実行しています。完了するまでお待ちください。', function () {
            window.location.reload(true);
        });
        return;
    },
    mappingDataEiken: function (yearNo, kaiNo, applyEikenId, mappingStatus, hasImportData) {
        if (typeof mappingStatus != 'undefined' && parseInt(mappingStatus) == 1) {
            window.location.href = EIKEN_EXAM_RESULT.urlListEikenMappingResult + '/year/' + yearNo + '/kai/' + kaiNo;
            return;
        }
        PROGRESS.run(function () {
            $.ajax({
                type: 'POST',
                url: EIKEN_EXAM_RESULT.urlMappingEikenResult,
                data: {
                    yearNo: yearNo,
                    kaiNo: kaiNo,
                    applyEikenId: applyEikenId,
                    hasImportData: hasImportData
                },
                success: function (data) {
                    PROGRESS.stop();
                },
                error: function () {
                }
            });


        }, function () {
            window.location.href = EIKEN_EXAM_RESULT.urlListEikenMappingResult + '/year/' + yearNo + '/kai/' + kaiNo;
        }, 2000);//delay time for run progress bar
    },
    mappingDataIba: function (yearNo, jisshiId, examType, applyEikenId, mappingStatus, hasImportData) {
        if (!jisshiId || !examType) {
            ERROR_MESSAGE.show('The jisshiId and examType is not found in the apply IBA ORG Table.', function () {
            });
            return;
        }
        if (typeof mappingStatus != 'undefined' && parseInt(mappingStatus) == 1) {
            window.location.href = EIKEN_EXAM_RESULT.urlListIbaMappingResult + '/year/' + yearNo + '/jisshiId/' + jisshiId + '/examType/' + examType;
            return;
        }
        PROGRESS.run(function () {
            $.ajax({
                type: 'POST',
                url: EIKEN_EXAM_RESULT.urlMappingIbaResult,
                data: {
                    yearNo: yearNo,
                    applyEikenId: applyEikenId,
                    jisshiId: jisshiId,
                    examType: examType,
                    hasImportData: hasImportData
                },
                success: function (data) {
                    PROGRESS.stop();
                },
                error: function () {

                }
            });


        }, function () {
            window.location.href = EIKEN_EXAM_RESULT.urlListIbaMappingResult + '/year/' + yearNo + '/jisshiId/' + jisshiId + '/examType/' + examType;
        }, 2000);//delay time for run progress bar
    },
    mappingTestResult: function(examName, yearNo, kaiNo, applyEikenId, mappingStatus, jisshiId, examType, hasImportData) {
        if (typeof hasImportData == 'undefined') {
            hasImportData = 0;
        }
        if (typeof jisshiId == 'undefined') {
            jisshiId = 0;
            examType = 0;
        }
        if(examName == EXAM_EIKEN){
            EIKEN_EXAM_RESULT.mappingDataEiken(yearNo, kaiNo, applyEikenId, mappingStatus, hasImportData);
        }else{
            EIKEN_EXAM_RESULT.mappingDataIba(yearNo, jisshiId, examType, applyEikenId, mappingStatus, hasImportData);
        }
    },
    /**
     * @author FPT-DuongTD
     * @param typeStatus
     * @param examName string "EIKEN" or "IBA"
     * @param yearNo int
     * @param kaiNo int kaiNo is not exist with IBA
     * @param applyEikenId id in applyEikenOrg or applyIBAOrg
     * @param mappingStatus o man hinh confirm-exam-result thi mapping status = 0 de mapping lai
     * @param jisshiId
     * @param examType
     * @param Import
     */
    mappingData: function(typeStatus, examName, yearNo, kaiNo, applyEikenId, mappingStatus, jisshiId, examType, ImportData) {
    	
    	if(typeof ImportData == 'undefined') {
    		ImportData = 0;
    	}
    	if(typeof jisshiId == 'undefined'){
            jisshiId = 0;
            examType = 0;
    	}
    	//jisshiId, examType is mandatory in IBA
        if (examName != EXAM_EIKEN && (!jisshiId || !examType)) {
            ERROR_MESSAGE.show('The jisshiId or examType is not found in the apply IBA ORG Table.', function () {});
            return;
        }
        if(typeof mappingStatus != 'undefined' && mappingStatus){
            if(examName == EXAM_EIKEN){
                window.location.href = EIKEN_EXAM_RESULT.urlMappingEikenError + '/year/' + yearNo + '/kai/' + kaiNo;
            } else {
                window.location.href = EIKEN_EXAM_RESULT.urlMappingIbaError + '/year/' + yearNo + '/jisshiId/' + jisshiId + '/examType/' + examType;
            }
            return;
        }
        PROGRESS.run(function(){
            if (typeStatus == 'save') {
                var urlSave = (examName == EXAM_EIKEN) ? EIKEN_EXAM_RESULT.urlSaveEikenResult : EIKEN_EXAM_RESULT.urlSaveIBAResult;
                $.ajax({
                    type : 'POST',
                    url : urlSave,
                    data : {
                        jisshiId: jisshiId,
                        examType: examType,
                    },
                    success : function(data) {
                    	 $.ajax({
                             type : 'POST',
                             url : examName == EXAM_EIKEN ? EIKEN_EXAM_RESULT.urlMappingEikenResult : EIKEN_EXAM_RESULT.urlMappingIbaResult,
                             data : {
                                 examName: examName,
                                 yearNo : yearNo,
                                 kaiNo : kaiNo,
                                 applyEikenId: applyEikenId,
                                 jisshiId: jisshiId,
                                 examType: examType,
                                 ImportData: ImportData
                             },
                             success : function(data) {
                                 PROGRESS.stop();
                             },
                             error : function() {
                             }
                         });
                    },
                    error : function() {
                    }
                });
            } else {
            	 $.ajax({
                     type : 'POST',
                     url : examName == EXAM_EIKEN ? EIKEN_EXAM_RESULT.urlMappingEikenResult : EIKEN_EXAM_RESULT.urlMappingIbaResult,
                     data : {
                         examName: examName,
                         yearNo : yearNo,
                         kaiNo : kaiNo,
                         applyEikenId: applyEikenId,
                         jisshiId: jisshiId,
                         examType: examType,
                         ImportData: ImportData
                     },
                     success : function(data) {
                         PROGRESS.stop();
                     },
                     error : function() {
                     }
                 });
            }
           
        }, function(){
            //redirect to mapping page
            if(examName == EXAM_EIKEN){
                window.location.href = EIKEN_EXAM_RESULT.urlMappingEikenError + '/year/' + yearNo + '/kai/' + kaiNo;
            } else {
                window.location.href = EIKEN_EXAM_RESULT.urlMappingIbaError + '/year/' + yearNo + '/jisshiId/' + jisshiId + '/examType/' + examType;
            }
        }, 2000);//delay time for run progress bar
    }
};