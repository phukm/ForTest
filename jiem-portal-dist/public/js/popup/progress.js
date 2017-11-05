/**
* @author FPT-DuongTD
* Show Popup progress (0 -> 100%)
**/
var PROGRESS = {
		stopFlag: false,
		startFlag: false,
                step: 10,
                rangeWait: 90,
                rangeMax: 100,
		stopCallBack: function(){},
		
		init: function() {
			PROGRESS.stopFlag = false;
			PROGRESS.startFlag = false;
			var popup='';
	        popup += '<div class="modal fade" id="processBar"  data-backdrop="static" >';
	        	popup += '<div class="modal-dialog common-dialog">';
	        		popup += '<div class="modal-content">';
	        			popup += '<div class="modal-header">';
	        				popup += '<h4 class="modal-title">英検試験結果と生徒名簿情報を突合しています</h4>';
	        			popup += '</div>';//end modal-header
	        			popup += '<div class="modal-body">';
	        				popup += ' <p>' + PROGRESS.processBar() + '</p>';
	        				popup += ' <p id="percentage" style="text-align: center;font-weight: bold; color: #e70012;">-'+PROGRESS.step+'%</p>';
	        
	        			popup += '</div>';//end modal-body
	        		popup += '</div>';//end modal-content
	        	popup += '</div>';//end modal-dialog
	        popup += '</div>';//end modal
	        if($('#processBar').length){
	            $('#processBar').remove()
	        }
	        $('body').append(popup);
		},
		show: function() {
			if ($('#processBar').is(':hidden')){
				$('#processBar').remove();
			}
			if (!$('#processBar').length) {
				PROGRESS.init();
				$('#processBar').modal('show');
	        }
		},
		processBar:function(){
	        var progressBar = '<p>処理が完了するまでお待ちください。</p>'
	        	progressBar += '<div class="row"><div class="col-lg-12 row"><div class="progress"><div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="background: #e70012;"></div></div><div class="col-lg-12"><span class="pull-left" style="color: #e70012;">0%</span><span class="pull-right"  style="color: #e70012;">100%</span></div></div>';
	        return progressBar;
	    },
		run: function(startCallBack, stopCallBAck, delayTimeOUt) {
			if(typeof delayTimeOUt == 'undefined'){
				delayTimeOUt = 1000;
			}
			if(typeof stopCallBAck != 'undefined'){
        		PROGRESS.stopCallBack = stopCallBAck;
			}
			PROGRESS.show();
			if(typeof startCallBack != 'undefined'){
				startCallBack();
			}
			if ($('#processBar').length) {
				var currentPercentage = parseInt($('#percentage').text()) || 0;
                                var setPercentage = 0;
				 if ( currentPercentage < 90 ) {
                                     setPercentage = currentPercentage + PROGRESS.step;
                                     $('.progress-bar').css('width', setPercentage + '%');
                                     $('#percentage').text(setPercentage + '%');
                                    if(PROGRESS.stopFlag) {
                                        setTimeout( PROGRESS.run, 100 );// nếu set stopFlag == true, example khi call ajax scucess thì progress sẽ chạy nhanh hơn bình thường để finish :)
                                        return;
                                    }
                                    setTimeout( PROGRESS.run, delayTimeOUt ); // có thể sẽ phải set số này lâu hơn để đảm bảo tiến tình chạy ngầm của mình đã finish
                                } else {
                                    setPercentage = currentPercentage + 1;
                                    if(setPercentage >= PROGRESS.rangeMax)
                                        setPercentage = PROGRESS.rangeMax-1;
                                    $('.progress-bar').css('width', setPercentage + '%');
                                    $('#percentage').text(setPercentage + '%');
                                    if(PROGRESS.stopFlag){
                                        PROGRESS.close();
                                        if(typeof PROGRESS.stopCallBack != 'undefined'){
                                            PROGRESS.stopCallBack();
                                        }
                                    }else{
                                        setTimeout( PROGRESS.run, delayTimeOUt );
                                    }
                                }
                        }
		},
		close: function (){
			if ($('#processBar').length) {
				$('#processBar').modal('hide');
	        }
		},
		stop: function() {
			PROGRESS.stopFlag = true;
		}
};