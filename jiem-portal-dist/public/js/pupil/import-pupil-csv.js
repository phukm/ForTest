var importPupilConfig = {
    urlSaveData: COMMON.baseUrl + 'pupil/import-pupil/save',
    urlSaveDataPupil: COMMON.baseUrl + 'pupil/import-pupil/save-pupil',
    urlDetailDuplicatePupil: COMMON.baseUrl + 'pupil/import-pupil/detail-duplicate',
    urlShowDataImportPaging: COMMON.baseUrl + 'pupil/import-pupil/show-data-paging',
    urlListPupil: COMMON.baseUrl + 'pupil/pupil/index',
    urlImport: COMMON.baseUrl + 'pupil/import-pupil/index',
    urlMappingSchoolYear: COMMON.baseUrl + 'pupil/import-pupil/mapping-school-year',
    ready: function () {
        importPupilConfig.forcusfield();
        
        var errorMappingSchoolyearPage = $('.show-error-mapping-schoolyear').height();
        if (errorMappingSchoolyearPage > 252) {
            $('.show-error-mapping-schoolyear').css('overflow-y', 'scroll');
        }
        
        var mappingGradePage = $('.create-grade .content-data-mapping').height();
        if (mappingGradePage >= 400) {
            $('.create-grade .content-data-mapping').css('overflow-y', 'scroll');
        } else {
            $('.create-grade .header-mapping').removeClass('header-mapping').addClass('header-mapping-no-scroll');
        }
        
        var masterDataPage = $('.create-grade .content-data').height();
        if (masterDataPage >= 500) {
            $('.create-grade .content-data').css('overflow-y', 'scroll');
        } else {
            $('.create-grade .header').css('margin-bottom', '0').removeClass('header');
        }
        var listError = $('.student_manager .import-error').innerHeight();
        if(listError >= 160){
            $('.student_manager .import-error').css('overflow-y', 'scroll');
        }
        
        var listData = $('.std_list .importstd-list').height();
        if (listData >= 500) {
            $('.std_list .importstd-list').css('overflow-y', 'scroll');
        }
        $("#check-duplicate").click(function () {
            $("#detailModal").modal('show');
        });

        $("#fileImport").on('change', function () {
            var value = $(this).val();
            var arr = value.split("\\fakepath\\");
            if (typeof arr[1] != 'undefined') {
                $("#filename").val(arr[1]);
            } else {
                $("#filename").val($(this).val());
            }
        });
        $('#import-pupil').on('submit', function (e) {
            $('#showMsgFileImport').html();
            e.preventDefault();
            $(this).ajaxSubmit({
                dataType: 'json',
                success: function (response) {
                    if (response.status == 0) {
                        $('#filename').addClass('error');
                        $('#showError').show();
                        $('#showMsgFileImport').html(response.message);
                    } else {
                        $('#showError').hide();
                        var response_html = response.content.replace(/td>\s+<td/g,'td><td');
                        response_html = response_html.replace(/th>\s+<th/g,'th><th');
                        response_html = response_html.replace(/tr>\s+<td/g,'tr><td');
                        response_html = response_html.replace(/tr>\s+<th/g,'tr><th');
                        $('#showContent').html(response_html);
                    }
                },
                error: function () {
                    
                }
            });
        });
    },
    mappingSchoolYear: function (){
        var form = $('#form_mapping_schoolyear');
        $.ajax({
            method: "POST",
            url: importPupilConfig.urlMappingSchoolYear,
            dataType: 'json',
            data: form.serialize(),
            success: function (response) { 
                var response_html = response.content.replace(/td>\s+<td/g, 'td><td');
                response_html = response_html.replace(/th>\s+<th/g, 'th><th');
                response_html = response_html.replace(/tr>\s+<td/g, 'tr><td');
                response_html = response_html.replace(/tr>\s+<th/g, 'tr><th');
                $('#showContent').html(response_html);
                
            }
        });
    },
    saveMasterData: function () {
        $.ajax({
            method: "POST",
            url: importPupilConfig.urlSaveData,
            dataType: 'json',
            data: {
                data: $('#masterDataImport').val(), 
                dataImport: $('#dataImportAfterCreateMasterData').val(),
                flagConfirmSave: $('#flagConfirmSave').val()
            },
            success: function (response) { 
                if (response.status == 1 && ($('#flagConfirmSave').val() == 1 || response.statusNotShowPopup == true)) {
                    ALERT_MESSAGE.show(response.message, function () {
                        var response_html = response.content.replace(/td>\s+<td/g, 'td><td');
                        response_html = response_html.replace(/th>\s+<th/g, 'th><th');
                        response_html = response_html.replace(/tr>\s+<td/g, 'tr><td');
                        response_html = response_html.replace(/tr>\s+<th/g, 'tr><th');
                        $('#showContent').html(response_html);
                    });
                    $('#confirmPopupModal').on('hidden.bs.modal', function () {
                        setTimeout(function () {
                            var response_html = response.content.replace(/td>\s+<td/g, 'td><td');
                            response_html = response_html.replace(/th>\s+<th/g, 'th><th');
                            response_html = response_html.replace(/tr>\s+<td/g, 'tr><td');
                            response_html = response_html.replace(/tr>\s+<th/g, 'tr><th');
                            $('#showContent').html(response_html);
                        }, 100);
                    });
                }else if(response.status == 1 && response.statusNotShowPopup == 0){
                    CONFIRM_MESSAGE.show(response.MSGPopupWarning,function(){
                                                    $('#flagConfirmSave').val(1);
                                                    importPupilConfig.saveMasterData();
                                                },function(){ 
                                                    return false; 
                                                }, null,null,null);
                    $('#confirmPopupModal #btnCancelConfirm').addClass('btn-red');
                    $('#confirmPopupModal #btnAgreeConfirm').removeClass('btn-red');
                }else{
                    ERROR_MESSAGE.show(response.message,function (){
                         window.location = importPupilConfig.urlImport;
                    });
                }
                
            }
        });
    },
    saveDataPupil: function (isCheckedDuplicate) {
        $.ajax({
            method: "POST",
            url: importPupilConfig.urlSaveDataPupil,
            dataType: 'json',
            data: {
                dataImportPupil: $('#dataImportPupil').val(),
                isCheckedDuplicate: isCheckedDuplicate
            },
            success: function (response) {
                $("#detailModal").modal('hide');
                if (response.status == 1) {
                    ALERT_MESSAGE.show(response.message, function(){
                        window.location = importPupilConfig.urlListPupil;
                    });
                }else if(response.status == -1){
                    ERROR_MESSAGE.show(response.message, function(){
                        window.location = importPupilConfig.urlImport;
                    });
                }else{
                    var response_html = response.content.replace(/td>\s+<td/g, 'td><td');
                    response_html = response_html.replace(/th>\s+<th/g, 'th><th');
                    response_html = response_html.replace(/tr>\s+<td/g, 'tr><td');
                    response_html = response_html.replace(/tr>\s+<th/g, 'tr><th');
                    $('#showContent').html(response_html);
                } 
            }
        });
    },
    showDetailDuplicate: function (keyDuplicate, status) {
        $.ajax({
            method: "POST",
            url: importPupilConfig.urlDetailDuplicatePupil,
            dataType: 'json',
            data: {
                keyDuplicate: keyDuplicate,
                dataDetailInFile: $('#dataDetailInFile').val(),
                dataDetailInDb: $('#dataDetailInDb').val(),
                status: status,
            },
            success: function (response) {
                var response_html = response.content;
                $('#detailModal').html(response_html);
                $("#detailModal").modal('show');
            },
            error: function () {

            }
        });
    },
    showDataImportPaging: function (currentPage){
        $.ajax({
            method: "POST",
            url: importPupilConfig.urlShowDataImportPaging,
            dataType: 'json',
            data: {
                currentPage: currentPage,
                dataImport: $('#dataImportPupil').val(),
                errors: parseInt(errorsImportData)
            },
            success: function (response) {
                var response_html = response.content.replace(/td>\s+<td/g, 'td><td');
                response_html = response_html.replace(/th>\s+<th/g, 'th><th');
                response_html = response_html.replace(/tr>\s+<td/g, 'tr><td');
                response_html = response_html.replace(/tr>\s+<th/g, 'tr><th');
                $('#showData').html(response_html);
                
                var listData = $('.std_list .importstd-list').height();
                if (listData >= 500) {
                    $('.std_list .importstd-list').css('overflow-y', 'scroll');
                }
            },
            error: function () {

            }
        });
    },
    duplicate: function (){
        $("#detailModal").modal('show');
    },
    forcusfield: function () {
        $("#csvfilefocus").focus();
        $("#csvfilefocus").keydown(function (e) {
            var code = e.keyCode || e.which;
            if (code == 13) {
                $("#fileImport").trigger('click');
            }
        });
        $("#csvfilefocus").keyup(function (e) {
            var code = e.keyCode || e.which;
            if (e.keyCode == 9 && e.shiftKey) {

                $("#csvfilefocus").focus();
            }
            if (e.keyCode == 9 && e.shiftKey != true) {
                $("#submitForm").focus();
            }
        });

        $("#submitForm").keyup(function (e) {
            var code = e.keyCode || e.which;
            if (e.keyCode == 9 && e.shiftKey) {
                var classBtn = $(this).attr("class");
                var arayClass = classBtn.split(" ");
                if ($.inArray("importstd-button-space", arayClass) == true) {
                    $("#fileImport").focus();
                }
            }
            if (e.keyCode == 9 && e.shiftKey != true) {
                $("#submitForm").focus();
            }
        });
    },
    cancelEven:function(){
        ALERT_MESSAGE.show(MSG54CancelImport, function () {
            window.location.href= importPupilConfig.urlImport;
        });
    }
};
