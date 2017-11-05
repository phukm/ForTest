var RECOMMEND_INVITATION = {
	getClassUrl: COMMON.baseUrl + 'invitation/recommended/getclass',
	getSchoolYearUrl: COMMON.baseUrl + 'invitation/recommended/getschoolyear',
	init: function () {
		//check all
	    $("#CheckAll").click(function () {
	        $('input[name="ListEikenLevel[]"]').prop('checked', $(this).prop("checked"));
	        if ($('input[name="HallType"]:checked').val() == '0') {
	            $('#setting .HallType_main').addClass('disabled').attr('disabled', 'disabled').prop('checked', false);
	        }
	    });
	    $('input[name="ListEikenLevel[]"]').click(function () {
	        if ($('input[name="ListEikenLevel[]"]').length == $('input[name="ListEikenLevel[]"]:checked').length) {
	            $("#CheckAll").prop('checked', true);
	        } else {
	            $("#CheckAll").prop('checked', false);
	        }
	    });

	    $('#ddbYear').change(function () {
	        RECOMMEND_INVITATION.getEikenSchedule();
	        RECOMMEND_INVITATION.getOrgSchoolYear();
	    });
	    $('#ddbSchoolYear').change(function(){
	        RECOMMEND_INVITATION.getClass();
	    });
	    /* Submit form Search Recommended */
	    $('#search').click(function () {
	        var kai = $('#ddbKai').val();
	        if (kai == 0 || typeof kai == 'undefined' || kai == '' || kai == null) {
	            $("#ddbKai").addClass("error");
	            ERROR_MESSAGE.show([{id: 'ddbKai', message: msg["MSG001"]}], null, 'inline');
	        } else {
	            localStorage.clear();
	            $("#frmSearchRecommend").submit();
	        }
	    });

	    /* Set Recommend Level Popup */
	    $('#updateLevel').click(function () {
	        if ($('input[name="ListEikenLevel[]"]:checked').length == 0) {
	            ERROR_MESSAGE.show(msg["MSG037"]);
	        } else {
	            $('#setRcm').modal('show');
	        }
	    });
	    /* Set RecommendLevel */
		var emptyStandardlevel = $('#emptyStandardlevel').val();
	    $('#EikenLevel').click(function () {
	        var kai = $('#ddbKai').val();
                if (typeof emptyStandardlevel != 'undefined' && emptyStandardlevel == 0) {
                        ERROR_MESSAGE.show([{id: '', message: msg["MSG031"]}], null, 'inline');
                }
	        else if (typeof kai == 'undefined' || kai == '' || kai == null) {
	            $("#ddbKai").addClass("error");
	            ERROR_MESSAGE.show([{id: 'ddbKai', message: msg["MSG035"]}], null, 'inline')
	        } else {
                    CONFIRM_MESSAGE.show(overWriteTargetKyu, function () {
                        localStorage.clear();
                        $('#resuleRecommend').attr('action', "/invitation/recommended/setRecommend").submit();
                    }, function(){
                        false;
                    }); 
	        }
	    });
	    //update recommend level
	    $('#UpdateRecommendLevel').click(function () {
	        $('#localStorageRecommendLevel').val(JSON.stringify(localStorage));
	        localStorage.clear();
	        $('#resuleRecommend').attr('action', "/invitation/recommended/update").submit();
	    });
	    /* Get Simple Test */
	    $('#SimpleTest').click(function () {
	        $('#resuleRecommend').attr('action', "/invitation/recommended/simpletest").submit();
	    });

	    /* Update Level Change */
	    $('#btnUpdateLevel').click(function () {
	        var ChooseLevel = $('#ddbLevelChange').val();
	        if (ChooseLevel == '' || ChooseLevel == 0 || ChooseLevel == null) {
	            $('#messages').html(msg['MSG001']);
	            return false;
	        }
                if (sessionStorage.getItem('listPupil')) {
                    var allitem = sessionStorage.getItem('listPupil');
                    allitem = allitem.split(',');
                    $.each(allitem,function (index, value) {
                        if($('#EikenLevel' + value).length){
                            $('#EikenLevel' + value).val(ChooseLevel);
                        }
                        localStorage.setItem(value, ChooseLevel);
                    });
                }
                
	        $('#messages').html('');
	        $('#ddbLevelChange').val(0);
                $('input[name="ListEikenLevel[]"]').attr('checked',false);
                $('#CheckAll').attr('checked',false);
                sessionStorage.clear();
	    }
	    );
	    //Reset
	    $('#reset').click(function () {
	        $('.EikenLevel').val("0");
	        localStorage.clear();
	    });
	    // Popup cancel
	    $('#btnCancel').click(function () {
	        $('#messages').html('');
	        $('#ddbLevelChange').val(0);
	    });
	    $('.EikenLevel').change(function () {
	        localStorage.setItem($(this).attr('id').replace('EikenLevel', ''), $(this).val());
	    });

	    $('#ddbSchoolYear').click(function () {
	        $('#dbschoolYear').val($('#ddbSchoolYear').val());
	    });
	    $('form:first *:input[type!=hidden]:first').focus();
	    $('#dbschoolYear').val($('#ddbSchoolYear').val());
            
            RECOMMEND_INVITATION.keepCheckedData();
	},
	historyLocalStorage: function(){
	    if(typeof localStorage != 'undefined' && localStorage != '' && localStorage != null){
	        $.each(localStorage, function (key, val){
	            $('#EikenLevel' + key).val(val);
	        });
	    }
	},
	getClass: function() {
	    var SchoolYear = $('#ddbSchoolYear').val();
	    var html = '<option value=""></option>';
	    if(SchoolYear != '' && SchoolYear != 'undefined' && SchoolYear != null){
	        $.ajax({
	            type: 'GET',
	            url: RECOMMEND_INVITATION.getClassUrl + '?year=' + $('#ddbYear').val() + '&schoolyear=' + SchoolYear,
	            data: {},
	            success: function (data) {                        
	                if(data != ''){
	                    $.each(data, function (key, val) {                                	                        
                                $('#ddbClass').append($('<option>', {
                                value: val.id,
                                text: val.className
                                }));
	                    });
	                }
	            },
	            error: function () {
	                ERROR_MESSAGE.show([{id: '', message: msg["MSG001"]}], null , 'inline');
	                $('#loadingModal').modal('hide');
	            }
	        });
	    }else{
	        $("#ddbClass").html(html);
	    }
	},
	getOrgSchoolYear: function() {
	    if($('#ddbKai').val() != 'undefined' && $('#ddbKai').val() != '' && $('#ddbKai').val() != null){
	        $.ajax({
	            type: 'GET',
	            url: RECOMMEND_INVITATION.getSchoolYearUrl + '?year=' + $('#ddbYear').val(),
	            data: {},
	            success: function (data) {
	                var options = '<option value=""></option>';
	                if(data == ''){
	                    $("#ddbClass").html('<option value=""></option>');
	                }else{
	                    $.each(data, function () {
	                        options = options + '<option value="' + this.id + '">' + this.displayName + '</option>';
	                    });
	                    $("#ddbSchoolYear").html(options);
	                }
	            },
	            error: function () {
	                ERROR_MESSAGE.show([{id: '', message: msg["MSG001"]}], null , 'inline')
	                $('#loadingModal').modal('hide');
	            }
	        });
	    }
	},
	getEikenSchedule: function() {
	    var element = $('#ddbYear').find('option:selected').attr('ref');
	    var pkai = $('#ddbYear').attr('rel');
	    var year = $('#ddbYear').val();
	    //var currentYear = (new Date).getFullYear();
	    if (typeof element != 'undefined') {
	        element = $.parseJSON(element);
	        var html = '';
	        var selected = '';
	        $.each(element, function (kai, id) {
	            if (pkai != null && pkai != '' && pkai == kai){
	                selected = 'selected';
	            }else {
	                selected = '';
	            }
	            html = html + '<option ' + selected + ' value="' + kai + '">' + kai + '</option>';
	        });
	        if (RECOMMEND_INVITATION.msieversion() && html == '') html = '<option value=""></option>';

	        $('#ddbKai').html(html);
	    }
	},
	//Check browser ie.
	msieversion: function() {
	    var ua = window.navigator.userAgent;
	    var msie = ua.indexOf("MSIE");
	    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))
	        return true;
	    else
	        return false;
	},
        keepCheckedData: function(){
            RECOMMEND_INVITATION.checkedDataSelect();
            RECOMMEND_INVITATION.setPupilCheckAll();
            $('input[name="ListEikenLevel[]"]').on('change', function () {
                var pupilList = sessionStorage.getItem('listPupil');
                if (!pupilList) {
                    pupilList = new Array();
                }
                else {
                    pupilList = pupilList.split(',');
                }
                $('input[name="ListEikenLevel[]"]').each(function () {
                    var idPupil = $(this).val();
                    var found = $.inArray('' + idPupil, pupilList);
                    if (this.checked) {
                        if (found < 0 && $.isNumeric(idPupil)) {
                            pupilList.push(idPupil);
                        }
                    } else {
                        if (found >= 0)
                            pupilList.splice(found, 1);
                    }
                });
                sessionStorage.setItem('listPupil', pupilList.join());
            });
        },
        checkedDataSelect: function(){
            if (sessionStorage.getItem('listPupil')) {
                var allitem = sessionStorage.getItem('listPupil');
                allitem = allitem.split(',');
                $.each(allitem,
                        function (index, value) {
                            $('#recom-index #checkbox-' + value).attr('checked',
                                    'checked');
                        });
                if ($('.checkbox1').length > 0
                        && $('.checkbox1').length == $('.checkbox1:checked').length)
                    $('#select_all').attr('checked', 'checked');
            }
        },
        setPupilCheckAll: function(){
            if ($('input[name="ListEikenLevel[]"]').length > 0
                && $('input[name="ListEikenLevel[]"]').length == $('input[name="ListEikenLevel[]"]:checked').length)
                $('#CheckAll').attr('checked', 'checked');
                $('#CheckAll').on('change', function () {
                    var pupilList = sessionStorage.getItem('listPupil');
                    if (!pupilList) {
                        pupilList = new Array();
                    }
                    else {
                        pupilList = pupilList.split(',');
                    }
                    if (!$(this).is(':checked')) {  
                        $('input[name="ListEikenLevel[]"]').each(function () {
                        var idPupil = $(this).val();
                        var found = $.inArray('' + idPupil, pupilList);
                            pupilList.splice(found, 1);
                        });
                    } else{
                        $('input[name="ListEikenLevel[]"]').each(function () {
                        var idPupil = $(this).val();
                        var found = $.inArray('' + idPupil, pupilList);
                            pupilList.push(idPupil);
                        });
                    }
                    sessionStorage.setItem('listPupil', pupilList.join());
                });
    }
}
