var EIKEN_MAPPING_RESULT = {
        states: listPupil,
        checkExists: true,
        loadPageFlag: false,
        elementTable: null,
        checkIE8:false,
        checkSltData:true,
        linkBack: COMMON.baseUrl + "history/eiken/exam-result",
        linkMapping: COMMON.baseUrl + "history/eiken/mapping-result",
        linkMappingIBA: COMMON.baseUrl + "history/iba/mapping-result",
        linkKeepSession: COMMON.baseUrl + "history/eiken/find-eiken",
        saveMapping: COMMON.baseUrl + "history/eiken/save-maping-eiken",
        cancelMapping: COMMON.baseUrl + "history/eiken/clear-session",
        init: function(){
            EIKEN_MAPPING_RESULT.handlerTableScroll();
            EIKEN_MAPPING_RESULT.autoFocus();
            EIKEN_MAPPING_RESULT.checkExists==true;
            EIKEN_MAPPING_RESULT.checkIE();
            $(document).on('click','.nav-confirm-result li',function(){
                EIKEN_MAPPING_RESULT.autoFocus($(this).find('a').attr("href"));
            });
            $(window).scroll(function(){
                 if(EIKEN_MAPPING_RESULT.checkIE8==true){
                        var height =  EIKEN_MAPPING_RESULT.elementTable.clientHeight;
                        var top = EIKEN_MAPPING_RESULT.elementTable.offsetTop - document.documentElement.scrollTop;
                        var bottom = document.documentElement.scrollTop - EIKEN_MAPPING_RESULT.elementTable.offsetTop;
                        if(top<0 && bottom<height&&bottom>200 && -top>242){
                            $('table thead').css('top',(-top)-242);
                        }else {
                            $('table thead').css('top',0);
                        }
                    }else{
                        var demension = EIKEN_MAPPING_RESULT.getDimensions(EIKEN_MAPPING_RESULT.elementTable);
                        if(demension.top<0 && demension.bottom<demension.height){
                            top = demension.top*(-1);
                            if(demension.bottom>200){
                                $('table thead').css('top',-demension.top);
                            }
                        }else {
                            $('table thead').css('top',0);
                        }
                    }

            });
            if($('.mapping-error-autocomplete').length){
                $(".mapping-error-autocomplete" ).autocomplete({
                  
                  source: function(req, response) {
                	    var results = $.ui.autocomplete.filter(EIKEN_MAPPING_RESULT.states, req.term);
                	    response(results.slice(0, 20));//for getting 20 results
            	   },
            	   //appendTo: ".data-result-exam",
                  select: function (event, ui) {
                      var $this = $(this);
                      $this.val(ui.item.value);
                      var elementFollow = '#'+$this.data('follow');
                      var elementFocus = '#'+ $this.data('focus');
                      //MSG18 - kiem tra theo msg18
                      var data = EIKEN_MAPPING_RESULT.checkSelectedData($(elementFocus),ui.item);
                      //neu check false bao msg18
                      // neu check true thi hien auto fill
                      if(EIKEN_MAPPING_RESULT.checkSltData == true){
                          EIKEN_MAPPING_RESULT.autoFill(elementFollow, elementFocus, ui.item);
                          EIKEN_MAPPING_RESULT.ajaxKeepSession($this.data('id'),ui.item);
                          
                      } else {
                          EIKEN_MAPPING_RESULT.showMSG18(data,elementFollow, elementFocus, ui.item);
                      }
                      
                      return false;
                  },
                  change: EIKEN_MAPPING_RESULT.onAutocompleteChange,
                  close: EIKEN_MAPPING_RESULT.onAutocompleteChange
             });
            }
            //re format height of 2 table
            $('#table-left tbody tr').each(function(index){  
                 $('#table-right tbody').find('tr').eq(index).css('height', $(this).height() + 'px');
            });
            $(document).on('click','.nav-confirm-result',function(){
            	 $('#table-left-success tbody tr').each(function(index){ 
                     $('#table-right-success tbody').find('tr').eq(index).css('height', $(this).height() + 'px');
                });
            });

        },
        onAutocompleteChange: function(event, ui){
            var $this = $(this);
            if($this.is(":focus")==false){
                if(EIKEN_MAPPING_RESULT.popupOpened) return;
                EIKEN_MAPPING_RESULT.popupOpened = true;
                if(EIKEN_MAPPING_RESULT.checkBlank($this)==true){
                    // blank - You must input a name.
                    ERROR_MESSAGE.popupMessage('氏名を入力してください。',
                            function(){
                                $this.val($this.attr('data-value'));
                                EIKEN_MAPPING_RESULT.popupOpened = false;
                            }
                            ,'エラーメッセージ');
                    return;
                }
                EIKEN_MAPPING_RESULT.checkNameExists($this.val());
                if(EIKEN_MAPPING_RESULT.checkExists==false){ 
                    // no exists - No pupils found.
                    ERROR_MESSAGE.popupMessage('生徒が見つかりませんでした。',
                            function(){
                                $this.val($this.attr('data-value'));
                                EIKEN_MAPPING_RESULT.popupOpened = false;
                            }
                            ,'エラーメッセージ');
                    return;
                } else {
                	EIKEN_MAPPING_RESULT.popupOpened = false;
                }
                EIKEN_MAPPING_RESULT.setNameExists(true);
            }
        },
        
        backValue: function(element){
            element.val(element.data('value'));
            return '';
        },
        ajaxKeepSession: function(eikenId,item){
        	var data = {};
        	
            if(item.birthday!==null){
                data.birthday = $.datepicker.formatDate("yy/m/d", new Date(item.birthday.date.split(" ")[0]));
            }
            
            data.eikenId = eikenId;
            data.pupilId = item.id ;
            data.nameKanji = item.firstNameKanji + item.lastNameKanji;
            data.nameKana = item.firstNameKana + item.lastNameKana;
            data.number = item.number;
            data.orgSchoolYearId = item.orgSchoolYearId;
            data.orgSchoolYearName = item.orgSchoolYearName;
            data.classId = item.classId;
            data.className = item.className;
            
            $.ajax({
                type:"POST",
                url: EIKEN_MAPPING_RESULT.linkKeepSession,
                data:data,
                dataType : 'html',
                success:function(data){
                    return;
                },
                error:function(){
                }
            });
        },
        ajaxSaveMapping: function(){
            $.ajax({
                type:"POST",
                url: EIKEN_MAPPING_RESULT.saveMapping,
                dataType : 'html',
                success:function(data){
                    window.location.replace(EIKEN_MAPPING_RESULT.linkBack);
                    return true;
                },
                error:function(){
                    return false;
                }
            });
        },
        autoFill: function(elementFollow, elementFocus, item){
        	$(elementFocus+' .jname input').attr('data-value', item.firstNameKanji + item.lastNameKanji);
        	$(elementFocus+' .jname input').attr('value', item.firstNameKanji + item.lastNameKanji);
             $(elementFollow+' .jname').text(item.firstNameKanji+item.lastNameKanji);
             $(elementFollow+' .jnameKana').text(item.firstNameKana+item.lastNameKana);
             if(item.birthday!==null){
                 strBirthday = item.birthday.date.split(" ");
                 $(elementFollow+' .jbirth-day').text($.datepicker.formatDate("yy/m/d", new Date(strBirthday[0])));
             }else{
                 $(elementFollow+' .jbirth-day').text('');
             }
             if(item.orgSchoolYearName !== null){
            	 $(elementFollow+' .jschool-year').text(item.orgSchoolYearName);
             }
             if(item.className !== null){
            	 $(elementFollow+' .jkumi').text(item.className);
             }
             if(item.number !== null){
            	 $(elementFollow+' .jnumber').text(item.number);
             }
              
        },
        checkSelectedData:function(apiData,pupilData){
            var data = [];
            //Edit by: minhbn1, always show all, don't check equal string
            //check Kanji - get da from data-value
            if(apiData.children('.jname').children('input').attr('data-value') !== ($.trim(pupilData.firstNameKanji)+$.trim(pupilData.lastNameKanji))){
                EIKEN_MAPPING_RESULT.checkSltData = false;
            } 
            data.push({id:"NameKanji",message:'氏名(漢字): '+ COMMON.escapeHtml($.trim(pupilData.firstNameKanji) + $.trim(pupilData.lastNameKanji))});
            //check Kantana
            var strNameKana = '';
            if(pupilData.nameKantana !== 'undefined'){
                    strNameKana = $.trim(pupilData.firstNameKana) + $.trim(pupilData.lastNameKana);
            }
            if($.trim(apiData.children('.jnameKana').text()) !== ($.trim(pupilData.firstNameKana)+$.trim(pupilData.lastNameKana))){
                EIKEN_MAPPING_RESULT.checkSltData = false;
            }
            data.push({id:"NameKana",message:'氏名(カナ): '+ COMMON.escapeHtml(strNameKana)});
            //check birthday
            if(pupilData.birthday==null){
                var birthDayPupil = '';
            }else {
                var birthDayPupil = $.datepicker.formatDate("yy/m/d", new Date(pupilData.birthday.date.split(" ")[0]));
            }
            if($.trim(apiData.children('.jbirth-day').text()) !== $.trim(birthDayPupil)){
                EIKEN_MAPPING_RESULT.checkSltData = false;
            }    
            data.push({id:"Birthday",message:'生年月日: ' + birthDayPupil});
            
            //check school year
            var strSchoolYear = '';
            if(pupilData.orgSchoolYearName !== 'undefined'){
                    strSchoolYear = pupilData.orgSchoolYearName;
            }
            if(apiData.children('.jschool-year').text() !== pupilData.orgSchoolYearName){
                EIKEN_MAPPING_RESULT.checkSltData = false;
            }
            data.push({id:"SchoolYear",message:'学年: '+ COMMON.escapeHtml(strSchoolYear)});
            //check class
            var strKumi = '';
            if(pupilData.className !== 'undefined'){
                strKumi = pupilData.className;
            }
            if(apiData.children('.jkumi').text() !== pupilData.className){
                EIKEN_MAPPING_RESULT.checkSltData = false;
                data.push({id:"Class",message:'クラス: '+ COMMON.escapeHtml(strKumi)});
            }
            var strNumber = '';
            if(pupilData.number !== 'undefined'){
                strNumber = $.trim(pupilData.number);
            }
            data.push({id:'number',message:'番号: '+COMMON.escapeHtml(strNumber)});
            //
            return data;
        },
        checkIE:function(){
            if(document.addEventListener){
                EIKEN_MAPPING_RESULT.checkIE8 = false;
            }else{
                EIKEN_MAPPING_RESULT.checkIE8 = true;
            }

        },
        setNameExists: function(value){
            EIKEN_MAPPING_RESULT.checkExists= value;
        },
        checkNameExists: function(value){
            var result = false;
            jQuery.grep(EIKEN_MAPPING_RESULT.states, function(el) {
                if(value==el.value) result = true;
            });
            EIKEN_MAPPING_RESULT.setNameExists(result);
        },
        checkBlank: function(element){
            if(element.val()==""){
                return true;
            }
            return false;
            
        },
        getDimensions: function(element){
            var rect = element.getBoundingClientRect();
            return rect;
        },
        mappingSave: function(){
            EIKEN_MAPPING_RESULT.ajaxSaveMapping();
        },
        mappingCancel: function(){
			$.ajax({
				type:"POST",
				url: EIKEN_MAPPING_RESULT.cancelMapping,
				dataType : 'html',
				success:function(data){
					window.location.replace(EIKEN_MAPPING_RESULT.linkBack);
					return true;
				},
				error:function(){
					return false;
				}
			});
		},
        showMSG1: function(){
            ERROR_MESSAGE.show('必須入力項目です');
        },
        showMSG18: function(data, elementFollow, elementFocus, item){
            var msg = EIKEN_MAPPING_RESULT.convertMsg18(data);
            EIKEN_MAPPING_RESULT.popupOpened = true;
            CONFIRM_MESSAGE.show(msg, function(){
                                            EIKEN_MAPPING_RESULT.autoFill(elementFollow, elementFocus, item);
                                            EIKEN_MAPPING_RESULT.ajaxKeepSession($('#input-'+$(elementFocus).attr('id')).data('id'),item);
                                            EIKEN_MAPPING_RESULT.popupOpened = false;
                                        }, function(){
                                            EIKEN_MAPPING_RESULT.backValue($('#input-'+$(elementFocus).attr('id')));
                                            //$('#input-'+$(elementFocus).attr('id')).val($('#input-'+$(elementFocus).attr('id')).data('value'));
                                            EIKEN_MAPPING_RESULT.popupOpened = false;
                                        } ,'選択された生徒の情報は以下の通りとなります。突合せを行ないますか？','OK','キャンセル');
        },
        convertMsg18: function(data) {
            var msg = '';// =;
            msg += '<ul>';
            //check empty
            if(jQuery.isEmptyObject(data)){
                
            }else{
                $.each(data, function(index, value) {
                      msg +='<li>'+value.message+'</li>';
                 });
            }
            msg += '</ul>';
            return msg;
        },
        autoFocus: function(e){
            //$(e+' .table-result-exam input:first').focus();
            if($('#table-left').length){
            	EIKEN_MAPPING_RESULT.elementTable = $('#table-left')[0];
			}else {
				EIKEN_MAPPING_RESULT.elementTable = $('#table-left-success')[0];
			}
			
        },
        processBar:function(exam_type){
            if(exam_type=='iba'){
                EIKEN_MAPPING_RESULT.linkMapping = EIKEN_MAPPING_RESULT.linkMappingIBA;
            }
            var progressBar = '<div class="row"><div class="col-lg-12 row"><div class="progress"><div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="background: #e70012;"></div></div><div class="col-lg-12"><span class="pull-left" style="color: #e70012;">0%</span><span class="pull-right"  style="color: #e70012;">100%</span></div></div>';
            EIKEN_MAPPING_RESULT.creatPopup('<p>お待ちください。。。</p> ' + progressBar,'英検検定結果がマッピング中です。',0);
            EIKEN_MAPPING_RESULT.runProcessBar();
        },
        runProcessBar: function(){
            EIKEN_MAPPING_RESULT.progress();
        },
        progress: function(){
            var val = parseInt($('#mapping-rate').text()) || 0;
             
            $('#mapping-rate').text(val+10+'%');
            $('.progress-bar').css('width', val + 10 + '%');

            if ( val < 100 ) {
                setTimeout( EIKEN_MAPPING_RESULT.progress, 500 );
            }else{
                EIKEN_MAPPING_RESULT.closeProcessBar();
                //Redirect to UC
                window.location.href= EIKEN_MAPPING_RESULT.linkMapping;
            }
            
        },
        closeProcessBar: function(){
            $('#processBar').modal('hide');
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
        handlerTableScroll: function(){            
            var isFirst = false;
            $('.jname input').on('keydown mousedown', function (e) {
                var currentLength = $(this).val().length;
                var currentCursorPostion = $(this).getCursorPosition();
                if (currentCursorPostion === currentLength && e.keyCode === 39)
                {
                    isFirst = false;
                    if (/chrom(e|ium)/.test(navigator.userAgent.toLowerCase())) {
                        $('#data-result-exam').on("keydown", function (e) {
                            // space and arrow keys
                            if ([32, 37, 38, 39, 40].indexOf(e.keyCode) > -1) {
                                e.preventDefault();
                            }
                        }, false);
                    }
                }
                if (e.keyCode === 37 && !isFirst || e.which === 1 || e.keyCode === 8)
                {        
                    $('#data-result-exam').unbind('keydown');
                    isFirst = true;
                }
            });
            
            $('.data-result-exam').on('scroll', function () {
                if ($(this).scrollLeft() > 110)
                {
                    $('.ui-autocomplete').css({'z-index': '-9999'});
                }
                else if($(this).scrollLeft() < 30)
                {
                    $('.ui-autocomplete').css({'z-index': '9999'});
                }
            });
        }
};

jQuery.fn.getCursorPosition = function () {
    if (this.lengh == 0)
        return -1;
    return $(this).getSelectionStart();
};
jQuery.fn.getSelectionStart = function () {
    if (this.lengh == 0)
        return -1;
    input = this[0];

    var pos = input.value.length;

    if (input.createTextRange) {
        var r = document.selection.createRange().duplicate();
        r.moveEnd('character', input.value.length);
        if (r.text == '')
            pos = input.value.length;
        pos = input.value.lastIndexOf(r.text);
    } else if (typeof (input.selectionStart) != "undefined")
        pos = input.selectionStart;

    return pos;
};