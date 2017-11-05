var IBA_HISTORY_CONFIRM_RESULT = {

	urlSave : COMMON.baseUrl + "history/iba/save-exam-result",
	urlListExam : COMMON.baseUrl + "history/eiken/exam-result",

	init : function() {
	},
	confirmOK : function(jisshiId, examType, applyId) {
		$.ajax({
			type : 'POST',
			url : IBA_HISTORY_CONFIRM_RESULT.urlSave,
			data : {
				jisshiId: jisshiId,
				examType: examType,
				applyId : applyId
			},
			success : function(data) {
				window.location.href = IBA_HISTORY_CONFIRM_RESULT.urlListExam;
			},
			error : function() {
			}
		});
	},
	confirmMapping : function(applyId, year) {
		$.ajax({
			type : 'POST',
			url : IBA_HISTORY_CONFIRM_RESULT.urlSave,
			data : {
				applyId : applyId,
				examType: examType,
				applyId : applyId
			},
			success : function(data) {
				EIKEN_EXAM_RESULT.processBar('IBA', year);
			},
			error : function() {
			}
		});
	},
        cancelSaveExamResult: function() {
         
            CONFIRM_MESSAGE.show(jsMessages.SHOW_POPUP_MSG_CONFIRM_EXAM_RESULT, 
                function() {
                     window.location.href = IBA_HISTORY_CONFIRM_RESULT.urlListExam;
                },
                function() {

                }, '確認', 'OK', '戻る');
        }
    }
$("document").ready(function() {
	IBA_HISTORY_CONFIRM_RESULT.init();
});