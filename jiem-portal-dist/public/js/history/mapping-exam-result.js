var sessionNameMapping = 'sessionStorageEikenResult';
var keyName = 'e';
var MAPPING_EXAM_RESULT = {
    ajaxGetListClass: COMMON.baseUrl + 'history/eiken/ajax-get-list-class',
    confirmStatus: COMMON.baseUrl + 'history/eiken/confirm-status',
    studentList: COMMON.baseUrl + 'pupil/pupil/index',
    actionDetails: COMMON.baseUrl + 'history/eiken/eiken-confirm-result',
    actionGetStudents: COMMON.baseUrl + 'history/eiken/get-students',
    actionCallSaveNextPupil: COMMON.baseUrl + 'history/eiken/call-save-next-pupil',
    actionEikenMappingResult: COMMON.baseUrl + 'history/eiken/eiken-mapping-result',
    actionSearch: COMMON.baseUrl + 'history/eiken/search',
    documentReferrer: COMMON.baseUrl + 'history/eiken/',
    documentReferrerExamResult: COMMON.baseUrl + 'history/eiken/exam-result',
    removeMappingAction: COMMON.baseUrl + 'history/eiken/removeMapping',    
    initital: function () {
        var token = $('#token').val();
        var year = $("#year").val();
        var kai = $("#kai").val();
        if (sessionStorage.getItem('token') != token) {
            MAPPING_EXAM_RESULT.clearSession();
        }
        sessionStorage.setItem('token', token);
        var listIdOld = JSON.parse(sessionStorage.getItem(sessionNameMapping));
        var count = 0;
        var curentIndex = 0;
        var eikenTestResultId = $('#id').val();

        if (typeof eikenTestResultId != 'undefined' && eikenTestResultId != '' && !listIdOld[keyName + eikenTestResultId]) {
            window.location.href = MAPPING_EXAM_RESULT.actionEikenMappingResult + '/year/' + year + '/kai/' + kai;

            return false;
        }

        if (typeof listIdOld != 'undefined' && !$.isEmptyObject(listIdOld) && listIdOld != null) {
            $.each(Object.keys(listIdOld), function (index, element) {
                if (element == keyName + eikenTestResultId) {
                    curentIndex = index;
                }
                count = index;
            });
        }

        if (curentIndex == 0) {
            $('#previoustPupil').addClass('disabled').attr('disabled', 'disabled').removeClass('tooltip-button');
        }
        if (curentIndex == count) {
            $('#nextPupil').addClass('disabled').attr('disabled', 'disabled').removeClass('tooltip-button');
        }
        if (typeof listIdOld != 'undefined' && !$.isEmptyObject(listIdOld) && listIdOld != null) {
            $.each(listIdOld, function (index) {
                $('#' + listIdOld[index]).prop('checked', true);
            });
        }
        else {
            $('input[name="id[]"]').prop('checked', false);
        }
        MAPPING_EXAM_RESULT.checkAll();
        $("#selectAll").click(function () {
            $('input[name="id[]"]').prop('checked', $(this).prop("checked"));
            MAPPING_EXAM_RESULT.sessionStorageEikenResult();
            MAPPING_EXAM_RESULT.disabledConfirm();
        });
        $('input[name="id[]"]').click(function () {
            MAPPING_EXAM_RESULT.updateSessionId(this);
            MAPPING_EXAM_RESULT.checkAll();
        });
        $('#btnConfirm').click(function () {
            listIdOld = JSON.parse(sessionStorage.getItem(sessionNameMapping));
            if (typeof listIdOld != 'undefined' && !$.isEmptyObject(listIdOld) && listIdOld != null) {
                MAPPING_EXAM_RESULT.saveConfirm(listIdOld);
            }
        });
        $('#btnDetails').click(function () {
            listIdOld = JSON.parse(sessionStorage.getItem(sessionNameMapping));
            if (typeof listIdOld != 'undefined' && !$.isEmptyObject(listIdOld) && listIdOld != null) {
                MAPPING_EXAM_RESULT.editConfirmDetails(listIdOld);
            }
        });
        $('#search').click(function () {
            MAPPING_EXAM_RESULT.leadingPupilListByParams();
        });
        $('#calendarBirthday').click(function () {
            $('#birthday').datepicker('show');
        });
        $('#btnSearch').click(function () {
            MAPPING_EXAM_RESULT.clearSession();
            $('#formSearch').attr('action', MAPPING_EXAM_RESULT.actionSearch).submit();
        });
        $('#btnCancel').click(function () {
            MAPPING_EXAM_RESULT.clearSession();
            window.location.href = MAPPING_EXAM_RESULT.actionEikenMappingResult + '/year/' + year + '/kai/' + kai;
        });        
    },
    removeMapping: function () {
        var html = '<tr><td colspan="8" class="text-left">紐付け（候補）データがありません。</td></tr>';
        $('#tablePupil tbody').html(html);
        $('#pupilId').val(0);
        $('#typeMapping').val(1);
    },
    editConfirmDetails: function (listIdOld) {
        var year = $("#year").val();
        if (!listIdOld) {
            ERROR_MESSAGE.show(messages.notCheckbox);
            return;
        }
        if (typeof pupilByYear == 'undefined' || pupilByYear != 1) {
            CONFIRM_MESSAGE.show(messages.registerStudentlistFirstMSG57, function () {
                window.location.href = MAPPING_EXAM_RESULT.studentList;
            }, null, null, messages.btnStudentList, null);
            return false;
        }
        window.location.href = MAPPING_EXAM_RESULT.actionDetails + '/' + listIdOld[Object.keys(listIdOld)[0]];
    },
    saveConfirm: function (listIdOld) {
        var year = $("#year").val();
        if (!listIdOld) {
            ERROR_MESSAGE.show(messages.notCheckbox);
            return;
        }
        if (typeof pupilByYear == 'undefined' || pupilByYear != 1) {
            CONFIRM_MESSAGE.show(messages.registerStudentlistFirstMSG57, function () {
                window.location.href = MAPPING_EXAM_RESULT.studentList;
            }, null, null, messages.btnStudentList, null);
            return false;
        }
        $.ajax({
            type: 'POST',
            url: MAPPING_EXAM_RESULT.confirmStatus,
            data: {listIdOld: listIdOld, year: year},
            dataType: "json",
            success: function (data) {
                if (data == saveDataSuccess) {
                    ERROR_MESSAGE.show(messages.eikenResultConfirmSuccessMEG58, function () {
                        MAPPING_EXAM_RESULT.clearSession();
                        window.location.reload(true);
                    });
                    return;
                }
                ERROR_MESSAGE.show(messages.noResultIsFoundMSG12, function () {
                    MAPPING_EXAM_RESULT.clearSession();
                    window.location.reload(true);
                });
            },
            error: function () {
                $('#loadingModal').modal('hide');
            }
        });
    },
    sessionStorageEikenResult: function () {
        var listIdNew = {};
        var listIdOld = JSON.parse(sessionStorage.getItem(sessionNameMapping));
        if ($.isEmptyObject(listIdOld)) {
            listIdOld = {};
        }
        $('input[name="id[]"]').each(function (index, element) {
            if (element.checked) {
                listIdNew[keyName + element.value] = element.value;
            }
            else {
                delete listIdNew[keyName + element.value];
                delete listIdOld[keyName + element.value];
            }
        });

        sessionStorage.setItem(sessionNameMapping, JSON.stringify($.extend(listIdNew, listIdOld)));
    },
    updateSessionId: function (elementId) {
        var listIdOld = JSON.parse(sessionStorage.getItem(sessionNameMapping));
        if ($.isEmptyObject(listIdOld)) {
            listIdOld = {};
        }
        if (elementId.checked) {
            listIdOld[keyName + elementId.value] = elementId.value;
        }
        else {
            delete listIdOld[keyName + elementId.value];
        }
        sessionStorage.setItem(sessionNameMapping, JSON.stringify(listIdOld));
    },
    callGetClass: function () {
        $('#schoolYearId').change(function () {
            MAPPING_EXAM_RESULT.getListClass();
        });
    },
    callGetClassByYear: function () {
        $('#schoolYearId').change(function () {
            MAPPING_EXAM_RESULT.getListClassByYear();
        });
        $('#yearId').change(function () {
            MAPPING_EXAM_RESULT.getListClassByYear();
        });
    },
    getListClass: function () {
        var schoolYearId = $("#schoolYearId").val();
        var year = $("#year").val();
        $.ajax({
            type: 'POST',
            url: MAPPING_EXAM_RESULT.ajaxGetListClass,
            data: {schoolYearId: schoolYearId, year: year},
            dataType: "json",
            success: function (data) {
                var html = '<option value=""></option>';
                if (data.classj) {
                    $.each(data.classj, function (index, value) {
                        html += '<option value="' + value.id + '">' + MAPPING_EXAM_RESULT.htmlencode(value.className) + '</option>';
                    });
                }
                $('#classId').html(html);

            },
            error: function () {
                $('#loadingModal').modal('hide');
            }
        });
    },
    getListClassByYear: function () {
        var schoolYearId = $("#schoolYearId").val();
        var year = $("#yearId").val();
        $.ajax({
            type: 'POST',
            url: MAPPING_EXAM_RESULT.ajaxGetListClass,
            data: {schoolYearId: schoolYearId, year: year},
            dataType: "json",
            success: function (data) {
                var html = '<option value=""></option>';
                if (data.classj) {
                    $.each(data.classj, function (index, value) {
                        html += '<option value="' + value.id + '">' + MAPPING_EXAM_RESULT.htmlencode(value.className) + '</option>';
                    });
                }
                $('#classId').html(html);

            },
            error: function () {
                $('#loadingModal').modal('hide');
            }
        });
    },
    htmlencode: function (str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
    },
    checkAll: function () {
        if ($('input[name="id[]"]').length != 0 && $('input[name="id[]"]').length == $('input[name="id[]"]:checked').length) {
            $("#selectAll").prop('checked', true);
        } else {
            $("#selectAll").prop('checked', false);
        }
        MAPPING_EXAM_RESULT.disabledConfirm();
    },
    leadingPupilListByParams: function () {
        var schoolYearId = $("#schoolYearId").val();
        var classId = $("#classId").val();
        var year = $("#yearId").val();
        var birthday = $("#birthday").val();
        var nameKana = $("#nameKana").val();
        var nameKanji = $("#nameKanji").val();
        $.ajax({
            type: 'POST',
            url: MAPPING_EXAM_RESULT.actionGetStudents,
            data: {schoolYearId: schoolYearId, classId: classId, year: year, birthday: birthday, nameKana: nameKana, nameKanji:nameKanji},
            dataType: "html",
            success: function (html) {
                $('#leadingPupilListByParams tbody').html(html);
            },
            error: function () {
                $('#loadingModal').modal('hide');
            }
        });
    },
    changePupil: function (pupilId) {
        var html = '';
        var element = dataPupil[pupilId];
        var birthday = '';
        if(element.birthday){
            birthday = element.birthday.date.substring(0, 10).replace('-', '/').replace('-', '/');
        }
        
        html = '<tr class="backgroup-color-white"><td>' + element.nameKanji + '</td><td>' + element.nameKana + '</td><td>' + birthday + '</td><td>' + element.year + '</td><td>' + element.displayName + '</td><td>' + element.className + '</td><td class="text-right">' + (element.number != null ? element.number : '') + '</td><td class="text-center"><button onclick="MAPPING_EXAM_RESULT.removeMapping()" class="btn" id="removeMapping" type="button">選択解除</button></td></tr>';
        $('#tablePupil tbody').html(html);
        $('#pupilId').val(pupilId);
        $('#typeMapping').val(0);
    },
    previoustPupil: function (eikenTestResultId) {
        var pupilId = $('#pupilId').val();
        MAPPING_EXAM_RESULT.callSaveData(eikenTestResultId, pupilId, 'previoust');
    },
    nextPupil: function (eikenTestResultId) {
        var pupilId = $('#pupilId').val();
        MAPPING_EXAM_RESULT.callSaveData(eikenTestResultId, pupilId, 'next');
    },
    savePupil: function (eikenTestResultId) {
        var pupilId = $('#pupilId').val();
        MAPPING_EXAM_RESULT.callSaveData(eikenTestResultId, pupilId, 'save');
    },
    callSaveData: function (eikenTestResultId, pupilId, type) {
        var year = $("#year").val();
        var kai = $("#kai").val();
        var typeMapping = $('#typeMapping').val();
        var listIdOld = JSON.parse(sessionStorage.getItem(sessionNameMapping));
        var indexId = 0;
        var count = 0;
        if (listIdOld) {
            $.each(Object.keys(listIdOld), function (index, element) {
                if (element == keyName + eikenTestResultId) {
                    indexId = index;
                }
                count = index;
            });
        }
        if (type == 'previoust' && indexId == 0) {
            return false;
        }
        if (type == 'next' && count == indexId) {
            return false;
        }

        $.ajax({
            type: 'POST',
            url: MAPPING_EXAM_RESULT.actionCallSaveNextPupil,
            data: {eikenTestResultId: eikenTestResultId, pupilId: pupilId, type: type, typeMapping: typeMapping},
            dataType: "json",
            success: function (data) {
                ERROR_MESSAGE.show(messages.eikenResultConfirmSuccessMEG58, function () {
                    if (data.type == 'next') {
                        window.location.href = MAPPING_EXAM_RESULT.actionDetails + '/' + listIdOld[Object.keys(listIdOld)[indexId + 1]];
                    }
                    if (data.type == 'previoust') {
                        window.location.href = MAPPING_EXAM_RESULT.actionDetails + '/' + listIdOld[Object.keys(listIdOld)[indexId - 1]];
                    }
                    if (data.type == 'save') {
                        sessionStorage.removeItem(sessionNameMapping);
                        window.location.href = MAPPING_EXAM_RESULT.actionEikenMappingResult + '/year/' + year + '/kai/' + kai;
                    }
                });
            },
            error: function () {
                $('#loadingModal').modal('hide');
            }
        });
    },
    clearSession: function () {
        sessionStorage.removeItem(sessionNameMapping);
        $('input[type="checkbox"]').prop('checked', false);
    },
    disabledConfirm: function () {
        if ($.isEmptyObject(JSON.parse(sessionStorage.getItem(sessionNameMapping)))) {
            $('#btnDetails').addClass('disabled').attr('disabled', 'disabled');
            $('#btnConfirm').addClass('disabled').attr('disabled', 'disabled');
        } else {
            $('#btnDetails').removeClass('disabled').removeAttr('disabled');
            $('#btnConfirm').removeClass('disabled').removeAttr('disabled');
        }
    }
}