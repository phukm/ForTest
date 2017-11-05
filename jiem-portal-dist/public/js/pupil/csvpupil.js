/**
 *Show pupup error message
 **/
var IMPORT_PUPIL = {
    loadErrorFile: function () {
        var $this = $('#csvfile');
        if ($('#csvfile').val() == '') {
            $('#filename').val('');
            $('.error2 li').remove();
            ERROR_MESSAGE.show([{id: 'filename', message: $masagepupil.MSG001}], function () {
            }, 'inline');
            $('#filename').addClass('error');
            return false;
        }
        var ext = $this.val().split('.').pop().toLowerCase();
        if ($.inArray(ext, ['csv']) == -1) {
            $('.error2 li').remove();
            $('#filename').addClass('error');
            ERROR_MESSAGE.show([{id: 'filename', message: $masagepupil.MSG21}], function () {
            }, 'inline');
            return false;
        }
    },
    getFiel: function (data, flag) {
        var $html = '';
        $.each(data, function (index, value) {
            if (value) {
                $html += '<tr id="' + index + '">';
                if (flag) {
                    $html += '<td class="cright">' + (parseInt(index) + 1) + '</td>';
                }
                for (i = 0; i < 24; i++) {
                    if (i == 3 || (12 < i && i < 24) || i == 14 || i == 11) {
                        var classj = 'class="cright"';
                    } else {
                        var classj = 'class="cleft"';
                    }
                    if (flag) {
                        if (i < 4 || i == 8 || i == 9 || i == 13 || i == 20) {
                            $html += '<td ' + classj + '>' + IMPORT_PUPIL.escapeHtml(value[i]) + '</td>';
                        }
                    } else {
                        if (i < 4) {
                            $html += '<td ' + classj + '>' + IMPORT_PUPIL.escapeHtml(value[i]) + '</td>';
                        }
                        if (i == 4) {
                            $html += '<td ' + classj + '>' + IMPORT_PUPIL.escapeHtml(value[4] + value[5]) + '</td>';
                        }
                        else if (i == 5) {
                            $html += '<td ' + classj + '>' + IMPORT_PUPIL.escapeHtml(value[6] + value[7]) + '</td>';
                        }
                        else if (i > 5) {
                            $html += '<td ' + classj + '>' + IMPORT_PUPIL.escapeHtml(value[i + 2]) + '</td>';
                        }
                    }

                }
                $html += '</tr>';
            }
        });
        return $html;
    },
    scrollToLineError: function (trId) {
        $('.importstd-list').animate({
            scrollTop: $("#line_import_" + trId).offset().top
        }, 500);

    },
    getEror: function (dataerror) {
        var $html = '<li><p>取り込み時のチェックでエラーとなったレコードを表示します。エラーの詳しい内容は対象行にカーソルを移動すると表示されます。</p></li>';
        $.each(dataerror, function (index, value) {
            $html += '<li><p>' + value + '</p></li>';
        });
        return $html;
    },
    getDataImportPupil: function (data, isError) {
        var html = '';
        $.each(data, function (index, value) {
            if (value) {
                var classj = 'class="cleft"';
                html += '<tr id="line_import_' + index + '">';
                if (isError) {
                    html += '<td class="cright">' + (parseInt(index) + 1) + '</td>';
                }else{
                	html += '';
                }
                for (i = 0; i < 26; i++) {
                	if(i == 3 || i == 10 || i == 12 || i == 13 || (i > 14 && i < 21) || (i > 21 && i < 26) || i == 11){
//                    if (i == 3 || (12 < i && i < 26) || i == 14 || i == 11) {
                        classj = 'class="cright"';
                    }else{
                    	classj = 'class="cleft"';
                    }
                    html += '<td ' + classj + '>' + IMPORT_PUPIL.escapeHtml(value[i]) + '</td>';

                }
                html += '</tr>';
            }
        });
        return html;
    },
    getErrorImportPupil: function (dataError) {
        var html = '<li><p>取込時のチェックでエラーとなったレコードとエラー内容を表示します。ファイル内容を修正して再度アップロードを行なってください。</p></li>';
        $.each(dataError, function (index, listError) {
            html += '<li>';
            html += '<div class="import-error-line"><strong>' + (parseInt(index) + 1) + '行目</strong></div>';
            html += '<div class="import-error-title">';
            html += '<ul>';
            for (var i in listError) {
                var fieldError = listError[i].field
                html += '<li><strong>' + fieldError.replace('（*）', '') + '</strong> : ' + listError[i].title + '</li>';
            }
            html += '</ul>';
            html += '</div>';
            html += '<div style="clear: both;"></div>';
            html += '</li>';
        });
        return html;
    },
    onclickform: function () {
        $('#upload-form').submit();
    },
    onclickforms: function () {
        $('#loadingModal').modal('show');
        $('#upoadfiles').submit();
        $('a[onclick]').removeAttr('onclick');
    },
    escapeHtml: function (str) {
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    },
    escapeHtmls: function (unsafe) {
        return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
    },
    addError: function () {
        $('.displaynone').hide();
        $('.importsave').css('display', 'none');
        $('#filename').addClass('error');
    },
    destroyToolTip: function () {
        $(document).tooltip({
            items: ".importstd-list-table tbody tr",
            disabled: true
        });
    },
    bindToolTip: function (errorObjectsList) {
        $(document).tooltip({
            items: ".importstd-list-table tbody tr",
            content: function () {
                var id = $(this).attr('id');
                var errorArray = errorObjectsList[id];
                var title = "<ul class=\"error-tooltip\">";
                $.each(errorArray, function (key, val) {
                    title += "<li>" + val + "</li>"
                });
                title += "</ul>";
                return title;
            },
            track: true,
            disabled: false
        });
    }
};
var ADD_PUPIL = {
};
var EDIT_PUPIL = {
};
$(document).ready(function () {
    $("#csvfilefocus").focus();
    $("#csvfilefocus").keydown(function (e) {
        var code = e.keyCode || e.which;
        if (code == 13) {
            $("#csvfile").trigger('click');
        }
    });
    $("#csvfilefocus").keyup(function (e) {

        var code = e.keyCode || e.which;
        if (e.keyCode == 9 && e.shiftKey) {
            $("#csvfilefocus").focus();
        }
        if (e.keyCode == 9 && e.shiftKey != true) {
            $("#upload-btn").focus();
        }
    });

    $("#upload-btn").keyup(function (e) {

        var code = e.keyCode || e.which;
        if (e.keyCode == 9 && e.shiftKey) {
            var classBtn = $(this).attr("class");
            var arayClass = classBtn.split(" ");
            if ($.inArray("importstd-button-space", arayClass) == true) {
                $("#csvfile").focus();
            }
        }
        if (e.keyCode == 9 && e.shiftKey != true) {
            $("#upload-btn").focus();
        }
    });
    if ($(".importstd-list").height() < 200) {
        $('.importstd-list').css('overflow-y', 'show');
    }
    $("#csvfile").on('change', function () {
        var value = $(this).val();
        var arr = value.split("\\fakepath\\");
        if (arr[1]) {
            $("#filename").val(arr[1]);
        } else {
            $("#filename").val($(this).val());
        }
    });
    var progressInterval;
    $('#upload-form').on('submit', function (e) {
        $('#filename').removeClass('error');
        var check = IMPORT_PUPIL.loadErrorFile();
        if (check == false) {
            IMPORT_PUPIL.addError();
            return false;
        }
        e.preventDefault();
        $(this).ajaxSubmit({
            dataType: 'json',
            success: function (response) {
                if (response.status == 1) {
                    $('.displaynone').show();
                    $('.importsave').css('display', 'inline-block');
                    $('.error2').remove();
                    $('.error li').remove();
                    if (response.error) {
                        $('.std_list .error').show();
                        $('.removebotom').hide();
                        //$('.importstd-list-table').width('3000px');
                        //$('.error').append(IMPORT_PUPIL.getEror(response.listerror));
                        $('.error').append(IMPORT_PUPIL.getErrorImportPupil(response.error));
                        $('.removeclass').hide();
                    }
                    if (response.data) {
                        ERROR_MESSAGE.clear();

                        if (response.error) {
                        	$('.rightleftLine').show();
                            var flag = true;
                        } else {
                            $('.rightleftLine').hide();
                            $('.error').hide();
                            //$('.importstd-list-table').width('3000px');
                            //$('.removeclass').show();
                            //$('.removebotom').show();
                            $('#scrfile').val(response.scrfile);
                            var flag = false;
                        }
                        //$('#load_dt_std').html(IMPORT_PUPIL.getFiel(response.data, flag));
                        $('#load_dt_std').html(IMPORT_PUPIL.getDataImportPupil(response.data, flag));
                        if (flag) {
                            //IMPORT_PUPIL.bindToolTip(response.error);
                        } else {
                            IMPORT_PUPIL.destroyToolTip();
                        }
                    }
                    else {
                        $('#scrfile').val(response.scrfile);
                    }
                }
                else if (response.status == 2)
                {
                    IMPORT_PUPIL.addError();
                    ERROR_MESSAGE.show([{id: 'filename', message: $masagepupil.MSG22}], function () {
                    }, 'inline');
                }
                else if (response.status == 3)
                {
                    IMPORT_PUPIL.addError();
                    ERROR_MESSAGE.show([{id: 'filename', message: $masagepupil.MSG23}], function () {
                    }, 'inline');
                }
                else if (response.status == 4)
                {
                    IMPORT_PUPIL.addError();
                    ERROR_MESSAGE.show([{id: 'filename', message: $masagepupil.MSNOR}], function () {
                    }, 'inline');
                }
            },
            error: function (a, b, c) {

            }
        });
    });

});