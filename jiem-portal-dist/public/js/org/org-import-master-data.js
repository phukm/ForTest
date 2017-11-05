var listError = [];
var ORG_MASTER_DATA = {
    ready: function () {
        $("#ddbYear").focus();
        $("#submitForm").click(function () {
            $(".import-upload-error ul li").each(function (index) {
                $(this).attr("style", "display:none");
            });
        });
        // break two element focus
        $("#filename").on('focus', function () {
            $("#filename").keyup(function (e) {
                if (e.keyCode == 9 && e.shiftKey) {
                    $("#ddlKai").trigger('focus');
                }
                if (e.keyCode == 9 && e.shiftKey != true) {
                    $("#csvfilefocus").trigger('focus');
                }
            });

        });
        $("#fileImport").on('focus', function () {
            $("#fileImport").keyup(function (e) {
                if (e.keyCode == 9 && e.shiftKey) {
                    $("#csvfilefocus").trigger('focus');
                }
                if (e.keyCode == 9 && e.shiftKey != true) {
                    $("#submitForm").trigger('focus');
                }
            });
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

        // focus botton
        $('#showMsgYear').click(function (e) {
            $("#ddbYear").focus();
            $("#ddbYear").trigger('click');
        });
        $('#showMsgKai').click(function (e) {
            $("#ddlKai").focus();
            $("#ddlKai").trigger('click');
        });
        $('#showMsgFileImport').click(function (e) {
            $("#csvfilefocus").focus();
            $("#csvfilefocus").trigger('click');

        });
        $('#showMsgFileImport').click(function (e) {
            $("#csvfilefocus").focus();
            $("#csvfilefocus").trigger('click');

        });

        // remove 2 div  jiem-error 
        $i = 0;
        $(document).find('.jiem-error').each(function () {
            $i++;
        });

        if ($i > 1) {
            $('.jiem-error:first').remove();
        }

        $("#fileImport").on('change', function () {
            var value = $(this).val();
            var arr = value.split("\\fakepath\\");
            if (typeof arr[1] != 'undefined') {
                $("#filename").val(arr[1]);
            } else {
                $("#filename").val($(this).val());
            }
        });
        $("#csvfilefocus").on('keyup', function (e) {
            var code = e.keyCode || e.which;

            if (e.keyCode == 13) {
                $("#fileImport").trigger('click');
            }
        });
        // submit
        
        $('#submitForm').on('click', function (e) {
            listError = [];
            var valCheck = MASTER_DATA_VAL.ready();
            if (valCheck)
            {
                $('.jiem-error').hide();
                // have the error

                $('#filename').removeClass('error');
                $('#loadingModal').modal('show');
                $('#import-materdata').submit();
            } else
            {
                $('.jiem-error').show();
                ERROR_MESSAGE.show(listError, null, 'inline');
                // add class format
                $('.jiem-error > .alert').addClass('import-upload-error');

                return;
            }
        });
    },
};
var MASTER_DATA_VAL = {
    ready: function () {
        var erorrId = MASTER_DATA_VAL.validateFile();
        var yearVal = $('#ddbYear').val();
        var kaiVal = $('#ddlKai').val();
        var checkError = true;


        if (yearVal == '')
        {
            $('#ddbYear').addClass('error');
            listError.push({id: 'ddbYear', message: jsMessages.MSG_NotSelect});
            checkError = false;
        } else {
            $('#ddbYear').removeClass('error');
            $('#showMsgYear').css("display", "none");
        }

        if (kaiVal == '')
        {
            $('#ddlKai').addClass('error');
            listError.push({id: 'ddlKai', message: jsMessages.MSG_NotSelect});

            checkError = false;
        }
        else {
            $('#ddlKai').removeClass('error');
            $('#showMsgKai').css("display", "none");
        }
        if (erorrId == 'ERROR1')
        {
            $('#filename').addClass('error');
            listError.push({id: 'csvfilefocus', message: jsMessages.MSG_NotExist});
            checkError = false;
        }
        if (erorrId == 'ERROR2') {
            checkError = false;
            listError.push({id: 'csvfilefocus', message: jsMessages.MSG_NotFile});
        }
        if (erorrId == 'ERROR0') {
            $('#filename').removeClass('error');
            $('#listMsgFileImport').css("display", "none");
        }
        return checkError;


    },
    // check file import validate
    validateFile: function () {
        var file = $("#fileImport").val();
        var erroID = 'ERROR0';
        //var filename = $_FILES[$("#fileImport")]['name'];
        // check importbox null value

        if (file != "")
        {
            var regex = new RegExp("(.*?)\.(csv)$");
            if (!regex.test(file)) {
                //wrong file name
                erroID = 'ERROR2';

            }
        }
        else {
            //Null value
            erroID = 'ERROR1';
        }
        return erroID;
    }
};