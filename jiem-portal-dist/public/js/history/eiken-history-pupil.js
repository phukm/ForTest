var EIKEN_HISTORY_PUPIL = {
    init : function() {
    	$('#btn-view').focus();
    }
}

$(document).ready(function () {
    EIKEN_HISTORY_PUPIL.init();
    if (noRecordExcel.length > 0) {
        ERROR_MESSAGE.show(noRecordExcel);
    }
});