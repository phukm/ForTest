var APPLY_EIKEN = {
    baseUrl : window.location.protocol + "//" + window.location.host + "/",
    getHalTypeInDantaiUrl : 'eiken/get-hall-type',
    applyEikenConfirm: COMMON.baseUrl + 'satellite',
    saveActionUrl: COMMON.baseUrl + 'eiken/save',
    applyEikenUrl: COMMON.baseUrl + 'eiken/apply-eiken',
    paymentByCreditUrl: COMMON.baseUrl + 'payment-eiken-exam/pay-by-credit',
    paymentByConbiniUrl: COMMON.baseUrl + 'payment-eiken-exam/send-message-to-sqs',
    paymentInformationUrl: COMMON.baseUrl + 'payment-eiken-exam/payment-infomation',
    init: function() {        
        $(document).ready(function() {
            APPLY_EIKEN.showApplyInfo(0);
            $('form:first *:input[type!=hidden]:first').focus();
            APPLY_EIKEN.setTabIndexIdentify();
            $('input[name="chooseKyu[]"]').change(function () {
                APPLY_EIKEN.showApplyInfo(0);
                if($(this).is(':checked')){
                    $('#hallType'+$(this).val()).removeAttr('disabled');
                    APPLY_EIKEN.displayPriceAndDate($(this).val());
                }
                else{
                    $('#hallType'+$(this).val()).attr('disabled','disabled');
                    $('#price'+$(this).val()).html('');
                    $('#examDate'+$(this).val()).html('');
                }
            });
        });
        APPLY_EIKEN.onlyNumber('#txtPostalCode1,#txtPostalCode2,#txtPhoneNo1,#txtPhoneNo2,#txtPhoneNo3', true);
        $('#btnNext').click(function () {
            if (APPLY_EIKEN.validate()) { 
                if($('#exemption').val()==1){
                    APPLY_EIKEN.zipCode(function(data){
                        if (!data.success) {
                            $('#txtPostalCode1').addClass('error');
                            $('#txtPostalCode2').addClass('error');
                            $('.postcode .errorMess').remove();
                            $('.postcode').append('<p class="col-xs-12 errorMess">'+data.messages+'</p>');
                        }
                        else{
                            APPLY_EIKEN.submit();
                        }
                    });
                }
                else{
                    APPLY_EIKEN.submit();
                }
            }
        });
         $('#btnApply').click(function () {
            APPLY_EIKEN.applyEiken();
        });
        
        $("#ddlJobName").change(function(){
            if($(this).val()== 1){
                $('.row-SchoolCode').show();
            }else{
                $('.row-SchoolCode').hide();
            }
        });
    },
    hallTypeLoad : function (){
        APPLY_EIKEN.showApplyInfo();
        if($(this).selectedIndex == 1){
            $('td.standardPrice').hide();
            $('td.mainPrice').show();
        }else{
            $('td.standardPrice').show();
            $('td.mainPrice').hide();
        }
    },
    daysInMonth: function (month, year) {
        return new Date(year, month, 0).getDate();
    },
    getDaysInMonth: function (month, year) {
        var number = APPLY_EIKEN.daysInMonth(month, year);
        var days = [];
        for (var i = 1; i <= number; i++) {
            days.push(i);
        }
        return days;
    },
    loadDay: function () {
        var year = $('#ddlYear').val();
        var month = $('#ddlMonth').val();        
        if ((year === null || year === '') || (month === null || month === ''))
        {
            $("#ddlMonth").empty();
            $("#ddlDay").empty();
        }
        if (year.length > 0 && (month === null || month === ''))
        {
            $("#ddlMonth").empty();
            var months = $("#ddlMonth");
            months.append($("<option />"));
            for (var i = 1; i <= 12; i++)
            {
                months.append($("<option />").val(i).text(i));
            }
        }
        else if (year.length > 0 && month.length > 0)
        {
        	var currentDay = $("#ddlDay").val();
            $("#ddlDay").empty();
            var options = $("#ddlDay");            
            var data = APPLY_EIKEN.getDaysInMonth(month, year);
            options.append($("<option />"));
            $.each(data, function () {
                options.append($("<option />").val(this).text(this));
            });
            $("#ddlDay").val(currentDay);
        }
        //update BD thanhnx 27/8/2015  validate if <=15 year old
        var currentDate = new Date();
        var currenYear = currentDate.getFullYear();
        if (currenYear - year <= 15) {
            $("#txtParent").val('');
            $("#parent").slideDown();
        }
        else
        {
            $("#parent").fadeOut('fast');
        }
    },
    validate: function () {
        var stringKana = /^[ァ-ン|ｧ-ﾝﾞﾟ]+$/;
        var halfSize = /[a-zA-Z0-9-_\'!@#$%^&*()\uff5F-\uff9F\u0020]/;
        var regexEmail = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        var firstNameKanji = $('input[name="txtFirstNameKanji"]').val();
        var lastNameKanji = $('input[name="txtLastNameKanji"]').val();
        var firstNameKana = $('input[name="txtFirstNameKana"]').val();
        var lastNameKana = $('input[name="txtLastNameKana"]').val();
        var birthYear = $('select[name="ddlYear"]').val();
        var birthMonth = $('select[name="ddlMonth"]').val();
        var birthDate = $('select[name="ddlDay"]').val();
        var chooseKyu = $('input[name="chooseKyu[]"]:checked');
        var postalCode1 = $('input[name="txtPostalCode1"]').val();
        var postalCode2 = $('input[name="txtPostalCode2"]').val();
        var city = $('select[name="ddlCity"]').val();
        var district = $('input[name="txtDistrict"]').val();
        var town = $('input[name="txtTown"]').val();
        var phoneNo1 = $('input[name="txtPhoneNo1"]').val();
        var phoneNo2 = $('input[name="txtPhoneNo2"]').val();
        var phoneNo3 = $('input[name="txtPhoneNo3"]').val();
        var email = $('input[name="txtEmail"]').val();
        var jobName = $('select[name="ddlJobName"]').val();
        var schoolCode = $('select[name="ddlSchoolCode"]').val();
        var date = new Date();
        var currentYear = date.getFullYear();
        var currentMonth = date.getMonth() + 1;
        var currentDate = date.getDate();
        var message = [];
        
        firstNameKanji = $.trim(firstNameKanji);
        lastNameKanji = $.trim(lastNameKanji);
        firstNameKana = $.trim(firstNameKana);
        lastNameKana = $.trim(lastNameKana);
        if (doubleEikenNotSupport && chooseKyu.length === 2) {
            message.push({id: 'eiken-level', message: translate.MSG42});
        }
        else if (chooseKyu.length > 2) {
            message.push({id: 'eiken-level', message: translate.MSG5});
        }
        else if (chooseKyu.length > 1 && applyEikenLevelId > 0) {
            message.push({id: 'eiken-level', message: translate.MSG5});
        }
        else if (chooseKyu.length === 1 && applyEikenLevelId > 0 && Math.abs(parseInt($('input[name="chooseKyu[]"]:checked:eq(0)').val()) - parseInt(applyEikenLevelId)) != 1) {
            message.push({id: 'eiken-level', message: translate.MSG6});
        }
        if (chooseKyu.length < 1) {
                message.push({id: 'eiken-level', message: translate.MSG1});
        }
        if (chooseKyu.length === 2 && parseInt($('input[name="chooseKyu[]"]:checked:eq(1)').val()) - parseInt($('input[name="chooseKyu[]"]:checked:eq(0)').val()) != 1) {
            message.push({id: 'eiken-level', message: translate.MSG6});
        }
        if (!$('#applyInfo').hasClass('hide')) {
            if (firstNameKanji.length < 1 && !$('input[name="txtFirstNameKanji"]').is(":disabled")) {
                message.push({id: 'txtFirstNameKanji', message: translate.MSG1});
            }
            if (lastNameKanji.length < 1 && !$('input[name="txtLastNameKanji"]').is(":disabled")) {
                message.push({id: 'txtLastNameKanji', message: translate.MSG1});
            }
            if (halfSize.test(firstNameKanji) && !$('input[name="txtFirstNameKanji"]').is(":disabled")) {
                message.push({id: 'txtFirstNameKanji', message: translate.input20FullSizeNameKanji});
            }
            if (halfSize.test(lastNameKanji) && !$('input[name="txtLastNameKanji"]').is(":disabled")) {
                message.push({id: 'txtLastNameKanji', message: translate.input20FullSizeNameKanji});
            }
            else if ((firstNameKanji.length + lastNameKanji.length) > 20 && !$('input[name="txtLastNameKanji"]').is(":disabled") && !$('input[name="txtFirstNameKanji"]').is(":disabled")){
                message.push({id: 'nameKanji', message: translate.input20FullSizeNameKanji});
            }
            if (firstNameKana.length < 1 && !$('input[name="txtFirstNameKana"]').is(":disabled")) {
                message.push({id: 'txtFirstNameKana', message: translate.MSG1});
            }
            if (lastNameKana.length < 1 && !$('input[name="txtLastNameKana"]').is(":disabled")) {
                message.push({id: 'txtLastNameKana', message: translate.MSG1});
            }
            if (firstNameKana.length >= 1 && !stringKana.test(firstNameKana) && !$('input[name="txtFirstNameKana"]').is(":disabled")) {
                message.push({id: 'txtFirstNameKana', message: translate.msgCheckStringKana});
            }
            if (lastNameKana.length >= 1 && !stringKana.test(lastNameKana) && !$('input[name="txtLastNameKana"]').is(":disabled")) {
                message.push({id: 'txtLastNameKana', message: translate.msgCheckStringKana});
            }
            if ($('input[name="rdSex"]:checked').length === 0 && !$('input[name="rdSex"]').is(":disabled")) {
                message.push({id: 'rdSex', message: translate.MSG1});
            }
            if (birthYear.length < 1 && !$('input[name="ddlYear"]').is(":disabled")) {
                message.push({id: 'birthdate', message: translate.MSG1});
            }
            else if (birthMonth.length < 1 && !$('input[name="ddlMonth"]').is(":disabled")) {
                message.push({id: 'birthdate', message: translate.MSG1});
            }
            else if (birthDate.length < 1 && !$('input[name="ddlDay"]').is(":disabled")) {
                message.push({id: 'birthdate', message: translate.MSG1});
            }
            else if ((birthYear >= currentYear&& birthMonth > currentMonth)
                    ||(birthYear >= currentYear&& birthMonth == currentMonth && birthDate == currentDate)){
                message.push({id: 'birthdate', message: translate.InvalidBirthday});
            }
            if (postalCode1.length < 1 && !$('input[name="txtPostalCode1"]').is(":disabled")) {
                message.push({id: 'txtPostalCode1', message: translate.MSG1});
            }
            if (postalCode2.length < 1 && !$('input[name="txtPostalCode2"]').is(":disabled")) {
                message.push({id: 'txtPostalCode2', message: translate.MSG1});
            }
            else if (postalCode1.length > 1 && (postalCode1.length + postalCode2.length)!= 7) {
                message.push({id: 'postcode', message: translate.ZipCode_Not_Found});
            }
            if (city.length < 1 && !$('input[name="ddlCity"]').is(":disabled")) {
                message.push({id: 'ddlCity', message: translate.MSG1});
            }
            if (district.length < 1 && !$('input[name="txtDistrict"]').is(":disabled")) {
                message.push({id: 'txtDistrict', message: translate.MSG1});
            }
            else if (halfSize.test(district) && !$('input[name="txtDistrict"]').is(":disabled")) {
                message.push({id: 'txtDistrict', message: translate.MSG22});
            }
            if (town.length < 1 && !$('input[name="txtTown"]').is(":disabled")) {
                message.push({id: 'txtTown', message: translate.MSG1});
            }
            else if (halfSize.test(town) && !$('input[name="txtTown"]').is(":disabled")) {
                message.push({id: 'txtTown', message: translate.MSG22});
            }
            if (phoneNo1.length < 1 && !$('input[name="txtPhoneNo1"]').is(":disabled")) {
                message.push({id: 'txtPhoneNo1', message: translate.MSG1});
            }
            if (phoneNo2.length < 1 && !$('input[name="txtPhoneNo2"]').is(":disabled")) {
                message.push({id: 'txtPhoneNo2', message: translate.MSG1});
            }
            if (phoneNo3.length < 1 && !$('input[name="txtPhoneNo3"]').is(":disabled")) {
                message.push({id: 'txtPhoneNo3', message: translate.MSG1});
            }
            if (phoneNo1.length == 1 && !$('input[name="txtPhoneNo1"]').is(":disabled")){
                if($.trim(phoneNo1).match(/[0-9]/) == null){
                    message.push({id: 'txtPhoneNo1', message: translate.MsgRequiredNumber});
                }
            }
            else if ($.trim(phoneNo1).match(/^(\d+-?)+\d+$/) == null && !$('input[name="txtPhoneNo1"]').is(":disabled")) {
                message.push({id: 'txtPhoneNo1', message: translate.MsgRequiredNumber});
            }
            if (phoneNo2.length == 1 && !$('input[name="txtPhoneNo2"]').is(":disabled")){
                if($.trim(phoneNo2).match(/[0-9]/) == null){
                    message.push({id: 'txtPhoneNo2', message: translate.MsgRequiredNumber});
                }
            }
            else if ($.trim(phoneNo2).match(/^(\d+-?)+\d+$/) == null && !$('input[name="txtPhoneNo2"]').is(":disabled")) {
                message.push({id: 'txtPhoneNo2', message: translate.MsgRequiredNumber});
            }
            if (phoneNo3.length == 1 && !$('input[name="txtPhoneNo3"]').is(":disabled")){
                if($.trim(phoneNo3).match(/[0-9]/) == null){
                    message.push({id: 'txtPhoneNo3', message: translate.MsgRequiredNumber});
                }
            }
            else if ($.trim(phoneNo3).match(/^(\d+-?)+\d+$/) == null && !$('input[name="txtPhoneNo3"]').is(":disabled")) {
                message.push({id: 'txtPhoneNo3', message: translate.MsgRequiredNumber});
            }
            if((phoneNo1.length + phoneNo2.length + phoneNo3.length) > 11){
                message.push({id: 'phoneNo', message: translate.msgInput11DigitOfPhoneNumber});
            }
            if (email.length >= 1 && !regexEmail.test(email) && !$('input[name="txtEmail"]').is(":disabled")) {
                message.push({id: 'txtEmail', message: translate.MsgInvalidEmail});
            }
            if (jobName.length < 1 && !$('input[name="ddlJobName"]').is(":disabled")) {
                message.push({id: 'ddlJobName', message: translate.MSG1});
            }
            if (jobName == 1 && schoolCode.length < 1 && !$('input[name="ddlSchoolCode"]').is(":disabled")) {
                message.push({id: 'ddlSchoolCode', message: translate.MSG1});
            }
            
        }
        $('input[name="chooseKyu[]"]').removeClass('errorNoneChecked');
        $('#eiken-level').removeClass('error');
        if (chooseKyu.length > 0) {
            $('input[name="chooseKyu[]"]').not(':checked').addClass('errorNoneChecked');
        }
        $('div input').removeClass('error');
        $('div textarea').removeClass('error');
        $('div select').removeClass('error');
        $('div').removeClass('error');
        $( ".errorMess" ).remove();
        if (message != '') {
            var checkRequiredNameKanji = 0;
            var checkFullSize = 0;
            var checkStringKana = 0;
            var checkRequiredNameKana = 0;
            var checkRequiredPostalCode = 0;
            var checkRequiredPhoneNo = 0;
            var checkNumberPhoneNo = 0;
            $.each(message, function (i, v) { 
                if($('.panel').hasClass('pc')){
                    if(v.id == 'txtPostalCode1' || v.id == 'txtPostalCode2' || v.id == 'postcode'){
                        $('#' + v.id).addClass('error');
                        if (v.message == translate.MSG1){
                            if(!checkRequiredPostalCode){
                                checkRequiredPostalCode = 1;
                                $('.postcode').append('<p class="col-xs-12 errorMess">'+v.message+'</p>');
                            }
                        }
                        else{
                            $('.postcode').append('<p class="col-xs-12 errorMess">'+v.message+'</p>');
                        }
                    }
                    else if(v.id == 'txtPhoneNo1' || v.id == 'txtPhoneNo2' || v.id == 'txtPhoneNo3' || v.id == 'phoneNo'){
                        if (v.message == translate.MSG1){
                            $('#' + v.id).addClass('error');
                            if(!checkRequiredPhoneNo){
                                checkRequiredPhoneNo = 1;
                                $('.phoneNo').append('<p class="col-xs-12 errorMess">'+v.message+'</p>');
                            }
                        }
                        else if (v.message == translate.MsgRequiredNumber && !checkRequiredPhoneNo){
                            $('#' + v.id).addClass('error');
                            if(!checkNumberPhoneNo){
                                checkNumberPhoneNo = 1;
                                $('.phoneNo').append('<p class="col-xs-12 errorMess">'+v.message+'</p>');
                            }
                        }
                        else if(!checkRequiredPhoneNo && !checkNumberPhoneNo){
                            $('#' + v.id).addClass('error');
                            $('.phoneNo').append('<p class="col-xs-12 errorMess">'+v.message+'</p>');
                        }
                    }
                    else if(v.id == 'txtFirstNameKanji' || v.id == 'txtLastNameKanji'|| v.id == 'nameKanji' ){
                        if (v.message == translate.MSG1){
                            $('#' + v.id).addClass('error');
                            if(!checkRequiredNameKanji){
                                checkRequiredNameKanji = 1;
                                $('#nameKanji').append('<p class="col-xs-12 errorMess">'+v.message+'</p>');
                            }
                        }
                        else if (v.message == translate.input20FullSizeNameKanji && !checkRequiredNameKanji && v.id != 'nameKanji'){
                            $('#' + v.id).addClass('error');
                            if(!checkFullSize){
                                checkFullSize = 1;
                                $('#nameKanji').append('<p class="col-xs-12 errorMess">'+v.message+'</p>');
                            }
                        }
                        else if(!checkRequiredNameKanji && !checkFullSize){
                            $('#' + v.id).addClass('error');
                            $('#nameKanji').append('<p class="col-xs-12 errorMess">'+v.message+'</p>');
                        }
                    }
                    else if(v.id == 'txtFirstNameKana' || v.id == 'txtLastNameKana'|| v.id == 'nameKana'){
                        if (v.message == translate.MSG1){ 
                            $('#' + v.id).addClass('error');
                            if(!checkRequiredNameKana){
                                checkRequiredNameKana = 1;
                                $('#nameKana').append('<p class="col-xs-12 errorMess">'+v.message+'</p>');
                            }
                        }
                        else if(!checkRequiredNameKana){
                            $('#' + v.id).addClass('error');
                            if(!checkStringKana){
                                checkStringKana = 1;
                                $('#nameKana').append('<p class="col-xs-12 errorMess">'+v.message+'</p>');
                            }
                        }
                    }
                    else{
                        $('#' + v.id).addClass('error');
                        $('#' + v.id).closest("div").append('<p class="errorMess">'+v.message+'</p>');
                    }
                }
                else{
                    if(v.id == 'txtPostalCode1' || v.id == 'txtPostalCode2' || v.id == 'postcode'){
                        $('#' + v.id).addClass('error');
                        if (v.message == translate.MSG1){
                            if(!checkRequiredPostalCode){
                                checkRequiredPostalCode = 1;
                                $('.postcode').append('<p class="col-xs-12 errorMess">'+v.message+'</p>');
                            }
                        }
                        else{
                            $('.postcode').append('<p class="col-xs-12 errorMess">'+v.message+'</p>');
                        }
                    }
                    else if(v.id == 'txtPhoneNo1' || v.id == 'txtPhoneNo2' || v.id == 'txtPhoneNo3' || v.id == 'phoneNo'){
                        if (v.message == translate.MSG1){
                            $('#' + v.id).addClass('error');
                            if(!checkRequiredPhoneNo){
                                checkRequiredPhoneNo = 1;
                                $('.phoneNo').append('<p class="col-xs-12 errorMess">'+v.message+'</p>');
                            }
                        }
                        else if (v.message == translate.MsgRequiredNumber && !checkRequiredPhoneNo){
                            $('#' + v.id).addClass('error');
                            if(!checkNumberPhoneNo){
                                checkNumberPhoneNo = 1;
                                $('.phoneNo').append('<p class="col-xs-12 errorMess">'+v.message+'</p>');
                            }
                        }
                        else if(!checkRequiredPhoneNo && !checkNumberPhoneNo){
                            $('#' + v.id).addClass('error');
                            $('.phoneNo').append('<p class="col-xs-12 errorMess">'+v.message+'</p>');
                        }
                    }
                    else{
                        $('#' + v.id).addClass('error');
                        $('#' + v.id).closest("div").append('<p class="errorMess">'+v.message+'</p>');
                    }
                }
            });
            return false;
        }

        return true;
    },
    onlyNumber: function (selector, number) {
        $(selector).keydown(function (e) {
            var listCode = [46, 8, 9, 27, 13];
            if ($.inArray(e.keyCode, listCode) != -1 ||
                (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                (e.keyCode >= 35 && e.keyCode <= 40)) {
                return;
            }
            if ((e.ctrlKey === true && e.keyCode === 67) || (e.ctrlKey === true && e.keyCode == 86)) {
                return;
            }
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    },
    setTabIndexIdentify: function() {
        var count = 0;
        $("select,input,.btn").each(function () {
            $(this).attr('tabindex', count);
            count++;
        });
    },
    submit: function () {
        $('#frmApplyEiken').attr('action', APPLY_EIKEN.applyEikenUrl).submit();
    },
    showApplyInfo: function () {
        var checkShowApplyInfo = false;
        $('input[name="chooseKyu[]"]').each(function () {
            if($(this).is(':checked')){
                if ($(this).val() != 0) APPLY_EIKEN.displayPriceAndDate($(this).val());
                $('#hallType'+$(this).val()).removeAttr('disabled');
                if($('#hallType'+$(this).val()).val() == 1){
                    checkShowApplyInfo = true;
                }
            }
            else{
                $('#hallType'+$(this).val()).attr('disabled','disabled');
            }
        });
        if(checkShowApplyInfo){
            $('#applyInfo').removeClass('hide');
            $('#exemption').val(1);
            if($('#ddlJobName').val() == 1){
                $('.row-SchoolCode').show();
            }else{
                $('.row-SchoolCode').hide();
            }
        }
        else{
            $('#applyInfo').addClass('hide');
            $('#exemption').val(0);
        };

        
    },
    displayPriceAndDate: function(id){
        if ($('#hallType' + id).val() == 0)
        {
            $('#price'+id).html(kyu[id]['priceName']);
            $('#examDate'+id).html(kyu[id]['examDate2Round']);
        }
        else {
            $('#price'+id).html(kyu2[id]['priceName']);
            $('#examDate'+id).html(kyu2[id]['examDate2Round']);
        }
    },
    loadAddressByZipcode: function () {
        var zipCode1 = $('#txtPostalCode1').val();
        var zipCode2 = $('#txtPostalCode2').val();
        $('#txtPostalCode1').removeClass('error');
        $('#txtPostalCode2').removeClass('error');
        $('.postcode .errorMess').remove();
        if(zipCode1.length < 1 || zipCode2.length < 1){
            if(zipCode1.length < 1){
                $('#txtPostalCode1').addClass('error');
            }
            if(zipCode2.length < 1){
                 $('#txtPostalCode2').addClass('error');
            }
            $('.postcode').append('<p class="col-xs-12 errorMess">'+translate.MSG1+'</p>');
            return false;
        }
        COMMON.zipcode.autoFill(zipCode1 + zipCode2, 'ddlCity', 'txtDistrict', 'txtTown', function (res) {
            if (res.success) {
                jQuery.validator.addMethod("maxZipCode", function () {
                    return false;
                });
                $('.postcode,#txtPostalCode1,#txtPostalCode2,#ddlCity,#txtDistrict,#txtTown').removeClass('error');
                $('.postcode .errorMess,.ddlCity .errorMess,.txtDistrict .errorMess,.txtTown .errorMess').remove();
            } else {
                $('#txtPostalCode1').addClass('error');
                $('#txtPostalCode2').addClass('error');
            }
        })
    },
    checkSelectedKyu: function(){
        var result = false;
        var str = '';
        $('input[name="chooseKyu[]"]:checked').each(function () {
            if($(this).val() == 1 || $(this).val() == 2){
                if(str != ''){
                    if($(this).val() == 2){
                        str = str+',準1級'
                    }
                    if($(this).val() == 1){
                        str = str+',1級'
                    }
                }else{
                    if($(this).val() == 2){
                        str = '準1級'
                    }
                    if($(this).val() == 1){
                        str = '1級'
                    }
                }
                result = true;
            }
        });
        var value_text = translate.msgShowPupilInfo;
        if(str != ''){
            value_text = str+translate.msgShowPupilInfoLv1;
        }
        $('#applyInfo .lblDescription').text(value_text);
        return result;
    },
    
    applyEiken: function(){
        $.ajax({
            type : 'POST',
            url : APPLY_EIKEN.saveActionUrl,
            dataType : 'json',
            success: function (result) {
                if (result['status'] == false){
                   ALERT_MESSAGE.show(result['message'], function(){
                       window.location.href = APPLY_EIKEN.baseUrl;
                   });  
                   return false;
                }                
                //  Main hall or Standard hall + individual + not paid before
                if ((result['isSupportCredit'] || result['isSupportConbini'])
                    && result['chooseKyu'].length > 0 && result['listPaid'].length == 0)
                {
                    CONFIRM_PAYMENT_METHOD_MESSAGE.show(result['message'],
                        function () {
                            if(result['msgCreditDeadline']){
                                ERROR_MESSAGE.show(result['msgCreditDeadline'], function () {
                                    window.location.href = APPLY_EIKEN.baseUrl;
                                    return false;
                                });
                                return false;
                            }
                            window.location.href = (result['applyEikenLevelId'].length == 1) ? APPLY_EIKEN.paymentByCreditUrl + '/' + result['applyEikenLevelId'][0] : APPLY_EIKEN.paymentByCreditUrl;
                        }, function () {
                            if(result['msgCombiniDeadline']){
                                ERROR_MESSAGE.show(result['msgCombiniDeadline'], function () {
                                    window.location.href = APPLY_EIKEN.baseUrl;
                                    return false;
                                });
                                return false;
                            }
                            APPLY_EIKEN.payByCombini(result['chooseKyu']);
                        }, function () {
                            window.location.href = APPLY_EIKEN.baseUrl;
                        }, '確認',
                        result['isSupportCredit'] ? translate.PAY_NOW : null,
                        result['isSupportConbini'] ? translate.payByCombini : null,
                        translate.PAY_LATER
                    );
                }
                // Main hall or Standard hall + individual + paid all kyu before
                else if ((result['isSupportCredit'] || result['isSupportConbini'])
                    && result['chooseKyu'].length == 0 && result['listPaid'].length > 0)
                {
                    ALERT_MESSAGE.show(result['message'],
                        function () {
                            setTimeout(function(){
                                ALERT_MESSAGE.show(result['message2'],
                                    function () {
                                        window.location.href = APPLY_EIKEN.baseUrl;
                                    }
                                );
                            }, 500);
                        }
                    );
                }
                // Main hall or Standard hall + individual + paid for one kyu when apply 2 kyu
                else if ((result['isSupportCredit'] || result['isSupportConbini'])
                    && result['chooseKyu'].length > 0 && result['listPaid'].length > 0)
                {
                    ALERT_MESSAGE.show(result['message'],
                        function () {
                            setTimeout(function () {
                                CONFIRM_PAYMENT_METHOD_MESSAGE.show(result['message2'],
                                    function () {
                                        if(result['msgCreditDeadline']){
                                            ERROR_MESSAGE.show(result['msgCreditDeadline'], function () {
                                                window.location.href = APPLY_EIKEN.baseUrl;
                                                return false;
                                            });
                                            return false;
                                        }
                                        window.location.href = (result['applyEikenLevelId'].length == 1) ? APPLY_EIKEN.paymentByCreditUrl + '/' + result['applyEikenLevelId'][0] : APPLY_EIKEN.paymentByCreditUrl;
                                    }, function () {
                                        if(result['msgCombiniDeadline']){
                                            ERROR_MESSAGE.show(result['msgCombiniDeadline'], function () {
                                                window.location.href = APPLY_EIKEN.baseUrl;
                                                return false;
                                            });
                                            return false;
                                        }
                                        APPLY_EIKEN.payByCombini(result['chooseKyu']);
                                    }, function () {
                                        window.location.href = APPLY_EIKEN.baseUrl;
                                    }, '確認',
                                    result['isSupportCredit'] ? translate.PAY_NOW : null,
                                    result['isSupportConbini'] ? translate.payByCombini : null,
                                    translate.PAY_LATER
                                );
                            }, 500);
                        }
                    );
                }
                // Standard hall + collective or main hall + collective
                else if (!result['isSupportCredit'] && !result['isSupportConbini']) {
                    ALERT_MESSAGE.show(result['message'],
                        function () {
                            window.location.href = APPLY_EIKEN.baseUrl;
                        }
                    );
                }
            }
        });
    },
    zipCode:function(callback){
        if(typeof callback !== 'function'){
            callback = function(){};
        }
        var zipCode = $('#txtPostalCode1').val() + $('#txtPostalCode2').val();
        $.ajax({
            type: 'POST',
            url: APPLY_EIKEN.baseUrl + 'eiken/zipcode',
            contentType: "application/json",
            dataType: 'json',
            data: JSON.stringify({zipcode: zipCode}),
            success: function (data) {
                callback(data);
            }
        });    
    },
    payByCombini:function(listKyu){
        $.ajax({
            type : 'POST',
            url : APPLY_EIKEN.paymentByConbiniUrl,
            data: {listKyu: listKyu},
            dataType : 'json',
            success : function(data) {
                // when success.
                if(data == 'true'){
                    ALERT_MESSAGE.show(translate.msgWaitReceiptNo,
                            function(){
                                window.location.href = APPLY_EIKEN.baseUrl;
                            },
                            function(){
                                window.location.href = APPLY_EIKEN.baseUrl;
                            }
                    );
                }else{
                    ALERT_MESSAGE.show(data,
                            function(){
                                window.location.href = APPLY_EIKEN.baseUrl;
                            },
                            function(){
                                window.location.href = APPLY_EIKEN.baseUrl;
                            }
                    );
                }
            }
        });    
    }
};

