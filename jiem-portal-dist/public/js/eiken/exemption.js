var EXEMPTION = {
    init: function () {
        $(document).on('click', '#btnExport', function () {
            if (JSON.parse(dataShow).length > 0) {
                $('#frm-export-exemption').submit();
            } else {
                ALERT_MESSAGE.show('エクスポートするデータがありません。');
            }
        });
    },
    showDataImportPaging: function (currentPage) {
        var year = $('#year').val();
        var kai = $("#kai").val();
        var eikenid = $("#eikenId").val();
        var name = $("#name").val();
        $.ajax({
            method: "POST",
            url: '/eiken/exemption/show-data-paging',
            dataType: 'json',
            data: {
                currentPage: currentPage,
                dataAPI: dataAPI,
                year: year,
                kai: kai,
                eikenid: eikenid,
                name: name
            },
            success: function (response) {
                var response_html = response.content.replace(/td>\s+<td/g, 'td><td');
                response_html = response_html.replace(/th>\s+<th/g, 'th><th');
                response_html = response_html.replace(/tr>\s+<td/g, 'tr><td');
                response_html = response_html.replace(/tr>\s+<th/g, 'tr><th');
                $('#showData').html(response_html);
            },
            error: function () {
            }
        });
    }
}

