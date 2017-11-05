var EIKEN_HISTORY_CONFIRM_RESULT = {
        urlMappingEikenAction : COMMON.baseUrl + 'history/eiken/mapping-eiken-exam-result',
        urlMappingResult : COMMON.baseUrl + 'history/eiken/mapping-result',
        urlSaveEikenAction : COMMON.baseUrl + 'history/eiken/save-eiken-exam-result',
        urlExamResultList : COMMON.baseUrl + 'history/eiken/exam-result',
        processBar:function(){
            var progressBar = '<div class="row"><div class="col-lg-12 row"><div class="progress"><div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="background: #e70012;"></div></div><div class="col-lg-12"><span class="pull-left" style="color: #e70012;">0%</span><span class="pull-right"  style="color: #e70012;">100%</span></div></div>';
            EIKEN_HISTORY_CONFIRM_RESULT.creatPopup('<p>お待ちください。。。</p> ' + progressBar,'英検検定結果がマッピング中です。',0);
            $.ajax({
                type : 'GET',
                url : EIKEN_HISTORY_CONFIRM_RESULT.urlMappingEikenAction,
                data : {},
                success : function(data) {
                },
                error : function(data) {
                }
            });
            EIKEN_HISTORY_CONFIRM_RESULT.runProcessBar();
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
        runProcessBar: function(){
            EIKEN_HISTORY_CONFIRM_RESULT.progress();
        },
        progress: function(){
            var val = parseInt($('#mapping-rate').text()) || 0;
             
            $('#mapping-rate').text(val+10+'%');
            $('.progress-bar').css('width', val + 10 + '%');

            if ( val < 90 ) {
                setTimeout( EIKEN_HISTORY_CONFIRM_RESULT.progress, 200 );
            } else {
                EIKEN_HISTORY_CONFIRM_RESULT.closeProcessBar();
                //Redirect to UC
                window.location.href = EIKEN_HISTORY_CONFIRM_RESULT.urlMappingResult + '/year/' + yearKaiValue.year + '/kai/' + yearKaiValue.kai;
            }
            
        },
        closeProcessBar: function(){
            $('#processBar').modal('hide');
        },
        saveData: function(){
            window.location.href = EIKEN_HISTORY_CONFIRM_RESULT.urlSaveEikenAction;
        },
        cancelSaveExamResult: function() {
         
        CONFIRM_MESSAGE.show(jsMessages.SHOW_POPUP_MSG_CONFIRM_EXAM_RESULT, 
            function() {
                 window.location.href = EIKEN_HISTORY_CONFIRM_RESULT.urlExamResultList;
            },
            function() {
                
            }, '確認', 'OK', '戻る');
        }
}