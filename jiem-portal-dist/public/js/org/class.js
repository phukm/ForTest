/**
*Show pupup error message
**/
var ORG_CLASS = {
                urlCheckShowMSGWarningClass: COMMON.baseUrl + 'org/class/is-first-character',
                urlCheckShowMSGWarningClassFroUpdate: COMMON.baseUrl + 'org/class/is-first-character-update',
		deleteclass: function(id) {
			$('#loaddatalist #' + id).remove();
			if ($('#loaddatalist tr').find('td').length){
				$('.hidens .btn-red').show();
			}else{
				ERROR_MESSAGE.clear();
				$('.hidens .btn-red').hide();
			}
		},
		clearform: function() {
			var n =COMMON.getCurrentYear();
			$('#numberofclass').val('');
			$("#year").children().removeAttr("selected");
			$("#year option[value='"+parseInt(n)+"']").prop('selected', true);
			$('#school_year_add').val('');
		},
		generateclass: function() {
			var arrayER = new Array();
			var $count = 0;
			var $yeartext = $('#year option:selected').html();
			var $yearvalue = $('#year option:selected').val();
			var $numberofclass = $('#numberofclass').val();
			var $schoolyearvalue = $(".addclass #school_year_add option:selected")
					.val();
			var $schoolyeartext = $(".addclass #school_year_add option:selected")
					.html();
			$count = parseInt($(".addclass #numberofclass").val());
			var rowCount = $('#counts tr').length - 1;
			if( $schoolyearvalue ==''){
				$('#school_year_add').addClass('error');
				arrayER.push({'id':'school_year_add', 'message':jsMessages.MSG001 })
			}else{
				$('#school_year_add').removeClass('error');
			}
			if( $yearvalue ==''){
				$('#year').addClass('error');
				arrayER.push({'id':'year', 'message':jsMessages.MSG001 })
			}else{
				$('#year').removeClass('error');
			}
			if( $.isNumeric($numberofclass) && ORG_CLASS.isInt($numberofclass)  && parseInt($numberofclass) > 99){
				$('#numberofclass').addClass('error');
				arrayER.push({'id':'numberofclass', 'message':jsMessages.MSG040 })
			}
			else if($.isNumeric($numberofclass) && ORG_CLASS.isInt($numberofclass) && parseInt($numberofclass) < 1 ){
				$('#numberofclass').addClass('error');
				arrayER.push({'id':'numberofclass', 'message':jsMessages.MSG014 })
			}
			else if($numberofclass ===''){
				$('#numberofclass').addClass('error');
				arrayER.push({'id':'numberofclass', 'message':jsMessages.MSG001 })
			}
			else if(!$.isNumeric($numberofclass) || ORG_CLASS.isInt($numberofclass) ==false){
				$('#numberofclass').addClass('error');
				arrayER.push({'id':'numberofclass', 'message':jsMessages.MSG014 })
			}else{
				$('#numberofclass').removeClass('error');
			}
			if(arrayER && arrayER.length != 0){
				ERROR_MESSAGE.show(arrayER, function(){}, 'inline');
			}else{
				var $htmls = '';
				for (i = 1; i < $count + 1; i++) {
					var idremove = rowCount + i;
					$htmls += '<tr id="' + idremove + '">';
					$htmls += '<td>' + $yeartext + '</td>';
					$htmls += '<td>' + $schoolyeartext + '</td>';
					$htmls += '<input class="form-control cl1" value="' + $yearvalue
							+ '" type="hidden" name="items[' + idremove + '][year]"/>';
					$htmls += '<input id="'+idremove+'year_date" class="form-control cl2" value="'
							+ $schoolyearvalue + '" type="hidden" name="items['
							+ idremove + '][year_date]"/>';
					$htmls += '<td><input id="'+idremove+'class" class="form-control cl3"  placeholder="" name="items['
							+ idremove + '][class_name]" onkeypress="return ORG_CLASS.runScript(event)"/></td>';
					$htmls += '<td><input id="'+idremove+'class_size" class="form-control cl4"  placeholder="" name="items['
							+ idremove + '][size_class]" onkeypress="return ORG_CLASS.runScript(event)"/></td>';
					$htmls += '<td><a href="javascript:void(0)" onclick="ORG_CLASS.deleteclass('
							+ idremove + ')" class="btn btn-views">削除</a></td>';
					$htmls += '</tr>';
				}
				$('.addclass #loaddatalist').prepend($htmls);
				$('.addclass .hidens').show();
				$('.hidens .btn-red').show();
				$('.addclass #counts').show();
				$('#loaddatalist tr.error').removeClass();
				ERROR_MESSAGE.clear();
			} 
		},
		runScript:function(e) {
		    if (e.keyCode == 13) {
		    	return false;
		    	//ORG_CLASS.edit();
		    }
		},
		isInt:function (n) {
			   return n % 1 === 0;
			},
		edit: function() {
			var flat = false;
			var er = false;
			var array = new Array();
			var arrayER = new Array();
			
			$('#loaddatalist tr').each(function(index, value) {
				        var array1 = new Array();
						var flat1 = true;
						var row = parseInt($(this).attr('id'));
						if (row) {
							var cl1 = ORG_CLASS.trim($(this).find(".cl1").val());
							var cl2 = ORG_CLASS.trim($(this).find(".cl2").val());
							var cl3 = ORG_CLASS.trim($(this).find(".cl3").val());
							var cl4 = ORG_CLASS.trim($(this).find(".cl4").val());
							if(cl3.length > 250){
								$(this).find(".cl3").addClass('error');
								er = true;
								arrayER.push({'id':row+'class', 'message':250+''+jsMessages.MSG0141 })							
								}
							if (cl3 == '') {
								$(this).find(".cl3").addClass('error');
								flat1 = false;
								arrayER.push({'id':row+'class', 'message':jsMessages.MSG001 })
							} else {
								if(er){
									$(this).find(".cl3").addClass('error');
									flat1 = false;
									arrayER.push({'id':row+'class', 'message':jsMessages.MSG046 })
								}else{
									$(this).find(".cl3").removeClass('error');
								}
							}
							if(cl4==''){
								$(this).val(0);
							}
							else if(!$.isNumeric(cl4) || ORG_CLASS.isInt(cl4) == false){
								$(this).find(".cl4").addClass('error');
									flat1 = false;
									arrayER.push({'id':row+'class_size', 'message':jsMessages.MSG014})
							}
							else if(cl4 > 9999999999){
								$(this).find(".cl4").addClass('error');
								flat1 = false;
								arrayER.push({'id':row+'class_size', 'message':10+''+jsMessages.MSG0141 })
							}
							else if(cl4 < 0 ){
								$(this).find(".cl4").addClass('error');
								flat1 = false;
								arrayER.push({'id':row+'class_size', 'message':jsMessages.MSG014})
							}
							else {
								$(this).find(".cl4").removeClass('error');
							}
							
							if (flat1 == true) {
								array1[0] = cl1;
								array1[1] = cl2;
								array1[2] = cl3;
								array1[3] = row;
								if(array1.length >0 && row !=0){
									array.push(array1);
								}
							} else {
								flat = true;
							}
						}

		});
		if(arrayER){
			 ERROR_MESSAGE.show(arrayER, function(){}, 'inline');
		}else{
			ERROR_MESSAGE.clear();
		}
		if (flat == false) {
			ORG_CLASS.checkduplicate(array, ORG_CLASS.submitform);
			return;
		}else{
			$('#loaddatalist tr').removeClass('error');
		}
		},
		isEmpty: function (obj) {
		    return Object.keys(obj).length === 0;
		},
		trim: function (data) {
			if(data){
				return data.replace(/^\s+|\s+$/gm,'');
			}
			return '';
		},
		submitform: function(result,dataClass) {
			if (result) {
				$('#loaddatalist tr input.cl4').removeClass('error');
				var arrayER = new Array();
				$('#loaddatalist tr').removeClass('error');
				$.each( result, function( key, value ) {
				    var $k = parseInt(key);
				    var id_err =  $('#loaddatalist #'+$k+' input.cl3').attr('id');
					$('#loaddatalist #'+$k+' input.cl3').addClass('error');
					arrayER.push({'id':id_err, 'message':'[' + $('#loaddatalist #'+$k+' input.cl3').val() + ']' + jsMessages.MSG046 })
				});
				ERROR_MESSAGE.show(arrayER, function(){}, 'inline');
				result = '';
			} 
			else {
				$('#loaddatalist tr input').removeClass('error');
				ERROR_MESSAGE.clear();
                                $.ajax({
                                        url :ORG_CLASS.urlCheckShowMSGWarningClass ,
                                        type : 'post',
                                        cache:false,
                                        data : {
                                                'data' : dataClass
                                        },
                                        dataType : 'json',
                                        success : function(data) {
                                            if (data.status === 0)
                                            {
                                                 CONFIRM_MESSAGE.show(data.MSG,function(){
                                                    $('#classmanager').submit();
                                                    },function(){ 
                                                        return false; 
                                                    }, null , data.textOK,  data.textCamcel);
                                                    $('#confirmPopupModal #btnCancelConfirm').addClass('btn-red');
                                                    $('#confirmPopupModal #btnAgreeConfirm').removeClass('btn-red');
                                            }else{
                                                $('#classmanager').submit();
                                            }
                                        }
                                });
			}
		}, 
		checkduplicate: function(datas, callback) {
			$.each( datas, function( key, value ) {
				if(typeof(value) === "undefined"){
					datas.splice(key,1)
				}
			})
                        
			$.ajax({
				url : 'checkduplicate',
				type : 'post',
				cache:false,
				data : {
					'data' : datas
				},
				dataType : 'json',
				success : function(data) {
					callback(data,datas);
				}
			});
		},
		getNumber: function (count) {
			var $count =0;
			 $count = parseInt($(".addclass #numberofclass").val());
			if($(".addclass #numberofclass").val() == ''){
				$count = 0;
			}
			if ($.isNumeric($count)) {
			
			} else {
				$(".addclass #numberofclass").val(0);
			}
			if (count === '+') {
				$count = $count + 1;
				if ($count > 99) {
					$count = 99;
				}
				if(isNaN($count)){
					$count ='';
				}
				$(".addclass #numberofclass").val($count);
			} else if (count == '-') {
				$count = $count - 1;
				if ($count < 1) {
					$count = 1;
				}
				if(isNaN($count)){
					$count ='';
				}
				$(".addclass #numberofclass").val($count);

			} else {
				return false;
			}
		}
};
var ORG_EDITCLASS = {
    edit: function () {
        //check exist ClassName
        var year = $('#year option:selected').val();
        var schoolYear = $('#school_year option:selected').val();
        var className = $('#classname').val();
        
        if(year == '' || schoolYear == '' || className == '' || year == null || schoolYear == null || className == null){
             $('#classmanager').submit();
        }else{
            $.ajax({
                method: "POST",
                url: "/org/class/check-duplicate-update",
                dataType: "json",
                data: $('#classmanager').serialize(),
                success: function (data) {
                    if (data.status)
                    {
                        ERROR_MESSAGE.inlineMessage(data.msg);
                    }
                else
                    {
                        ERROR_MESSAGE.clear();
                        $.ajax({
                        url :ORG_CLASS.urlCheckShowMSGWarningClassFroUpdate ,
                        type : 'post',
                        cache:false,
                        data : $('#classmanager').serialize(),
                        dataType : 'json',
                        success : function(data) {
                                if (data.status === 0)
                                {
                                 CONFIRM_MESSAGE.show(data.MSG,function(){
                                        $('#classmanager').submit();
                                    },function(){ 
                                        return false;
                                    }, null , data.textOK,  data.textCamcel);
                                    $('#confirmPopupModal #btnCancelConfirm').addClass('btn-red');
                                    $('#confirmPopupModal #btnAgreeConfirm').removeClass('btn-red');
                            }else{
                                    $('#classmanager').submit();
                                }
                            }
                        });
                    }
                }
            });
        }
    }
};
$(document).ready(function(){
    COMMON.showCrossEditMessage('#org-class-save');
	$('.keyenter input').keypress(function (event) {
	      if (event.keyCode == 13) {
	    	  	return false;
		    	//ORG_CLASS.generateclass();
		        event.preventDefault();
	      }
    });
    $('.keyenter select').keypress(function (event) {
        if (event.keyCode == 13) {
        	return false;
        	//ORG_CLASS.generateclass();
        	event.preventDefault();
        }
      });	
	$('#year').focus();
	if(typeof $mess != 'undefined' && $mess){
		ERROR_MESSAGE.show([{id: 'classname', message: jsMessages.MSG046}], function(){}, 'inline');
		$("#classname").addClass('error');
	}
	jQuery.validator.addMethod("character", function(value, element) {
	  return this.optional(element) || /[0-9]/.test(value);
	}, "Khong duoc nhap ky tu dac biet");

//check so am
	jQuery.validator.addMethod("soam", function(value, element) {
	  if(value<0){
	      return false;
	      }else{
	          return true;
	          }
		}, "am");
	//	check first character
		var validator = $("#classmanager").validate({
			onfocusout: false,
		    onkeyup: false,
		    onclick : false,
		    focusInvalid: false, 
			rules: {
				year : {
					required: true
				},
				school_year : {
					required: true
				},
				classname : {
					required: true,
					maxlength: 250
				},
				sizes : {
					maxlength: 10,
					character:"[0-9]",
					soam:true
				}
			},
			messages: {
				classname: {
					maxlength: jsMessages.MSG1000
				},
				sizes: {
					maxlength:'10'+jsMessages.MSG0141,
					character:jsMessages.MSG014,
					soam:jsMessages.MSG014
				}
			},
			onfocusout:function (element) {
		  },
		  errorPlacement: function(error, element){
		        ERROR_MESSAGE.show(validator.errorList, function(){}, 'inline');
		  }
		
		})
});