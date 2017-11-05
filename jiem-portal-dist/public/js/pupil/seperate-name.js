var SEPERATE_NAME = {
    COOKIES_TOKEN: 'cookiesToken',
    ready: function () {
        SEPERATE_NAME.forcusfield();
        var tokenCookies = Math.random().toString(36).substr(2, 9);
        $("#cookiesToken").val(tokenCookies);
        $("#fileImport").on('change', function () {
            var value = $(this).val();
            var arr = value.split("\\fakepath\\");
            if (typeof arr[1] != 'undefined') {
                $("#filename").val(arr[1]);
            } else {
                $("#filename").val($(this).val());
            }
        });
        if(error.length != 0){
            $('#filename').addClass('error');
            $('#showError').show();
            $('#showMsgFileImport').html(error);
        }else{
            $('#showError').hide();
            $('#filename').removeClass('error');
        };     
        
        function getCookie( name ) {
           var parts = document.cookie.split(name + "=");
           if (parts.length == 2) return parts.pop().split(";").shift();
        }
        function expireCookie( cName ) {
            document.cookie = 
              encodeURIComponent( cName ) +
              "=deleted; expires=" +
              new Date( 0 ).toUTCString();
        }
        
        function startTimer(){
            downloadTimer = window.setInterval( function() {
                var token = getCookie(SEPERATE_NAME.COOKIES_TOKEN);                
                if( (token == tokenCookies)) {
                  $('#loadingModal').modal('hide');
                  $('body').removeClass('modal-open');
                  window.clearInterval( downloadTimer );
                  expireCookie( SEPERATE_NAME.COOKIES_TOKEN );
                  $('#showError').hide();
                  $('#filename').removeClass('error');
                }
            }, 1000);
        }
        $("#submitForm").on('click', function (e) {
            $('#loadingModal').modal('show');
            startTimer();
        });
        
        $('#submitExportType').click(function(){
            var radioList = $('input[name=radioOption]');
            var exportType = 0;
            for(i = 0; i < radioList.length; i++){
                if(radioList[i].checked){
                    exportType = radioList[i].value;
                }
            }
            $('#exportType').val(exportType);
            $('#exportTemplate').submit();
        });
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
};
